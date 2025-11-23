<?php





require_once 'config.php';
require_once 'helpers.php';





if ($_SERVER['REQUEST_METHOD'] !== 'POST') {


    header("Location: register.php");
    exit();
}





if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {

    header("Location: register.php?error=csrf_invalid");
    exit();
}







$name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';

$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
// Get the password (don't sanitize passwords, we'll hash them)
$password = isset($_POST['password']) ? $_POST['password'] : '';

$confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

$role = isset($_POST['role']) ? sanitizeInput($_POST['role']) : '';

$ashesiId = isset($_POST['ashesi_id']) ? sanitizeInput($_POST['ashesi_id']) : '';




if (empty($name) || empty($email) || empty($password) || empty($confirmPassword) || empty($role) || empty($ashesiId)) {
    // Build URL with error message and preserve user's input
    $redirectUrl = "register.php?error=empty_fields";
    $redirectUrl .= "&name=" . urlencode($name);
    $redirectUrl .= "&email=" . urlencode($email);
    $redirectUrl .= "&role=" . urlencode($role);
    $redirectUrl .= "&ashesi_id=" . urlencode($ashesiId);
    header("Location: " . $redirectUrl);
    exit();
}





if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=invalid_email&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}



if ($password !== $confirmPassword) {
    header("Location: register.php?error=passwords_mismatch&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}








if (strlen($password) < 8) {
    header("Location: register.php?error=weak_password&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}

$hasUpperCase = preg_match('/[A-Z]/', $password);
$hasLowerCase = preg_match('/[a-z]/', $password);
$hasNumbers = preg_match('/[0-9]/', $password);
if (!$hasUpperCase || !$hasLowerCase || !$hasNumbers) {
    header("Location: register.php?error=weak_password&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}



if ($role !== 'student' && $role !== 'faculty') {
    header("Location: register.php?error=invalid_role&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}




try {



    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");

    // PDO::PARAM_STR means it's a string
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);

    $stmt->execute();


    $emailCount = $stmt->fetchColumn();

    if ($emailCount > 0) {
        header("Location: register.php?error=email_exists&name=" . urlencode($name) . "&email=" . urlencode($email));
        exit();
    }
} catch (PDOException $e) {
    // If there's a database error, log it and show generic message
    error_log("Database error: " . $e->getMessage());
    header("Location: register.php?error=system_error");
    exit();
}



try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE ashesi_id = :ashesi_id");
    $stmt->bindParam(':ashesi_id', $ashesiId, PDO::PARAM_STR);
    $stmt->execute();
    $idCount = $stmt->fetchColumn();
    if ($idCount > 0) {
        header("Location: register.php?error=ashesi_id_exists&name=" . urlencode($name) . "&email=" . urlencode($email));
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: register.php?error=system_error");
    exit();
}






// Even if hackers get the database, they can't read passwords
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);



try {


    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role, ashesi_id, created_at) 
        VALUES (:name, :email, :password, :role, :ashesi_id, NOW())
    ");


    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(':role', $role, PDO::PARAM_STR);
    $stmt->bindParam(':ashesi_id', $ashesiId, PDO::PARAM_STR);

    $stmt->execute();




    setFlashMessage('success', 'Account created successfully! Please log in.');

    header("Location: index.php");
    exit();
} catch (PDOException $e) {

    error_log("Registration error: " . $e->getMessage());
    header("Location: register.php?error=system_error&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}
?>