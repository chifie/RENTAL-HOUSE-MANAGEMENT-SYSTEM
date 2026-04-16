<?php
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// 1. Get the filter value (Default to current month if not set)
$month_filter = $_GET['filter_month'] ?? date('F Y');

// 2. Fetch filtered payments with tenant names
$sql = "SELECT payments.*, tenants.full_name 
        FROM payments 
        JOIN tenants ON payments.tenant_id = tenants.id 
        WHERE payments.month_for = ? 
        ORDER BY payments.payment_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$month_filter]);
$payments = $stmt->fetchAll();

// 3. Get list of unique months for the dropdown (to show only months that have payments)
$months_sql = "SELECT DISTINCT month_for FROM payments ORDER BY payment_date DESC";
$available_months = $pdo->query($months_sql)->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2"><i class="fas fa-history me-2 text-primary"></i>Payment History</h1>
                <div>
                    <form class="d-inline-block me-2" method="GET">
                        <div class="input-group input-group-sm">
                            <label class="input-group-text bg-white">Filter Month:</label>
                            <select name="filter_month" class="form-select" onchange="this.form.submit()">
                                <?php foreach($available_months as $m): ?>
                                    <option value="<?php echo $m; ?>" <?php echo ($m == $month_filter) ? 'selected' : ''; ?>>
                                        <?php echo $m; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                    <a href="add_payment.php" class="btn btn-primary btn-sm"><i class="fas fa-plus me-2"></i>New Payment</a>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-muted">Showing payments for: <span class="text-primary"><?php echo $month_filter; ?></span></h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Tenant</th>
                                <th>Amount Paid</th>
                                <th>Month For</th>
                                <th>Reference</th>
                                <th class="text-center">Actions</th> 
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($payments)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No payments found for <strong><?php echo $month_filter; ?></strong>.</td></tr>
                            <?php else: ?>
                                <?php foreach($payments as $p): ?>
                                <tr>
                                    <td><?php echo date('d M, Y', strtotime($p['payment_date'])); ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($p['full_name']); ?></td>
                                    
                                    <td class="text-success fw-bold">
                                        <?php echo formatMoney($p['amount_paid']); ?>
                                    </td>
                                    
                                    <td>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill">
                                            <?php echo htmlspecialchars($p['month_for']); ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?php echo htmlspecialchars($p['reference_no'] ?: '---'); ?></td>
                                    
                                    <td class="text-center">
                                        <a href="view_receipt.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Receipt">
                                            <i class="fas fa-file-invoice me-1"></i> Receipt
                                        </a>
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

<?php include '../includes/footer.php'; ?>