<?php
/**
 * Helper Functions
 * HRMS - Human Resource Management System
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Format date for display
 */
function formatDate($date, $format = DATE_FORMAT) {
    if (empty($date) || $date == '0000-00-00') {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = DATETIME_FORMAT) {
    if (empty($datetime) || $datetime == '0000-00-00 00:00:00') {
        return '-';
    }
    return date($format, strtotime($datetime));
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, 2);
}

/**
 * Format number
 */
function formatNumber($number, $decimals = 2) {
    return number_format($number, $decimals);
}

/**
 * Get user IP address
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

/**
 * Get flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'];
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        return ['type' => $type, 'message' => $message];
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = '';
        switch ($flash['type']) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
            case 'info':
                $alertClass = 'alert-info';
                break;
            default:
                $alertClass = 'alert-info';
        }
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($flash['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

/**
 * Upload file
 */
function uploadFile($file, $targetDir, $allowedTypes, $maxSize = MAX_FILE_SIZE) {
    $errors = [];

    // Check if file was uploaded
    if (!isset($file) || $file['error'] == UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error code: ' . $file['error']];
    }

    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size exceeds maximum allowed size'];
    }

    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $filename;

    // Create directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $targetPath];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }
}

/**
 * Delete file
 */
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

/**
 * Log audit trail
 */
function logAudit($userId, $actionType, $tableName, $recordId, $oldValues = null, $newValues = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO audit_logs (user_id, action_type, table_name, record_id, old_values, new_values, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
        $newValuesJson = $newValues ? json_encode($newValues) : null;
        $ipAddress = getUserIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt->execute([$userId, $actionType, $tableName, $recordId, $oldValuesJson, $newValuesJson, $ipAddress, $userAgent]);
        return true;
    } catch (Exception $e) {
        error_log("Audit log failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate employee code
 */
function generateEmployeeCode() {
    $db = getDB();
    $stmt = $db->query("SELECT employee_code FROM staff ORDER BY staff_id DESC LIMIT 1");
    $lastEmployee = $stmt->fetch();

    if ($lastEmployee) {
        $lastCode = $lastEmployee['employee_code'];
        $number = intval(substr($lastCode, 3)) + 1;
        return 'EMP' . str_pad($number, 5, '0', STR_PAD_LEFT);
    } else {
        return 'EMP00001';
    }
}

/**
 * Generate transaction code
 */
function generateTransactionCode() {
    $db = getDB();
    $prefix = 'TXN' . date('Ymd');

    $stmt = $db->prepare("SELECT transaction_code FROM sales_transactions WHERE transaction_code LIKE ? ORDER BY transaction_id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $lastTransaction = $stmt->fetch();

    if ($lastTransaction) {
        $lastCode = $lastTransaction['transaction_code'];
        $number = intval(substr($lastCode, -4)) + 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    } else {
        return $prefix . '0001';
    }
}

/**
 * Calculate working days between dates
 */
function calculateWorkingDays($fromDate, $toDate) {
    $workingDays = 0;
    $currentDate = strtotime($fromDate);
    $endDate = strtotime($toDate);

    while ($currentDate <= $endDate) {
        $dayOfWeek = date('N', $currentDate);
        if ($dayOfWeek < 7) { // Monday to Saturday
            $workingDays++;
        }
        $currentDate = strtotime('+1 day', $currentDate);
    }

    return $workingDays;
}

/**
 * Get system setting
 */
function getSetting($key, $default = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();

        if ($result) {
            return $result['setting_value'];
        }
        return $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Update system setting
 */
function updateSetting($key, $value, $userId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE system_settings
            SET setting_value = ?, updated_by = ?, updated_at = NOW()
            WHERE setting_key = ?
        ");
        $stmt->execute([$value, $userId, $key]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Check if user has permission
 */
function hasPermission($role, $requiredRole) {
    $hierarchy = ['Admin' => 3, 'Manager' => 2, 'Staff' => 1];
    return $hierarchy[$role] >= $hierarchy[$requiredRole];
}

/**
 * Get month name
 */
function getMonthName($month) {
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    return $months[$month] ?? '';
}

/**
 * Get financial year
 */
function getFinancialYear($date = null) {
    $date = $date ? strtotime($date) : time();
    $year = date('Y', $date);
    $month = date('n', $date);

    if ($month >= 4) {
        return $year . '-' . ($year + 1);
    } else {
        return ($year - 1) . '-' . $year;
    }
}

/**
 * Paginate array
 */
function paginate($items, $page = 1, $perPage = RECORDS_PER_PAGE) {
    $total = count($items);
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    return [
        'items' => array_slice($items, $offset, $perPage),
        'total' => $total,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'per_page' => $perPage
    ];
}

/**
 * Validate required fields
 */
function validateRequired($data, $requiredFields) {
    $errors = [];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    return $errors;
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Debug print (only in development)
 */
function debug($var) {
    if (error_reporting() !== 0) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
}
?>
