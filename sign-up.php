<?php
require_once './resource/php/init.php';
require_once './resource/php/class/Auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$auth = new Auth();
$errors = [];

// Store data
$stored_data = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'student_number' => ''
];

$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $pass  = $_POST['password'];
    $cpass = $_POST['confirm_password'];
    $snum  = $_POST['student_number'];

    $stored_data = [
        'first_name' => htmlspecialchars($fname),
        'last_name' => htmlspecialchars($lname),
        'email' => htmlspecialchars($email),
        'student_number' => htmlspecialchars($snum)
    ];

    // Email domain validation
    $valid_ceu_domains = ['@ceu.edu.ph', '@mls.ceu.edu.ph'];
    $user_email_domain = strtolower(substr($email, strpos($email, '@')));

    if (!in_array($user_email_domain, $valid_ceu_domains)) {
        $errors[] = "Registration is only for CEU Students and Staff.";
    }

    // Student number format validation (if provided)
    if (!empty($snum)) {
        if (!preg_match('/^\d{4}-\d{5}$/', $snum)) {
            $errors[] = "Invalid student number format. Must be XXXX-XXXXX (e.g., 2022-12345)";
        }
    }

    // Errors validation
    if (empty($errors)) {
        $errors = $auth->register($fname, $lname, $email, $pass, $cpass, $snum);

        if (empty($errors)) {
            $success_message = "Registration successful! We've sent a verification link to your email. Please verify your email before logging in.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign-up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="resource/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body class="signup-page">
    <!--pop-up-->
    <div class="col-md-6">
        <div id="successAlert"
            class="alert alert-success alert-dismissible fade d-none"
            role="alert">
            We have sent a verification link to your email!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>

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
                            <h2 class="greetings fw-bold mb-1">Sign up</h2>
                            <form method="POST" action="sign-up.php">
                                <div class="row mb-3 mt-3">
                                    <div class="col-md-6">
                                        <label class="label-text">First Name:</label>
                                        <input type="text" class="form-control" name="first_name" placeholder="Enter your first name" value="<?= $stored_data['first_name'] ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="label-text">Last Name:</label>
                                        <input type="text" class="form-control" name="last_name" placeholder="Enter your last name" value="<?= $stored_data['last_name'] ?>" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="label-text">CEU Email:</label>
                                        <input type="email" class="form-control" name="email" placeholder="Enter your email" value="<?= $stored_data['email'] ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="label-text">Student Number:</label>
                                        <input type="text" class="form-control" name="student_number" placeholder="XXXX-XXXXX" value="<?= $stored_data['student_number'] ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="label-text">Password:</label>
                                        <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="label-text">Confirm Password:</label>
                                        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm password" required>
                                    </div>
                                </div>

                                <button class="log-button btn btn-primary w-100 mb-2">Create Account</button>
                                <a href="login.php"><i class=" icon-i fa-solid fa-arrow-right"></i></a>
                            </form>
                        </div>
                    </div>
                    <br>
                    <?php
                    // Show success message
                    if (!empty($success_message)) {
                        echo "
                            <div class='alert alert-success alert-dismissible fade show' role='alert'>
                                {$success_message}
                                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                            </div>
                            ";
                    }

                    // Show error messages
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

<script>
    function showAlert() {
        const alertBox = document.getElementById("successAlert");
        alertBox.classList.remove("d-none");
        alertBox.classList.add("show");
    }

    // Auto-show success message if exists
    <?php if (!empty($success_message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showAlert();
        });
    <?php endif; ?>
</script>