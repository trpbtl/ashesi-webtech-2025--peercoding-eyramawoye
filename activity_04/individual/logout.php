<?php
require_once 'config.php';
require_once 'helpers.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Start a new session to store the flash message
session_start();

setFlashMessage('success', 'You have been logged out successfully.');
header('Location: index.php');
exit();
?>
