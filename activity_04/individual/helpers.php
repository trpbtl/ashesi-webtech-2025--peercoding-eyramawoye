<?php
/**
 * Helper Functions
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('index.php', 'Please log in to access this page', 'error');
    }
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        redirect('dashboard.php', 'Access denied', 'error');
    }
}

function redirectToDashboard() {
    if (!isset($_SESSION['role'])) {
        header('Location: logout.php');
        exit();
    }
    
    header('Location: dashboard.php');
    exit();
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function isStrongPassword($password) {
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $length = strlen($password) >= 8;
    
    return $uppercase && $lowercase && $number && $length;
}

function redirect($page, $message = null, $type = 'success') {
    if ($message) {
        setFlashMessage($type, $message);
    }
    header("Location: $page");
    exit();
}

function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>Pending</span>',
        'approved' => '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Approved</span>',
        'rejected' => '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800"><i class="fas fa-times-circle mr-1"></i>Rejected</span>'
    ];
    return $badges[$status] ?? '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">Unknown</span>';
}
?>
