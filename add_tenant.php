<?php
<?php
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';

$success = "";
$error = "";

// 1. Fetch available houses (using exact ENUM 'Available' from your SQL)
try {
    $stmt = $pdo->query("SELECT id, house_number, rent_amount FROM houses WHERE status = 'Available' ORDER BY house_number ASC");
    $available_houses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
    $available_houses = [];
}

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name  = clean($_POST['full_name']);
    $phone      = clean($_POST['phone']);
    $house_id   = (int)$_POST['house_id'];
    $start_date = clean($_POST['start_date']); 

    if (!empty($full_name) && !empty($phone) && !empty($house_id) && !empty($start_date)) {
        try {
            $pdo->beginTransaction();

            // DOUBLE CHECK: Ensure house is still available (Prevents race conditions)
            $check = $pdo->prepare("SELECT status FROM houses WHERE id = ? FOR UPDATE");
            $check->execute([$house_id]);
            $house_status = $check->fetchColumn();

            if ($house_status !== 'Available') {
                throw new Exception("This house was just taken by someone else. Please choose another.");
            }

            // Insert the new tenant (Adding 'move_in_date' which exists in your SQL)
            $sql = "INSERT INTO tenants (full_name, phone, house_id, status, next_payment_date, move_in_date) 
                    VALUES (?, ?, ?, 'Active', ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$full_name, $phone, $house_id, $start_date, $start_date]);

            // Update house status to 'Occupied'
            $updateStmt = $pdo->prepare("UPDATE houses SET status = 'Occupied' WHERE id = ?");
            $updateStmt->execute([$house_id]);

            $pdo->commit();
            
            // Set success message in session and redirect to avoid re-submission on refresh
            $_SESSION['success_msg'] = "Tenant <strong>$full_name</strong> registered successfully!";
            header("Location: tenants.php");
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
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
                        <a href="tenants.php" class="btn btn-white shadow-sm rounded-circle me-3">
                            <i class="fas fa-arrow-left text-primary"></i>
                        </a>
                        <div>
                            <h2 class="h4 fw-bold mb-0">Onboard New Tenant</h2>
                            <p class="text-muted small mb-0">Link a person to an available house</p>
                        </div>
                    </div>

                    <?php if($error): ?>
                        <div class="alert alert-danger border-0 shadow-sm mb-4 animate__animated animate__shakeX">
                            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-dark text-white py-3">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-id-card me-2"></i>Tenant Information</h6>
                        </div>
                        <div class="card-body p-4 p-md-5 bg-white">
                            <form method="POST" action="">
                                <div class="row g-4">
                                    
                                    <div class="col-md-7">
                                        <div class="form-floating">
                                            <input type="text" name="full_name" class="form-control border-0 bg-light" id="tName" placeholder="John Doe" required>
                                            <label for="tName">Full Name</label>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="form-floating">
                                            <input type="text" name="phone" class="form-control border-0 bg-light" id="tPhone" placeholder="07xxxxxxxx" required>
                                            <label for="tPhone">Phone Number</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select name="house_id" class="form-select border-0 bg-light" id="tHouse" required>
                                                <option value="">-- Select House --</option>
                                                <?php foreach ($available_houses as $house): ?>
                                                    <option value="<?php echo $house['id']; ?>">
                                                        House <?php echo htmlspecialchars($house['house_number']); ?> 
                                                        (<?php echo formatMoney($house['rent_amount']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="tHouse">Assign House</label>
                                        </div>
                                        <?php if(empty($available_houses)): ?>
                                            <div class="form-text text-danger mt-2">
                                                <i class="fas fa-warning me-1"></i> No houses available. 
                                                <a href="add_house.php" class="fw-bold">Add one first.</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="date" name="start_date" class="form-control border-0 bg-light" id="tDate" value="<?php echo date('Y-m-d'); ?>" required>
                                            <label for="tDate">Lease Start Date</label>
                                        </div>
                                        <div class="form-text mt-2 text-muted">First payment cycle begins today.</div>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg px-5 py-3 shadow-sm w-100 fw-bold" <?php echo empty($available_houses) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-user-check me-2"></i> Register & Link Tenant
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