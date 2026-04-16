<?php
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: payments.php"); exit(); }

try {
    // JOIN admin to get the real name of the receiver
    $sql = "SELECT payments.*, tenants.full_name, houses.house_number, admin.full_name as receiver_name 
            FROM payments 
            JOIN tenants ON payments.tenant_id = tenants.id 
            JOIN houses ON tenants.house_id = houses.id 
            LEFT JOIN admin ON payments.received_by = admin.id
            WHERE payments.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $payment = $stmt->fetch();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

if (!$payment) { die("Receipt not found."); }

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="receipt-container shadow-lg" id="printableReceipt">
                
                <div class="paid-watermark">PAID</div>

                <div class="receipt-header">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h2 class="fw-bold text-primary mb-0">RENTAL MS</h2>
                            <p class="small text-muted mb-0">Dar es Salaam, Tanzania<br>Tel: +255 617 008 046</p>
                        </div>
                        <div class="col-6 text-end">
                            <h4 class="text-uppercase fw-light text-muted mb-1">Receipt</h4>
                            <h5 class="fw-bold mb-0">#<?php echo strtoupper(substr($payment['reference_no'], 0, 8)); ?></h5>
                            <p class="small text-muted mb-0">Date: <?php echo date('d M, Y', strtotime($payment['payment_date'])); ?></p>
                        </div>
                    </div>
                </div>

                <hr class="my-4 opacity-10">

                <div class="row mb-5">
                    <div class="col-6">
                        <label class="receipt-label">Tenant Details</label>
                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($payment['full_name']); ?></h6>
                        <p class="text-muted small">House No: <span class="text-dark fw-bold"><?php echo htmlspecialchars($payment['house_number']); ?></span></p>
                    </div>
                    <div class="col-6 text-end">
                        <label class="receipt-label">Payment Method</label>
                        <h6 class="fw-bold mb-0">Electronic Transfer / Cash</h6>
                        <p class="text-muted small">Ref: <?php echo htmlspecialchars($payment['reference_no']); ?></p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless receipt-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="fw-bold">Rent Payment</span><br>
                                    <span class="text-muted small">For the period of <?php echo htmlspecialchars($payment['month_for']); ?></span>
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    <?php echo formatMoney($payment['amount_paid']); ?>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-top">
                                <td class="text-end text-uppercase small fw-bold pt-3">Total Paid</td>
                                <td class="text-end pt-3">
                                    <h3 class="fw-bold text-primary mb-0"><?php echo formatMoney($payment['amount_paid']); ?></h3>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row mt-5 align-items-end">
                    <div class="col-7">
                        <p class="small text-muted mb-3 italic">"Thank you for your prompt payment."</p>
                        <div class="signature-box">
                            <p class="small mb-0"><?php echo htmlspecialchars($payment['receiver_name'] ?? 'Authorized Personnel'); ?></p>
                            <label class="receipt-label">Issued By</label>
                        </div>
                    </div>
                    <div class="col-5 text-center">
                        <div class="stamp-box">
                            OFFICIAL<br>STAMP
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4 d-print-none">
                <button onclick="window.print();" class="btn btn-primary btn-lg px-5 shadow">
                    <i class="fas fa-print me-2"></i>Print Official Receipt
                </button>
                <div class="mt-3">
                    <a href="view_payments.php?id=<?php echo $payment['tenant_id']; ?>" class="text-muted text-decoration-none small">
                        <i class="fas fa-arrow-left me-1"></i> Return to History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Receipt Styling */
.receipt-container {
    background: #fff;
    padding: 50px;
    position: relative;
    border-radius: 4px;
    overflow: hidden;
    font-family: 'Inter', 'Segoe UI', sans-serif;
}

.receipt-label {
    text-transform: uppercase;
    font-size: 10px;
    font-weight: 700;
    color: #adb5bd;
    display: block;
    margin-bottom: 4px;
    letter-spacing: 1px;
}

.receipt-table thead th {
    border-bottom: 2px solid #f8f9fa;
    text-transform: uppercase;
    font-size: 12px;
    color: #6c757d;
    padding-bottom: 15px;
}

.paid-watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 120px;
    font-weight: 900;
    color: rgba(40, 167, 69, 0.08);
    pointer-events: none;
    z-index: 0;
    border: 10px solid rgba(40, 167, 69, 0.08);
    padding: 10px 40px;
    border-radius: 20px;
}

.signature-box {
    border-top: 1px dashed #dee2e6;
    display: inline-block;
    padding-top: 5px;
    min-width: 180px;
}

.stamp-box {
    width: 100px;
    height: 100px;
    border: 3px double #dee2e6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 10px;
    color: #dee2e6;
    font-weight: bold;
    text-align: center;
    line-height: 1.2;
}

/* Printing logic */
@media print {
    body { background: none !important; color: #000 !important; }
    .receipt-container { 
        box-shadow: none !important; 
        padding: 0 !important; 
        width: 100% !important; 
    }
    .d-print-none, .navbar, .sidebar { display: none !important; }
    .paid-watermark { color: rgba(0, 0, 0, 0.05) !important; border-color: rgba(0, 0, 0, 0.05) !important; }
    .text-primary { color: #000 !important; }
}
</style>

<?php include '../includes/footer.php'; ?>