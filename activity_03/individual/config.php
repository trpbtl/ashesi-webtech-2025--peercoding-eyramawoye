<?php
/**
 * Configuration File
 * Database connection and session management
 */

// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database credentials - CORRECTED!
define('DB_HOST', 'localhost');
define('DB_NAME', 'webtech_2025A_eyram_awoye');
define('DB_USER', 'webtech_2025A_eyram_awoye');  // Changed from 'eyram_awoye'
define('DB_PASS', 'your_actual_password_here');  // Put your real password here

/**
 * Get Database Connection using PDO
 * Returns PDO connection object
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

/**
 * Get Database Connection using MySQLi (alternative)
 * Returns mysqli connection object
 */
function getMysqliConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
