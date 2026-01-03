<?php
/**
 * Configuration File
 * IMPORTANT: Edit all values marked with "CHANGE THIS"
 */

// Prevent direct access
if (!defined('TERMINKALENDER')) {
    die('Direct access not permitted');
}

// Database Configuration
define('DB_HOST', 'mysqlsvr75.world4you.com');
define('DB_NAME', '5355254db2');
define('DB_USER', 'sql3456525');
define('DB_PASS', 'vyh2r+*5');

// SMTP Configuration for email sending
define('SMTP_HOST', 'smtp.world4you.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'gernot.winter@magazintraining.com');
define('SMTP_PASSWORD', 'lriw3xna');
define('SMTP_FROM', 'gernot.winter@magazintraining.com');
define('SMTP_FROM_NAME', 'Terminkalender DDr. Fabian Winter');
define('SMTP_TO', 'ordination@winter.wien');

// Timezone
date_default_timezone_set('Europe/Vienna');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
session_name('TERMINKALENDER_SESSION');

// Security Settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// Booking Settings
define('MONTHS_IN_ADVANCE', 6);
define('DELETE_OLD_APPOINTMENTS_DAYS', 30); // Delete appointments older than 30 days

// Colors and Branding
define('BRAND_COLOR', '#227ac1');
define('BRAND_NAME', 'DDr. Fabian Winter');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
?>
