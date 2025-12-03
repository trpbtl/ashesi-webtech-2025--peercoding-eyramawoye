<?php
require_once 'config.php';
require_once 'helpers.php';

requireLogin();

$role = $_SESSION['role'];

if ($role === 'faculty') {
    header('Location: faculty_dashboard.php');
} elseif ($role === 'student') {
    header('Location: student_dashboard.php');
} else {
    header('Location: logout.php');
}
exit();
?>
