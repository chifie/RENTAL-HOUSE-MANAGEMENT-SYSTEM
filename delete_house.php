<?php
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';

// 1. Check if an ID was actually sent in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // 2. Prepare the delete statement
        $stmt = $pdo->prepare("DELETE FROM houses WHERE id = ?");
        
        // 3. Execute and redirect with a success message (optional)
        if ($stmt->execute([$id])) {
            // Success! Go back to the houses list
            header("Location: houses.php?msg=deleted");
            exit();
        } else {
            // Something went wrong in the DB
            header("Location: houses.php?error=failed");
            exit();
        }
    } catch (PDOException $e) {
        // Handle foreign key errors (e.g., if a tenant is already in this house)
        header("Location: houses.php?error=has_tenant");
        exit();
    }
} else {
    // No ID found, just go back
    header("Location: houses.php");
    exit();
}