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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Logs</title>
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
<?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">';
        echo    $_SESSION['success_message'];
        echo    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        unset($_SESSION['success_message']);
    }
  ?>

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
          <a class="nav-link active text-white" aria-current="page" href="#">Admin Logs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="account-management.php">Account Management</a>
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

<!-- logs table -->
<section class="logs container my-5">
    <div class="card shadow">
        <div class="logs-header card-header text-white">
            <h3 class="card-title mb-0"><i class="fas fa-history me-2"></i>Admin Activity Logs</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Account Type</th>
                            <th>Log Action</th>
                            <th>Log Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                         try {
                                // Query to fetch user logs with user details
                                $query = "SELECT 
                                            al.user_id,
                                            al.log_action, 
                                            al.log_date, 
                                            u.first_name, 
                                            u.last_name,
                                            u.account_type,
                                            u.email 
                                          FROM tbl_admin_log al 
                                          INNER JOIN tbl_users u ON al.user_id = u.user_id 
                                          ORDER BY al.log_date DESC";
                                $stmt = $pdo->query($query);
                                
                                if ($stmt->rowCount() > 0) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['account_type']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['log_action']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['log_date']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center py-4'>No logs found</td></tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='5' class='text-center text-danger py-4'>Error fetching logs: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
    <!-- logs table -->

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
</body>
</html>