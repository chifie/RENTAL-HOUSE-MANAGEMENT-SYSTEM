<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$house_id = $_GET['id'] ?? 0;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_inquiry'])) {
    $hid = (int)$_POST['house_id'];
    $name = clean($_POST['full_name']);    // User inputs full_name
    $phone = clean($_POST['phone']);       // User inputs phone
    $msg = clean($_POST['message']);

    if (!empty($name) && !empty($phone)) {
        // UPDATED: Using 'customer_name' and 'customer_phone' to match your SQL dump
        $stmt = $pdo->prepare("INSERT INTO inquiries (house_id, customer_name, customer_phone, message, status) VALUES (?, ?, ?, ?, 'New')");
        if ($stmt->execute([$hid, $name, $phone, $msg])) {
            $success = "Your inquiry has been sent successfully!";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}

// Fetch House Details
$stmt = $pdo->prepare("SELECT * FROM houses WHERE id = ?");
$stmt->execute([$house_id]);
$house = $stmt->fetch();

// If house doesn't exist, stop execution
if (!$house) {
    die("House not found.");
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-7">
            <div class="mb-4">
                <a href="index.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="fas fa-arrow-left"></i> Back to Listings</a>
                <h2 class="fw-bold text-dark">House <?php echo htmlspecialchars($house['house_number']); ?></h2>
                <span class="badge bg-info mb-3"><?php echo htmlspecialchars($house['category']); ?></span>
            </div>
            
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold">Features & Description</h5>
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($house['features'])); ?></p>
                <hr>
                <h4 class="text-primary fw-bold"><?php echo formatMoney($house['rent_amount']); ?> <small class="text-muted" style="font-size: 0.6em;">/ month</small></h4>
            </div>
        </div>
        
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Interested in this house?</h5>
                    <p class="small text-muted mb-4">Fill out the form below and the landlord will get back to you.</p>
                    
                    <?php if(isset($success)): ?>
                        <div class="alert alert-success border-0 shadow-sm small"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger border-0 shadow-sm small"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="house_id" value="<?php echo (int)$house_id; ?>">
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase" style="font-size: 0.75rem;">Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Enter your name" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase" style="font-size: 0.75rem;">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="07xxxxxxxx" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase" style="font-size: 0.75rem;">Message (Optional)</label>
                            <textarea name="message" class="form-control" rows="3" placeholder="Any specific questions?"></textarea>
                        </div>
                        <button type="submit" name="send_inquiry" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i>Send Inquiry
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>