<?php
session_start();
require_once 'resource/php/init.php';
require_once 'resource/php/class/Auth.php';

// Security: Redirect user if they are not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Store current page as previous page (except for change password page itself)
if (basename($_SERVER['PHP_SELF']) !== 'change-pass.php') {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}

// Determine back URL based on user role and session history
if (isset($_SESSION['previous_page']) && $_SESSION['previous_page'] !== 'change-pass.php') {
    $back_url = $_SESSION['previous_page'];
} else {
    // Fallback based on user role
    if (isset($_SESSION['user_role'])) {
        $back_url = ($_SESSION['user_role'] === 'admin') ? 'home-admin.php' : 'home-user.php';
    } else {
        $back_url = 'index.php';
    }
}

$auth = new Auth();
$errors = [];
$success_message = '';

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? null;
    $new_password = $_POST['new_password'] ?? null;
    $confirm_password = $_POST['confirm_password'] ?? null;

    // Check if new password is the same as current password
    if ($current_password === $new_password) {
        $errors[] = "New password cannot be the same as your current password.";
        exit();
    } else {
        $errors = $auth->changePassword(
            $_SESSION['user_id'],
            $current_password,
            $new_password,
            $confirm_password
        );
    }

    if (empty($errors)) {
        $success_message = "Password updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="resource/css/home-admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
</head>

<body>
    <nav class="navbar">
        <a class="navbar-brand" href="#">
            <img class="ceu-logo img-fluid" src="./resource/img/ceu-molecules.png" />
        </a>
    </nav>
    <main class="change-password-page">
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-6">
                    <div class="change-pass-card">
                        <a href="<?php echo htmlspecialchars($back_url); ?>" class="back-arrow">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div class="card-header text-center">
                            <h3 class="text-password mt-1 mb-3">Change Password</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success"><?= $success_message ?></div>
                            <?php endif; ?>
                            <?php if (!empty($errors)): ?>
                                <?php foreach ($errors as $error): ?>
                                    <div class="alert alert-danger"><?= $error ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-4 position-relative">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password" id="currentPassword" required>
                                    <i class="fa-solid fa-eye password-toggle-icon"></i>
                                </div>
                                <div class="mb-4 position-relative">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" id="newPassword" required>
                                    <i class="fa-solid fa-eye password-toggle-icon"></i>
                                </div>
                                <div class="mb-4 position-relative">
                                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required>
                                    <i class="fa-solid fa-eye password-toggle-icon"></i>
                                </div>
                                <div class="d-flex justify-content-center mt-4">
                                    <button type="submit" class="btn btn-update-password">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
    crossorigin="anonymous"></script>

<script src="resource/js/change-pass.js"></script>