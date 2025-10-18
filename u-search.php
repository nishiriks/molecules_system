<?php
session_start();
require_once 'resource/php/init.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$config = new config();
$pdo = $config->con();
$page_title = "All Products";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    $page_title = "Searching for: \"" . htmlspecialchars($search_term) . "\"";

    $sql = "SELECT * FROM tbl_inventory WHERE name LIKE ? ORDER BY name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$search_term%"]);
}
else if (isset($_GET['type']) && !empty($_GET['type'])) {
    $product_type = $_GET['type'];
    $page_title = "Showing: " . htmlspecialchars($product_type);
    
    $sql = "SELECT * FROM tbl_inventory WHERE product_type = ? ORDER BY name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_type]);
} 
else {
    $sql = "SELECT * FROM tbl_inventory ORDER BY name ASC";
    $stmt = $pdo->query($sql);
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search for Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"  href="resource/css/search.css">
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

<!-- nav -->
<nav class="sidebar close">
  <header>
    <div class="toggle-container">
      <i class="fa-solid fa-angle-right toggle"></i>
    </div>
  </header>

  <div class="menu-bar">
      <ul class="menu-links">
      <li class="search-box">
          <i class="fa-solid fa-magnifying-glass icon"></i>
          <input type="search" placeholder=" Search..." class="search-input">
      </li>
      <li class="nav-links chemicals-btn">
        <a href="u-search.php?type=Chemical">
          <i class="fa-solid fa-flask icon"></i>
          <span class="text nav-text">Chemicals</span>
        </a>
      </li>
      <li class="nav-links chemicals-btn">
        <a href="u-search.php?type=Supplies">
          <i class="fa-solid fa-prescription-bottle icon"></i>
          <span class="text nav-text">Supplies</span>
        </a>
      </li>
      <li class="nav-links chemicals-btn">
        <a href="u-search.php?type=Models">
          <i class="fa-solid fa-diagram-project  icon"></i>
          <span class="text nav-text">Model/Charts</span>
        </a>
      </li>
      <li class="nav-links chemicals-btn">
        <a href="u-search.php?type=Equipment">
          <i class="fa-solid fa-microscope icon"></i>
          <span class="text nav-text">Equipments</span>
        </a>
      </li>
      <li class="nav-links chemicals-btn">
        <a href="u-search.php?type=Specimen">
          <i class="fa-solid fa-vial icon"></i>
          <span class="text nav-text">Specimens</span>
        </a>
      </li>
      <li class="nav-links chemicals-btn">
        <a href="u-search-others.php">
          <i class="fa-solid fa-ellipsis icon"></i>
          <span class="text nav-text">Others</span>
        </a>
      </li>
    </ul>

    <div class="bottom-content">
  <li class="nav-links">
    <a href="logout.php">
      <i class="fa-solid fa-arrow-right-from-bracket icon"></i>
      <span class="text nav-text">Logout</span>
    </a>
  </li>
</div>
</div>
</nav>

<!-- main content for user page-->
<main class="user-page content">
<div class="container-fluid">
    <div class="row row-cols-1 row-cols-md-4 row-cols-lg-4 g-3">
      <?php if (empty($products)): ?>
        <div class="col-12">
            <div class="card p-5 text-center">
                <p class="fs-4 mt-3">No products found in this category.</p>
                <a href="u-search.php" class="btn btn-primary mt-3 mx-auto" style="max-width: 250px;">View All Products</a>
            </div>
        </div>
      <?php else: ?>
         <?php foreach ($products as $product): ?>
            <div class="col product-col">
                <div class="card h-100">
                    <img class="card-img-top" src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="card-body">
                        <h5 class="card-text"><?= htmlspecialchars($product['name']) ?></h5>
                        <h5 class="card-text stock-text">
                            Stock: <?= htmlspecialchars($product['stock']) ?> <?= htmlspecialchars($product['measure_unit']) ?>
                        </h5>
                        <div class="info">
                             <!-- <form action="cartAction.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?//= $product['product_id'] ?>">
                                <button type="submit" class="btn-request">Request</button>
                            </form> -->
                            <button class="btn-view" 
                                    data-product-id="<?= $product['product_id'] ?>"
                                    data-type="<?= htmlspecialchars($product['product_type']) ?>"
                                    data-image="<?= htmlspecialchars($product['image_path']) ?>"
                                    data-stock="<?= htmlspecialchars($product['stock']) ?>">
                                View Product
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
</div>


<!-- pop-up page -->

<div id="equipment-popup" class="product-popup">
  <div class="popup-content">
    <button class="close-btn">&times;</button>
    <div class="popup-image-container">
      <img class="popup-image" src="./resource/img/hydrochloric-acid.jpg" alt="Microscope Equipment">
    </div>
    <div class="popup-details">
      <div class="equipment-title-container">
        <div class="equipment-titles-group">
          <h5 class="equipment-title">Microscope</h5>
          <h5 class="popup-product-type equip-title">Equipment</h5>
        </div>
        <span class="stock-info">Stock: 5</span>
      </div>
      <h5 class="reservation-title mb-3">Reservation Queue</h5>
      <div class="reservation-item">
        August 31, 2025 - PHL 301 - Biology 101
      </div>
      <div class="reservation-item">
        August 31, 2025 - PHL 301 - Biology 101
      </div>
    </div>
    <div class="request-button-container">
      <form action="cartAction.php" method="POST">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" id="equipment-popup-product-id" value="">

          <div class="quantity-control-container">
              <button type="button" class="quantity-btn" id="equipment-decrement-btn">-</button>
              <input type="number" class="quantity-input" name="quantity" id="equipment-quantity-input" value="1" min="1">
              <button type="button" class="quantity-btn" id="equipment-increment-btn">+</button>
          </div>

          <button type="submit" class="request-button">Request</button>
      </form>
    </div>
  </div>
</div>

<div id="chemical-popup" class="product-popup">
    <div class="popup-content">
        <button class="close-btn">&times;</button>
        <div class="popup-image-container">
            <img class="popup-image" src="./resource/img/hydrochloric-acid.jpg" alt="Hydrochloric Acid">
        </div>
        <div class="popup-details">
            <div class="chemical-info-header">
                <div class="chemical-titles">
                    <h5 class="chemical-title">Hydrochloric Acid</h5>
                    <h5 class="popup-product-type chem-title"></h5>
                </div>
                <span class="stock-info">Stock: 5 ml</span>
            </div>
        </div>
        <div class="request-button-container">
          <form action="cartAction.php" method="POST">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" id="chemical-popup-product-id" value="">

              <div class="quantity-control-container">
                  <button type="button" class="quantity-btn" id="chemical-decrement-btn">-</button>
                  <input type="number" class="quantity-input" name="quantity" id="chemical-quantity-input" value="1" min="1">
                  <button type="button" class="quantity-btn" id="chemical-increment-btn">+</button>
              </div>

              <button type="submit" class="request-button">Request</button>
          </form>
        </div>
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

<!-- bubble nav -->
<input type="checkbox" class="nav--checkbox" id="nav-toggle">

<label for="nav-toggle" class="nav--button">
    <span>&nbsp;</span>
</label>

<div class="nav--small nav--btn-1">
    <a href="u-cart.php"><i class="fa-solid fa-cart-shopping cart-icon"></i></a>
</div>

<div class="nav--small nav--btn-2">
    <a href="index.php"><i class="fa-solid fa-house house-icon"></i></a>
</div>

<div class="nav--small nav--btn-3">
    <a href="change-pass.php"><i class="fa-solid fa-lock-open"></i></a>
</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

  <script src="resource/js/script.js"></script>
    
</body>
</html>
    
