<?php
session_start();
require_once 'resource/php/init.php';
require_once 'resource/php/class/cartItems.php';
require_once 'resource/php/class/Auth.php';
Auth::requireUserAccess();

if (basename($_SERVER['PHP_SELF']) !== 'change-pass.php') {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}

$config = new config();
$pdo = $config->con();
$cart = new CartItems($pdo, $_SESSION['user_id']);
$items_in_cart = $cart->getItems();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css"  href="resource/css/home-admin.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>
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
            <a class="nav-link active text-white" aria-current="page" href="u-about.php">About</a>
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

    <main class="user-cart">
        <div class="container">
            <h2 class="requests-heading mt-2">My Cart</h2>
            <div class="row">
                <?php if (empty($items_in_cart)): ?>
                    <div class="col-12">
                        <p class="text-center fs-4 mt-5">Your cart is empty.</p>
                        <div class="text-center">
                            <a href="u-search.php" class="btn btn-primary">Browse Products</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($items_in_cart as $item): ?>
                        <?php 
                        $img = $item['image_path'] ?? 'resource/img/default.png';
                        $name = $item['name'] ?? 'Unnamed Item';
                        $ptype = $item['product_type'] ?? 'No type';
                        $amount = $item['amount'] ?? 0;
                        $measure = $item['measure_unit'] ?? '';
                        ?>
                        <div class="col-12 mb-3">
                            <div class="cart-card-item">
                                <div class="item-details-left">
                                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>" class="item-img img-fluid">
                                    <div class="item-info">
                                        <h5 class="item-name"><?= htmlspecialchars($name) ?></h5>
                                        <p class="item-type"><?= htmlspecialchars($ptype) ?></p>
                                    </div>
                                </div>
                                <div class="item-details-right">
                                    <div class="item-amount">Amount: <?= htmlspecialchars($amount) ?> <?= htmlspecialchars($measure) ?></div>
                                    <div class="item-actions">
                                        <button class="edit-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#edit-popup"
                                                data-item-id="<?= $item['item_id'] ?>"
                                                data-item-name="<?= htmlspecialchars($name) ?>"
                                                data-item-amount="<?= htmlspecialchars($amount) ?>">
                                            Edit
                                        </button>

                                        <form action="cartAction.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                            <button type="submit" class="remove-btn">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($items_in_cart)): ?>
                <div class="finalize-btn-container text-end mt-4">
                    <a href="u-finalize.php" class="finalize-request-btn" style="text-decoration: none;">Finalize Request</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="modal fade" id="edit-popup" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edit-popup-title">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-form" action="cartAction.php" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" id="edit-item-id" name="item_id" value="">
                        
                        <div class="mb-3">
                            <label for="edit-item-name" class="form-label">Item Name:</label>
                            <input type="text" id="edit-item-name" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-item-amount" class="form-label">Amount:</label>
                            <input type="number" id="edit-item-amount" name="amount" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 save-btn">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="resource/js/scripts.js"></script>
