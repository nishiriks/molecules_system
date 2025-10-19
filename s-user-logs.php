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

// Pagination variables
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$month = isset($_GET['month']) ? (int)$_GET['month'] : '';
$year = isset($_GET['year']) ? (int)$_GET['year'] : '';

// Build WHERE conditions for search and filter
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($month) && !empty($year)) {
    $where_conditions[] = "MONTH(ul.log_date) = :month AND YEAR(ul.log_date) = :year";
    $params[':month'] = $month;
    $params[':year'] = $year;
} elseif (!empty($year)) {
    $where_conditions[] = "YEAR(ul.log_date) = :year";
    $params[':year'] = $year;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total number of records
$count_query = "SELECT COUNT(*) as total FROM tbl_user_log ul INNER JOIN tbl_users u ON ul.user_id = u.user_id $where_clause";
$count_stmt = $pdo->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $limit);

// Ensure page is within valid range
if ($page < 1) $page = 1;
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

// Handle direct page number input
if (isset($_GET['goto_page'])) {
    $goto_page = (int)$_GET['goto_page'];
    if ($goto_page >= 1 && $goto_page <= $total_pages) {
        header("Location: ?page=" . $goto_page . "&search=" . urlencode($search) . "&month=" . $month . "&year=" . $year);
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Logs</title>
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
          <a class="nav-link text-white" href="s-account-management.php">Account Management</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white active" aria-current="page" href="s-user-logs.php">User Logs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="s-admin-logs.php">Admin Logs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="s-holiday-management.php">Holiday Management</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="s-ai.php">AI Report</a>
        </li>
        <li class="nav-item">
          <a class="nav-link  text-white" href="change-pass.php">Change Password</a>
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
        <div class="logs-header card-header text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-history me-2"></i>User Activity Logs</h3>
            <span class="badge bg-light text-dark">Page <?php echo $page; ?> of <?php echo $total_pages; ?> (Total: <?php echo $total_records; ?> records)</span>
        </div>
        
        <!-- Search and Filter Section -->
        <div class="card-body border-bottom">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search by Name or Email</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter name or email...">
                </div>
                <div class="col-md-3">
                    <label for="month" class="form-label">Month</label>
                    <select class="form-select" id="month" name="month">
                        <option value="">All Months</option>
                        <?php
                        $months = [
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        ];
                        foreach ($months as $num => $name) {
                            $selected = ($month == $num) ? 'selected' : '';
                            echo "<option value='$num' $selected>$name</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year" class="form-label">Year</label>
                    <select class="form-select" id="year" name="year">
                        <option value="">All Years</option>
                        <?php
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= 2020; $y--) {
                            $selected = ($year == $y) ? 'selected' : '';
                            echo "<option value='$y' $selected>$y</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary-filter w-100">Filter</button>
                </div>
            </form>
            
            <?php if (!empty($search) || !empty($month) || !empty($year)): ?>
            <div class="mt-3">
                <a href="s-user-logs.php" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
                <small class="text-muted ms-2">
                    <?php
                    $filter_text = [];
                    if (!empty($search)) $filter_text[] = "Search: \"$search\"";
                    if (!empty($month) && !empty($year)) $filter_text[] = "Date: " . $months[$month] . " $year";
                    elseif (!empty($year)) $filter_text[] = "Year: $year";
                    echo "Active filters: " . implode(', ', $filter_text);
                    ?>
                </small>
            </div>
            <?php endif; ?>
        </div>

        <div class="card-body">
            <div class="table-responsive text-center align-middle">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-res">
                        <tr class="text-center align-middle">
                            <th>Log ID</th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Account Type</th>
                            <th>Log Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                         try {
                                // Query to fetch user logs with user details
                                $query = "SELECT 
                                            ul.log_id, 
                                            ul.user_id, 
                                            ul.log_date, 
                                            u.first_name, 
                                            u.last_name,
                                            u.account_type,
                                            u.email 
                                          FROM tbl_user_log ul 
                                          INNER JOIN tbl_users u ON ul.user_id = u.user_id 
                                          $where_clause
                                          ORDER BY ul.log_date DESC
                                          LIMIT :limit OFFSET :offset";
                                $stmt = $pdo->prepare($query);
                                
                                // Bind search parameters
                                foreach ($params as $key => $value) {
                                    $stmt->bindValue($key, $value);
                                }
                                
                                // Bind pagination parameters
                                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                                $stmt->execute();
                                
                                if ($stmt->rowCount() > 0) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['log_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['account_type']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['log_date']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center py-4'>No logs found</td></tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='6' class='text-center text-danger py-4'>Error fetching logs: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Enhanced Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center align-items-center">
                    <!-- First Page -->
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=1&search=<?php echo urlencode($search); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" aria-label="First">
                            <span aria-hidden="true">&laquo;&laquo;</span>
                        </a>
                    </li>
                    
                    <!-- Previous Page -->
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <!-- Page Number Input -->
                    <li class="page-item">
                        <form method="GET" class="d-flex mx-2" style="width: 120px;">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <input type="hidden" name="month" value="<?php echo $month; ?>">
                            <input type="hidden" name="year" value="<?php echo $year; ?>">
                            <input type="number" class="form-control form-control-sm" name="goto_page" min="1" max="<?php echo $total_pages; ?>" value="<?php echo $page; ?>" placeholder="Page">
                            <button type="submit" class="btn btn-sm btn-primary-filter ms-1">Go</button>
                        </form>
                    </li>
                    
                    <!-- Next Page -->
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                    
                    <!-- Last Page -->
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>" aria-label="Last">
                            <span aria-hidden="true">&raquo;&raquo;</span>
                        </a>
                    </li>
                </ul>
                
                <div class="text-center mt-2">
                    <small class="text-muted">Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_records); ?> of <?php echo $total_records; ?> entries</small>
                </div>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</section>

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