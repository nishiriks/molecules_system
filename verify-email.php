<?php
session_start();
require_once './resource/php/init.php';
require_once './resource/php/class/Auth.php';

$auth = new Auth();
$message = '';
$message_type = 'danger';

if (isset($_GET['code'])) {
    $verification_code = $_GET['code'];
    $result = $auth->verifyEmail($verification_code);
    
    if ($result === true) {
        $message = "Email verified successfully! You can now log in to your account.";
        $message_type = 'success';
    } else {
        $message = $result;
    }
} else {
    $message = "Invalid verification link.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Molecules CEU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verification-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="verification-card p-5 text-center">
                    <img src="resource/img/molecules-logo.png" class="mb-4" style="max-width: 150px;">
                    <h2 class="mb-4">Email Verification</h2>
                    
                    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                        <?= $message ?>
                    </div>
                    
                    <?php if ($message_type === 'success'): ?>
                        <a href="login.php" class="btn btn-primary btn-lg w-100">Proceed to Login</a>
                    <?php else: ?>
                        <div class="d-grid gap-2">
                            <a href="login.php" class="btn btn-primary">Go to Login</a>
                            <a href="sign-up.php" class="btn btn-outline-secondary">Back to Sign Up</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>