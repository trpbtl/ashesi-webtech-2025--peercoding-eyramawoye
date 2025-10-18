<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', '127.0.0.1');                  // or 'localhost'
define('DB_USER', 'eyram.awoye');                // ← use exact MySQL username
define('DB_PASS', 'Eyramawo@1234%');             // ← same password used in phpMyAdmin
define('DB_NAME', 'webtech_2025A_eyram_awoye');  // ← your database name

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("aww, connection failed: " . $conn->connect_error);
}

echo "Connection successful! Congratulations, you have risen above the others!";
?>
