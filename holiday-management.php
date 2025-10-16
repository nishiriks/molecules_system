<?php
session_start();
require_once 'resource/php/init.php';

$is_logged_in = isset($_SESSION['user_id']);
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user is Super Admin
if ($_SESSION['account_type'] !== 'Super Admin') {
    header('Location: index.php');
    exit();
}

$config = new config();
$pdo = $config->con();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_holiday'])) {
        $holiday_name = $_POST['holiday_name'];
        $holiday_type = $_POST['holiday_type'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        
        // Validate dates
        if (strtotime($date_to) < strtotime($date_from)) {
            $_SESSION['error_message'] = "End date cannot be earlier than start date.";
            header('Location: holiday-management.php');
            exit();
        }
        
        $stmt = $pdo->prepare("INSERT INTO tbl_holidays (holiday_name, holiday_date_from, holiday_date_to, holiday_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$holiday_name, $date_from, $date_to, $holiday_type]);
        
        // Get the inserted holiday ID
        $holiday_id = $pdo->lastInsertId();
        
        // Log the action
        $current_date = date('Y-m-d H:i:s');
        $log_action = 'Add Holiday: ' . $holiday_name . ' (ID: ' . $holiday_id . ')';
        $stmt = $pdo->prepare("INSERT INTO tbl_admin_log (user_id, log_date, log_action) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $current_date, $log_action]);
        
        $_SESSION['success_message'] = "Holiday added successfully!";
        header('Location: holiday-management.php');
        exit();
    }
    
    if (isset($_POST['edit_holiday'])) {
        $holiday_id = $_POST['holiday_id'];
        $holiday_name = $_POST['holiday_name'];
        $holiday_type = $_POST['holiday_type'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        
        // Validate dates
        if (strtotime($date_to) < strtotime($date_from)) {
            $_SESSION['error_message'] = "End date cannot be earlier than start date.";
            header('Location: holiday-management.php');
            exit();
        }
        
        $stmt = $pdo->prepare("UPDATE tbl_holidays SET holiday_name = ?, holiday_date_from = ?, holiday_date_to = ?, holiday_type = ? WHERE holiday_id = ?");
        $stmt->execute([$holiday_name, $date_from, $date_to, $holiday_type, $holiday_id]);
        
        // Log the action
        $current_date = date('Y-m-d H:i:s');
        $log_action = 'Edit Holiday: ' . $holiday_name . ' (ID: ' . $holiday_id . ')';
        $stmt = $pdo->prepare("INSERT INTO tbl_admin_log (user_id, log_date, log_action) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $current_date, $log_action]);
        
        $_SESSION['success_message'] = "Holiday updated successfully!";
        header('Location: holiday-management.php');
        exit();
    }
    
    if (isset($_POST['delete_holiday'])) {
        $holiday_id = $_POST['holiday_id'];
        
        // Get holiday name for logging
        $stmt = $pdo->prepare("SELECT holiday_name FROM tbl_holidays WHERE holiday_id = ?");
        $stmt->execute([$holiday_id]);
        $holiday = $stmt->fetch(PDO::FETCH_ASSOC);
        $holiday_name = $holiday['holiday_name'];
        
        $stmt = $pdo->prepare("DELETE FROM tbl_holidays WHERE holiday_id = ?");
        $stmt->execute([$holiday_id]);
        
        // Log the action
        $current_date = date('Y-m-d H:i:s');
        $log_action = 'Delete Holiday: ' . $holiday_name . ' (ID: ' . $holiday_id . ')';
        $stmt = $pdo->prepare("INSERT INTO tbl_admin_log (user_id, log_date, log_action) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $current_date, $log_action]);
        
        $_SESSION['success_message'] = "Holiday deleted successfully!";
        header('Location: holiday-management.php');
        exit();
    }
}

// Search and filter functionality
$search = $_GET['search'] ?? '';
$holiday_type_filter = $_GET['holiday_type'] ?? '';

// Build query
$query = "SELECT * FROM tbl_holidays WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND holiday_name LIKE ?";
    $search_term = "%$search%";
    $params[] = $search_term;
}

if (!empty($holiday_type_filter)) {
    $query .= " AND holiday_type = ?";
    $params[] = $holiday_type_filter;
}

$query .= " ORDER BY holiday_date_from ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$holidays = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to format date display
function formatHolidayDate($date_from, $date_to, $holiday_type) {
    $from_timestamp = strtotime($date_from);
    $to_timestamp = strtotime($date_to);
    
    if ($date_from == $date_to) {
        // Single day
        if ($holiday_type === 'Recurring Holiday') {
            return date('F j', $from_timestamp); // Month Day (no year)
        } else {
            return date('F j, Y', $from_timestamp); // Month Day, Year
        }
    } else {
        // Date range
        if ($holiday_type === 'Recurring Holiday') {
            return date('F j', $from_timestamp) . ' - ' . date('F j', $to_timestamp);
        } else {
            return date('F j, Y', $from_timestamp) . ' - ' . date('F j, Y', $to_timestamp);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Holiday Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css"  href="resource/css/logs.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

  <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
</head>

<body>
<!-- navbar -->
<nav class="navbar">
  <a class="navbar-brand" href="#">
    <img class="ceu-logo img-fluid" src="./resource/img/ceu-molecules.png"/>
  </a>
  <button class="navbar-toggler me-3 custom-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
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
          <a class="nav-link text-white" href="user-logs.php">User Logs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="admin-logs.php">Admin Logs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="account-management.php">Account Management</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active text-white" href="holiday-management.php">Holiday Management</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="ai.php">AI Report</a>
        </li>
        <li class="nav-item">
          <a class="nav-link  text-white" href="logout.php">Log out</a>
        </li>
      </ul>
    </div>
</nav>
<?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">';
        echo    $_SESSION['success_message'];
        echo    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        unset($_SESSION['success_message']);
    }
    
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">';
        echo    $_SESSION['error_message'];
        echo    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        unset($_SESSION['error_message']);
    }
  ?>
<main>
    <!-- Add Holiday Form -->
    <section class="container my-4">
        <div class="card shadow">
            <div class="logs-header card-header text-white">
                <h4 class="card-title mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Holiday</h4>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3" id="addHolidayForm">
                    <div class="col-md-3">
                        <label for="holiday_name" class="form-label">Holiday Name</label>
                        <input type="text" class="form-control" id="holiday_name" name="holiday_name" required>
                    </div>
                    <div class="col-md-2">
                        <label for="holiday_type" class="form-label">Holiday Type</label>
                        <select class="form-select" id="holiday_type" name="holiday_type" required>
                            <option value="">Select Type</option>
                            <option value="Recurring Holiday">Recurring Holiday</option>
                            <option value="Special Holiday">Special Holiday</option>
                            <option value="Suspension">Suspension</option>
                            <option value="Scheduled Break">Scheduled Break</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" required>
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" name="add_holiday" class="btn btn-success-holiday w-100">
                            <i class="fas fa-plus me-2"></i>Add Holiday
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Holidays table -->
    <section class="container my-4">
        <div class="card shadow table-container">
            <div class="logs-header card-header table-header">
                <h3 class="card-title mb-0"><i class="fas fa-calendar-alt me-2"></i>Holiday Management</h3>
            </div>
            <div class="card-body">
                <!-- Search and Filter Form -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search holiday name..." name="search" value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-primary-search" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="holiday_type">
                            <option value="">All Holiday Types</option>
                            <option value="Recurring Holiday" <?= $holiday_type_filter === 'Recurring Holiday' ? 'selected' : '' ?>>Recurring Holiday</option>
                            <option value="Special Holiday" <?= $holiday_type_filter === 'Special Holiday' ? 'selected' : '' ?>>Special Holiday</option>
                            <option value="Suspension" <?= $holiday_type_filter === 'Suspension' ? 'selected' : '' ?>>Suspension</option>
                            <option value="Scheduled Break" <?= $holiday_type_filter === 'Scheduled Break' ? 'selected' : '' ?>>Scheduled Break</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary-filter w-100">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-res">
                            <tr class="text-center align-middle">
                                <th class="">Holiday Name</th>
                                <th class="">Date</th>
                                <th class="">Holiday Type</th>
                                <th class="">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($holidays) > 0): ?>
                                <?php foreach ($holidays as $holiday): ?>
                                    <tr class="text-center align-middle">
                                        <td><?= htmlspecialchars($holiday['holiday_name']) ?></td>
                                        <td><?= formatHolidayDate($holiday['holiday_date_from'], $holiday['holiday_date_to'], $holiday['holiday_type']) ?></td>
                                        <td>
                                            <span class="badge 
                                                <?= $holiday['holiday_type'] === 'Recurring Holiday' ? 'bg-primary' : 
                                                   ($holiday['holiday_type'] === 'Special Holiday' ? 'bg-success' : 
                                                   ($holiday['holiday_type'] === 'Suspension' ? 'bg-warning' : 'bg-info')) ?>">
                                                <?= htmlspecialchars($holiday['holiday_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- Edit Holiday Button -->
                                                <button type="button" class="btn btn-sm btn-outline-primary-edit" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal"
                                                        data-holiday-id="<?= $holiday['holiday_id'] ?>"
                                                        data-holiday-name="<?= htmlspecialchars($holiday['holiday_name']) ?>"
                                                        data-holiday-type="<?= htmlspecialchars($holiday['holiday_type']) ?>"
                                                        data-date-from="<?= $holiday['holiday_date_from'] ?>"
                                                        data-date-to="<?= $holiday['holiday_date_to'] ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                
                                                <!-- Delete Holiday Button -->
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal"
                                                        data-holiday-id="<?= $holiday['holiday_id'] ?>"
                                                        data-holiday-name="<?= htmlspecialchars($holiday['holiday_name']) ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">No holidays found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete holiday: <strong id="deleteHolidayName"></strong>?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="holiday_id" id="deleteHolidayId">
                    <button type="submit" name="delete_holiday" class="btn btn-danger">Delete Holiday</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Holiday Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Holiday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editForm">
                    <input type="hidden" name="holiday_id" id="editHolidayId">
                    <div class="mb-3">
                        <label for="edit_holiday_name" class="form-label">Holiday Name</label>
                        <input type="text" class="form-control" id="edit_holiday_name" name="holiday_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_holiday_type" class="form-label">Holiday Type</label>
                        <select class="form-select" id="edit_holiday_type" name="holiday_type" required>
                            <option value="Recurring Holiday">Recurring Holiday</option>
                            <option value="Special Holiday">Special Holiday</option>
                            <option value="Suspension">Suspension</option>
                            <option value="Scheduled Break">Scheduled Break</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="edit_date_from" name="date_from" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="edit_date_to" name="date_to" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editForm" name="edit_holiday" class="btn btn-primary-update">Update Holiday</button>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete Modal Handler
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const holidayId = button.getAttribute('data-holiday-id');
        const holidayName = button.getAttribute('data-holiday-name');
        
        document.getElementById('deleteHolidayId').value = holidayId;
        document.getElementById('deleteHolidayName').textContent = holidayName;
    });

    // Edit Modal Handler
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const holidayId = button.getAttribute('data-holiday-id');
        const holidayName = button.getAttribute('data-holiday-name');
        const holidayType = button.getAttribute('data-holiday-type');
        const dateFrom = button.getAttribute('data-date-from');
        const dateTo = button.getAttribute('data-date-to');
        
        document.getElementById('editHolidayId').value = holidayId;
        document.getElementById('edit_holiday_name').value = holidayName;
        document.getElementById('edit_holiday_type').value = holidayType;
        document.getElementById('edit_date_from').value = dateFrom;
        document.getElementById('edit_date_to').value = dateTo;
    });

    // Date validation for add form
    const addForm = document.getElementById('addHolidayForm');
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');

    addForm.addEventListener('submit', function(e) {
        if (dateFrom.value && dateTo.value) {
            if (new Date(dateTo.value) < new Date(dateFrom.value)) {
                e.preventDefault();
                alert('End date cannot be earlier than start date.');
                dateTo.focus();
            }
        }
    });

    // Date validation for edit form
    const editForm = document.getElementById('editForm');
    const editDateFrom = document.getElementById('edit_date_from');
    const editDateTo = document.getElementById('edit_date_to');

    editForm.addEventListener('submit', function(e) {
        if (editDateFrom.value && editDateTo.value) {
            if (new Date(editDateTo.value) < new Date(editDateFrom.value)) {
                e.preventDefault();
                alert('End date cannot be earlier than start date.');
                editDateTo.focus();
            }
        }
    });

    // Real-time validation - update min date when date_from changes
    dateFrom.addEventListener('change', function() {
        if (this.value) {
            dateTo.min = this.value;
        }
    });

    editDateFrom.addEventListener('change', function() {
        if (this.value) {
            editDateTo.min = this.value;
        }
    });
});
</script>
</body>
</html>