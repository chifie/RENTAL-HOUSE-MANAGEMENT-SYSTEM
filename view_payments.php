<?php
/**
 * admin/view_payments.php
 * Purpose: Display payment history for a specific tenant
 */
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';

// Get Tenant ID from URL
$tenant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($tenant_id <= 0) {
    redirect('tenants.php');
}

// 1. Fetch Tenant Details
$stmt = $pdo->prepare("SELECT tenants.*, houses.house_number 
                       FROM tenants 
                       JOIN houses ON tenants.house_id = houses.id 
                       WHERE tenants.id = ?");
$stmt->execute([$tenant_id]);
$tenant = $stmt->fetch();

if (!$tenant) {
    redirect('tenants.php');
}

// 2. Fetch Payment History for this Tenant
$sql = "SELECT payments.*, admin.full_name AS received_by_name 
        FROM payments 
        LEFT JOIN admin ON payments.received_by = admin.id 
        WHERE payments.tenant_id = ? 
        ORDER BY payments.payment_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$tenant_id]);
$payments = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">Payment History</h1>
                    <p class="text-muted">Tenant: <strong><?php echo htmlspecialchars($tenant['full_name']); ?></strong> | House: <?php echo htmlspecialchars($tenant['house_number']); ?></p>
                </div>
                <div>
                    <a href="tenants.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Tenants
                    </a>
                    <a href="add_payment.php?tenant_id=<?php echo $tenant_id; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Record New Payment
                    </a>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="text-uppercase small">Total Paid</h6>
                            <h3 class="mb-0">
                                <?php 
                                    $total = array_sum(array_column($payments, 'amount_paid'));
                                    echo formatMoney($total); 
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-info text-white">
                        <div class="card-body text-center">
                            <h6 class="text-uppercase small">Next Due Date</h6>
                            <h3 class="mb-0"><?php echo date('d M, Y', strtotime($tenant['next_payment_date'])); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Reference No</th>
                                    <th>Period (Month)</th>
                                    <th>Amount Paid</th>
                                    <th>Received By</th>
                                    <th class="text-center">Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($payments)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-receipt fa-3x mb-3 d-block opacity-25"></i>
                                            No payments found for this tenant.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($payments as $p): ?>
                                    <tr>
                                        <td class="fw-bold">
                                            <?php echo date('d M, Y', strtotime($p['payment_date'])); ?>
                                        </td>
                                        <td class="text-muted small">
                                            <?php echo htmlspecialchars($p['reference_no'] ?? 'N/A'); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?php echo htmlspecialchars($p['month_for'] ?? 'General'); ?>
                                            </span>
                                        </td>
                                        <td class="text-success fw-bold">
                                            <?php echo formatMoney($p['amount_paid']); ?>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($p['received_by_name'] ?? 'System'); ?></small>
                                        </td>
                                        <td class="text-center">
                                            <a href="print_receipt.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-dark" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
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