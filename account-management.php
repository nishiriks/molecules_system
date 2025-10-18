<?php
session_start();
require_once 'resource/php/init.php';
require_once 'resource/php/class/Auth.php';
Auth::requireAccountType('Super Admin');

if (basename($_SERVER['PHP_SELF']) !== 'change-pass.php') {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}

$config = new config();
$pdo = $config->con();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        // Get user details for logging
        $stmt = $pdo->prepare("SELECT first_name, last_name, is_active FROM tbl_users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $target_user = $stmt->fetch(PDO::FETCH_ASSOC);
        $target_user_name = $target_user['first_name'] . ' ' . $target_user['last_name'];
        
        // Toggle active status
        $new_status = $target_user['is_active'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE tbl_users SET is_active = ? WHERE user_id = ?");
        $stmt->execute([$new_status, $user_id]);
        
        // Log the action
        $current_date = date('Y-m-d H:i:s');
        $action = $new_status ? 'Activated' : 'Deactivated';
        $log_action = $action . ' User: ' . $target_user_name . ' (ID: ' . $user_id . ')';
        $stmt = $pdo->prepare("INSERT INTO tbl_admin_log (user_id, log_date, log_action) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $current_date, $log_action]);
        
        $_SESSION['success_message'] = "User " . strtolower($action) . " successfully!";
        header('Location: account-management.php');
        exit();
    }
    
    if (isset($_POST['change_account_type'])) {
        $user_id = $_POST['user_id'];
        $account_type = $_POST['account_type'];
        
        // Get user details for logging
        $stmt = $pdo->prepare("SELECT first_name, last_name, account_type as old_type FROM tbl_users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $target_user = $stmt->fetch(PDO::FETCH_ASSOC);
        $target_user_name = $target_user['first_name'] . ' ' . $target_user['last_name'];
        
        $stmt = $pdo->prepare("UPDATE tbl_users SET account_type = ? WHERE user_id = ?");
        $stmt->execute([$account_type, $user_id]);
        
        // Log the action
        $current_date = date('Y-m-d H:i:s');
        $log_action = 'Change Account Type: ' . $target_user_name . ' (ID: ' . $user_id . ') - Changed from ' . $target_user['old_type'] . ' to ' . $account_type;
        $stmt = $pdo->prepare("INSERT INTO tbl_admin_log (user_id, log_date, log_action) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $current_date, $log_action]);
        
        $_SESSION['success_message'] = "Account type updated successfully!";
        header('Location: account-management.php');
        exit();
    }
}

// Search and filter functionality
$search = $_GET['search'] ?? '';
$account_type_filter = $_GET['account_type'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query - Show all users (both active and inactive)
$query = "SELECT * FROM tbl_users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($account_type_filter)) {
    $query .= " AND account_type = ?";
    $params[] = $account_type_filter;
}

if (!empty($status_filter)) {
    if ($status_filter === 'active') {
        $query .= " AND is_active = 1";
    } elseif ($status_filter === 'inactive') {
        $query .= " AND is_active = 0";
    }
}

$query .= " ORDER BY user_id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" 
  crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" type="text/css"  href="resource/css/logs.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

  <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
  <style>
    .user-inactive {
        color: #6c757d !important;
        text-decoration: line-through !important;
    }
    .status-badge {
        font-size: 0.75em;
    }
  </style>
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
          <a class="nav-link active text-white" aria-current="page" href="#">Account Management</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="holiday-management.php">Holiday Management</a>
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
  ?>
<main>
    <!-- accounts table -->
    <section class="container my-5">
        <div class="card shadow table-container">
            <div class="logs-header card-header table-header">
                <h3 class="card-title mb-0"><i class="fas fa-users me-2"></i>Account Management</h3>
            </div>
            <div class="card-body">
                <!-- Search and Filter Form -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search by name or email..." name="search" value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-primary-search" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="account_type">
                            <option value="">All Account Types</option>
                            <option value="Student" <?= $account_type_filter === 'Student' ? 'selected' : '' ?>>Student</option>
                            <option value="Faculty" <?= $account_type_filter === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                            <option value="Admin" <?= $account_type_filter === 'Admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="Super Admin" <?= $account_type_filter === 'Super Admin' ? 'selected' : '' ?>>Super Admin</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
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
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Account Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="text-center align-middle <?= !$user['is_active'] ? 'user-inactive' : '' ?>">
                                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="badge 
                                                <?= $user['account_type'] === 'Super Admin' ? 'bg-danger' : 
                                                   ($user['account_type'] === 'Admin' ? 'bg-warning' : 
                                                   ($user['account_type'] === 'Faculty' ? 'bg-info' : 'bg-success')) ?>">
                                                <?= htmlspecialchars($user['account_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge status-badge <?= $user['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- Change Account Type Button -->
                                                <button type="button" class="btn btn-sm btn-outline-primary-account"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#changeTypeModal"
                                                        data-user-id="<?= $user['user_id'] ?>"
                                                        data-current-type="<?= htmlspecialchars($user['account_type']) ?>"
                                                        data-user-name="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>"
                                                        <?= !$user['is_active'] ? 'disabled' : '' ?>>
                                                    <i class="fas fa-user-cog"></i> Change Type
                                                </button>
                                                
                                                <!-- Toggle Active Status Button -->
                                                <button type="button" class="btn btn-sm <?= $user['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#toggleStatusModal"
                                                        data-user-id="<?= $user['user_id'] ?>"
                                                        data-user-name="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>"
                                                        data-current-status="<?= $user['is_active'] ?>">
                                                    <i class="fas <?= $user['is_active'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i> 
                                                    <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Toggle Status Confirmation Modal -->
<div class="modal fade" id="toggleStatusModal" tabindex="-1" aria-labelledby="toggleStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="toggleStatusModalLabel">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <strong><span id="statusAction"></span></strong> user: <strong id="toggleStatusUserName"></strong>?</p>
                <p class="text-danger"><small id="statusMessage"></small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="user_id" id="toggleStatusUserId">
                    <button type="submit" name="delete_user" class="btn" id="toggleStatusButton">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Change Account Type Modal -->
<div class="modal fade" id="changeTypeModal" tabindex="-1" aria-labelledby="changeTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeTypeModalLabel">Change Account Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Change account type for: <strong id="changeTypeUserName"></strong></p>
                <p>Current type: <strong id="currentAccountType"></strong></p>
                <form method="POST" id="changeTypeForm">
                    <input type="hidden" name="user_id" id="changeTypeUserId">
                    <div class="mb-3">
                        <label for="account_type" class="form-label">New Account Type:</label>
                        <select class="form-select" name="account_type" id="account_type" required>
                            <option value="Student">Student</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Admin">Admin</option>
                            <option value="Super Admin">Super Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="changeTypeForm" name="change_account_type" class="btn btn-primary-update">Update Account Type</button>
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

<script src="resource/js/am.js"></script>
</body>
</html>