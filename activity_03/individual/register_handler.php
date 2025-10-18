<?php
/**
 * ============================================
 * REGISTER HANDLER - BEGINNER FRIENDLY VERSION
 * ============================================
 * 
 * WHAT THIS FILE DOES:
 * This file receives data from the registration form (register.php)
 * and creates a new user account in the database.
 * 
 * STEPS IT FOLLOWS:
 * 1. Check if the form was submitted properly
 * 2. Validate the security token (CSRF protection)
 * 3. Get all the data the user entered
 * 4. Check if the data is valid (not empty, correct format)
 * 5. Check if email or ID already exists in database
 * 6. Hash the password (encrypt it for security)
 * 7. Insert the new user into the database
 * 8. Redirect to login page with success message
 */

// ============================================
// STEP 1: Include the config file
// ============================================
// This gives us access to the database connection ($pdo)
// and helper functions we created
require_once 'config.php';
require_once 'helpers.php';


// ============================================
// STEP 2: Check if form was submitted with POST method
// ============================================
// Forms should use POST method (not GET) for security
// GET shows data in URL, POST hides it
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If someone tries to access this page directly (not from form)
    // redirect them back to registration page
    header("Location: register.php");
    exit(); // Always use exit() after header() to stop script
}

// ============================================
// STEP 3: Validate CSRF Token (Security Check)
// ============================================
// CSRF = Cross-Site Request Forgery
// This prevents hackers from submitting forms from other websites
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    // Token is invalid - possible attack
    header("Location: register.php?error=csrf_invalid");
    exit();
}

// ============================================
// STEP 4: Get data from the form
// ============================================
// $_POST is a special PHP array that contains form data
// isset() checks if the value exists
// sanitizeInput() cleans the data to prevent XSS attacks

// Get the name
$name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';

// Get the email
$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';

// Get the password (don't sanitize passwords, we'll hash them)
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Get the confirm password
$confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Get the role (student or faculty)
$role = isset($_POST['role']) ? sanitizeInput($_POST['role']) : '';

// Get the Ashesi ID
$ashesiId = isset($_POST['ashesi_id']) ? sanitizeInput($_POST['ashesi_id']) : '';

// ============================================
// STEP 5: Validate all fields are filled
// ============================================
// empty() returns true if variable is empty
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

// ============================================
// STEP 6: Validate email format
// ============================================
// filter_var() is a PHP function that validates different types of data
// FILTER_VALIDATE_EMAIL checks if email is in correct format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=invalid_email&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}

// ============================================
// STEP 7: Check if passwords match
// ============================================
if ($password !== $confirmPassword) {
    header("Location: register.php?error=passwords_mismatch&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}

// ============================================
// STEP 8: Validate password strength
// ============================================
// Good passwords should be:
// - At least 8 characters long
// - Contain uppercase letters (A-Z)
// - Contain lowercase letters (a-z)
// - Contain numbers (0-9)

if (strlen($password) < 8) {
    header("Location: register.php?error=weak_password&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}

// preg_match() checks if a pattern exists in a string (regular expressions)
$hasUpperCase = preg_match('/[A-Z]/', $password);  // Check for uppercase
$hasLowerCase = preg_match('/[a-z]/', $password);  // Check for lowercase
$hasNumbers = preg_match('/[0-9]/', $password);    // Check for numbers

if (!$hasUpperCase || !$hasLowerCase || !$hasNumbers) {
    header("Location: register.php?error=weak_password&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}

// ============================================
// STEP 9: Validate role is either student or faculty
// ============================================
if ($role !== 'student' && $role !== 'faculty') {
    header("Location: register.php?error=invalid_role&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}

// ============================================
// STEP 10: Check if email already exists in database
// ============================================
// We use try-catch to handle any database errors
try {
    // Prepare SQL statement
    // SELECT checks if email exists
    // COUNT(*) counts how many matching records
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    
    // Bind the email value to the :email placeholder
    // PDO::PARAM_STR means it's a string
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    
    // Execute the query
    $stmt->execute();
    
    // Get the count result
    // fetchColumn() gets the first column of the first row
    $emailCount = $stmt->fetchColumn();
    
    // If count is greater than 0, email already exists
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

// ============================================
// STEP 11: Check if Ashesi ID already exists
// ============================================
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

// ============================================
// STEP 12: Hash the password
// ============================================
// NEVER store plain text passwords in database!
// password_hash() creates a secure encrypted version
// PASSWORD_DEFAULT uses bcrypt algorithm (very secure)
// Even if hackers get the database, they can't read passwords
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// ============================================
// STEP 13: Insert new user into database
// ============================================
try {
    // Prepare INSERT statement
    // This adds a new row to the users table
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role, ashesi_id, created_at) 
        VALUES (:name, :email, :password, :role, :ashesi_id, NOW())
    ");
    
    // Bind all the values to placeholders
    // This is like filling in the blanks in the SQL statement
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);  // Use hashed password!
    $stmt->bindParam(':role', $role, PDO::PARAM_STR);
    $stmt->bindParam(':ashesi_id', $ashesiId, PDO::PARAM_STR);
    
    // Execute the INSERT query
    $stmt->execute();
    
    // ============================================
    // STEP 14: Registration successful!
    // ============================================
    // Set a success message in the session
    setFlashMessage('success', 'Account created successfully! Please log in.');
    
    // Redirect to login page
    header("Location: index.php");
    exit();
    
} catch (PDOException $e) {
    // If insertion fails, log error
    error_log("Registration error: " . $e->getMessage());
    header("Location: register.php?error=system_error&name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
}

/**
 * ============================================
 * SUMMARY OF WHAT WE DID:
 * ============================================
 * 
 * 1. Received form data from register.php
 * 2. Checked security token (CSRF)
 * 3. Validated all fields are filled
 * 4. Validated email format
 * 5. Checked passwords match
 * 6. Checked password strength
 * 7. Made sure email doesn't already exist
 * 8. Made sure Ashesi ID doesn't already exist
 * 9. Hashed (encrypted) the password
 * 10. Inserted new user into database
 * 11. Redirected to login page with success message
 * 
 * ============================================
 * KEY PHP CONCEPTS USED:
 * ============================================
 * 
 * $_POST - Array containing form data
 * isset() - Checks if variable exists
 * empty() - Checks if variable is empty
 * header() - Redirects to another page
 * exit() - Stops script execution
 * filter_var() - Validates data format
 * preg_match() - Matches patterns (regex)
 * password_hash() - Encrypts passwords
 * PDO prepare() - Creates safe SQL statement
 * bindParam() - Adds values to SQL statement
 * execute() - Runs the SQL query
 * 
 * ============================================
 * SECURITY FEATURES:
 * ============================================
 * 
 * 1. CSRF token prevents fake form submissions
 * 2. Prepared statements prevent SQL injection
 * 3. Password hashing protects user passwords
 * 4. Input sanitization prevents XSS attacks
 * 5. Email and ID uniqueness prevents duplicates
 * 6. Password strength requirements
 */
?>
