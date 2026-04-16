<?php
/**
 * admin/tenants.php - Improved Version
 */
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Handle Search Query
$search = isset($_GET['search']) ? clean($_GET['search']) : '';

// Fetch tenants with optional search logic
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT t.*, h.house_number as house_no 
                           FROM tenants t 
                           JOIN houses h ON t.house_id = h.id 
                           WHERE t.full_name LIKE ? OR h.house_number LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);
    $tenants = $stmt->fetchAll();
} else {
    $tenants = getAllTenants($pdo);
}

$today = date('Y-m-d');
?>

<div class="container-fluid bg-light min-vh-100">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                <div class="mb-3 mb-md-0">
                    <h1 class="h3 fw-bold mb-1"><i class="fas fa-users me-2 text-primary"></i>Tenant Directory</h1>
                    <p class="text-muted small mb-0">Manage active residents and payment statuses.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="add_tenant.php" class="btn btn-primary shadow-sm px-4">
                        <i class="fas fa-user-plus me-2"></i>New Tenant
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form method="GET" class="row g-2">
                        <div class="col-md-10">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by name or house number..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th class="ps-4 py-3">Tenant Details</th>
                                <th>Unit</th>
                                <th>Phone</th>
                                <th>Due Date</th>
                                <th class="text-center pe-4">Quick Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($tenants)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                                        <p>No tenants found match your criteria.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($tenants as $row): 
                                    // Prepare WhatsApp Link with formatted phone
                                    $phone = $row['phone'];
                                    if (substr($phone, 0, 1) == '0') {
                                        $phone = '255' . substr($phone, 1);
                                    }
                                    $wa_msg = "Habari " . $row['full_name'] . ", huu ni ukumbusho wa kodi ya nyumba " . $row['house_no'] . ". Tarehe yako ya malipo ni " . date('d/m/Y', strtotime($row['next_payment_date'])) . ". Karibu!";
                                    $wa_link = "https://wa.me/" . $phone . "?text=" . urlencode($wa_msg);
                                    
                                    // Row Highlight if Overdue
                                    $is_overdue = ($today > $row['next_payment_date']);
                                    $row_class = $is_overdue ? 'bg-danger-light' : '';
                                ?>
                                    <tr class="<?php echo $row_class; ?>">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                            <span class="badge bg-secondary-soft text-secondary small">ID: <?php echo htmlspecialchars($row['id_number'] ?? 'N/A'); ?></span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">#<?php echo htmlspecialchars($row['house_no']); ?></span>
                                        </td>
                                        <td>
                                            <a href="tel:<?php echo $row['phone']; ?>" class="btn btn-sm btn-light border rounded-pill">
                                                <i class="fas fa-phone-alt me-1 text-success small"></i><?php echo htmlspecialchars($row['phone']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="mb-1 fw-bold <?php echo $is_overdue ? 'text-danger' : ''; ?>">
                                                <?php echo date('d M, Y', strtotime($row['next_payment_date'])); ?>
                                            </div>
                                            <?php echo getStatusBadge($row['next_payment_date']); ?>
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                                                    Manage
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                    <li><a class="dropdown-item text-success" href="<?php echo $wa_link; ?>" target="_blank"><i class="fab fa-whatsapp me-2"></i>Send Reminder</a></li>
                                                    <li><a class="dropdown-item" href="add_payment.php?tenant_id=<?php echo $row['id']; ?>"><i class="fas fa-money-bill-wave me-2"></i>Add Payment</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item" href="edit_tenant.php?id=<?php echo $row['id']; ?>"><i class="fas fa-edit me-2"></i>Edit Profile</a></li>
                                                    <?php if(isAdmin()): ?>
                                                        <li><a class="dropdown-item text-danger" href="delete_tenant.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Je, una uhakika?')"><i class="fas fa-trash me-2"></i>Remove Tenant</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
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
    /* Styling for the UI improvements */
    .bg-danger-light { background-color: rgba(220, 53, 69, 0.05); }
    .bg-secondary-soft { background-color: #f1f3f5; font-size: 0.75rem; }
    .dropdown-item { font-size: 0.9rem; padding: 0.5rem 1rem; }
    .table thead th { font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
</style>

<?php include '../includes/footer.php'; ?>