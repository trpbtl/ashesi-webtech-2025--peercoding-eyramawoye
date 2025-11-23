<?php
// Generate password hash for Password123!
$password = 'Password123!';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "<h2>Password Hash Generator</h2>";
echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
echo "<p><strong>Hash:</strong> <code>" . $hash . "</code></p>";
echo "<hr>";
echo "<p>Copy this hash and use it in your schema.sql file</p>";
echo "<p><strong>Test verification:</strong> ";
if (password_verify($password, $hash)) {
    echo "<span style='color:green;'>✓ Hash verified successfully!</span>";
} else {
    echo "<span style='color:red;'>✗ Verification failed!</span>";
}
echo "</p>";
?>
