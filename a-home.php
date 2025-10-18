<?php
session_start();
require_once 'resource/php/init.php';
require_once 'resource/php/class/Auth.php';
Auth::requireAccountType('Admin');


if (basename($_SERVER['PHP_SELF']) !== 'change-pass.php') {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}

$config = new config();
$pdo = $config->con();

$filter_status = $_GET['status'] ?? 'ALL';

$sql = "SELECT 
            r.request_id,
            r.request_date,
            c.cart_id,
            c.cart_status, 
            u.first_name, 
            u.last_name, 
            u.account_type,
            GROUP_CONCAT(DISTINCT inv.product_type SEPARATOR ', ') AS product_types
        FROM tbl_requests AS r
        JOIN tbl_cart AS c ON r.cart_id = c.cart_id
        JOIN tbl_users AS u ON c.user_id = u.user_id
        LEFT JOIN tbl_cart_items AS items ON c.cart_id = items.cart_id
        LEFT JOIN tbl_inventory AS inv ON items.product_id = inv.product_id
        WHERE c.cart_status != 'active'";

$params = [];

if ($filter_status !== 'ALL') {
    $sql .= " AND c.cart_status = ?";
    $params[] = $filter_status;
}

$sql .= " GROUP BY r.request_id ORDER BY r.request_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home for Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"  href="resource/css/home-admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

</head>
<body>

 <!-- 1st nav -->
<nav class="navbar navbar-expand-lg">
  <a class="navbar-brand" href="#">
    <img class="ceu-logo img-fluid" src="./resource/img/ceu-molecules.png" alt="CEU Molecules Logo"/>
  </a>
  
  <button class="navbar-toggler me-3 custom-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  
  <div class="d-none d-lg-block ms-auto">
      <ul class="navbar-nav pe-3">
        <li class="nav-item">
          <a class="nav-link text-white active" href="a-home.php">Requests</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="a-search.php">Inventory</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="change-pass.php">Change Password</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="logout.php">Logout</a>
        </li>
      </ul>
  </div>
</nav>


<!-- main content -->
<main class="home-admin">
    <div class="container">
      <h2 class="requests-heading">Requests</h2>
      <div class="filter-buttons">
            <a href="a-home.php" class="filter-btn <?= ($filter_status === 'ALL') ? 'active' : '' ?>">ALL</a>
            <a href="a-home.php?status=pending" class="filter-btn <?= ($filter_status === 'pending') ? 'active' : '' ?>">Submitted</a>
            <a href="a-home.php?status=approved" class="filter-btn <?= ($filter_status === 'Approved') ? 'active' : '' ?>">Faculty Approved</a>
            <a href="a-home.php?status=pickup" class="filter-btn <?= ($filter_status === 'Pickup') ? 'active' : '' ?>">For Pick-up</a>
            <a href="a-home.php?status=completed" class="filter-btn <?= ($filter_status === 'Completed') ? 'active' : '' ?>">Completed</a>
            <a href="a-home.php?status=returned" class="filter-btn <?= ($filter_status === 'Returned') ? 'active' : '' ?>">Returned</a>
            <a href="a-home.php?status=canceled" class="filter-btn <?= ($filter_status === 'Canceled') ? 'active' : '' ?>">Canceled</a>
            <a href="a-home.php?status=disapproved" class="filter-btn <?= ($filter_status === 'Disapproved') ? 'active' : '' ?>">Disapproved</a>

            <div class="row">
                <?php if (empty($requests)): ?>
                    <div class="col-12"><p class="text-center fs-4 mt-5">No requests found.</p></div>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <div class="col-12 mb-3">
                            <div class="request-card">
                                <div class="request-details-container">
                                    <div class="request-text">
                                        <h5 class="request-title">
                                            <?= htmlspecialchars($request['product_types'] ?? 'General') ?> Request
                                        </h5>
                                        <p class="request-info">From: <?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?> (<?= htmlspecialchars($request['account_type']) ?>)</p>
                                        <p class="request-info">Status: <?= htmlspecialchars($request['cart_status']) ?></p>
                                    </div>
                                </div>
                                <div class="right-column-container">
                                    <div class="request-timestamp">
                                        <span class="timestamp-text"><?= date('m/d/Y - g:ia', strtotime($request['request_date'])) ?></span>
                                    </div>
                                    <div class="view-button-container">
                                        <a href="a-order-details.php?id=<?= $request['request_id'] ?>" class="view-button-btn">View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
      </div>
    </div>
</main>

<!-- footer -->
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

</body>
</html>