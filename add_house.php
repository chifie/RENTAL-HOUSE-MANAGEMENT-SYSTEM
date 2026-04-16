<?php
require_once '../includes/auth.php'; 
require_once '../config/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $house_number = $_POST['house_number'];
    $rent_amount = $_POST['rent_amount'];
    $description = $_POST['description'];
    $status = 'Available'; // Default status for new houses

    try {
        $stmt = $pdo->prepare("INSERT INTO houses (house_number, rent_amount, description, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$house_number, $rent_amount, $description, $status]);
        $success = "House $house_number added successfully!";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<div class="container-fluid bg-light min-vh-100">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-5">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-7">
                    
                    <div class="d-flex align-items-center mb-4">
                        <a href="manage_houses.php" class="btn btn-light rounded-circle me-3 shadow-sm">
                            <i class="fas fa-arrow-left text-primary"></i>
                        </a>
                        <h2 class="h4 fw-bold mb-0">Register New House</h2>
                    </div>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success border-0 shadow-sm animate__animated animate__fadeIn">
                            <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-dark text-white py-3">
                            <p class="mb-0 small text-uppercase fw-bold opacity-75">House Specifications</p>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            <form method="POST" action="">
                                
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" name="house_number" class="form-control border-0 bg-light" id="hName" placeholder="House 101" required>
                                            <label for="hName">House Number or Name</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="number" name="rent_amount" class="form-control border-0 bg-light" id="hRent" placeholder="500000" required>
                                            <label for="hRent">Monthly Rent (Tsh)</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-floating mb-4">
                                            <textarea name="description" class="form-control border-0 bg-light" id="hDesc" placeholder="Details" style="height: 120px"></textarea>
                                            <label for="hDesc">House Description (Optional)</label>
                                        </div>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary btn-lg px-5 py-3 shadow fw-bold w-100 w-md-auto">
                                            <i class="fas fa-save me-2"></i> Save House Details
                                        </button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-white rounded shadow-sm border-start border-info border-4">
                        <small class="text-muted">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            New houses are automatically set to <strong>Available</strong>. You can link a tenant to this house later in the "Register Tenant" section.
                        </small>
                    </div>

                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>