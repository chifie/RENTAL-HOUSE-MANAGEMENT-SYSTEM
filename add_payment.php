<?php
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';

$success = "";
$error = "";

// 1. Fetch Active Tenants and their linked House Numbers
try {
    $stmt = $pdo->query("SELECT t.id, t.full_name, h.house_number, h.rent_amount 
                         FROM tenants t 
                         JOIN houses h ON t.house_id = h.id 
                         WHERE t.status = 'Active'");
    $active_tenants = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
}

// 2. Handle Payment Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenant_id    = clean($_POST['tenant_id']);
    $amount_paid  = clean($_POST['amount_paid']);
    $payment_date = clean($_POST['payment_date']);
    $ref_number   = clean($_POST['ref_number']); // For M-Pesa/Bank IDs

    if (!empty($tenant_id) && !empty($amount_paid)) {
        try {
            $pdo->beginTransaction();

            // Insert into payments table
            $sql = "INSERT INTO payments (tenant_id, amount_paid, payment_date, reference_no) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tenant_id, $amount_paid, $payment_date, $ref_number]);

            // UPDATE TENANT: Push the next_payment_date forward by 1 month
            // This ensures the dashboard knows when they are due again
            $updateSql = "UPDATE tenants 
                          SET next_payment_date = DATE_ADD(next_payment_date, INTERVAL 1 MONTH) 
                          WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$tenant_id]);

            $pdo->commit();
            $success = "Payment of " . formatMoney($amount_paid) . " recorded successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Transaction failed: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="container-fluid bg-light min-vh-100">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-5">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    
                    <div class="d-flex align-items-center mb-4">
                        <a href="payments.php" class="btn btn-white shadow-sm rounded-circle me-3">
                            <i class="fas fa-arrow-left text-primary"></i>
                        </a>
                        <div>
                            <h2 class="h4 fw-bold mb-0">Record Rent Payment</h2>
                            <p class="text-muted small mb-0">April 5 Transaction Log</p>
                        </div>
                    </div>

                    <?php if($success): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white py-3">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Payment Details</h6>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            <form method="POST">
                                <div class="row g-4">
                                    
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <select name="tenant_id" class="form-select border-0 bg-light" id="pTenant" required>
                                                <option value="">-- Select Tenant --</option>
                                                <?php foreach ($active_tenants as $t): ?>
                                                    <option value="<?php echo $t['id']; ?>">
                                                        <?php echo htmlspecialchars($t['full_name']); ?> 
                                                        (House <?php echo $t['house_number']; ?> - <?php echo formatMoney($t['rent_amount']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="pTenant">Tenant Name & House</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="number" name="amount_paid" class="form-control border-0 bg-light" id="pAmount" placeholder="Amount" required>
                                            <label for="pAmount">Amount Paid (Tsh)</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" name="ref_number" class="form-control border-0 bg-light" id="pRef" placeholder="M-Pesa ID">
                                            <label for="pRef">Reference No (e.g. M-Pesa ID)</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-floating">
                                            <input type="date" name="payment_date" class="form-control border-0 bg-light" id="pDate" value="<?php echo date('Y-m-d'); ?>" required>
                                            <label for="pDate">Payment Date</label>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg px-5 py-3 shadow-sm w-100 fw-bold">
                                            <i class="fas fa-receipt me-2"></i> Confirm Payment
                                        </button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>