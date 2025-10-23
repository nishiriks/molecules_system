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
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add-item-btn'])) {
    $item_name = $_POST['item_name'];
    $product_type = $_POST['type'];
    $quantity = $_POST['amount'];
    $unit = $_POST['unit'];
    
    $image_path = './resource/img/default.png'; 

    // Determine if item is consumable based on product type
    $is_consumable = 0; // Default to non-consumable
    if ($product_type === 'Chemical' || $product_type === 'Supplies' || $product_type === 'Specimen') {
        $is_consumable = 1;
    }

    $sql = "INSERT INTO tbl_inventory (name, product_type, stock, measure_unit, image_path, is_consumable) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$item_name, $product_type, $quantity, $unit, $image_path, $is_consumable])) {
        $success_message = "Item '$item_name' was added successfully!";
    } else {
        $success_message = "Error: Could not add the item.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Inventory</title>
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
            <a class="nav-link text-white" href="a-home.php">Requests</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="a-calendar.php">Calendar</a>
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

  <main class="equipment-page">
      <div class="container-fluid py-5">
          <div class="row justify-content-center">
              <div class="col-lg-8 col-md-10">
                  <div class="request-form-card">
                      <!-- Success Message -->
                      <?php if (!empty($success_message)): ?>
                          <div class="alert alert-success alert-dismissible fade show" role="alert">
                              <?php echo htmlspecialchars($success_message); ?>
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>
                      <?php endif; ?>
                      
                      <form method="post" action="">
                        <div class="row mb-3 align-items-end">
                          <div class="col-md">
                              <label for="name" class="form-label">Item Name:</label>
                              <input type="text" class="form-control" id="name" name="item_name" placeholder="Enter item name" required>
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
                              <button type="submit" class="btn finalize-btn" name="add-item-btn">Add Item</button>
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
</body>
</html>