<?php
// 1. Security & Connections
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';

// 2. Page Elements
include '../includes/header.php';

// 3. Date Configuration
$today = date('Y-m-d');
$current_month_year = date('F Y');

// 4. Data Retrieval
$total_revenue = getTotalRevenue($pdo);
$expected_income = getExpectedIncome($pdo);
$monthly_collected = getMonthlyRevenue($pdo, $current_month_year);
$overdue_list = getOverdueTenants($pdo);

// Daily Stats for April 4
$stmt = $pdo->prepare("SELECT SUM(amount_paid) as total, COUNT(*) as count FROM payments WHERE payment_date = ?");
$stmt->execute([$today]);
$res_today = $stmt->fetch();
$today_revenue = $res_today['total'] ?? 0;
$today_count = $res_today['count'] ?? 0;

// Occupancy Logic
$total_houses = countRecords($pdo, 'houses');
$occupied_houses = countRecords($pdo, 'tenants', "WHERE status='Active'");
$occupancy_rate = ($total_houses > 0) ? round(($occupied_houses / $total_houses) * 100) : 0;
?>

<style>
    /* Responsive Styling */
    .stat-card { transition: all 0.3s ease; border: none !important; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .quick-link-item { border: none !important; border-bottom: 1px solid #f8f9fa !important; transition: 0.2s; }
    .quick-link-item:hover { background-color: #f8f9fa !important; padding-left: 25px !important; }
    
    @media (max-width: 768px) {
        .main-content { padding: 10px !important; }
        .card-body { padding: 1.25rem !important; }
        h1 { font-size: 1.5rem !important; }
    }
</style>

<div class="container-fluid bg-light min-vh-100">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
            
            <div class="row align-items-center mb-4">
                <div class="col-12 col-md-7 text-center text-md-start mb-3 mb-md-0">
                    <h1 class="h3 fw-bold text-dark mb-1">Rental Management System</h1>
                    <p class="text-muted small mb-0">Overview for <?php echo date('l, d F Y'); ?></p>
                </div>
                <div class="col-12 col-md-5 text-center text-md-end">
                    <a href="add_payment.php" class="btn btn-primary shadow-sm px-4 fw-bold w-100 w-md-auto">
                        <i class="fas fa-plus-circle me-2"></i>Record New Payment
                    </a>
                </div>
            </div>

            <?php if (count($overdue_list) > 0): ?>
            <div class="alert bg-white border-0 shadow-sm border-start border-danger border-5 mb-4 py-3">
                <div class="row align-items-center text-center text-md-start">
                    <div class="col-md-1 mb-2 mb-md-0">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                    </div>
                    <div class="col-md-8 mb-3 mb-md-0">
                        <h6 class="mb-0 fw-bold text-danger">Rent Overdue Alert</h6>
                        <small class="text-muted">You have <strong><?php echo count($overdue_list); ?></strong> tenants past their payment deadline.</small>
                    </div>
                    <div class="col-md-3 text-md-end">
                        <a href="overdue_report.php" class="btn btn-danger btn-sm px-4 rounded-pill fw-bold">View List</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card shadow-sm h-100 border-bottom border-warning border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted small fw-bold text-uppercase">Collected Today</span>
                                <i class="fas fa-coins text-warning"></i>
                            </div>
                            <h3 class="fw-bold mb-1"><?php echo formatMoney($today_revenue); ?></h3>
                            <span class="badge bg-success-subtle text-success small"><?php echo $today_count; ?> Payments</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card shadow-sm h-100 border-bottom border-primary border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted small fw-bold text-uppercase">Monthly Total</span>
                                <i class="fas fa-calendar-check text-primary"></i>
                            </div>
                            <h3 class="fw-bold mb-1"><?php echo formatMoney($monthly_collected); ?></h3>
                            <small class="text-muted">Total for <?php echo date('M'); ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card shadow-sm h-100 border-bottom border-info border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted small fw-bold text-uppercase">Expected Target</span>
                                <i class="fas fa-bullseye text-info"></i>
                            </div>
                            <h3 class="fw-bold mb-1 text-info"><?php echo formatMoney($expected_income); ?></h3>
                            <small class="text-muted">Projected Revenue</small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card shadow-sm h-100 bg-dark text-white border-bottom border-secondary border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-white-50 small fw-bold text-uppercase">System Revenue</span>
                                <i class="fas fa-wallet text-white-50"></i>
                            </div>
                            <h3 class="fw-bold mb-1"><?php echo formatMoney($total_revenue); ?></h3>
                            <small class="text-white-50">Lifetime Collections</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0">Recent Houses</h6>
                            <a href="manage_houses.php" class="btn btn-sm btn-light fw-bold text-primary">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="small text-muted text-uppercase">
                                        <th class="ps-4">House No.</th>
                                        <th>Rent Amount</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Edit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT * FROM houses ORDER BY id DESC LIMIT 5");
                                    while ($h = $stmt->fetch()):
                                        $badge = ($h['status'] == 'Available') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">House <?php echo $h['house_number']; ?></td>
                                        <td class="fw-semibold text-dark"><?php echo formatMoney($h['rent_amount']); ?></td>
                                        <td><span class="badge <?php echo $badge; ?> rounded-pill px-3 py-2 small fw-bold"><?php echo $h['status']; ?></span></td>
                                        <td class="text-end pe-4">
                                            <a href="edit_house.php?id=<?php echo $h['id']; ?>" class="text-muted"><i class="fas fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body text-center">
                            <h6 class="text-muted small fw-bold text-uppercase mb-3">Occupancy Rate</h6>
                            <h2 class="fw-bold text-primary mb-2"><?php echo $occupancy_rate; ?>%</h2>
                            <div class="progress rounded-pill mb-3" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: <?php echo $occupancy_rate; ?>%"></div>
                            </div>
                            <small class="text-muted"><?php echo $occupied_houses; ?> Houses Occupied out of <?php echo $total_houses; ?></small>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-dark text-white border-0 py-3 text-center">
                            <h6 class="mb-0 fw-bold small text-uppercase">Quick Admin Shortcuts</h6>
                        </div>
                        <div class="list-group list-group-flush">
                            <a href="add_tenant.php" class="list-group-item list-group-item-action quick-link-item py-3">
                                <i class="fas fa-user-plus me-3 text-primary"></i> Register New Tenant
                            </a>
                            <a href="add_house.php" class="list-group-item list-group-item-action quick-link-item py-3">
                                <i class="fas fa-home me-3 text-success"></i> Add New House
                            </a>
                            <a href="payments.php" class="list-group-item list-group-item-action quick-link-item py-3">
                                <i class="fas fa-file-invoice-dollar me-3 text-info"></i> View All Payments
                            </a>
                            <a href="settings.php" class="list-group-item list-group-item-action quick-link-item py-3">
                                <i class="fas fa-tools me-3 text-secondary"></i> System Configuration
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>