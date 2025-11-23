<?php
/**
 * Configuration File
 * Database connection and session management
 */

// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'webtech_2025A_eyram_awoye');
define('DB_USER', 'root');  // Default XAMPP user
define('DB_PASS', '');  // Default XAMPP password (empty)

/**
 * Get Database Connection using PDO
 */
function getDatabaseConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>
