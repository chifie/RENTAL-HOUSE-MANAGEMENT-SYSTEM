<?php
require_once '../config/db.php';

try {
    // 1. Make sure the password column is large enough
    $pdo->exec("ALTER TABLE admin MODIFY password VARCHAR(255) NOT NULL");

    // 2. Delete the old 'levina' user if they exist
    $pdo->exec("DELETE FROM admin WHERE username = 'levina'");

    // 3. Create the NEW password hash
    $password = 'levina*1';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 4. Insert the fresh user
    $stmt = $pdo->prepare("INSERT INTO admin (username, password, full_name) VALUES (?, ?, ?)");
    $stmt->execute(['levina', $hashed_password, 'Levina Admin']);

    echo "<h1 style='color:green;'>✅ Success! Admin user has been reset.</h1>";
    echo "<p>Password set to: <b>levina*1</b></p>";
    echo "<p><a href='login.php'>Go to Login Page Now</a></p>";

} catch (PDOException $e) {
    echo "<h1 style='color:red;'>❌ Error: " . $e->getMessage() . "</h1>";
}
?>