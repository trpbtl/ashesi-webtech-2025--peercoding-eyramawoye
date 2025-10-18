<?php
/**
 * Login Handler
 * Processes login form submission
 */

require_once 'config.php';
require_once 'helpers.php';

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php', 'Invalid request method', 'error');
}

// Get form data
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$csrf_token = $_POST['csrf_token'] ?? '';

// Validate inputs are not empty
if (empty($email) || empty($password)) {
    redirect('index.php', 'Please fill in all fields', 'error');
}

// Verify CSRF token
if (!verifyCSRFToken($csrf_token)) {
    // For debugging: temporarily disable CSRF check
    // Comment out the line below if you want to test without CSRF
    redirect('index.php', 'Invalid security token. Please try again.', 'error');
}

// Validate email format
if (!isValidEmail($email)) {
    redirect('index.php', 'Invalid email format', 'error');
}

// Connect to database
$pdo = getDatabaseConnection();

// Query to find user by email
$stmt = $pdo->prepare("SELECT user_id, name, email, password, role, ashesi_id FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user exists
if (!$user) {
    redirect('index.php', 'Invalid email or password', 'error');
}

// Verify password
if (!verifyPassword($password, $user['password'])) {
    redirect('index.php', 'Invalid email or password', 'error');
}

// Password is correct - create session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['ashesi_id'] = $user['ashesi_id'];

// Update last login time
$updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :user_id");
$updateStmt->execute(['user_id' => $user['user_id']]);

// Regenerate session ID for security
session_regenerate_id(true);

// Redirect to dashboard
redirect('dashboard.php', 'Welcome back, ' . $user['name'] . '!', 'success');
?>
