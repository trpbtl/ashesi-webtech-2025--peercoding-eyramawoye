<?php
/**
 * Login Handler
 * 
 * This file processes the login form submission.
 * It validates credentials, starts a session, and redirects users.
 * 
 * SECURITY MEASURES:
 * 1. CSRF token validation
 * 2. Prepared statements (SQL injection prevention)
 * 3. Password verification (bcrypt)
 * 4. Input sanitization
 * 5. Session hijacking prevention
 * 
 * Flow:
 * 1. Receive POST data from login form
 * 2. Validate CSRF token
 * 3. Check if fields are filled
 * 4. Query database for user
 * 5. Verify password
 * 6. Create session variables
 * 7. Redirect to dashboard
 */

// Include configuration file
require_once 'config.php';
require_once 'helpers.php';

// This file should only be accessed via POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// ========================================
// Step 1: Validate CSRF Token
// ========================================
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    // Invalid token - possible CSRF attack
    header("Location: index.php?error=csrf_invalid");
    exit();
}

// ========================================
// Step 2: Get and Sanitize Input Data
// ========================================
$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : ''; // Don't sanitize password (it's hashed)
$remember = isset($_POST['remember']);

// ========================================
// Step 3: Validate Input
// ========================================
// Check if fields are not empty
if (empty($email) || empty($password)) {
    header("Location: index.php?error=empty_fields&email=" . urlencode($email));
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.php?error=invalid_email&email=" . urlencode($email));
    exit();
}

// ========================================
// Step 4: Query Database for User
// ========================================
try {
    // Prepare SQL statement to find user by email
    // We use prepared statements to prevent SQL injection
    $stmt = $pdo->prepare("
        SELECT user_id, name, email, password, role, ashesi_id 
        FROM users 
        WHERE email = :email
        LIMIT 1
    ");
    
    // Bind parameters (: email is a placeholder)
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    
    // Execute the query
    $stmt->execute();
    
    // Fetch the user record
    $user = $stmt->fetch();
    
    // ========================================
    // Step 5: Verify User Exists and Password is Correct
    // ========================================
    if ($user && password_verify($password, $user['password'])) {
        // Password is correct! Proceed with login
        
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);
        
        // ========================================
        // Step 6: Store User Information in Session
        // ========================================
        // Session variables persist across pages until logout
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['ashesi_id'] = $user['ashesi_id'];
        $_SESSION['logged_in_at'] = time(); // Timestamp for session timeout
        
        // ========================================
        // Step 7: Update Last Login Time in Database
        // ========================================
        $updateStmt = $pdo->prepare("
            UPDATE users 
            SET last_login = NOW() 
            WHERE user_id = :user_id
        ");
        $updateStmt->bindParam(':user_id', $user['user_id'], PDO::PARAM_INT);
        $updateStmt->execute();
        
        // ========================================
        // Step 8: Handle "Remember Me" Functionality
        // ========================================
        if ($remember) {
            // Set a cookie that lasts for 30 days
            // In a real application, you'd use a secure token stored in database
            setcookie('remember_user', $user['user_id'], time() + (30 * 24 * 60 * 60), '/', '', true, true);
            // Parameters: name, value, expiry, path, domain, secure, httponly
        }
        
        // ========================================
        // Step 9: Set Success Message and Redirect
        // ========================================
        setFlashMessage('success', 'Welcome back, ' . $user['name'] . '!');
        
        // Redirect based on user role
        if ($user['role'] === 'student') {
            header("Location: student_dashboard.php");
        } elseif ($user['role'] === 'faculty') {
            header("Location: faculty_dashboard.php");
        } else {
            // Default redirect (for admin or other roles)
            header("Location: student_dashboard.php");
        }
        exit();
        
    } else {
        // ========================================
        // Login Failed - Invalid Credentials
        // ========================================
        
        // Don't specify whether email or password was wrong (security best practice)
        header("Location: index.php?error=invalid_credentials&email=" . urlencode($email));
        exit();
    }
    
} catch (PDOException $e) {
    // ========================================
    // Database Error Handling
    // ========================================
    
    // Log the error (in production, use error_log() instead of displaying)
    error_log("Login error: " . $e->getMessage());
    
    // Show generic error to user (don't expose database details)
    setFlashMessage('error', 'A system error occurred. Please try again later.');
    header("Location: index.php");
    exit();
}

/**
 * EXPLANATION OF KEY CONCEPTS:
 * 
 * 1. PREPARED STATEMENTS:
 *    - Use placeholders (:email) instead of direct variable insertion
 *    - PDO automatically escapes special characters
 *    - Prevents SQL injection attacks
 * 
 * 2. PASSWORD VERIFICATION:
 *    - password_verify() compares plain text with hashed password
 *    - Uses bcrypt algorithm (very secure)
 *    - Can't reverse-engineer the original password
 * 
 * 3. SESSION MANAGEMENT:
 *    - $_SESSION array stores data across pages
 *    - session_regenerate_id() prevents session fixation
 *    - Sessions are stored server-side (secure)
 * 
 * 4. CSRF PROTECTION:
 *    - Token generated on form page
 *    - Verified on submission
 *    - Prevents unauthorized form submissions from other sites
 * 
 * 5. ERROR HANDLING:
 *    - Try-catch blocks capture database errors
 *    - Don't expose sensitive information to users
 *    - Log errors for debugging
 */
?>
