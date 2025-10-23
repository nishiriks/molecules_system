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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"  href="resource/css/style.css">
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
                <div class="verification-card p-5 text-center">
                    <img src="resource/img/molecules-logo.png" class="mb-4" style="max-width: 150px;">
                    <h2 class="greetings fw-bold mb-4">Email Verification</h2>
                    
                    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                        <?= $message ?>
                    </div>
                    
                    <?php if ($message_type === 'success'): ?>
                        <a href="login.php" class="btn btn-primary btn-lg w-100">Proceed to Login</a>
                    <?php else: ?>
                        <div class="d-grid gap-2">
                            <a href="login.php" class="g-log btn btn-primary">Go to Login</a>
                            <a href="sign-up.php" class="g-back btn btn-outline-secondary">Back to Sign Up</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>