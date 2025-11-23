<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
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
        'message' => $message,
        'type' => $type // 'success' or 'error'
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
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}
function calculateAttendancePercentage($present, $total) {
    if ($total == 0) return 0;
    return round(($present / $total) * 100, 1);
}
function hasPermission($required_role) {
    if (!isLoggedIn()) return false;
    $user_role = $_SESSION['role'];

    if ($user_role === 'admin') return true;

    return $user_role === $required_role;
}
function redirect($page, $message = null, $type = 'success') {
    if ($message) {
        setFlashMessage($type, $message);
    }
    header("Location: $page");
    exit();
}
function logActivity($action, $details = '') {
    $user_id = $_SESSION['user_id'] ?? 'Guest';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] User $user_id: $action - $details\n";
}
?>