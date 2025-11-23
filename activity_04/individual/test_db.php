<?php
/**
 * Database Connection Test
 * Run this file to test your database connection
 */

echo "<h2>Testing Database Connection...</h2>";

// Test 1: Check config values
echo "<h3>Step 1: Configuration Values</h3>";
define('DB_HOST', 'localhost');
define('DB_NAME', 'webtech_2025A_eyram_awoye');
define('DB_USER', 'root');
define('DB_PASS', '');

echo "Host: " . DB_HOST . "<br>";
echo "Database: " . DB_NAME . "<br>";
echo "User: " . DB_USER . "<br>";
echo "Password: " . (empty(DB_PASS) ? "(empty)" : "(set)") . "<br><br>";

// Test 2: Try to connect
echo "<h3>Step 2: Attempting Connection...</h3>";
try {
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ <strong style='color:green;'>Successfully connected to MySQL server!</strong><br><br>";
    
    // Test 3: Check if database exists
    echo "<h3>Step 3: Checking Database...</h3>";
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $dbExists = $stmt->fetch();
    
    if ($dbExists) {
        echo "✅ <strong style='color:green;'>Database '" . DB_NAME . "' exists!</strong><br><br>";
        
        // Test 4: Connect to specific database
        echo "<h3>Step 4: Connecting to Database...</h3>";
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        echo "✅ <strong style='color:green;'>Connected to database successfully!</strong><br><br>";
        
        // Test 5: Check tables
        echo "<h3>Step 5: Checking Tables...</h3>";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "✅ <strong style='color:green;'>Found " . count($tables) . " table(s):</strong><br>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>" . $table . "</li>";
            }
            echo "</ul><br>";
            
            // Test 6: Check users table
            if (in_array('users', $tables)) {
                echo "<h3>Step 6: Checking Users Table...</h3>";
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                $result = $stmt->fetch();
                echo "✅ <strong style='color:green;'>Users table has " . $result['count'] . " user(s)</strong><br><br>";
                
                $stmt = $pdo->query("SELECT name, email, role FROM users LIMIT 5");
                $users = $stmt->fetchAll();
                echo "<table border='1' cellpadding='5' cellspacing='0'>";
                echo "<tr><th>Name</th><th>Email</th><th>Role</th></tr>";
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                    echo "</tr>";
                }
                echo "</table><br>";
            }
            
            echo "<hr>";
            echo "<h2 style='color:green;'>🎉 ALL TESTS PASSED!</h2>";
            echo "<p>Your database is set up correctly. You can now:</p>";
            echo "<ol>";
            echo "<li><a href='index.php'>Go to Login Page</a></li>";
            echo "<li>Use test credentials: jane.smith@ashesi.edu.gh / Password123!</li>";
            echo "</ol>";
            echo "<p><strong>Note:</strong> Delete this test_db.php file after testing for security!</p>";
            
        } else {
            echo "⚠️ <strong style='color:orange;'>Database exists but no tables found!</strong><br>";
            echo "<p>You need to run the schema.sql file in phpMyAdmin:</p>";
            echo "<ol>";
            echo "<li>Go to <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
            echo "<li>Select database: " . DB_NAME . "</li>";
            echo "<li>Click 'SQL' tab</li>";
            echo "<li>Copy and paste content from schema.sql</li>";
            echo "<li>Click 'Go'</li>";
            echo "</ol>";
        }
        
    } else {
        echo "❌ <strong style='color:red;'>Database '" . DB_NAME . "' does NOT exist!</strong><br><br>";
        echo "<h3>How to Create the Database:</h3>";
        echo "<ol>";
        echo "<li>Go to <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
        echo "<li>Click 'New' in the left sidebar</li>";
        echo "<li>Database name: <strong>" . DB_NAME . "</strong></li>";
        echo "<li>Collation: utf8mb4_unicode_ci</li>";
        echo "<li>Click 'Create'</li>";
        echo "<li>Then run the schema.sql file</li>";
        echo "</ol>";
    }
    
} catch (PDOException $e) {
    echo "❌ <strong style='color:red;'>Connection FAILED!</strong><br><br>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br><br>";
    
    echo "<h3>Common Solutions:</h3>";
    echo "<ul>";
    echo "<li><strong>Wrong password?</strong> Default XAMPP password is empty. Check config.php</li>";
    echo "<li><strong>MySQL not running?</strong> Check XAMPP Control Panel - MySQL should be green</li>";
    echo "<li><strong>Port conflict?</strong> Make sure MySQL is using port 3306</li>";
    echo "</ul>";
    
    echo "<h3>Quick Fix Steps:</h3>";
    echo "<ol>";
    echo "<li>Open XAMPP Control Panel</li>";
    echo "<li>Stop MySQL if running</li>";
    echo "<li>Start MySQL again</li>";
    echo "<li>Refresh this page</li>";
    echo "</ol>";
}
?>
