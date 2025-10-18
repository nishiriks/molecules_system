<?php
session_start();
require_once 'resource/php/init.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

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
    <main class="equipment-page">
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="request-form-card">
                        <form method="post" action="">
                          <div class="row mb-3 align-items-end">
                            <div class="col-md">
                                <label for="name" class="form-label">Item Name:</label>
                                <input type="text" class="form-control" id="name" name="prof_name" placeholder="Enter item name" required>
                            </div>
                        </div>
                            
                        <div class="row mb-3 align-items-end">
                          <div class="col-md">
                                <label for="type" class="form-label">Product Type:</label>
                                <select name="type" id="type" class="form-select">
                                  <option value="Chemical">Chemical</option>
                                  <option value="Equipment">Equipment</option>
                                  <option value="Models">Models</option>
                                  <option value="Specimen">Specimen</option>
                                  <option value="Supplies">Supplies</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label for="amount" class="form-label">Quantity:</label>
                                <input type="number" class="form-control" id="amount" name="amount" placeholder="Enter Stock" min="1" required>
                            </div>
                            <div class="col-md">
                                <label for="unit" class="form-label">Unit Measure:</label>
                                <input type="text" class="form-control" id="unit" name="unit" placeholder="Enter Unit of Measurement" min="1" required>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn request-item-btn" name="request-item-btn">Request Item</button>
                            </div>
                        </form>
                    </div>
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
    
</body>
</html>
    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

<script src="resource/js/script.js"></script>