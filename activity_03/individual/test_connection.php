<?php
/**
 * DATABASE CONNECTION TEST
 * 
 * Use this file to test if your database credentials are correct.
 * Open this file in your browser: http://localhost/your-folder/test_connection.php
 */

// Test configuration
$db_host = 'localhost';
$db_name = 'webtech_2025A_eyram_awoye';
$db_user = 'eyram.awoye';  
$db_pass = 'Eyramawo@1234%';

echo "<h2>Testing Database Connection...</h2>";
echo "<hr>";

// Test 1: Try to connect with PDO
echo "<h3>Test 1: PDO Connection</h3>";
try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ <span style='color:green;'><strong>PDO Connection Successful!</strong></span><br>";
    echo "Connected to database: <strong>$db_name</strong><br>";
    
    // Test 2: Check if users table exists
    echo "<hr>";
    echo "<h3>Test 2: Check Users Table</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✅ <span style='color:green;'><strong>Users table EXISTS!</strong></span><br>";
        
        // Test 3: Count users
        echo "<hr>";
        echo "<h3>Test 3: Count Users</h3>";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total users in database: <strong>{$count['count']}</strong><br>";
        
        if ($count['count'] > 0) {
            echo "✅ <span style='color:green;'>You have test users! Try logging in.</span><br>";
            
            // Show test users (without passwords)
            echo "<hr>";
            echo "<h3>Test Users (for login testing):</h3>";
            $stmt = $pdo->query("SELECT user_id, name, email, role, ashesi_id FROM users LIMIT 5");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
            echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Ashesi ID</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>{$user['user_id']}</td>";
                echo "<td>{$user['name']}</td>";
                echo "<td>{$user['email']}</td>";
                echo "<td>{$user['role']}</td>";
                echo "<td>{$user['ashesi_id']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<p><strong>Default password for all test users: Password123!</strong></p>";
        } else {
            echo "⚠️ <span style='color:orange;'><strong>No users found!</strong> You need to run the SQL inserts from schema.sql</span><br>";
        }
    } else {
        echo "❌ <span style='color:red;'><strong>Users table does NOT exist!</strong></span><br>";
        echo "You need to run schema.sql to create the database tables.<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ <span style='color:red;'><strong>Connection Failed!</strong></span><br>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br><br>";
    
    echo "<h4>Common Solutions:</h4>";
    echo "<ul>";
    echo "<li><strong>Access denied:</strong> Check username and password in config.php</li>";
    echo "<li><strong>Unknown database:</strong> Create the database first in phpMyAdmin</li>";
    echo "<li><strong>Connection refused:</strong> Make sure XAMPP/WAMP is running</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If connection successful, update <strong>config.php</strong> with the same credentials</li>";
echo "<li>If users table exists but no users, run the INSERT statements from <strong>schema.sql</strong></li>";
echo "<li>Then try registering or logging in at <strong>index.php</strong></li>";
echo "<li><strong>DELETE THIS FILE</strong> after testing (security risk)</li>";
echo "</ol>";

?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h2 {
            color: #c41e3a;
        }
        table {
            background: white;
            margin: 20px 0;
        }
        th {
            background-color: #c41e3a;
            color: white;
        }
    </style>
</head>
<body>
</body>
</html>
