<?php
require_once 'config/db.php';
$username = 'levina';
$password = 'levina*1';

$stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    $check = password_verify($password, $user['password']);
    echo $check ? "SUCCESS: PHP can read your DB hash!" : "FAIL: The hash in your DB is wrong.";
} else {
    echo "FAIL: User 'levina' does not exist in the table.";
}
?>