<?php
// update_admin.php
require_once 'config/db.php';

$new_user = "levina"; 
$new_pass = "levina*1"; 

// Generate a professional-grade secure hash
$hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);

try {
    $sql = "UPDATE admin SET username = ?, password = ? WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    
    if($stmt->execute([$new_user, $hashed_pass])) {
        echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>";
        echo "<h2 style='color:green;'>✅ Credentials Updated!</h2>";
        echo "<p>Username: <b>$new_user</b></p>";
        echo "<p>Password: <b>$new_pass</b></p>";
        echo "<a href='admin/login.php' style='padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Go to Login Page</a>";
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>