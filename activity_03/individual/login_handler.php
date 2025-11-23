<?php
require_once 'config.php';
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php', 'Invalid request method', 'error');
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$csrf_token = $_POST['csrf_token'] ?? '';

if (empty($email) || empty($password)) {
    redirect('index.php', 'Please fill in all fields', 'error');
}

if (!verifyCSRFToken($csrf_token)) {


    redirect('index.php', 'Invalid security token. Please try again.', 'error');
}

if (!isValidEmail($email)) {
    redirect('index.php', 'Invalid email format', 'error');
}

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("SELECT user_id, name, email, password, role, ashesi_id FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    redirect('index.php', 'Invalid email or password', 'error');
}

if (!verifyPassword($password, $user['password'])) {
    redirect('index.php', 'Invalid email or password', 'error');
}

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['ashesi_id'] = $user['ashesi_id'];

$updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :user_id");
$updateStmt->execute(['user_id' => $user['user_id']]);

session_regenerate_id(true);

redirect('dashboard.php', 'Welcome back, ' . $user['name'] . '!', 'success');
?>