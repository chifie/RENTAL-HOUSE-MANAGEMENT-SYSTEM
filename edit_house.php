<?php
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';

$success = "";
$error = "";

// 1. Get the House ID from the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: houses.php");
    exit();
}

$id = $_GET['id'];

// 2. Fetch the current data for this specific house
$stmt = $pdo->prepare("SELECT * FROM houses WHERE id = ?");
$stmt->execute([$id]);
$house = $stmt->fetch();

// If house doesn't exist, go back
if (!$house) {
    header("Location: houses.php");
    exit();
}

// 3. Handle the Update Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $house_no = clean($_POST['house_no']);
    $features = clean($_POST['features']);
    $price    = clean($_POST['price']);
    $status   = clean($_POST['status']);

    if (!empty($house_no) && !empty($price)) {
        $sql = "UPDATE houses SET house_no = ?, features = ?, price = ?, status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$house_no, $features, $price, $status, $id])) {
            $success = "House updated successfully! <a href='houses.php' class='alert-link'>Return to list</a>";
            // Refresh local data to show new values in the form
            $house['house_no'] = $house_no;
            $house['features'] = $features;
            $house['price'] = $price;
            $house['status'] = $status;
        } else {
            $error = "Update failed. Please try again.";
        }
    } else {
        $error = "House Number and Price are required.";
    }
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="houses.php">Houses</a></li>
                    <li class="breadcrumb-item active">Edit House</li>
                </ol>
            </nav>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-warning text-dark py-3 fw-bold">
                            <i class="fas fa-edit me-2"></i> Edit House: <?php echo htmlspecialchars($house['house_no'] ?? 'N/A'); ?>
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
                                        <label class="form-label fw-bold">House Number</label>
                                        <input type="text" name="house_no" class="form-control" 
                                               value="<?php echo htmlspecialchars($house['house_no'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Price (Tsh)</label>
                                        <input type="number" name="price" class="form-control" 
                                               value="<?php echo htmlspecialchars($house['price'] ?? 0); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="Available" <?php echo ($house['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                                        <option value="Occupied" <?php echo ($house['status'] == 'Occupied') ? 'selected' : ''; ?>>Occupied</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Features</label>
                                    <textarea name="features" class="form-control" rows="4"><?php echo htmlspecialchars($house['features'] ?? ''); ?></textarea>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-warning fw-bold">
                                        <i class="fas fa-save me-2"></i> Update Changes
                                    </button>
                                    <a href="houses.php" class="btn btn-light border">Cancel</a>
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