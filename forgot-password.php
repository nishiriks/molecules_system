<?php
session_start();
require_once './resource/php/init.php';
require_once './resource/php/class/Auth.php';

$auth = new Auth();
$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    if (empty($email)) {
        $message = "Please enter your email address.";
        $message_type = 'danger';
    } else {
        $result = $auth->sendPasswordReset($email);
        if ($result === true) {
            $message = "Password reset instructions have been sent to your email.";
            $message_type = 'success';
        } else {
            $message = $result;
            $message_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Molecules CEU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="resource/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .forgot-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="forgot-card p-5">
                    <div class="text-center mb-4">
                        <img src="resource/img/molecules-logo.png" class="mb-3" style="max-width: 150px;">
                        <h2 class="fw-bold">Forgot Password</h2>
                        <p class="text-muted">Enter your email to reset your password</p>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <?= $message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="forgot-password.php">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" placeholder="Enter your CEU email" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">Send Reset Link</button>
                        
                        <div class="text-center">
                            <a href="login.php" class="text-decoration-none">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>