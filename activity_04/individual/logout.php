<?php
require_once 'config.php';
require_once 'helpers.php';

$_SESSION = array();

session_destroy();

session_start();

setFlashMessage('success', 'You have been logged out successfully.');
header('Location: index.php');
exit();
?>
