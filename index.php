<?php
/**
 * Main Entry Point
 * HRMS - Human Resource Management System
 */

require_once 'config/config.php';

// Check if database is set up
if (!checkDatabaseSetup()) {
    redirect(APP_URL . '/setup.php');
}

// Redirect based on login status
if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard.php');
} else {
    redirect(APP_URL . '/login.php');
}
?>
