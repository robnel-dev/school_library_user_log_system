<?php
// Application configuration
define('APP_NAME', 'Library Users Statistics');
define('APP_VERSION', '1.0.0');
define('DEFAULT_TIMEZONE', 'Asia/Manila');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'library_stats');
define('DB_USER', 'root');
define('DB_PASS', '');

// Session configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Set default timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>