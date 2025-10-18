<?php
/**
 * Helper Functions
 * 
 * This file contains reusable functions used throughout the application.
 * Include this file at the top of any page that needs these functions.
 */

/**
 * Check if user is logged in
 * Returns true if user has an active session, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirect to appropriate dashboard based on user role
 */
function redirectToDashboard() {
    if (!isset($_SESSION['role'])) {
        header('Location: logout.php');
        exit();
    }
    
    header('Location: dashboard.php');
    exit();
}

/**
 * Generate CSRF token for form security
 * Stores token in session and returns it
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from form submission
 * Returns true if valid, false otherwise
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message (success or error notifications)
 * These messages are displayed once and then cleared
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type // 'success' or 'error'
    ];
}

/**
 * Get and clear flash message
 * Returns the message array and removes it from session
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Sanitize user input to prevent XSS attacks
 * Converts special characters to HTML entities
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 * Returns true if email is valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password using bcrypt
 * Returns hashed password string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * Returns true if password matches hash, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check password strength
 * Returns true if password meets requirements, false otherwise
 * Requirements: At least 8 characters, 1 uppercase, 1 lowercase, 1 number
 */
function isStrongPassword($password) {
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $length = strlen($password) >= 8;
    
    return $uppercase && $lowercase && $number && $length;
}

/**
 * Format date for display
 * Converts database date to readable format
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Calculate attendance percentage
 * Returns percentage as float (0-100)
 */
function calculateAttendancePercentage($present, $total) {
    if ($total == 0) return 0;
    return round(($present / $total) * 100, 1);
}

/**
 * Check if user has permission for an action
 * Returns true if user role has permission, false otherwise
 */
function hasPermission($required_role) {
    if (!isLoggedIn()) return false;
    
    $user_role = $_SESSION['role'];
    
    // Admin has all permissions
    if ($user_role === 'admin') return true;
    
    // Check specific role
    return $user_role === $required_role;
}

/**
 * Redirect to page with optional message
 */
function redirect($page, $message = null, $type = 'success') {
    if ($message) {
        setFlashMessage($type, $message);
    }
    header("Location: $page");
    exit();
}

/**
 * Log activity (for debugging or audit trail)
 * In production, this could write to a file or database
 */
function logActivity($action, $details = '') {
    $user_id = $_SESSION['user_id'] ?? 'Guest';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] User $user_id: $action - $details\n";
    
    // Uncomment to enable file logging
    // file_put_contents('logs/activity.log', $log_entry, FILE_APPEND);
}
?>
