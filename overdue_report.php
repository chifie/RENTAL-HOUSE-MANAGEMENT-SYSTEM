<?php
/**
 * admin/overdue_report.php
 * Updated: April 4, 2026 - Optimized Debt Priority & UI
 */

// 1. Security & Connections
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';

// 2. Page Elements
include '../includes/header.php';

// 3. Fetch overdue tenants (Ideally your function sorts by next_payment_date ASC)
$overdue_tenants = getOverdueTenants($pdo);
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active">Financial Reports</li>
                        </ol>
                    </nav>
                    <h2 class="text-danger fw-bold mb-0"><i class="fas fa-clock me-2"></i>Overdue Rent Tracker</h2>
                    <p class="text-muted mb-0">Actionable list of expired tenancies as of <?php echo date('d M, Y'); ?>.</p>
                </div>
                <div class="d-print-none">
                    <button onclick="window.print()" class="btn btn-dark shadow-sm">
                        <i class="fas fa-print me-2"></i>Print Report
                    </button>
                </div>
            </div>

            <div class="row mb-4 d-print-none">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-danger text-white">
                        <div class="card-body">
                            <h6 class="small text-uppercase opacity-75">Total Defaulters</h6>
                            <h3 class="fw-bold mb-0"><?php echo count($overdue_tenants); ?> Tenants</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th class="ps-4 py-3">Tenant Details</th>
                                    <th class="py-3 text-center">House No</th>
                                    <th class="py-3">Expired On</th>
                                    <th class="py-3">Debt Duration</th>
                                    <th class="py-3 text-end pe-4 d-print-none">Action Required</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($overdue_tenants)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="animate__animated animate__bounceIn">
                                                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                                                <h4 class="text-dark fw-bold">All Accounts Clear</h4>
                                                <p class="text-muted">Every active tenant has paid their rent for this period.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($overdue_tenants as $t): 
                                        $today = strtotime(date('Y-m-d'));
                                        $expiry = strtotime($t['next_payment_date']);
                                        $diff = $today - $expiry;
                                        $days_late = floor($diff / (86400));

                                        // --- ENHANCED WHATSAPP LOGIC ---
                                        $phone = $t['phone_number'];
                                        if (substr($phone, 0, 1) == '0') {
                                            $phone = '255' . substr($phone, 1);
                                        }
                                        
                                        // Professional Swahili Prompt
                                        $msg = "Habari " . $t['full_name'] . ",\n\n";
                                        $msg .= "Huu ni ukumbusho wa kodi ya nyumba " . $t['house_number'] . ". ";
                                        $msg .= "Kumbukumbu zetu zinaonyesha kodi yako iliisha tarehe " . date('d/m/Y', $expiry) . ". ";
                                        $msg .= "Tafadhali wasilisha malipo leo ili kuepuka usumbufu. \n\nAhsante.";
                                        
                                        $wa_link = "https://wa.me/" . $phone . "?text=" . urlencode($msg);
                                        
                                        // Visual Urgency Logic
                                        $row_class = ($days_late > 7) ? 'table-danger' : '';
                                        $badge_class = ($days_late > 7) ? 'bg-danger' : 'bg-warning text-dark';
                                    ?>
                                    <tr class="<?php echo $row_class; ?>">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($t['full_name']); ?></div>
                                            <small class="text-muted"><i class="fas fa-phone-alt small me-1"></i><?php echo $t['phone_number']; ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-3 py-2"><?php echo htmlspecialchars($t['house_number']); ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo date('d M, Y', $expiry); ?></div>
                                            <small class="text-danger small">Expired</small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $badge_class; ?> p-2 px-3">
                                                <i class="fas fa-exclamation-circle me-1"></i> <?php echo $days_late; ?> Days Late
                                            </span>
                                        </td>
                                        <td class="text-end pe-4 d-print-none">
                                            <div class="btn-group shadow-sm">
                                                <a href="add_payment.php?tenant_id=<?php echo $t['id']; ?>" class="btn btn-sm btn-success px-3" title="Record Payment">
                                                    <i class="fas fa-cash-register me-1"></i> Pay
                                                </a>
                                                <a href="<?php echo $wa_link; ?>" target="_blank" class="btn btn-sm btn-outline-success px-3" title="WhatsApp Reminder">
                                                    <i class="fab fa-whatsapp"></i>
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

            <div class="row mt-4 d-print-none">
                <div class="col-md-6">
                    <div class="p-3 bg-white rounded shadow-sm border-start border-primary border-4">
                        <h6 class="fw-bold mb-1"><i class="fas fa-lightbulb text-warning me-2"></i>Landlord Pro-Tip:</h6>
                        <small class="text-muted">
                            Sending a WhatsApp reminder is <strong>free</strong>. Always send a reminder before taking legal action. 
                            Rows highlighted in <span class="text-danger fw-bold">red</span> are more than 1 week late.
                        </small>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>