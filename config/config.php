<?php
/**
 * Task Reminder - Database Configuration
 * Contains only database connection settings
 */

date_default_timezone_set('Asia/Karachi');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'task_reminder');
define('DB_CHARSET', 'utf8mb4');

/**
 * Create database connection
 * @return PDO|false Returns PDO connection or false on failure
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Test database connection
 * @return bool Returns true if connection successful
 */
function testDBConnection() {
    $conn = getDBConnection();
    if ($conn) {
        return true;
    }
    return false;
}

// Initialize connection (optional - can be called when needed)
// $db = getDBConnection();

