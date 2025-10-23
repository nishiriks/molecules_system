<?php
session_start();
require_once './resource/php/init.php';
require_once './resource/php/class/Auth.php';

$auth = new Auth();
$message = '';
$message_type = '';
$valid_token = false;
$email = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $result = $auth->validateResetToken($token);
    
    if ($result === true) {
        $valid_token = true;
        // Get email from token for hidden field
        $user_data = $auth->getUserByResetToken($token);
        $email = $user_data['email'];
    } else {
        $message = $result;
        $message_type = 'danger';
    }
} else {
    $message = "Invalid reset link.";
    $message_type = 'danger';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];
    
    $result = $auth->resetPassword($email, $new_password, $confirm_password);
    if ($result === true) {
        $message = "Password has been reset successfully! You can now login with your new password.";
        $message_type = 'success';
        $valid_token = false; // Hide form after success
    } else {
        $message = $result;
        $message_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Molecules CEU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="resource/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <section class="log-in d-flex justify-content-center align-items-center">
        <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="reset-card p-5">
                    <div class="text-center mb-4">
                        <img src="resource/img/molecules-logo.png" class="mb-3" style="max-width: 150px;">
                        <h2 class="greetings fw-bold">Reset Password</h2>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <?= $message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($valid_token): ?>
                    <form method="POST" action="reset-password.php?token=<?= $_GET['token'] ?>">
                        <input type="hidden" name="email" value="<?= $email ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" placeholder="Enter new password" required minlength="8">
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Confirm new password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">Reset Password</button>
                    </form>
                    <?php endif; ?>
                    
                    <div class="text-center">
                        <a href="login.php" class="text-decoration-none">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>