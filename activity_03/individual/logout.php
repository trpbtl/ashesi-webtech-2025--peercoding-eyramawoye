<?php
/**
 * ============================================
 * LOGOUT PAGE - BEGINNER FRIENDLY
 * ============================================
 * 
 * WHAT THIS FILE DOES:
 * When a user clicks "logout", this file:
 * 1. Destroys their session (logs them out)
 * 2. Clears all session data
 * 3. Redirects them to the login page
 * 
 * WHY WE NEED THIS:
 * - Security: Prevents unauthorized access after logout
 * - Privacy: Clears user data from server
 * - Clean state: Next login starts fresh
 */

// ============================================
// Start the session
// ============================================
// We need to start the session BEFORE we can destroy it
// Think of it like opening a box before you can empty it
session_start();

// ============================================
// STEP 1: Clear all session variables
// ============================================
// $_SESSION is an array that stores user data
// Setting it to an empty array [] clears everything
$_SESSION = [];

// ============================================
// STEP 2: Destroy the session cookie
// ============================================
// Sessions use cookies to remember users
// We need to delete this cookie too

// Check if a session cookie exists
if (isset($_COOKIE[session_name()])) {
    // Get session cookie parameters
    $params = session_get_cookie_params();
    
    // Delete the cookie by setting expiration to past time
    // time() - 3600 means 1 hour ago
    setcookie(
        session_name(),           // Cookie name
        '',                       // Empty value
        time() - 3600,           // Expired time (1 hour ago)
        $params['path'],         // Cookie path
        $params['domain'],       // Cookie domain
        $params['secure'],       // Secure flag
        $params['httponly']      // HTTP only flag
    );
}

// ============================================
// STEP 3: Destroy the session completely
// ============================================
// This removes the session file from the server
session_destroy();

// ============================================
// STEP 4: Redirect to login page
// ============================================
// Send user back to login with a message
header("Location: index.php");

// Always exit after redirect
exit();

/**
 * ============================================
 * EXPLANATION:
 * ============================================
 * 
 * WHAT IS A SESSION?
 * - A way to store user data across multiple pages
 * - Stored on the server (secure)
 * - Each user has a unique session ID
 * - Session ID is stored in a cookie on user's browser
 * 
 * LOGOUT PROCESS:
 * 1. Clear session data ($_SESSION = [])
 * 2. Delete session cookie (setcookie with past time)
 * 3. Destroy session file (session_destroy())
 * 4. Redirect to login page
 * 
 * WHY THREE STEPS?
 * - Each step cleans a different part
 * - Together they ensure complete logout
 * - Prevents security issues
 * 
 * USAGE:
 * Add this link to any page where user is logged in:
 * <a href="logout.php">Logout</a>
 */
?>
