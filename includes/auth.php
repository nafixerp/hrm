<?php
/**
 * Authentication Functions
 * HRMS - Human Resource Management System
 */

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/login.php');
    }
}

/**
 * Require role
 */
function requireRole($role) {
    requireLogin();
    if (!hasPermission($_SESSION['user_role'], $role)) {
        setFlashMessage('error', 'You do not have permission to access this page');
        redirect(APP_URL . '/dashboard.php');
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT u.*, s.staff_id, s.first_name, s.last_name, s.photo_path
            FROM users u
            LEFT JOIN staff s ON u.user_id = s.user_id
            WHERE u.user_id = ?
        ");
        $stmt->execute([getCurrentUserId()]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Login user
 */
function loginUser($username, $password) {
    try {
        $db = getDB();

        // Get user by username or email
        $stmt = $db->prepare("
            SELECT * FROM users
            WHERE (username = ? OR email = ?) AND is_active = 1
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Verify password
        if (!verifyPassword($password, $user['password_hash'])) {
            // Log failed attempt
            logAudit($user['user_id'], 'LOGIN_FAILED', 'users', $user['user_id']);
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Check if user is active
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Your account has been deactivated'];
        }

        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();

        // Update last login
        $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $updateStmt->execute([$user['user_id']]);

        // Log successful login
        logAudit($user['user_id'], 'LOGIN_SUCCESS', 'users', $user['user_id']);

        return ['success' => true, 'message' => 'Login successful', 'user' => $user];

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred during login'];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    if (isLoggedIn()) {
        logAudit(getCurrentUserId(), 'LOGOUT', 'users', getCurrentUserId());
    }

    // Destroy session
    session_unset();
    session_destroy();

    // Redirect to login
    redirect(APP_URL . '/login.php');
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isLoggedIn()) {
        $loginTime = $_SESSION['login_time'] ?? 0;
        $currentTime = time();

        if (($currentTime - $loginTime) > SESSION_TIMEOUT) {
            setFlashMessage('warning', 'Your session has expired. Please login again.');
            logoutUser();
        }

        // Update login time on activity
        $_SESSION['login_time'] = $currentTime;
    }
}

/**
 * Register new user
 */
function registerUser($username, $email, $password, $role = 'Staff') {
    try {
        $db = getDB();

        // Check if username exists
        $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        // Check if email exists
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Validate password strength
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }

        // Hash password
        $passwordHash = hashPassword($password);

        // Insert user
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, role, is_active)
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([$username, $email, $passwordHash, $role]);
        $userId = $db->lastInsertId();

        // Log user registration
        logAudit($userId, 'USER_REGISTERED', 'users', $userId);

        return ['success' => true, 'message' => 'User registered successfully', 'user_id' => $userId];

    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred during registration'];
    }
}

/**
 * Change password
 */
function changePassword($userId, $oldPassword, $newPassword) {
    try {
        $db = getDB();

        // Get current password hash
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Verify old password
        if (!verifyPassword($oldPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // Validate new password
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }

        // Hash new password
        $newPasswordHash = hashPassword($newPassword);

        // Update password
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->execute([$newPasswordHash, $userId]);

        // Log password change
        logAudit($userId, 'PASSWORD_CHANGED', 'users', $userId);

        return ['success' => true, 'message' => 'Password changed successfully'];

    } catch (Exception $e) {
        error_log("Password change error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while changing password'];
    }
}

/**
 * Reset password
 */
function resetPassword($userId, $newPassword) {
    try {
        $db = getDB();

        // Validate password
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }

        // Hash password
        $passwordHash = hashPassword($newPassword);

        // Update password
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->execute([$passwordHash, $userId]);

        // Log password reset
        logAudit($userId, 'PASSWORD_RESET', 'users', $userId);

        return ['success' => true, 'message' => 'Password reset successfully'];

    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while resetting password'];
    }
}

/**
 * Activate/Deactivate user
 */
function toggleUserStatus($userId, $isActive) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE users SET is_active = ? WHERE user_id = ?");
        $stmt->execute([$isActive, $userId]);

        $action = $isActive ? 'USER_ACTIVATED' : 'USER_DEACTIVATED';
        logAudit(getCurrentUserId(), $action, 'users', $userId);

        return ['success' => true, 'message' => 'User status updated successfully'];

    } catch (Exception $e) {
        error_log("User status toggle error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while updating user status'];
    }
}

/**
 * Get all users
 */
function getAllUsers($filters = []) {
    try {
        $db = getDB();
        $sql = "
            SELECT u.*, s.employee_code,
                   CONCAT(s.first_name, ' ', s.last_name) as full_name
            FROM users u
            LEFT JOIN staff s ON u.user_id = s.user_id
            WHERE 1=1
        ";

        $params = [];

        if (isset($filters['role']) && !empty($filters['role'])) {
            $sql .= " AND u.role = ?";
            $params[] = $filters['role'];
        }

        if (isset($filters['is_active'])) {
            $sql .= " AND u.is_active = ?";
            $params[] = $filters['is_active'];
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();

    } catch (Exception $e) {
        error_log("Get users error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user by ID
 */
function getUserById($userId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT u.*, s.staff_id, s.employee_code,
                   CONCAT(s.first_name, ' ', s.last_name) as full_name,
                   s.photo_path
            FROM users u
            LEFT JOIN staff s ON u.user_id = s.user_id
            WHERE u.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}

// Check session timeout on every request
if (isLoggedIn()) {
    checkSessionTimeout();
}
?>
