<?php
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: tenants.php"); exit(); }

// --- THE FIX ---
// If 'price' still fails, replace 'houses.price' with your actual column name (e.g. houses.amount)
// --- THE CORRECTED SQL ---
try {
    // We fetch 'rent_amount' but rename it to 'price' using AS
    $stmt = $pdo->prepare("SELECT tenants.*, houses.house_number, houses.rent_amount AS price 
                           FROM tenants 
                           JOIN houses ON tenants.house_id = houses.id 
                           WHERE tenants.id = ?");
    $stmt->execute([$id]);
    $tenant = $stmt->fetch();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

if (!$tenant) { 
    echo "<div class='container mt-5'><div class='alert alert-danger'>Tenant not found.</div></div>"; 
    exit(); 
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Tenant Profile</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center border-end">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fa-7x text-secondary"></i>
                            </div>
                            <h4 class="mb-1"><?php echo htmlspecialchars($tenant['full_name']); ?></h4>
                            <span class="badge bg-primary px-3 py-2"><?php echo htmlspecialchars($tenant['status']); ?></span>
                        </div>
                        
                        <div class="col-md-8 px-4">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold text-muted" style="width: 35%;">Phone Number:</td>
                                        <td><?php echo htmlspecialchars($tenant['phone']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">House Number:</td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?php echo htmlspecialchars($tenant['house_number'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Monthly Rent:</td>
                                        <td class="text-success h5 fw-bold">
                                            <?php echo formatMoney($tenant['price'] ?? 0); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Join Date:</td>
                                        <td>
                                            <?php 
                                                $date = !empty($tenant['created_at']) ? strtotime($tenant['created_at']) : time();
                                                echo date('d M, Y', $date); 
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex gap-2 mt-4">
                                <a href="tenants.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back
                                </a>
                                <a href="add_payment.php?tenant_id=<?php echo $tenant['id']; ?>" class="btn btn-success px-4">
                                    <i class="fas fa-money-bill-wave me-1"></i> Collect Rent
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>