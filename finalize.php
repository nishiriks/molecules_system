<?php
session_start();
require_once './resource/php/init.php';
require_once './resource/php/class/cartItems.php';
require_once './resource/php/class/requestForm.php';

$showAlert = false;
if (isset($_SESSION['show_finalized_alert']) && $_SESSION['show_finalized_alert'] === true) {
    $showAlert = true;
    unset($_SESSION['show_finalized_alert']);
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$config = new config();
$pdo = $config->con();
$cart = new CartItems($pdo, $_SESSION['user_id']);

// ✅ Fetch holidays once for later validation + JS
$stmt = $pdo->query("SELECT holiday_date_from, holiday_date_to, holiday_type FROM tbl_holidays");
$holidays = $stmt->fetchAll(PDO::FETCH_ASSOC);

$blockedDates = [];
foreach ($holidays as $holiday) {
    $h_to = $holiday['holiday_date_to'] ?: $holiday['holiday_date_from'];

    if (strtolower($holiday['holiday_type']) === 'recurring holiday') {
        $blockedDates[] = [
            'type' => 'recurring',
            'from' => date('m-d', strtotime($holiday['holiday_date_from'])),
            'to'   => date('m-d', strtotime($h_to))
        ];
    } else {
        $blockedDates[] = [
            'type' => 'once',
            'from' => $holiday['holiday_date_from'],
            'to'   => $h_to
        ];
    }
}

// --- FORM PROCESSING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalize-btn'])) {
    $active_cart_id = $cart->getActiveCartId();

    if ($active_cart_id) {
        $date_from = $_POST['date_from'] ?? '';
        $date_to   = $_POST['date_to'] ?? '';

        $today = date('Y-m-d');

        // block past dates
        if (strtotime($date_from) < strtotime($today)) {
            die("❌ Date From cannot be earlier than today.");
        }

        // ✅ Block Sundays only if start or end falls on Sunday
        if (date('w', strtotime($date_from)) == 0) {
            die("❌ Date From cannot be Sunday.");
        }
        if (!empty($date_to) && date('w', strtotime($date_to)) == 0) {
            die("❌ Date To cannot be Sunday.");
        }

        // Date To must be >= Date From
        if (!empty($date_to) && strtotime($date_to) < strtotime($date_from)) {
            die("❌ Date To cannot be before Date From.");
        }

        // ✅ Block holidays server-side (applies to all days in range)
        $userDates = [];
        $start = new DateTime($date_from);
        $end   = new DateTime($date_to ?: $date_from);
        $end->modify('+1 day');
        $interval = new DateInterval('P1D');
        foreach (new DatePeriod($start, $interval, $end) as $dt) {
            $userDates[] = $dt->format('Y-m-d');
        }

        foreach ($userDates as $uDate) {
            $monthDay = date('m-d', strtotime($uDate));
            foreach ($blockedDates as $block) {
                if ($block['type'] === 'once') {
                    if ($uDate >= $block['from'] && $uDate <= $block['to']) {
                        die("❌ Selected dates fall on a holiday/break.");
                    }
                } else {
                    if ($monthDay >= $block['from'] && $monthDay <= $block['to']) {
                        die("❌ Selected dates fall on a recurring holiday.");
                    }
                }
            }
        }

        $request = new requestForm(
            $_POST['prof_name'],
            $_POST['subject'],
            $date_from,
            $date_to,
            $_POST['time_from'],
            $_POST['time_to'],
            $_POST['room'],
            'pending'
        );
        
        $request->reqOrder($active_cart_id);
        $cart->finalizeRequest($_POST);
        $_SESSION['show_finalized_alert'] = true;
    }

    header('Location: equipment.php');
    exit();
}

// get cart items including product_type
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
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
  </head>
<body>
    <nav class="navbar">
      <a class="navbar-brand" href="#">
        <img class="ceu-logo img-fluid" src="./resource/img/ceu-molecules.png"/>
      </a>
      <button class="navbar-toggler me-3 custom-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title">CEU Molecules</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
          <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
            <li class="nav-item"><a class="nav-link text-white" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="#">Profile</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="user-search.php">Search</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="cart.php">Requests</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="#">About</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="#">Help</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="logout.php">Logout</a></li>
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
                            <div class="col-md-5">
                                <label for="name" class="form-label">Name of Instructor or Graduate Student:</label>
                                <input type="text" class="form-control" id="name" name="prof_name" placeholder="Enter name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="subject" class="form-label">Subject:</label>
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Enter subject" required>
                            </div>
                            <div class="col-md-3">
                                <label for="room" class="form-label">Room:</label>
                                <input type="text" class="form-control" id="room" name="room" placeholder="Enter Room" required>
                            </div>
                        </div>
                            
                        <div class="row mb-4 align-items-end">
                            <div class="col-md-3">
                                <label for="date-from" class="form-label">Date of Use (From):</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" required>
                            </div>
                            <div class="col-md-3">
                                <label for="date-to" class="form-label">To: (Input same day for 1-day use)</label>
                                <input type="date" class="form-control" id="date_to" name="date_to">
                            </div>
                            <div class="col-md-2">
                                <label for="time-from" class="form-label">Time (From):</label>
                                <input type="time" class="form-control" id="time_from" name="time_from" required>
                            </div>
                            <div class="col-md-2">
                                <label for="time-to" class="form-label">Time (To):</label>
                                <input type="time" class="form-control" id="time_to" name="time_to" required>
                            </div>
                        </div>

                            <h4 class="request-details-title mt-4 mb-3">Request Details:</h4>
                            <div id="request-list-container">
                                <?php foreach ($items_in_cart as $item): ?>
                                    <div class="request-item-card d-flex align-items-center mb-3">
                                        <div class="item-details-simple flex-grow-1">
                                            <h5 class="item-name mb-0">
                                                <?= htmlspecialchars($item['name']) ?> 
                                                (<?= htmlspecialchars($item['product_type']) ?>)
                                                - Amount: <?= htmlspecialchars($item['amount']) ?>
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
          <i class="fa-regular fa-copyright"></i> 2025 Copyright 
          <strong>CENTRO ESCOLAR UNIVERSITY MALOLOS, Chemical Laboratory</strong><br>
          Developed by <strong>Renz Matthew Magsakay, Krizia Jane Lleva & Angelique Mae Gabriel</strong>
          </small>
        </p>
      </div>
    </footer>

    <script>
      // pass blocked dates to JS
      const blockedDates = <?php echo json_encode($blockedDates); ?>;
    </script>
    <script src="resource/js/finalize.js"></script>

    <?php if ($showAlert): ?>
    <script type="text/javascript">
        alert('Request submitted for approval');
        window.location.href = 'index.php';
    </script>
    <?php endif; ?>
    
  </body>
</html>
