<?php
session_start();
require_once 'resource/php/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'Admin' || !isset($_GET['id'])) {
    header('Location: login.php');
    exit();
}

$config = new config();
$pdo = $config->con();
$request_id = $_GET['id'];

$sql_request = "SELECT r.*, u.first_name, u.last_name, c.cart_status
                FROM tbl_requests r
                JOIN tbl_cart c ON r.cart_id = c.cart_id
                JOIN tbl_users u ON c.user_id = u.user_id
                WHERE r.request_id = ?";
$stmt_request = $pdo->prepare($sql_request);
$stmt_request->execute([$request_id]);
$details = $stmt_request->fetch(PDO::FETCH_ASSOC);

if (!$details) {
    header('Location: home-admin.php');
    exit();
}

$sql_items = "SELECT i.amount, inv.name, inv.measure_unit 
              FROM tbl_cart_items i
              JOIN tbl_inventory inv ON i.product_id = inv.product_id
              WHERE i.cart_id = ?";
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$details['cart_id']]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Order Details Page</title>
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
<nav class="navbar">
  <a class="navbar-brand" href="#">
    <img class="ceu-logo img-fluid" src="./resource/img/ceu-molecules.png" />
  </a>
  <button class="navbar-toggler me-3 custom-toggler" type="button" data-bs-toggle="offcanvas"
    data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
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
          <a class="nav-link active text-white" aria-current="page" href="home-admin.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#">Change Password</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="admin-search.php">Search</a>
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
<main class="admin-order-details-page">
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="request-form-card">
                        <form method="post" action="">
                            <h4 class="request-details-title mt-1 mb-3 text-center">Request Details</h4>
                            
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">Name of Requester:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($details['first_name'] . ' ' . $details['last_name']) ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Subject:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($details['subject']) ?>" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Room:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($details['room']) ?>" readonly>
                                </div>
                            </div>

                            <div class="row mb-4 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Date of Use (From):</label>
                                    <input type="date" class="form-control" value="<?= date('Y-m-d', strtotime($details['date_from'])) ?>" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To:</label>
                                    <input type="date" class="form-control" value="<?= date('Y-m-d', strtotime($details['date_to'])) ?>" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Time (From):</label>
                                    <input type="time" class="form-control" value="<?= htmlspecialchars($details['time_from']) ?>" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Time (To):</label>
                                    <input type="time" class="form-control" value="<?= htmlspecialchars($details['time_to']) ?>" readonly>
                                </div>
                            </div>
                            
                            <h4 class="request-details-title mt-1 mb-3">Items:</h4>
                            <div id="request-list-container">
                                <ul class="list-group">
                                    <?php foreach ($items as $item): ?>
                                        <li class="list-group-item">
                                            <?= htmlspecialchars($item['name']) ?> - 
                                            <strong>Amount:</strong> <?= htmlspecialchars($item['amount']) ?> <?= htmlspecialchars($item['measure_unit']) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="mb-4 mt-4">
                                <label for="remarks" class="form-label request-details-title remarks">Remarks:</label>
                                <div class="remarks-container">
                                    <textarea class="form-control" id="remarks" name="remarks" rows="4" placeholder="Add remarks here..."></textarea>
                                    <button type="button" class="btn edit-remarks-btn">Edit Remarks</button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">            
                              <div class="status-container">
                                  <button type="submit" class="btn finalize-btn" name="view-btn">View Form</button>
                                  <a href="home-admin.php" type="submit" class="btn finalize-btn ms-3">Cancel</a>
                              </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
  </main>


<footer>
  <div class="container-fluid">
    <p class="text-center text-white pt-2">
      <small>CEU MALOLOS MOLECULES || <strong>Chemical Laboratory: sample@ceu.edu.ph</strong><br>
        <i class="fa-regular fa-copyright"></i> 2025 Copyright <strong>CENTRO ESCOLAR UNIVERSITY MALOLOS, Chemical
          Laboratory</strong><br>
        Developed by <strong>Renz Matthew Magsakay (official.renzmagsakay@gmail.com), Krizia Jane Lleva
          (lleva2234517@mls.ceu.edu.ph) & Angelique Mae Gabriel (gabriel2231439@mls.ceu.edu.ph)</strong>
      </small>
    </p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>

</html>