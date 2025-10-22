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

date_default_timezone_set('Asia/Manila');

// Get month and year from request or use current
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Get all requests for the selected month with date ranges
$sql = "SELECT 
            r.request_id,
            r.request_date,
            r.date_from,
            r.date_to,
            r.status,
            c.cart_id,
            u.first_name, 
            u.last_name, 
            u.account_type,
            GROUP_CONCAT(DISTINCT inv.product_type SEPARATOR ', ') AS product_types
        FROM tbl_requests AS r
        JOIN tbl_cart AS c ON r.cart_id = c.cart_id
        JOIN tbl_users AS u ON c.user_id = u.user_id
        LEFT JOIN tbl_cart_items AS items ON c.cart_id = items.cart_id
        LEFT JOIN tbl_inventory AS inv ON items.product_id = inv.product_id
        WHERE c.cart_status != 'active'
        AND (
            (MONTH(r.date_from) = ? AND YEAR(r.date_from) = ?) OR
            (MONTH(r.date_to) = ? AND YEAR(r.date_to) = ?) OR
            (r.date_from <= ? AND r.date_to >= ?)
        )
        GROUP BY r.request_id 
        ORDER BY r.date_from";

// Calculate the first and last day of the month for range checking
$first_day_of_month = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
$last_day_of_month = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

$stmt = $pdo->prepare($sql);
$stmt->execute([$month, $year, $month, $year, $last_day_of_month, $first_day_of_month]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group requests by date for easier display
$requests_by_date = [];
foreach ($requests as $request) {
    $date_from = $request['date_from'];
    $date_to = $request['date_to'];
    
    // If date_from and date_to are the same, use just one day
    if ($date_from == $date_to) {
        $date = date('Y-m-d', strtotime($date_from));
        if (!isset($requests_by_date[$date])) {
            $requests_by_date[$date] = [];
        }
        $requests_by_date[$date][] = $request;
    } else {
        // Create date range
        $current_date = $date_from;
        while (strtotime($current_date) <= strtotime($date_to)) {
            $date = date('Y-m-d', strtotime($current_date));
            if (!isset($requests_by_date[$date])) {
                $requests_by_date[$date] = [];
            }
            $requests_by_date[$date][] = $request;
            
            // Move to next day
            $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Calendar - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="resource/css/calendar.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="#">
            <img class="ceu-logo img-fluid" src="./resource/img/ceu-molecules.png" alt="CEU Molecules Logo" />
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
                    <a class="nav-link text-white active" href="a-calendar.php">Calendar</a>
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

    <!-- Main Content -->
    <main class="calendar-main">
        <div class="container">
            <h2 class="calendar-heading">Order Calendar</h2>

            <!-- Calendar Navigation -->
            <div class="calendar-nav mb-4">
                <a href="a-calendar.php?month=<?= $month - 1 <= 0 ? 12 : $month - 1 ?>&year=<?= $month - 1 <= 0 ? $year - 1 : $year ?>" class="nav-btn">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>

                <h3 class="calendar-title"><?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h3>

                <a href="a-calendar.php?month=<?= $month + 1 > 12 ? 1 : $month + 1 ?>&year=<?= $month + 1 > 12 ? $year + 1 : $year ?>" class="nav-btn">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            </div>

            <!-- Calendar -->
            <div class="calendar-container">
                <div class="calendar-grid">
                    <!-- Day Headers -->
                    <div class="calendar-day-header">Sun</div>
                    <div class="calendar-day-header">Mon</div>
                    <div class="calendar-day-header">Tue</div>
                    <div class="calendar-day-header">Wed</div>
                    <div class="calendar-day-header">Thu</div>
                    <div class="calendar-day-header">Fri</div>
                    <div class="calendar-day-header">Sat</div>

                    <!-- Calendar Days -->
                    <?php
                    // Get first day of month and number of days
                    $first_day = date('w', mktime(0, 0, 0, $month, 1, $year));
                    $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
                    $today = date('Y-m-d');

                    // Fill empty days at start
                    for ($i = 0; $i < $first_day; $i++) {
                        $prev_month = $month - 1;
                        $prev_year = $year;
                        if ($prev_month == 0) {
                            $prev_month = 12;
                            $prev_year = $year - 1;
                        }
                        $prev_days = date('t', mktime(0, 0, 0, $prev_month, 1, $prev_year));
                        $day_num = $prev_days - $first_day + $i + 1;
                        echo '<div class="calendar-day other-month">';
                        echo '<div class="day-number">' . $day_num . '</div>';
                        echo '</div>';
                    }

                    // Fill current month days
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $current_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
                        $is_today = $current_date == $today;

                        echo '<div class="calendar-day' . ($is_today ? ' today' : '') . '">';
                        echo '<div class="day-number">' . $day . '</div>';

                        // Display requests for this day
                        if (isset($requests_by_date[$current_date])) {
                            foreach ($requests_by_date[$current_date] as $request) {
                                $status_class = strtolower($request['status']);
                                $date_from = date('m/d/Y', strtotime($request['date_from']));
                                $date_to = date('m/d/Y', strtotime($request['date_to']));
                                
                                // Create display text
                                if ($request['date_from'] == $request['date_to']) {
                                    $date_display = $date_from;
                                } else {
                                    $date_display = $date_from . ' - ' . $date_to;
                                }
                                
                                $display_text = htmlspecialchars($request['first_name'] . ' ' . substr($request['last_name'], 0, 1) . '.');
                                $product_types = !empty($request['product_types']) ? $request['product_types'] : 'General';
                                $tooltip_text = htmlspecialchars(
                                    $product_types . ' Request - ' . 
                                    $request['first_name'] . ' ' . $request['last_name'] . 
                                    ' (' . $date_display . ')'
                                );
                                
                                // Ensure all data attributes are properly set
                                echo '<div class="calendar-event ' . $status_class . '" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top"
                                        title="' . $tooltip_text . '" 
                                        data-date="' . $current_date . '"
                                        data-request-id="' . $request['request_id'] . '"
                                        onclick="window.location.href=\'a-order-details.php?id=' . $request['request_id'] . '\'">';
                                echo $display_text;
                                echo '</div>';
                            }
                        }

                        echo '</div>';

                        // Break line after Saturday
                        if (($first_day + $day) % 7 == 0 && $day != $days_in_month) {
                            // This automatically handles line breaks due to CSS grid
                        }
                    }

                    // Fill empty days at end
                    $total_cells = 42; // 6 rows × 7 days
                    $filled_cells = $first_day + $days_in_month;
                    $remaining_cells = $total_cells - $filled_cells;

                    if ($remaining_cells > 0) {
                        for ($i = 1; $i <= $remaining_cells; $i++) {
                            echo '<div class="calendar-day other-month">';
                            echo '<div class="day-number">' . $i . '</div>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Legend -->
            <div class="legend mt-4">
                <div class="legend-item">
                    <div class="legend-color pending"></div>
                    <span>Pending</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color submitted"></div>
                    <span>Submitted</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color pickup"></div>
                    <span>For Pick-up</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color received"></div>
                    <span>Received</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color returned"></div>
                    <span>Returned</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color broken"></div>
                    <span>Broken</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color lost"></div>
                    <span>Lost</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color canceled"></div>
                    <span>Canceled</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color disapproved"></div>
                    <span>Disapproved</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
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

    <!-- Day details -->
    <div class="modal fade" id="dayDetailsModal" tabindex="-1" aria-labelledby="dayDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dayDetailsModalLabel">Orders for <span id="modalDate"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="dayOrdersList" class="day-orders-container">
                        <!-- Orders will be loaded here via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="resource/js/calendar.js"></script>
</body>

</html>