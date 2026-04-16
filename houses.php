<?php
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Fetch all houses using a simple query
// Using try-catch to handle potential database name errors gracefully
try {
    $stmt = $pdo->query("SELECT * FROM houses ORDER BY id DESC");
    $houses = $stmt->fetchAll();
} catch (PDOException $e) {
    $houses = [];
    $db_error = "Database Error: " . $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
                <h1 class="h2">Manage Houses</h1>
                <a href="add_house.php" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i>Add New House
                </a>
            </div>

            <?php if(isset($db_error)): ?>
                <div class="alert alert-danger"><?php echo $db_error; ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>House No</th>
                                    <th>Features</th>
                                    <th>Price (Monthly)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($houses)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No houses found. Click "Add New House" to get started.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($houses as $house): ?>
                                    <tr>
                                        <td class="fw-bold">
                                            <?php echo htmlspecialchars($house['house_no'] ?? 'N/A'); ?>
                                        </td>
                                        
                                        <td class="small text-muted">
                                            <?php echo htmlspecialchars($house['features'] ?? 'No description provided'); ?>
                                        </td>
                                        
                                        <td>
                                            <?php 
                                                // Uses formatMoney from functions.php, defaults to 0 if key is missing
                                                echo formatMoney($house['price'] ?? 0); 
                                            ?>
                                        </td>
                                        
                                        <td>
                                            <?php 
                                                $status = $house['status'] ?? 'Available';
                                                $badgeClass = ($status == 'Available') ? 'bg-success' : 'bg-danger';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>">
                                                <?php echo htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                        
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit_house.php?id=<?php echo $house['id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_house.php?id=<?php echo $house['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this house?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>