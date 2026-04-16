<?php
// 1. Start buffering and session at the very top
ob_start(); 
session_start(); 

// 2. Load database connection
require_once '../config/db.php'; 

$error = "";  

if ($_SERVER['REQUEST_METHOD'] == 'POST') {     
    $username = trim($_POST['username']);     
    $password = trim($_POST['password']);      

    if (!empty($username) && !empty($password)) {         
        // Search for user - Ensure your table name is 'admin' or 'users'
        // Added 'role' to the selection
        $stmt = $pdo->prepare("SELECT id, username, password, full_name, role FROM admin WHERE username = :username");         
        $stmt->execute(['username' => $username]);         
        $user = $stmt->fetch();          

        // Verify password hash
        if ($user && password_verify($password, $user['password'])) {             
            // SYNCED WITH auth.php
            $_SESSION['user_id'] = $user['id'];             
            $_SESSION['username'] = $user['username']; 
            $_SESSION['full_name'] = $user['full_name']; 
            $_SESSION['role'] = $user['role']; // CRITICAL: This allows role checking (Admin vs Staff)
            
            // Redirect using JavaScript
            echo "<script>window.location.href='dashboard.php';</script>";
            exit();          
        } else {             
            $error = "Invalid Username or Password!";         
        }     
    } else {         
        $error = "Please fill in all fields.";     
    } 
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Rental System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f0f2f5; height: 100vh; display: flex; align-items: center; }
        .login-card { border: none; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .input-group-text { cursor: pointer; background: white; border-left: none; }
        .form-control { border-right: none; }
        .btn-primary { padding: 12px; font-weight: bold; border-radius: 8px; background-color: #0d6efd; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card p-4 login-card">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary">Rental Admin</h3>
                    <p class="text-muted small">Please sign in to continue</p>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger py-2 text-center small"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-user text-muted"></i></span>
                            <input type="text" name="username" class="form-control" style="border-right: 1px solid #ced4da;" placeholder="Enter username" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-lock text-muted"></i></span>
                            <input type="password" name="password" id="passInput" class="form-control" placeholder="••••••••" required>
                            <span class="input-group-text" onclick="togglePassword()">
                                <i class="fa-solid fa-eye text-muted" id="eyeIcon"></i>
                            </span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">LOG IN</button>
                </form>
            </div>
            <p class="text-center mt-4 text-muted small">&copy; 2026 Levina Rental System</p>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passInput = document.getElementById("passInput");
    const eyeIcon = document.getElementById("eyeIcon");
    
    if (passInput.type === "password") {
        passInput.type = "text";
        eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        passInput.type = "password";
        eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
    }
}
</script>

</body>
</html>