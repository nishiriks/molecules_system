<?php
session_start();
require_once 'resource/php/init.php';
require_once 'resource/php/class/Auth.php';
Auth::requireUserAccess();

if (basename($_SERVER['PHP_SELF']) !== 'change-pass.php') {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}

$config = new config();
$pdo = $config->con();
$request_id = $_GET['id'];
$current_user_id = $_SESSION['user_id'];

// --- Query 1: Get the main request details with a security check ---
// This query ensures the request being viewed belongs to the currently logged-in user
$sql_request = "SELECT r.*, u.first_name, u.last_name, c.cart_status
                FROM tbl_requests r
                JOIN tbl_cart c ON r.cart_id = c.cart_id
                JOIN tbl_users u ON c.user_id = u.user_id
                WHERE r.request_id = ? AND c.user_id = ?"; // Security check here
$stmt_request = $pdo->prepare($sql_request);
$stmt_request->execute([$request_id, $current_user_id]);
$details = $stmt_request->fetch(PDO::FETCH_ASSOC);

// If no request was found (or it belongs to another user), redirect
if (!$details) {
    header('Location: user-request.php');
    exit();
}

// --- Query 2: Get the list of items for that request ---
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
  <title>User Order Details Page</title>
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
  <a class="navbar-brand" href="index.php">
    <img class="ceu-logo img-fluid" src="./resource/img/ceu-molecules.png"/>
  </a>
  <div class="right-side-icons">
    <a href="u-cart.php"><i class="fa-solid fa-cart-shopping cart-icon"></i></a>
      <button class="navbar-toggler me-3 custom-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="offcanvasNavbarLabel">CEU Molecules</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
        <li class="nav-item">
          <a class="nav-link text-white" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="change-pass.php">Change Password</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="u-search.php">Search</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="u-request.php">Requests</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="u-about.php">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="u-help.php">Help</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
</nav>
<main class="user-order-details-page">
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="request-form-card">
                        <form>
                            <h4 class="request-details-title mt-1 mb-3 text-center">Request Details: #<?= $details['request_id'] ?></h4>
                            
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">Name of Instructor or Graduate Student:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($details['prof_name']) ?>" readonly>
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
                                <label class="form-label request-details-title remarks">Remarks:</label>
                                <div class="remarks-container">
                                    <textarea class="form-control" rows="4" readonly><?= htmlspecialchars($details['remarks'] ?? 'No remarks.') ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <div class="status-container">
                                  <button type="submit" class="btn finalize-btn" name="view-btn">View Form</button>
                                  <a href="user-request.php" type="submit" class="btn finalize-btn ms-3">Cancel</a>
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
    integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
    crossorigin="anonymous"></script>
</body>

</html>