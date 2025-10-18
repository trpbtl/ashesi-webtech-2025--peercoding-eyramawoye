<?php
// helpers.php — reusable utility functions for the app

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect logged-in users to their dashboard based on role
function redirectToDashboard() {
    if (!isset($_SESSION['role'])) {
        header("Location: login.php");
        exit();
    }

    switch ($_SESSION['role']) {
        case 'student':
            header("Location: student_dashboard.php");
            break;
        case 'faculty':
            header("Location: faculty_dashboard.php");
            break;
        case 'admin':
            header("Location: admin_dashboard.php");
            break;
        default:
            header("Location: login.php");
    }
    exit();
}

// Flash message system for one-time messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $msg = $_SESSION['flash'];
        unset($_SESSION['flash']); // remove after showing once
        return $msg;
    }
    return null;
}

// Generate a CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token — renamed to match login_handler.php
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
