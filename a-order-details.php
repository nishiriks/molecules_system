<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'resource/php/init.php';
require_once 'resource/php/class/Auth.php';
Auth::requireAccountType('Admin');

if (basename($_SERVER['PHP_SELF']) !== 'change-pass.php') {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}

$config = new config();
$pdo = $config->con();
$request_id = $_GET['id'] ?? '';

if (empty($request_id)) {
    header('Location: a-home.php');
    exit();
}

// Function to check if consumable items have sufficient stock
function checkSufficientStock($items) {
    $insufficient_items = [];
    foreach ($items as $item) {
        if ($item['is_consumables'] == 1) {
            if ($item['stock'] < $item['amount']) {
                $insufficient_items[] = [
                    'name' => $item['name'],
                    'requested' => $item['amount'],
                    'available' => $item['stock'],
                    'unit' => $item['measure_unit']
                ];
            }
        }
    }
    return $insufficient_items;
}

// Function to subtract consumable items from inventory
function subtractConsumableItems($pdo, $items) {
    foreach ($items as $item) {
        if ($item['is_consumables'] == 1) {
            $new_stock = $item['stock'] - $item['amount'];
            if ($new_stock < 0) $new_stock = 0;
            
            $sql_update = "UPDATE tbl_inventory SET stock = ? WHERE product_id = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$new_stock, $item['product_id']]);
        }
    }
}

// Function to add back consumable items to inventory
function addBackConsumableItems($pdo, $items) {
    foreach ($items as $item) {
        if ($item['is_consumables'] == 1) {
            $new_stock = $item['stock'] + $item['amount'];
            
            $sql_update = "UPDATE tbl_inventory SET stock = ? WHERE product_id = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$new_stock, $item['product_id']]);
        }
    }
}

$sql_request = "SELECT r.*, u.first_name, u.last_name, c.cart_status
                FROM tbl_requests r
                JOIN tbl_cart c ON r.cart_id = c.cart_id
                JOIN tbl_users u ON c.user_id = u.user_id
                WHERE r.request_id = ?";
$stmt_request = $pdo->prepare($sql_request);
$stmt_request->execute([$request_id]);
$details = $stmt_request->fetch(PDO::FETCH_ASSOC);

if (!$details) {
    header('Location: a-home.php');
    exit();
}

$sql_items = "SELECT i.amount, i.report_status, i.report_qty, inv.name, inv.measure_unit, inv.product_type, inv.product_id, inv.is_consumables, inv.stock
              FROM tbl_cart_items i
              JOIN tbl_inventory inv ON i.product_id = inv.product_id
              WHERE i.cart_id = ?";
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$details['cart_id']]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Check if there are any reported items (damaged or lost)
$has_reported_items = false;
foreach ($items as $item) {
    if (in_array($item['report_status'], ['Damaged', 'Lost'])) {
        $has_reported_items = true;
        break;
    }
}

// Handle Mark as Completed
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_completed'])) {
    $sql_update = "UPDATE tbl_requests SET status = 'Completed' WHERE request_id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$request_id]);
    
    // REDIRECT to prevent resubmission
    header("Location: a-order-details.php?id=" . $request_id);
    exit();
}

// Handle Mark as Incomplete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_incomplete'])) {
    $sql_update = "UPDATE tbl_requests SET status = 'Returned' WHERE request_id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$request_id]);
    
    // REDIRECT to prevent resubmission
    header("Location: a-order-details.php?id=" . $request_id);
    exit();
}

// Handle Mark as Paid
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_paid'])) {
    try {
        $pdo->beginTransaction();
        
        // Update request status to Paid
        $sql_update_request = "UPDATE tbl_requests SET status = 'Paid' WHERE request_id = ?";
        $stmt_update_request = $pdo->prepare($sql_update_request);
        $stmt_update_request->execute([$request_id]);
        
        // Update report_status only for items that are Damaged or Lost
        $sql_update_items = "UPDATE tbl_cart_items SET report_status = 'Paid' WHERE cart_id = ? AND report_status IN ('Damaged', 'Lost')";
        $stmt_update_items = $pdo->prepare($sql_update_items);
        $stmt_update_items->execute([$details['cart_id']]);
        
        $pdo->commit();
        
        // REDIRECT to prevent resubmission
        header("Location: a-order-details.php?id=" . $request_id);
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Error updating payment status: " . $e->getMessage();
    }
}

// Handle PDF generation - EXACT COPY FROM u-order-details.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view-btn'])) {
    // Prepare data for PDF generation
    $material_type = [];
    
    // Determine material types based on items - CHECK ALL ITEMS
    foreach ($items as $item) {
        $product_type = strtolower($item['product_type']);
        
        // Check for each material type in every item
        if (strpos($product_type, 'apparatus') !== false) {
            if (!in_array('Apparatus', $material_type)) {
                $material_type[] = 'Apparatus';
            }
        }
        if (strpos($product_type, 'chemical') !== false || strpos($product_type, 'supply') !== false || $product_type === 'supplies') {
            if (!in_array('Chemicals/Supplies', $material_type)) {
                $material_type[] = 'Chemicals/Supplies';
            }
        }
        if (strpos($product_type, 'equip') !== false) {
            if (!in_array('Equipment', $material_type)) {
                $material_type[] = 'Equipment';
            }
        }
        if (strpos($product_type, 'model') !== false || strpos($product_type, 'chart') !== false) {
            if (!in_array('Models/Charts', $material_type)) {
                $material_type[] = 'Models/Charts';
            }
        }
        if (strpos($product_type, 'specimen') !== false) {
            if (!in_array('Specimen', $material_type)) {
                $material_type[] = 'Specimen';
            }
        }
    }
    
    // DEBUG: Log what material types we found
    error_log("=== DEBUG: Detected material types: " . implode(', ', $material_type));
    error_log("=== DEBUG: All items and their types:");
    foreach ($items as $index => $item) {
        error_log("Item $index: " . $item['name'] . " - Type: " . $item['product_type']);
    }
    
    // If no specific types detected, default to Equipment
    if (empty($material_type)) {
        $material_type[] = 'Equipment';
    }
    
    // Prepare materials data for PDF - FIXED FORMAT
    $materials_data = [];
    $item_count = count($items);
    
    for ($i = 0; $i < 8; $i++) {
        if ($i * 2 < $item_count) {
            $item1 = $items[$i * 2];
            $materials_data[] = [
                'quantity_1' => $item1['amount'] . ' ' . $item1['measure_unit'],
                'material_1' => $item1['name']
            ];
        } else {
            $materials_data[] = ['quantity_1' => '', 'material_1' => ''];
        }
        
        if ($i * 2 + 1 < $item_count) {
            $item2 = $items[$i * 2 + 1];
            $materials_data[$i]['quantity_2'] = $item2['amount'] . ' ' . $item2['measure_unit'];
            $materials_data[$i]['material_2'] = $item2['name'];
        } else {
            $materials_data[$i]['quantity_2'] = '';
            $materials_data[$i]['material_2'] = '';
        }
    }
    
    // Calculate days
    $date1 = new DateTime($details['date_from']);
    $date2 = new DateTime($details['date_to']);
    $days = $date2->diff($date1)->days + 1;
    
    // Date formatting (for admin version only)
    $date_from = date('m/d/Y', strtotime($details['date_from']));
    $date_to = date('m/d/Y', strtotime($details['date_to']));
    $date_display = ($date_from === $date_to) ? $date_from : $date_from . ' - ' . $date_to;
    
    $time_from = date('g:ia', strtotime($details['time_from']));
    $time_to = date('g:ia', strtotime($details['time_to']));
    $time_display = ($time_from === $time_to) ? $time_from : $time_from . ' - ' . $time_to;
    
    // Create hidden form for PDF generation
    echo '<form id="pdfForm" method="post" action="generate_pdf.php" style="display: none;">';
    echo '<input type="hidden" name="material_type" value="' . htmlspecialchars(implode(',', $material_type)) . '">';
    echo '<input type="hidden" name="instructor_name" value="' . htmlspecialchars($details['prof_name']) . '">';
    echo '<input type="hidden" name="signature" value="' . htmlspecialchars($details['first_name'] . ' ' . $details['last_name']) . '">';
    echo '<input type="hidden" name="subject" value="' . htmlspecialchars($details['subject']) . '">';
    echo '<input type="hidden" name="date_of_use" value="' . htmlspecialchars($date_display) . '">';
    echo '<input type="hidden" name="time" value="' . htmlspecialchars($time_display) . '">';
    echo '<input type="hidden" name="days" value="' . htmlspecialchars($days) . '">';
    echo '<input type="hidden" name="room" value="' . htmlspecialchars($details['room']) . '">';
    echo '<input type="hidden" name="remarks" value="' . htmlspecialchars($details['remarks'] ?? '') . '">';
    echo '<input type="hidden" name="issue_date" value="' . date('m/d/Y', strtotime($details['request_date'])) . '">';
    echo '<input type="hidden" name="return_date" value="' . htmlspecialchars($date_to) . '">';
    
    // Add materials data
    foreach ($materials_data as $index => $material) {
        $i = $index + 1;
        echo '<input type="hidden" name="quantity_1_' . $i . '" value="' . htmlspecialchars($material['quantity_1']) . '">';
        echo '<input type="hidden" name="material_1_' . $i . '" value="' . htmlspecialchars($material['material_1']) . '">';
        echo '<input type="hidden" name="quantity_2_' . $i . '" value="' . htmlspecialchars($material['quantity_2']) . '">';
        echo '<input type="hidden" name="material_2_' . $i . '" value="' . htmlspecialchars($material['material_2']) . '">';
    }
    
    echo '</form>';
    echo '<script>document.getElementById("pdfForm").submit();</script>';
    exit();
}

// Initialize error message variable
$error_message = '';

// Handle Approve button (change from Pending to Submitted)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_btn'])) {
    // Check if there's sufficient stock before approving
    $insufficient_items = checkSufficientStock($items);
    
    if (empty($insufficient_items)) {
        $sql_update = "UPDATE tbl_requests SET status = 'Submitted' WHERE request_id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$request_id]);
        
        // Subtract consumable items from inventory
        subtractConsumableItems($pdo, $items);
        
        // REDIRECT to prevent resubmission
        header("Location: a-order-details.php?id=" . $request_id);
        exit();
    } else {
        // Set error message
        $error_message = "Cannot approve request: Insufficient stock for the following items:<br>";
        foreach ($insufficient_items as $item) {
            $error_message .= "- {$item['name']}: Requested {$item['requested']} {$item['unit']}, Available {$item['available']} {$item['unit']}<br>";
        }
    }
}

// Handle Disapprove button (change from Pending to Disapproved)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['disapprove_btn'])) {
    $sql_update = "UPDATE tbl_requests SET status = 'Disapproved' WHERE request_id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$request_id]);
    
    // REDIRECT to prevent resubmission
    header("Location: a-order-details.php?id=" . $request_id);
    exit();
}

// Handle Return to Pending button (change from Disapproved to Pending)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_to_pending_btn'])) {
    $sql_update = "UPDATE tbl_requests SET status = 'Pending' WHERE request_id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$request_id]);
    
    // REDIRECT to prevent resubmission
    header("Location: a-order-details.php?id=" . $request_id);
    exit();
}

// Handle Cancel button (change to Cancelled)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_btn'])) {
    $sql_update = "UPDATE tbl_requests SET status = 'Cancelled' WHERE request_id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$request_id]);
    
    // Add back consumable items to inventory
    addBackConsumableItems($pdo, $items);
    
    // REDIRECT to prevent resubmission
    header("Location: a-order-details.php?id=" . $request_id);
    exit();
}

// Handle Restore button (change from Cancelled to Submitted)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restore_btn'])) {
    // Check if there's sufficient stock before restoring
    $insufficient_items = checkSufficientStock($items);
    
    if (empty($insufficient_items)) {
        $sql_update = "UPDATE tbl_requests SET status = 'Submitted' WHERE request_id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$request_id]);
        
        // Subtract consumable items from inventory
        subtractConsumableItems($pdo, $items);
        
        // REDIRECT to prevent resubmission
        header("Location: a-order-details.php?id=" . $request_id);
        exit();
    } else {
        // Set error message
        $error_message = "Cannot restore request: Insufficient stock for the following items:<br>";
        foreach ($insufficient_items as $item) {
            $error_message .= "- {$item['name']}: Requested {$item['requested']} {$item['unit']}, Available {$item['available']} {$item['unit']}<br>";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'] ?? '';
    if (!empty($new_status)) {
        $sql_update = "UPDATE tbl_requests SET status = ? WHERE request_id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$new_status, $request_id]);
        
        // REDIRECT to prevent resubmission
        header("Location: a-order-details.php?id=" . $request_id);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_remarks'])) {
    $new_remarks = $_POST['remarks'] ?? '';
    
    // Validate remarks length
    if (strlen($new_remarks) > 200) {
        $error_message = "Remarks cannot exceed 200 characters. Current length: " . strlen($new_remarks) . " characters.";
    } else {
        $sql_update_remarks = "UPDATE tbl_requests SET remarks = ? WHERE request_id = ?";
        $stmt_update_remarks = $pdo->prepare($sql_update_remarks);
        $stmt_update_remarks->execute([$new_remarks, $request_id]);
        
        // REDIRECT to prevent resubmission
        header("Location: a-order-details.php?id=" . $request_id);
        exit();
    }
}

$date_from = date('m/d/Y', strtotime($details['date_from']));
$date_to = date('m/d/Y', strtotime($details['date_to']));
$date_display = ($date_from === $date_to) ? $date_from : $date_from . ' - ' . $date_to;

$time_from = date('g:ia', strtotime($details['time_from']));
$time_to = date('g:ia', strtotime($details['time_to']));
$time_display = ($time_from === $time_to) ? $time_from : $time_from . ' - ' . $time_to;

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
<body>
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
<main class="admin-order-details-page">
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <!-- Error Message Alert -->
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $error_message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <!-- Error Message Alert from Session -->
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    <!-- Success Message Alert -->
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>
                    
                    <div class="request-form-card">
                        <a href="a-home.php"><i class="fas fa-arrow-left"></i></a>
                        <form method="post" action="">
                            <h4 class="request-details-title mt-1 mb-3 text-center">Request Details <p><?= date('m/d/Y - g:ia', strtotime($details['request_date'])) ?></p></h4>
                            
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Name of Requester:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($details['first_name'] . ' ' . $details['last_name']) ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Name of Professor:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($details['prof_name']) ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Subject:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($details['subject']) ?>" readonly>
                                </div>
                            </div>

                            <div class="row mb-4 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Date of Use:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($date_display) ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Time:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($time_display) ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Room:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($details['room']) ?>" readonly>
                                </div>
                            </div>
                            
                            <h4 class="request-details-title mt-1 mb-3">Items:</h4>
                            <div id="request-list-container">
                                <ul class="list-group">
                                    <?php foreach ($items as $item): ?>
                                        <li class="list-group-item">
                                            <?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['product_type']) ?>) - 
                                            <strong>Amount:</strong> <?= htmlspecialchars($item['amount']) ?> <?= htmlspecialchars($item['measure_unit']) ?>
                                            <?php if ($item['is_consumables'] == 1): ?>
                                                <br><small class="text-muted">Available Stock: <?= htmlspecialchars($item['stock']) ?> <?= htmlspecialchars($item['measure_unit']) ?></small>
                                            <?php endif; ?>
                                            <?php if (isset($item['report_status']) && !empty($item['report_status'])): ?>
                                                <br><small class="<?= $item['report_status'] == 'Paid' ? 'text-success' : 'text-danger' ?>">
                                                    <strong>Reported as <?= htmlspecialchars($item['report_status']) ?>: 
                                                    <?= htmlspecialchars($item['report_qty'] ?? $item['amount']) ?> <?= htmlspecialchars($item['measure_unit']) ?></strong>
                                                    <?php if ($item['report_status'] == 'Paid'): ?>
                                                        (Payment Completed)
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="mb-4 mt-4">
                                <label class="form-label request-details-title">Status:</label>
                                <div class="row align-items-end">
                                    <div class="col-md-6">
                                        <?php if ($details['status'] == 'Pending'): ?>
                                            <!-- Pending State - Display current status and action buttons -->
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="text" class="form-control" value="Pending" readonly style="width: auto; flex: 1;">
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn finalize-btn" name="approve_btn">Approve</button>
                                                    <button type="submit" class="btn finalize-btn" name="disapprove_btn">Disapprove</button>
                                                </div>
                                            </div>
                                        <?php elseif ($details['status'] == 'Disapproved'): ?>
                                            <!-- Disapproved State - Display current status and return to pending button -->
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="text" class="form-control" value="Disapproved" readonly style="width: auto; flex: 1;">
                                                <button type="submit" class="btn finalize-btn" name="return_to_pending_btn">Return to Pending</button>
                                            </div>
                                        <?php elseif ($details['status'] == 'Cancelled'): ?>
                                            <!-- Cancelled State - Display current status and restore button -->
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="text" class="form-control" value="Cancelled" readonly style="width: auto; flex: 1;">
                                                <button type="submit" class="btn finalize-btn" name="restore_btn">Restore to Submitted</button>
                                            </div>
                                        <?php elseif (in_array($details['status'], ['Damaged', 'Lost'])): ?>
                                            <!-- Damaged/Lost State - Show only status and Paid button -->
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($details['status']) ?>" readonly style="width: auto; flex: 1;">
                                                <?php 
                                                // Check if there are still unpaid damaged/lost items
                                                $has_unpaid_items = false;
                                                foreach ($items as $item) {
                                                    if (in_array($item['report_status'], ['Damaged', 'Lost'])) {
                                                        $has_unpaid_items = true;
                                                        break;
                                                    }
                                                }
                                                ?>
                                                <?php if ($has_unpaid_items): ?>
                                                    <button type="submit" class="btn finalize-btn" name="mark_paid">Mark as Paid</button>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif ($details['status'] == 'Paid'): ?>
                                            <!-- Paid State - Show only status (no buttons) -->
                                            <input type="text" class="form-control" value="Paid" readonly style="width: auto; flex: 1;">
                                        <?php elseif ($details['status'] == 'Completed'): ?>
                                            <!-- Completed State - Show only status (no buttons) -->
                                            <input type="text" class="form-control" value="Completed" readonly style="width: auto; flex: 1;">
                                        <?php else: ?>
                                            <!-- Other Statuses - Display dropdown for status updates -->
                                            <div class="d-flex align-items-center gap-3">
                                                <select class="form-select" name="status" style="flex: 1;">
                                                    <option value="Submitted" <?= $details['status'] == 'Submitted' ? 'selected' : '' ?>>Submitted</option>
                                                    <option value="Pickup" <?= $details['status'] == 'Pickup' ? 'selected' : '' ?>>For Pickup</option>
                                                    <option value="Received" <?= $details['status'] == 'Received' ? 'selected' : '' ?>>Received</option>
                                                    <option value="Returned" <?= $details['status'] == 'Returned' ? 'selected' : '' ?>>Returned</option>
                                                </select>
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn finalize-btn" name="update_status">Update Status</button>
                                                    <button type="submit" class="btn finalize-btn" name="cancel_btn">Cancel</button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 mt-4">
                                <label for="remarks" class="form-label request-details-title remarks">Remarks:</label>
                                <div class="remarks-container">
                                    <textarea class="form-control" id="remarks" name="remarks" rows="4" 
                                              placeholder="Add remarks here... (Maximum 200 characters)"
                                              maxlength="200"><?= htmlspecialchars($details['remarks'] ?? '') ?></textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small class="text-muted">
                                            <span id="charCount"><?= strlen($details['remarks'] ?? '') ?></span>/200 characters
                                        </small>
                                        <button type="submit" class="btn edit-remarks-btn" name="update_remarks">Save Remarks</button>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">            
                                <div class="status-container">
                                    <button type="submit" class="btn finalize-btn" name="view-btn">View Form</button>
                                    
                                    <?php if (in_array($details['status'], ['Returned', 'Paid'])): ?>
                                        <!-- Show Completed button for Returned or Paid status -->
                                        <button type="submit" class="btn finalize-btn ms-3" name="mark_completed">Mark as Completed</button>
                                    <?php elseif ($details['status'] == 'Completed'): ?>
                                        <!-- Show Mark Incomplete button for Completed status -->
                                        <button type="submit" class="btn finalize-btn ms-3" name="mark_incomplete">Mark Incomplete</button>
                                    <?php endif; ?>
                                    
                                    <!-- Report button - Only show if no existing damage/loss report -->
                                    <?php 
                                    // Check if order already has damage/loss report
                                    $sql_check_report = "SELECT COUNT(*) as report_count 
                                                    FROM tbl_cart_items 
                                                    WHERE cart_id = ? AND report_status IN ('Damaged', 'Lost')";
                                    $stmt_check_report = $pdo->prepare($sql_check_report);
                                    $stmt_check_report->execute([$details['cart_id']]);
                                    $has_existing_report = $stmt_check_report->fetch(PDO::FETCH_ASSOC)['report_count'] > 0;
                                    
                                    // Only show report button if no existing report and status allows it
                                    $show_report_button = !$has_existing_report && in_array($details['status'], ['Submitted', 'Pickup', 'Received', 'Returned']);
                                    ?>
                                    
                                    <?php if ($show_report_button): ?>
                                        <a href="a-report-damage-loss.php?request_id=<?= $request_id ?>" class="btn finalize-btn ms-3">Report Damage / Loss</a>
                                    <?php endif; ?>
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

<script>
// Character counter for remarks textarea
document.addEventListener('DOMContentLoaded', function() {
    const remarksTextarea = document.getElementById('remarks');
    const charCount = document.getElementById('charCount');
    
    if (remarksTextarea && charCount) {
        // Update character count on input
        remarksTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
        
        // Prevent pasting more than 200 characters
        remarksTextarea.addEventListener('paste', function(e) {
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            if (this.value.length + pastedText.length > 200) {
                e.preventDefault();
                // Get only the portion that fits within the limit
                const allowedLength = 200 - this.value.length;
                if (allowedLength > 0) {
                    document.execCommand('insertText', false, pastedText.substring(0, allowedLength));
                }
            }
        });
    }
});
</script>
</body>
</html>