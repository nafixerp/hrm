<?php
/**
 * Application Configuration
 * HRMS - Human Resource Management System
 */

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Application Settings
define('APP_NAME', 'HRMS - Human Resource Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/hrm');
define('BASE_PATH', dirname(dirname(__FILE__)));

// Directory Paths
define('UPLOAD_PATH', BASE_PATH . '/assets/uploads/');
define('STAFF_PHOTO_PATH', UPLOAD_PATH . 'staff_photos/');
define('DOCUMENT_PATH', UPLOAD_PATH . 'documents/');

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes in seconds

// Pagination
define('RECORDS_PER_PAGE', 25);

// Date and Time Formats
define('DATE_FORMAT', 'd-m-Y');
define('DATE_FORMAT_SQL', 'Y-m-d');
define('DATETIME_FORMAT', 'd-m-Y H:i:s');
define('TIME_FORMAT', 'H:i:s');

// Currency
define('CURRENCY_SYMBOL', '₹');
define('CURRENCY_CODE', 'INR');

// Working Days Settings
define('WORKING_DAYS_PER_MONTH', 26);
define('WORKING_HOURS_PER_DAY', 8);
define('OVERTIME_MULTIPLIER', 1.5);
define('LATE_GRACE_MINUTES', 15);

// Biometric Settings
define('BIOMETRIC_MATCH_THRESHOLD', 75);
define('BIOMETRIC_LIVENESS_CHECK', true);
define('MAX_FAILED_AUTH_ATTEMPTS', 3);

// Leave Settings
define('CARRY_FORWARD_ENABLED', true);
define('MAX_CARRY_FORWARD_DAYS', 10);

// Email Settings (for future use)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@hrms.com');
define('SMTP_FROM_NAME', 'HRMS System');

// File Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/auth.php';

// Check if database tables are set up
function checkDatabaseSetup() {
    try {
        $db = getDB();
        $stmt = $db->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() == 0) {
            return false;
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
