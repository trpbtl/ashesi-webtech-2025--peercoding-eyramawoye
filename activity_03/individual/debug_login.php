<?php
/**
 * DEBUG LOGIN FLOW
 * This file helps diagnose login issues step by step
 */

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Login Flow Debug</h1>";
echo "<hr>";

// Test 1: Check if files exist
echo "<h2>Test 1: File Check</h2>";
$files = [
    'config.php',
    'helpers.php',
    'index.php',
    'login_handler.php',
    'dashboard.php',
    'register.php',
    'register_handler.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ <span style='color:green;'>$file exists</span><br>";
    } else {
        echo "❌ <span style='color:red;'>$file MISSING!</span><br>";
    }
}

echo "<hr>";

// Test 2: Include config and helpers
echo "<h2>Test 2: Load Config & Helpers</h2>";
try {
    require_once 'config.php';
    echo "✅ <span style='color:green;'>config.php loaded</span><br>";
    
    require_once 'helpers.php';
    echo "✅ <span style='color:green;'>helpers.php loaded</span><br>";
} catch (Exception $e) {
    echo "❌ <span style='color:red;'>Error: " . $e->getMessage() . "</span><br>";
    die();
}

echo "<hr>";

// Test 3: Database Connection
echo "<h2>Test 3: Database Connection</h2>";
try {
    $pdo = getDatabaseConnection();
    echo "✅ <span style='color:green;'><strong>Database connected successfully!</strong></span><br>";
    echo "Connected to: <strong>" . DB_NAME . "</strong><br>";
} catch (Exception $e) {
    echo "❌ <span style='color:red;'><strong>Database connection FAILED!</strong></span><br>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "<br><strong>Credentials being used:</strong><br>";
    echo "Host: " . DB_HOST . "<br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "Username: " . DB_USER . "<br>";
    echo "Password: " . (DB_PASS ? "[SET]" : "[EMPTY]") . "<br>";
    die();
}

echo "<hr>";

// Test 4: Check Users Table
echo "<h2>Test 4: Check Users Table</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ <span style='color:green;'>Users table EXISTS</span><br>";
        
        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "Total users: <strong>{$result['count']}</strong><br>";
        
        if ($result['count'] > 0) {
            echo "✅ <span style='color:green;'>You have users in database</span><br>";
            
            // Show users
            echo "<h3>User List:</h3>";
            $stmt = $pdo->query("SELECT user_id, name, email, role, ashesi_id, created_at FROM users LIMIT 10");
            $users = $stmt->fetchAll();
            
            echo "<table border='1' cellpadding='8' style='border-collapse:collapse; margin:10px 0;'>";
            echo "<tr style='background:#c41e3a; color:white;'>";
            echo "<th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Ashesi ID</th><th>Created</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>{$user['user_id']}</td>";
                echo "<td>{$user['name']}</td>";
                echo "<td>{$user['email']}</td>";
                echo "<td>{$user['role']}</td>";
                echo "<td>{$user['ashesi_id']}</td>";
                echo "<td>{$user['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "⚠️ <span style='color:orange;'>No users found. Run insert_test_users.sql</span><br>";
        }
    } else {
        echo "❌ <span style='color:red;'>Users table does NOT exist!</span><br>";
        echo "Run schema.sql to create tables.<br>";
    }
} catch (Exception $e) {
    echo "❌ <span style='color:red;'>Error: " . $e->getMessage() . "</span><br>";
}

echo "<hr>";

// Test 5: Test Helper Functions
echo "<h2>Test 5: Helper Functions</h2>";
try {
    // Test isLoggedIn
    if (function_exists('isLoggedIn')) {
        echo "✅ isLoggedIn() exists<br>";
        $loggedIn = isLoggedIn();
        echo "Currently logged in: " . ($loggedIn ? "YES" : "NO") . "<br>";
    } else {
        echo "❌ isLoggedIn() NOT FOUND<br>";
    }
    
    // Test generateCSRFToken
    if (function_exists('generateCSRFToken')) {
        echo "✅ generateCSRFToken() exists<br>";
        $token = generateCSRFToken();
        echo "CSRF Token: " . substr($token, 0, 20) . "...<br>";
    } else {
        echo "❌ generateCSRFToken() NOT FOUND<br>";
    }
    
    // Test other functions
    $functions = ['verifyCSRFToken', 'setFlashMessage', 'getFlashMessage', 'sanitizeInput', 'hashPassword', 'verifyPassword'];
    foreach ($functions as $func) {
        if (function_exists($func)) {
            echo "✅ $func() exists<br>";
        } else {
            echo "❌ $func() NOT FOUND<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 6: Test Password Verification
echo "<h2>Test 6: Password Verification Test</h2>";
if ($result['count'] > 0) {
    // Get first user
    $stmt = $pdo->query("SELECT email, password FROM users LIMIT 1");
    $testUser = $stmt->fetch();
    
    echo "Testing with user: <strong>{$testUser['email']}</strong><br>";
    echo "Stored hash: " . substr($testUser['password'], 0, 30) . "...<br>";
    
    // Test with common password
    $testPassword = "Password123!";
    if (password_verify($testPassword, $testUser['password'])) {
        echo "✅ <span style='color:green;'>Password 'Password123!' MATCHES!</span><br>";
        echo "<strong>Use this to login:</strong><br>";
        echo "Email: {$testUser['email']}<br>";
        echo "Password: Password123!<br>";
    } else {
        echo "⚠️ <span style='color:orange;'>Password 'Password123!' does not match</span><br>";
        echo "This user might have a different password.<br>";
    }
}

echo "<hr>";

// Test 7: Session Check
echo "<h2>Test 7: Session Status</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ <span style='color:green;'>Session is ACTIVE</span><br>";
    echo "Session ID: " . session_id() . "<br>";
    
    if (!empty($_SESSION)) {
        echo "<h3>Session Variables:</h3>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
    } else {
        echo "Session is empty (not logged in)<br>";
    }
} else {
    echo "❌ <span style='color:red;'>Session is NOT active</span><br>";
}

echo "<hr>";

// Test 8: Simulate Login
echo "<h2>Test 8: Simulate Login Test</h2>";
if ($result['count'] > 0) {
    $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
    $user = $stmt->fetch();
    
    echo "<form method='POST' style='background:#f5f5f5; padding:20px; border-radius:8px; max-width:400px;'>";
    echo "<h3>Try Logging In:</h3>";
    echo "<label>Email:</label><br>";
    echo "<input type='email' name='test_email' value='{$user['email']}' style='width:100%; padding:8px; margin:5px 0;'><br>";
    echo "<label>Password:</label><br>";
    echo "<input type='text' name='test_password' value='Password123!' style='width:100%; padding:8px; margin:5px 0;'><br>";
    echo "<button type='submit' name='test_login' style='background:#c41e3a; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; margin-top:10px;'>Test Login</button>";
    echo "</form>";
    
    if (isset($_POST['test_login'])) {
        echo "<hr>";
        echo "<h3>Login Test Result:</h3>";
        
        $email = $_POST['test_email'];
        $password = $_POST['test_password'];
        
        echo "Testing: $email / $password<br><br>";
        
        // Query user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "✅ User found in database<br>";
            echo "Name: {$user['name']}<br>";
            echo "Role: {$user['role']}<br>";
            
            if (password_verify($password, $user['password'])) {
                echo "✅ <span style='color:green; font-size:18px;'><strong>PASSWORD MATCHES! ✅</strong></span><br>";
                echo "<br><strong>Login would be SUCCESSFUL!</strong><br>";
                echo "<br><a href='index.php' style='background:#c41e3a; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Real Login Page</a>";
            } else {
                echo "❌ <span style='color:red;'><strong>PASSWORD DOES NOT MATCH</strong></span><br>";
                echo "<br>Possible issues:<br>";
                echo "• Password is not 'Password123!'<br>";
                echo "• Password was not hashed correctly<br>";
                echo "• Try registering a new account<br>";
            }
        } else {
            echo "❌ User not found in database<br>";
        }
    }
}

echo "<hr>";
echo "<h2>📋 Summary</h2>";
echo "<ul style='line-height:2;'>";
echo "<li>If all tests pass ✅, your login system should work</li>";
echo "<li>If database connection fails ❌, check credentials in config.php</li>";
echo "<li>If no users found ⚠️, run insert_test_users.sql</li>";
echo "<li>If password doesn't match ❌, use the registration form to create a new account</li>";
echo "</ul>";

echo "<h3>Next Steps:</h3>";
echo "<ol style='line-height:2;'>";
echo "<li>Fix any ❌ errors shown above</li>";
echo "<li>Make sure you have users in the database</li>";
echo "<li>Go to <a href='index.php' style='color:#c41e3a; font-weight:bold;'>index.php</a> and try logging in</li>";
echo "<li>If registration needed, go to <a href='register.php' style='color:#c41e3a; font-weight:bold;'>register.php</a></li>";
echo "<li><strong>DELETE THIS FILE after testing</strong> (security risk)</li>";
echo "</ol>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
        }
        h1 { color: #c41e3a; }
        h2 { 
            background: #c41e3a; 
            color: white; 
            padding: 10px; 
            border-radius: 5px;
        }
        table {
            background: white;
            width: 100%;
        }
    </style>
</head>
<body>
</body>
</html>
```