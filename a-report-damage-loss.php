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
$request_id = $_GET['request_id'] ?? '';

if (empty($request_id)) {
    header('Location: a-home.php');
    exit();
}

// Fetch request details
$sql_request = "SELECT r.*, c.cart_id 
                FROM tbl_requests r
                JOIN tbl_cart c ON r.cart_id = c.cart_id
                WHERE r.request_id = ?";
$stmt_request = $pdo->prepare($sql_request);
$stmt_request->execute([$request_id]);
$request_details = $stmt_request->fetch(PDO::FETCH_ASSOC);

if (!$request_details) {
    header('Location: a-home.php');
    exit();
}

// Check if this order already has a damage/loss report
$sql_check_existing = "SELECT COUNT(*) as report_count 
                       FROM tbl_cart_items 
                       WHERE cart_id = ? AND report_status IN ('Damaged', 'Lost')";
$stmt_check_existing = $pdo->prepare($sql_check_existing);
$stmt_check_existing->execute([$request_details['cart_id']]);
$existing_report = $stmt_check_existing->fetch(PDO::FETCH_ASSOC);

// Only allow one report per order
if ($existing_report['report_count'] > 0) {
    $_SESSION['error_message'] = "This order already has a damage/loss report. Only one report is allowed per order.";
    header('Location: a-order-details.php?id=' . $request_id);
    exit();
}

// Fetch only non-consumable items for this order with their current report status
$sql_items = "SELECT i.item_id, i.amount, i.report_status, i.report_qty, 
                     inv.name, inv.product_type, inv.product_id, inv.is_consumables, inv.measure_unit, inv.stock
              FROM tbl_cart_items i
              JOIN tbl_inventory inv ON i.product_id = inv.product_id
              WHERE i.cart_id = ? AND inv.is_consumables = 0";
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$request_details['cart_id']]);
$non_consumable_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// If no non-consumable items, redirect back
if (empty($non_consumable_items)) {
    $_SESSION['error_message'] = "No non-consumable items found in this order.";
    header('Location: a-order-details.php?id=' . $request_id);
    exit();
}

// Calculate available quantities for reporting
foreach ($non_consumable_items as &$item) {
    $item['available_for_report'] = $item['amount'];
    
    // Subtract already reported quantities (except Paid ones)
    if (!empty($item['report_status']) && $item['report_status'] !== 'Paid') {
        $item['available_for_report'] -= $item['report_qty'];
    }
    
    // Ensure available quantity is not negative
    if ($item['available_for_report'] < 0) {
        $item['available_for_report'] = 0;
    }
}
unset($item); // Break the reference

// Check if there are any items available for reporting
$has_available_items = false;
foreach ($non_consumable_items as $item) {
    if ($item['available_for_report'] > 0) {
        $has_available_items = true;
        break;
    }
}

if (!$has_available_items) {
    $_SESSION['error_message'] = "All non-consumable items in this order have already been reported or paid.";
    header('Location: a-order-details.php?id=' . $request_id);
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
    $report_type = $_POST['report_type'] ?? '';
    $reported_items = $_POST['reported_items'] ?? [];
    
    // Validate inputs
    if (empty($report_type)) {
        $error_message = "Please select a report type.";
    } elseif (empty($reported_items)) {
        $error_message = "Please select at least one item to report.";
    } else {
        // Validate each reported item
        $valid_items = [];
        $has_errors = false;
        
        foreach ($reported_items as $item_id => $item_data) {
            $quantity = intval($item_data['quantity'] ?? 0);
            
            if ($quantity > 0) {
                // Find the selected item to validate quantity and availability
                $selected_item = null;
                foreach ($non_consumable_items as $item) {
                    if ($item['item_id'] == $item_id) {
                        $selected_item = $item;
                        break;
                    }
                }
                
                if ($selected_item && $quantity <= $selected_item['available_for_report']) {
                    $valid_items[] = [
                        'item_id' => $item_id,
                        'quantity' => $quantity,
                        'item_name' => $selected_item['name'],
                        'item_type' => $selected_item['product_type'],
                        'measure_unit' => $selected_item['measure_unit'],
                        'product_id' => $selected_item['product_id'],
                        'current_stock' => $selected_item['stock']
                    ];
                } else {
                    $has_errors = true;
                    $error_message = "Invalid quantity for one or more items. Please check the available quantities.";
                    break;
                }
            }
        }
        
        if (!$has_errors && !empty($valid_items)) {
            try {
                $pdo->beginTransaction();
                
                // Update all reported items and subtract from inventory
                foreach ($valid_items as $valid_item) {
                    // Update cart item report status and quantity
                    $sql_update_item = "UPDATE tbl_cart_items SET report_status = ?, report_qty = ? WHERE item_id = ?";
                    $stmt_update_item = $pdo->prepare($sql_update_item);
                    $stmt_update_item->execute([$report_type, $valid_item['quantity'], $valid_item['item_id']]);
                    
                    // Subtract reported quantity from inventory stock
                    $new_stock = $valid_item['current_stock'] - $valid_item['quantity'];
                    if ($new_stock < 0) {
                        $new_stock = 0; // Ensure stock doesn't go negative
                    }
                    
                    $sql_update_stock = "UPDATE tbl_inventory SET stock = ? WHERE product_id = ?";
                    $stmt_update_stock = $pdo->prepare($sql_update_stock);
                    $stmt_update_stock->execute([$new_stock, $valid_item['product_id']]);
                }
                
                // Update status in tbl_requests based on report type
                $new_status = ($report_type == 'Damaged') ? 'Damaged' : 'Lost';
                $sql_update_request = "UPDATE tbl_requests SET status = ? WHERE request_id = ?";
                $stmt_update_request = $pdo->prepare($sql_update_request);
                $stmt_update_request->execute([$new_status, $request_id]);
                
                // Build comprehensive remarks with all reported items
                $item_descriptions = [];
                foreach ($valid_items as $item) {
                    $item_descriptions[] = "{$item['item_name']} ({$item['item_type']}) - {$item['quantity']} {$item['measure_unit']}";
                }
                
                $items_list = implode(', ', $item_descriptions);
                $remarks = "The following items must be replaced or paid for at the Cashier's office before the end of the semester: " . $items_list . ".";
                
                $sql_update_remarks = "UPDATE tbl_requests SET remarks = ? WHERE request_id = ?";
                $stmt_update_remarks = $pdo->prepare($sql_update_remarks);
                $stmt_update_remarks->execute([$remarks, $request_id]);
                
                $pdo->commit();
                
                $_SESSION['success_message'] = "Damage/loss report submitted successfully for " . count($valid_items) . " item(s). Inventory stock has been updated. This report is final and cannot be modified.";
                header("Location: a-order-details.php?id=" . $request_id);
                exit();
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error_message = "Error submitting report: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Report Damage / Loss</title>
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
  </nav>

<main class="admin-order-details-page">
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-md-12">
                    <!-- Error Message Alert -->
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $error_message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Final Report Warning -->
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Notice</h6>
                        <p class="mb-0">
                            <strong>This damage/loss report is FINAL and cannot be modified after submission.</strong><br>
                            Reporting damaged/lost items will automatically subtract them from inventory stock. Please double-check all details before submitting. Only one report is allowed per order.
                        </p>
                    </div>
                    
                    <div class="request-form-card">
                        <a href="a-order-details.php?id=<?= $request_id ?>"><i class="fas fa-arrow-left"></i></a>
                        <form method="post" action="">
                            <h4 class="request-details-title mt-1 mb-3 text-center">Report Damage / Loss</h4>
                            
                            <div class="mb-4">
                                <label class="form-label request-details-title">Report Type <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="report_type" id="damaged" value="Damaged" required 
                                           <?= isset($_POST['report_type']) && $_POST['report_type'] == 'Damaged' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="damaged">
                                        Damaged
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="report_type" id="lost" value="Lost" required
                                           <?= isset($_POST['report_type']) && $_POST['report_type'] == 'Lost' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="lost">
                                        Lost
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label request-details-title">Select Items to Report <span class="text-danger">*</span></label>
                                <p class="text-muted">Check the items that are damaged/lost and specify the quantity for each. Reported quantities will be subtracted from inventory stock.</p>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">Report</th>
                                                <th width="30%">Item Name</th>
                                                <th width="15%">Type</th>
                                                <th width="12%">Ordered</th>
                                                <th width="12%">Available</th>
                                                <th width="13%">Current Stock</th>
                                                <th width="13%">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($non_consumable_items as $item): ?>
                                                <?php if ($item['available_for_report'] > 0): ?>
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" class="form-check-input item-checkbox" 
                                                                   name="reported_items[<?= $item['item_id'] ?>][include]" 
                                                                   value="1" 
                                                                   data-item-id="<?= $item['item_id'] ?>"
                                                                   data-max-quantity="<?= $item['available_for_report'] ?>"
                                                                   data-current-stock="<?= $item['stock'] ?>">
                                                        </td>
                                                        <td>
                                                            <?= htmlspecialchars($item['name']) ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($item['product_type']) ?></td>
                                                        <td><?= $item['amount'] ?> <?= $item['measure_unit'] ?></td>
                                                        <td><?= $item['available_for_report'] ?> <?= $item['measure_unit'] ?></td>
                                                        <td><?= $item['stock'] ?> <?= $item['measure_unit'] ?></td>
                                                        <td>
                                                            <input type="number" 
                                                                   class="form-control form-control-sm quantity-input" 
                                                                   name="reported_items[<?= $item['item_id'] ?>][quantity]" 
                                                                   min="1" 
                                                                   max="<?= $item['available_for_report'] ?>" 
                                                                   value="1" 
                                                                   disabled
                                                                   data-item-id="<?= $item['item_id'] ?>"
                                                                   data-current-stock="<?= $item['stock'] ?>">
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Check the box next to each item you want to include in the report, then specify the quantity for each selected item. The reported quantity will be subtracted from inventory stock.
                                    </small>
                                </div>
                            </div>

                            <!-- Selected Items Summary -->
                            <div class="mb-4" id="selectedItemsSummary" style="display: none;">
                                <label class="form-label request-details-title">Selected Items Summary</label>
                                <div class="alert alert-info">
                                    <div id="summaryContent"></div>
                                </div>
                            </div>

                            <!-- Stock Impact Warning -->
                            <div class="mb-4" id="stockImpactWarning" style="display: none;">
                                <label class="form-label request-details-title text-warning">Stock Impact Warning</label>
                                <div class="alert alert-warning">
                                    <div id="stockImpactContent"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">            
                                <div class="status-container">
                                    <a href="a-order-details.php?id=<?= $request_id ?>" class="btn finalize-btn me-3">Cancel</a>
                                    <button type="submit" class="btn finalize-btn btn-warning" name="submit_report">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Submit Final Report
                                    </button>
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
document.addEventListener('DOMContentLoaded', function() {
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const reportTypeRadios = document.querySelectorAll('input[name="report_type"]');
    const selectedItemsSummary = document.getElementById('selectedItemsSummary');
    const summaryContent = document.getElementById('summaryContent');
    const stockImpactWarning = document.getElementById('stockImpactWarning');
    const stockImpactContent = document.getElementById('stockImpactContent');
    
    // Function to update summary and stock impact
    function updateSummary() {
        const selectedItems = [];
        let totalItems = 0;
        let lowStockItems = [];
        
        itemCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const itemId = checkbox.getAttribute('data-item-id');
                const quantityInput = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                const quantity = parseInt(quantityInput.value) || 0;
                const currentStock = parseInt(checkbox.getAttribute('data-current-stock')) || 0;
                const row = checkbox.closest('tr');
                const itemName = row.cells[1].textContent.trim();
                const itemType = row.cells[2].textContent.trim();
                const measureUnit = 'units';
                
                if (quantity > 0) {
                    selectedItems.push({
                        name: itemName,
                        type: itemType,
                        quantity: quantity,
                        unit: measureUnit,
                        currentStock: currentStock,
                        newStock: currentStock - quantity
                    });
                    totalItems += quantity;
                    
                    // Check for low stock impact
                    if (currentStock - quantity <= 2) { // Warning if stock will be 2 or less
                        lowStockItems.push({
                            name: itemName,
                            currentStock: currentStock,
                            newStock: currentStock - quantity
                        });
                    }
                }
            }
        });
        
        if (selectedItems.length > 0) {
            let summaryHTML = '<strong>You are reporting the following items:</strong><br>';
            selectedItems.forEach(item => {
                summaryHTML += `• ${item.name} (${item.type}) - ${item.quantity} ${item.unit}<br>`;
            });
            summaryHTML += `<br><strong>Total items: ${totalItems}</strong>`;
            
            summaryContent.innerHTML = summaryHTML;
            selectedItemsSummary.style.display = 'block';
            
            // Show stock impact warning if any items will have low stock
            if (lowStockItems.length > 0) {
                let stockImpactHTML = '<strong>Stock Impact Warning:</strong><br>';
                lowStockItems.forEach(item => {
                    const status = item.newStock <= 0 ? 'OUT OF STOCK' : 'LOW STOCK';
                    const alertClass = item.newStock <= 0 ? 'text-danger' : 'text-warning';
                    stockImpactHTML += `• ${item.name}: ${item.currentStock} → ${item.newStock} <span class="${alertClass}"><strong>(${status})</strong></span><br>`;
                });
                stockImpactHTML += '<br>These items will have critically low stock levels after this report.';
                
                stockImpactContent.innerHTML = stockImpactHTML;
                stockImpactWarning.style.display = 'block';
            } else {
                stockImpactWarning.style.display = 'none';
            }
        } else {
            selectedItemsSummary.style.display = 'none';
            stockImpactWarning.style.display = 'none';
        }
    }
    
    // Handle checkbox changes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const itemId = this.getAttribute('data-item-id');
            const quantityInput = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
            
            if (this.checked) {
                quantityInput.disabled = false;
                quantityInput.focus();
            } else {
                quantityInput.disabled = true;
                quantityInput.value = '1';
            }
            
            updateSummary();
        });
    });
    
    // Handle quantity input changes
    quantityInputs.forEach(input => {
        input.addEventListener('input', function() {
            const max = parseInt(this.max);
            const value = parseInt(this.value) || 0;
            const currentStock = parseInt(this.getAttribute('data-current-stock')) || 0;
            
            if (value > max) {
                this.value = max;
            } else if (value < 1) {
                this.value = 1;
            } else if (value > currentStock) {
                // If trying to report more than current stock, show warning but allow
                // (since these are non-consumables that exist physically but are damaged/lost)
                this.value = value; // Still allow it
            }
            
            updateSummary();
        });
        
        input.addEventListener('change', updateSummary);
    });
    
    // Handle report type changes
    reportTypeRadios.forEach(radio => {
        radio.addEventListener('change', updateSummary);
    });
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const reportType = document.querySelector('input[name="report_type"]:checked');
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        
        if (!reportType) {
            e.preventDefault();
            alert('Please select a report type (Damaged or Lost).');
            return;
        }
        
        if (checkedItems.length === 0) {
            e.preventDefault();
            alert('Please select at least one item to report.');
            return;
        }
        
        // Validate that all checked items have valid quantities
        let hasInvalidQuantity = false;
        checkedItems.forEach(checkbox => {
            const itemId = checkbox.getAttribute('data-item-id');
            const quantityInput = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
            const quantity = parseInt(quantityInput.value) || 0;
            
            if (quantity < 1) {
                hasInvalidQuantity = true;
                quantityInput.focus();
            }
        });
        
        if (hasInvalidQuantity) {
            e.preventDefault();
            alert('Please enter a valid quantity (at least 1) for all selected items.');
            return;
        }
        
        // Final confirmation with stock impact warning
        const confirmed = confirm(
            'WARNING: This action will subtract the reported quantities from inventory stock.\n\n' +
            'This report is FINAL and cannot be modified after submission.\n\n' +
            'Are you sure you want to submit this damage/loss report?'
        );
        if (!confirmed) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>