<?php
/**
 * admin/edit_tenant.php
 * Purpose: Update tenant details and handle house relocation logic
 */
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';

$success = "";
$error = "";

// 1. Get Tenant ID from URL
$tenant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($tenant_id <= 0) {
    redirect('tenants.php');
}

// 2. Fetch Current Tenant Data
try {
    $stmt = $pdo->prepare("SELECT * FROM tenants WHERE id = ?");
    $stmt->execute([$tenant_id]);
    $tenant = $stmt->fetch();

    if (!$tenant) {
        redirect('tenants.php');
    }

    // Fetch houses: Current house + all available houses
    $stmt = $pdo->prepare("SELECT id, house_number, rent_amount FROM houses WHERE status = 'Available' OR id = ?");
    $stmt->execute([$tenant['house_id']]);
    $houses = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
}

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name  = clean($_POST['full_name']);
    $phone      = clean($_POST['phone']);
    $id_number  = clean($_POST['id_number']);
    $new_house_id = clean($_POST['house_id']);
    $status     = clean($_POST['status']);
    $next_date  = clean($_POST['next_payment_date']);
    
    $old_house_id = $tenant['house_id'];

    if (!empty($full_name) && !empty($phone)) {
        try {
            $pdo->beginTransaction();

            // Update Tenant Details
            $sql = "UPDATE tenants SET 
                    full_name = ?, 
                    phone = ?, 
                    id_number = ?, 
                    house_id = ?, 
                    status = ?, 
                    next_payment_date = ? 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$full_name, $phone, $id_number, $new_house_id, $status, $next_date, $tenant_id]);

            // Handle House Status Logic if house changed
            if ($new_house_id != $old_house_id) {
                // Set old house to Available
                $pdo->prepare("UPDATE houses SET status = 'Available' WHERE id = ?")->execute([$old_house_id]);
                // Set new house to Occupied
                $pdo->prepare("UPDATE houses SET status = 'Occupied' WHERE id = ?")->execute([$new_house_id]);
            }

            $pdo->commit();
            $success = "Tenant updated successfully! <a href='tenants.php' class='alert-link'>Back to list</a>";
            
            // Refresh local data to show updated values in form
            $tenant['full_name'] = $full_name;
            $tenant['phone'] = $phone;
            $tenant['id_number'] = $id_number;
            $tenant['house_id'] = $new_house_id;
            $tenant['status'] = $status;
            $tenant['next_payment_date'] = $next_date;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Update failed: " . $e->getMessage();
        }
    } else {
        $error = "Full Name and Phone are required.";
    }
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-dark text-white py-3">
                            <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Tenant: <?php echo htmlspecialchars($tenant['full_name']); ?></h5>
                        </div>
                        <div class="card-body p-4">
                            
                            <?php if($success): ?>
                                <div class="alert alert-success border-0 shadow-sm"><?php echo $success; ?></div>
                            <?php endif; ?>

                            <?php if($error): ?>
                                <div class="alert alert-danger border-0 shadow-sm"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Full Name</label>
                                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($tenant['full_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Phone Number</label>
                                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($tenant['phone']); ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">ID / NIDA Number</label>
                                        <input type="text" name="id_number" class="form-control" value="<?php echo htmlspecialchars($tenant['id_number']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="Active" <?php echo ($tenant['status'] == 'Active') ? 'selected' : ''; ?>>Active (Living Here)</option>
                                            <option value="Inactive" <?php echo ($tenant['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive (Moved Out)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Assigned House</label>
                                        <select name="house_id" class="form-select" required>
                                            <?php foreach ($houses as $h): ?>
                                                <option value="<?php echo $h['id']; ?>" <?php echo ($tenant['house_id'] == $h['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($h['house_number']); ?> (<?php echo formatMoney($h['rent_amount']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Next Payment Due Date</label>
                                        <input type="date" name="next_payment_date" class="form-control" value="<?php echo $tenant['next_payment_date']; ?>" required>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <a href="tenants.php" class="btn btn-light border px-4">Cancel</a>
                                    <button type="submit" class="btn btn-dark px-5">
                                        <i class="fas fa-save me-2"></i>Update Tenant
                                    </button>
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