<?php
/**
 * Logout Handler
 * Destroys session and redirects to login page
 */

require_once 'config.php';
require_once 'helpers.php';

// Debug CSRF (remove after testing)
if (!verifyCSRFToken($csrf_token)) {
    echo "Session Token: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "<br>";
    echo "Form Token: " . $csrf_token . "<br>";
    exit();
}

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page with message
header('Location: index.php');
exit();
?>
