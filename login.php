<?php 
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); 
    exit();
}

require_once './resource/php/init.php';
require_once './resource/php/class/Auth.php';
$auth = new Auth();

$errors = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_errors']);

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

if (isset($_GET['registered']) && $_GET['registered'] == 'success') {
    $success_message = "Registration successful! Please log in.";
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['user_email'];
    $password = $_POST['user_password'];

    $login_errors = $auth->login($email, $password); 

    if (!empty($login_errors)) {
        $_SESSION['login_errors'] = $login_errors;
        header('Location: login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log-in</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"  href="resource/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">


    <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>

</head>
<body class="login-page">
    <section class="log-in d-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="card custom-card p-0">
                    <div class="row">
                        <!-- Left Side -->
                        <div class="col-md-6">
                            <img src="resource/img/ceu-building.jpg" class="bg-img" />
                        </div>
                        <!-- Right side -->
                        <div class="col-md-6 p-4">
                            <img src="resource/img/molecules-logo.png" class="logo-img mb-3">
                            <h2 class="greetings fw-bold mb-1">Welcome Back!</h2>
                            <p class="text-email mb-4 d-flex flex-column align-items-center">Login with Email</p>
                            <form method="post">
                                <div class="mb-3 text-start input-wrapper">
                                    <label class="label-text">Email:</label>
                                    <input type="email" class="form-control d-flex flex-column align-items-center" placeholder="Enter email" name="user_email">
                                </div>
                                
                                <div class="mb-3 text-start input-wrapper">
                                    <label class="label-text">Password:</label>
                                    <input type="password" class="form-control" placeholder="Enter password" name="user_password">
                                </div>
                                
                                <div class="d-flex justify-content-between mb-3 input-wrapper">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="remember" />
                                        <label class=" form-check-label" for="remember">Remember Me</label>
                                    </div>
                                    <a href="forgot-password.php" class="label-t">Forgot your password?</a>
                                </div>
                                <button name="log-button" type="submit" class="log-button btn btn-primary w-100 mb-2">Log In</button>
                                <p class="log-footer">Don’t Have an Account? <a href="sign-up.php" class="sign-footer">Sign up here</a></p>
                            </form>
                            <?php //logUserMsg()?>
                        </div>
                    </div>
                    <br>
                    <?php
                            if (!empty($errors)) {
                            foreach ($errors as $error) {
                                $auth->showAlert($error);
                            }
                        }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>
</html>