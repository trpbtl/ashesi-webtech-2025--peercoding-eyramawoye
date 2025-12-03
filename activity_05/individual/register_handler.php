<?php
require_once 'config.php';
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    header("Location: register.php?error=csrf_invalid");
    exit();
}

$name = sanitizeInput($_POST['name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$role = sanitizeInput($_POST['role'] ?? '');
$ashesiId = sanitizeInput($_POST['ashesi_id'] ?? '');

if (empty($name) || empty($email) || empty($password) || empty($confirmPassword) || empty($role) || empty($ashesiId)) {
    $redirectUrl = "register.php?error=empty_fields&name=" . urlencode($name) . "&email=" . urlencode($email) . "&role=" . urlencode($role) . "&ashesi_id=" . urlencode($ashesiId);
    header("Location: " . $redirectUrl);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=invalid_email&name=" . urlencode($name));
    exit();
}

if ($password !== $confirmPassword) {
    header("Location: register.php?error=passwords_mismatch&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}

if (!isStrongPassword($password)) {
    header("Location: register.php?error=weak_password&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}

if ($role !== 'student' && $role !== 'faculty') {
    header("Location: register.php?error=invalid_role");
    exit();
}

try {
    $pdo = getDatabaseConnection();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: register.php?error=email_exists&name=" . urlencode($name));
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE ashesi_id = :ashesi_id");
    $stmt->execute(['ashesi_id' => $ashesiId]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: register.php?error=ashesi_id_exists&name=" . urlencode($name) . "&email=" . urlencode($email));
        exit();
    }
    
    $hashedPassword = hashPassword($password);
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, ashesi_id) VALUES (:name, :email, :password, :role, :ashesi_id)");
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
        'role' => $role,
        'ashesi_id' => $ashesiId
    ]);
    
    setFlashMessage('success', 'Account created successfully! Please log in.');
    header("Location: index.php");
    exit();
    
} catch (PDOException $e) {
    header("Location: register.php?error=system_error");
    exit();
}
?>
