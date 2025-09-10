<?php
session_start();
require_once './resource/php/init.php';
require_once './resource/php/class/cartItems.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$config = new config();
$pdo = $config->con();
$cart = new CartItems($pdo, $_SESSION['user_id']);

if (isset($_POST['finalize-btn'])) {
    header('Location: home-admin.php'); 
    exit();
}

$items_in_cart = $cart->getItems();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Page (Equipment)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"  href="resource/css/home-admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">


    <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>

</head>
<body>
    <nav class="navbar">
      <a class="navbar-brand" href="#">
        <img class="ceu-logo img-fluid" src="./resource/img/ceu-molecules.png"/>
      </a>
      <button class="navbar-toggler me-3 custom-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="offcanvasNavbarLabel">CEU Molecules</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
            <li class="nav-item">
              <a class="nav-link active text-white" aria-current="page" href="#">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="#">Profile</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="user-search.php">Search</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="#">Requests</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="#">About</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="#">Help</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="logout.php">Logout</a>
            </li>
          </ul>
        </div>
    </nav>

    <main class="equipment-page">
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="request-form-card">
                        <form method="post" action="">
                          <div class="row mb-3 align-items-end">
                            <div class="col-md">
                                <label for="name" class="form-label">Name of Instructor or Graduate Student:</label>
                                <input type="text" class="form-control" id="name" name="prof_name" placeholder="Enter name" required>
                            </div>
                            <div class="col-md">
                                <label for="signature" class="form-label">Attach Signature:</label>
                                <input type="file" class="form-control" id="signature" name="signature" required>
                            </div>
                            <div class="col-md">
                                <label for="subject" class="form-label">Subject:</label>
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Enter subject" required>
                            </div>
                        </div>
                            
                        <div class="row mb-4 align-items-end">
                            <div class="col-md-3">
                                <label for="date-from" class="form-label">Date of Use (From):</label>
                                <input type="date" class="form-control" id="date-from" name="date_from" required>
                            </div>
                            <div class="col-md-3">
                                <label for="date-to" class="form-label">To (Disregard if one day use):</label>
                                <input type="date" class="form-control" id="date-to" name="date_to">
                            </div>
                            <div class="col-md-2">
                                <label for="time-from" class="form-label">Time (From):</label>
                                <input type="time" class="form-control" id="time-from" name="time_from" required>
                            </div>
                            <div class="col-md-2">
                                <label for="time-to" class="form-label">Time (To):</label>
                                <input type="time" class="form-control" id="time-to" name="time_to" required>
                            </div>
                            <div class="col-md-2">
                                <label for="room" class="form-label">Room:</label>
                                <input type="text" class="form-control" id="room" name="room" placeholder="Enter Room" required>
                            </div>
                        </div>
                            <h4 class="request-details-title mt-4 mb-3">Request Details:</h4>
                            <div id="request-list-container">
                                <?php foreach ($items_in_cart as $item): ?>
                                    <div class="request-item-card d-flex align-items-center mb-3">
                                        <div class="item-details-simple flex-grow-1">
                                            <h5 class="item-name mb-0">
                                                <?= htmlspecialchars($item['name']) ?> - Amount: <?= htmlspecialchars($item['amount']) ?>
                                            </h5>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn finalize-btn" name="finalize-btn">Finalize Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
      <div class="container-fluid">
        <p class="text-center text-white pt-2"><small>
          CEU MALOLOS MOLECULES || <strong>Chemical Laboratory: sample@ceu.edu.ph</strong><br>
          <i class="fa-regular fa-copyright"></i> 2025 Copyright <strong>CENTRO ESCOLAR UNIVERSITY MALOLOS, Chemical Laboratory</strong><br>
          Developed by <strong>Renz Matthew Magsakay (official.renzmagsakay@gmail.com), Krizia Jane Lleva (lleva2234517@mls.ceu.edu.ph) & Angelique Mae Gabriel (gabriel2231439@mls.ceu.edu.ph)</strong>
          </small>
        </p>
      </div>
    </footer>

    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    
  </body>
</html>