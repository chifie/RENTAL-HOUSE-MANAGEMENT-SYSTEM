<?php
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';

if (isset($_GET['id']) && isset($_GET['house_id'])) {
    $tenant_id = $_GET['id'];
    $house_id  = $_GET['house_id'];

    try {
        $pdo->beginTransaction();

        // 1. Delete the tenant
        $stmt = $pdo->prepare("DELETE FROM tenants WHERE id = ?");
        $stmt->execute([$tenant_id]);

        // 2. Set the house back to 'Available'
        $updateHouse = $pdo->prepare("UPDATE houses SET status = 'Available' WHERE id = ?");
        $updateHouse->execute([$house_id]);

        $pdo->commit();
        header("Location: tenants.php?msg=removed");
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: tenants.php?error=failed");
    }
} else {
    header("Location: tenants.php");
}
exit();