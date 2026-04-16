<?php
// admin/register_admin.php
require_once '../includes/auth.php'; // ONLY LOGGED IN ADMINS CAN ADD NEW ADMINS
require_once '../config/db.php';
require_once '../includes/functions.php';

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = clean($_POST['full_name']);
    $username = clean($_POST['username']);
    $phone    = clean($_POST['phone']);
    $password = $_POST['password'];

    // 1. Check if username already exists
    $check = $pdo->prepare("SELECT id FROM admin WHERE username = ?");
    $check->execute([$username]);

    if ($check->rowCount() > 0) {
        $error = "Username already taken!";
    } else {
        // 2. Hash the password for safety
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert into database
        $stmt = $pdo->prepare("INSERT INTO admin (username, password, full_name, phone) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$username, $hashed_password, $fullname, $phone])) {
            $success = "New Admin registered successfully!";
        } else {
            $error = "Something went wrong. Try again.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h2 class="border-bottom pb-2">Add New System Admin</h2>
            
            <div class="col-md-6 mt-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="0617008046">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Admin Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>