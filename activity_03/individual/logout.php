<?php
require_once 'config.php';
require_once 'helpers.php';

if (!verifyCSRFToken($csrf_token)) {
    echo "Session Token: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "<br>";
    echo "Form Token: " . $csrf_token . "<br>";
    exit();
}

$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

header('Location: index.php');
exit();
?>