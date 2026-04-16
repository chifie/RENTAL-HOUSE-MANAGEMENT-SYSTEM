<?php
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';

$success = "";
$error = "";

// Handle Deleting a House (Optional but helpful)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM houses WHERE id = ? AND status = 'Available'");
        $stmt->execute([$id]);
        $success = "House deleted successfully!";
    } catch (PDOException $e) {
        $error = "Cannot delete: House might be occupied by a tenant.";
    }
}

// 1. Fetch ALL houses to see what is happening in the database
try {
    $stmt = $pdo->query("SELECT * FROM houses ORDER BY house_number ASC");
    $all_houses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="container-fluid bg-light min-vh-100">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 fw-bold mb-0">Property Management</h2>
                    <p class="text-muted small mb-0">Total Houses: <?php echo count($all_houses); ?></p>
                </div>
                <a href="add_house.php" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus me-2"></i>Add New House
                </a>
            </div>

            <?php if($success): ?>
                <div class="alert alert-success border-0 shadow-sm"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert alert-danger border-0 shadow-sm"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 px-4 py-3">House No.</th>
                                <th class="border-0 py-3">Monthly Rent</th>
                                <th class="border-0 py-3">Status</th>
                                <th class="border-0 py-3 text-end px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_houses)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-home fa-3x mb-3 opacity-25"></i>
                                        <p>No houses registered yet.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_houses as $house): ?>
                                    <tr>
                                        <td class="px-4 fw-bold"><?php echo htmlspecialchars($house['house_number']); ?></td>
                                        <td><?php echo number_format($house['rent_amount']); ?> Tsh</td>
                                        <td>
                                            <?php if(trim($house['status']) == 'Available'): ?>
                                                <span class="badge bg-success-soft text-success px-3">Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-soft text-danger px-3">Occupied</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end px-4">
                                            <a href="edit_house.php?id=<?php echo $house['id']; ?>" class="btn btn-sm btn-light text-primary me-2">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if(trim($house['status']) == 'Available'): ?>
                                            <a href="?delete=<?php echo $house['id']; ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Delete this house?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
    /* Styling for the badges */
    .bg-success-soft { background-color: #e8fadf; color: #28a745; }
    .bg-danger-soft { background-color: #fde8e8; color: #dc3545; }
    .badge { font-weight: 500; border-radius: 30px; }
</style>

<?php include '../includes/footer.php'; ?>