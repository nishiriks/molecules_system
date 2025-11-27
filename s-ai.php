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
    $GEMINI_API_KEY = $config->getGeminiKey();

    // Get current user
    $current_user = $_SESSION['user_id'] ?? 'unknown';

    // Handle view report from URL parameter
    $viewingReport = false;
    $currentReportContent = '';
    if (isset($_GET['view_report']) && !empty($_GET['view_report'])) {
        $reportId = $_GET['view_report'];
        $report = getSavedReport($reportId, $pdo);
        if ($report) {
            $currentReportContent = $report['content'];
            $viewingReport = true;
        }
    }

    // Handle saving reports to database
    function getSavedReports($pdo) {
        $sql = "SELECT * FROM tbl_ai_reports ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getSavedReport($reportId, $pdo) {
        $sql = "SELECT * FROM tbl_ai_reports WHERE report_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reportId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function saveReportToDatabase($reportData, $pdo, $user) {
        $sql = "INSERT INTO tbl_ai_reports (report_id, title, content, report_type, date_range_start, date_range_end, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $reportData['id'],
            $reportData['title'],
            $reportData['content'],
            $reportData['type'],
            $reportData['date_start'] ?? null,
            $reportData['date_end'] ?? null,
            $user
        ]);
    }

    function deleteReportFromDatabase($reportId, $pdo) {
        $sql = "DELETE FROM tbl_ai_reports WHERE report_id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$reportId]);
    }

    function clearAllReportsFromDatabase($pdo) {
        $sql = "DELETE FROM tbl_ai_reports";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postData = json_decode(file_get_contents('php://input'), true);
        $prompt = trim($postData['prompt'] ?? '');
        $action = $postData['action'] ?? '';
        $dateFrom = $postData['date_from'] ?? '';
        $dateTo = $postData['date_to'] ?? '';

        // Handle get report data for PDF export
        if ($action === 'get_report_data') {
            $report_id = $postData['report_id'] ?? '';
            $report = getSavedReport($report_id, $pdo);
            
            if ($report) {
                header('Content-Type: application/json');
                echo json_encode([
                    "success" => true,
                    "title" => $report['title'],
                    "content" => $report['content'],
                    "date" => $report['created_at'],
                    "type" => $report['report_type']
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(["success" => false, "error" => "Report not found"]);
                exit;
            }
        }

        // Handle save report action
        if ($action === 'save_report') {
            $title = trim($postData['title'] ?? '');
            $content = trim($postData['content'] ?? '');
            
            if (!empty($title) && !empty($content)) {
                $report_id = uniqid('report_');
                $success = saveReportToDatabase([
                    'id' => $report_id,
                    'title' => $title,
                    'content' => $content,
                    'type' => $postData['type'] ?? 'stat_summary',
                    'date_start' => $postData['date_start'] ?? null,
                    'date_end' => $postData['date_end'] ?? null
                ], $pdo, $current_user);
                
                if ($success) {
                    header('Content-Type: application/json');
                    echo json_encode(["success" => true, "id" => $report_id]);
                    exit;
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(["success" => false, "error" => "Database error saving report"]);
                    exit;
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(["success" => false, "error" => "Title and content are required"]);
                exit;
            }
        }
        
        // Handle delete report action
        if ($action === 'delete_report') {
            $report_id = $postData['report_id'] ?? '';
            $success = deleteReportFromDatabase($report_id, $pdo);
            
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode(["success" => true]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(["success" => false, "error" => "Error deleting report"]);
                exit;
            }
        }
        
        // Handle clear all reports action
        if ($action === 'clear_all_reports') {
            $success = clearAllReportsFromDatabase($pdo);
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode(["success" => true]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(["success" => false, "error" => "Error clearing reports"]);
                exit;
            }
        }

        // --- CUSTOM_DATE_RANGE_ANALYSIS ---
        if ($prompt === 'CUSTOM_DATE_RANGE_ANALYSIS' && !empty($dateFrom) && !empty($dateTo)) {
            // Validate dates
            if (strtotime($dateFrom) === false || strtotime($dateTo) === false) {
                header('Content-Type: application/json');
                echo json_encode(["answer" => "Error: Invalid date format"]);
                exit;
            }

            // Get inventory data
            $invSql = "SELECT name, stock, measure_unit, product_type FROM tbl_inventory";
            $invRows = $pdo->query($invSql)->fetchAll(PDO::FETCH_ASSOC);

            $invText = "Current Inventory Status:\n";
            foreach ($invRows as $r) {
                $invText .= "- {$r['name']} ({$r['product_type']}): {$r['stock']} {$r['measure_unit']}\n";
            }

            // Get orders for custom date range
            $ordersSql = "
                SELECT 
                    i.name,
                    i.product_type,
                    r.status,
                    COUNT(r.request_id) as request_count,
                    SUM(ci.amount) as total_amount
                FROM tbl_requests r
                JOIN tbl_cart_items ci ON r.cart_id = ci.cart_id
                JOIN tbl_inventory i ON ci.product_id = i.product_id
                WHERE r.request_date BETWEEN ? AND ?
                GROUP BY i.name, i.product_type, r.status
                ORDER BY total_amount DESC, request_count DESC
            ";
            $stmt = $pdo->prepare($ordersSql);
            $stmt->execute([$dateFrom, $dateTo]);
            $ordersRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $ordersText = "Orders from $dateFrom to $dateTo:\n";
            if (!empty($ordersRows)) {
                foreach ($ordersRows as $r) {
                    $ordersText .= "- {$r['name']}: {$r['total_amount']} units ordered, {$r['request_count']} requests, Status: {$r['status']}\n";
                }
            } else {
                $ordersText .= "(No orders in this date range)\n";
            }

            // Get upcoming orders after the date range
            $upcomingSql = "
                SELECT 
                    i.name,
                    SUM(ci.amount) as total_amount,
                    COUNT(r.request_id) as request_count
                FROM tbl_requests r
                JOIN tbl_cart_items ci ON r.cart_id = ci.cart_id
                JOIN tbl_inventory i ON ci.product_id = i.product_id
                WHERE r.status IN ('pending', 'submitted')
                    AND r.date_from >= ?
                GROUP BY i.name
                ORDER BY total_amount DESC
            ";
            $upcomingStmt = $pdo->prepare($upcomingSql);
            $upcomingStmt->execute([$dateTo]);
            $upcomingRows = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

            $upcomingText = "Upcoming Orders (After $dateTo):\n";
            if (!empty($upcomingRows)) {
                foreach ($upcomingRows as $r) {
                    $upcomingText .= "- {$r['name']}: {$r['total_amount']} units in {$r['request_count']} requests\n";
                }
            } else {
                $upcomingText .= "(No upcoming orders)\n";
            }

            $prompt = "Analyze the inventory and order data for the custom date range from $dateFrom to $dateTo.
            Provide insights on:
            1. Inventory utilization during this period
            2. Popular products and demand patterns
            3. Stock levels vs consumption
            4. Recommendations for inventory management based on the analysis
            
            Return the answer formatted in valid HTML with clear headings and bullet lists or tables (use <h2>, <ul>, <li>, <table> if needed). 
            Do NOT include <html> or <body> tags, only the HTML for the content itself.
            
            Here is the data:\n\n".$invText."\n\n".$ordersText."\n\n".$upcomingText;
        }

        // --- CURRENT_INVENTORY_SUMMARY ---
        if ($prompt === 'CURRENT_INVENTORY_SUMMARY') {
            $firstDay = date('Y-m-01');
            $lastDay  = date('Y-m-t');

            // Get current inventory
            $invSql = "SELECT name, stock, measure_unit, product_type FROM tbl_inventory";
            $invRows = $pdo->query($invSql)->fetchAll(PDO::FETCH_ASSOC);

            $invText = "Current Inventory Status:\n";
            foreach ($invRows as $r) {
                $invText .= "- {$r['name']} ({$r['product_type']}): {$r['stock']} {$r['measure_unit']}\n";
            }

            // Get current month orders (all statuses for trend analysis)
            $ordersSql = "
                SELECT 
                    i.name,
                    i.product_type,
                    r.status,
                    COUNT(r.request_id) as request_count,
                    SUM(ci.amount) as total_amount
                FROM tbl_requests r
                JOIN tbl_cart_items ci ON r.cart_id = ci.cart_id
                JOIN tbl_inventory i ON ci.product_id = i.product_id
                WHERE r.request_date BETWEEN ? AND ?
                GROUP BY i.name, i.product_type, r.status
                ORDER BY total_amount DESC, request_count DESC
            ";
            $stmt = $pdo->prepare($ordersSql);
            $stmt->execute([$firstDay, $lastDay]);
            $ordersRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $ordersText = "Current Month Orders ($firstDay to $lastDay):\n";
            if (!empty($ordersRows)) {
                foreach ($ordersRows as $r) {
                    $ordersText .= "- {$r['name']}: {$r['total_amount']} units ordered, {$r['request_count']} requests, Status: {$r['status']}\n";
                }
            } else {
                $ordersText .= "(No orders this month)\n";
            }

            // Get upcoming orders (pending/submitted status)
            $upcomingSql = "
                SELECT 
                    i.name,
                    SUM(ci.amount) as total_amount,
                    COUNT(r.request_id) as request_count
                FROM tbl_requests r
                JOIN tbl_cart_items ci ON r.cart_id = ci.cart_id
                JOIN tbl_inventory i ON ci.product_id = i.product_id
                WHERE r.status IN ('pending', 'submitted')
                    AND r.date_from >= CURDATE()
                GROUP BY i.name
                ORDER BY total_amount DESC
            ";
            $upcomingRows = $pdo->query($upcomingSql)->fetchAll(PDO::FETCH_ASSOC);

            $upcomingText = "Upcoming Orders (Pending/Submitted):\n";
            if (!empty($upcomingRows)) {
                foreach ($upcomingRows as $r) {
                    $upcomingText .= "- {$r['name']}: {$r['total_amount']} units in {$r['request_count']} requests\n";
                }
            } else {
                $upcomingText .= "(No upcoming orders)\n";
            }

            $prompt = "Analyze the current inventory status, current month order trends, and upcoming orders to provide comprehensive inventory management recommendations for the current month. 
            Consider stock levels, demand patterns from current orders, and anticipated demand from upcoming orders.
            Provide specific recommendations for restocking, inventory optimization, and risk assessment.
            Return the answer formatted in valid HTML with clear headings and bullet lists or tables (use <h2>, <ul>, <li>, <table> if needed). 
            Do NOT include <html> or <body> tags, only the HTML for the content itself.
            Here is the data:\n\n".$invText."\n\n".$ordersText."\n\n".$upcomingText;
        }

        // --- PREVIOUS_MONTH_ANALYSIS ---
        if ($prompt === 'PREVIOUS_MONTH_ANALYSIS') {
            $firstDay = date('Y-m-01', strtotime('-1 month'));
            $lastDay  = date('Y-m-t', strtotime('-1 month'));
            $monthName = date('F Y', strtotime('-1 month'));

            $invSql = "SELECT name, stock, measure_unit, product_type FROM tbl_inventory";
            $invRows = $pdo->query($invSql)->fetchAll(PDO::FETCH_ASSOC);

            $invText = "Current Inventory data:\n";
            foreach ($invRows as $r) {
                $invText .= "- {$r['name']} ({$r['product_type']}): {$r['stock']} {$r['measure_unit']}\n";
            }

            $salesSql = "
                SELECT i.name,
                    i.product_type,
                    SUM(ci.amount) as total_amount,
                    COUNT(r.request_id) as request_count
                FROM tbl_requests r
                JOIN tbl_cart_items ci ON r.cart_id = ci.cart_id
                JOIN tbl_inventory i ON ci.product_id = i.product_id
                WHERE (r.status = 'received' OR r.status = 'returned')
                AND r.request_date BETWEEN ? AND ?
                GROUP BY i.name, i.product_type
                ORDER BY total_amount DESC
            ";
            $stmt = $pdo->prepare($salesSql);
            $stmt->execute([$firstDay, $lastDay]);
            $salesRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $salesText = "Previous Month Completed Orders ($monthName):\n";
            if (!empty($salesRows)) {
                foreach ($salesRows as $r) {
                    $salesText .= "- {$r['name']} ({$r['product_type']}): {$r['total_amount']} units in {$r['request_count']} requests\n";
                }
            } else {
                $salesText .= "(No completed orders in $monthName)\n";
            }

            $prompt = "Analyze the previous month's completed orders (received or returned status) and compare with current inventory levels.
            Note that returned status only applies to Apparatus, Models, and Equipment.
            Identify consumption patterns, popular items, and provide insights for inventory planning.
            Return the answer formatted in valid HTML with clear headings and bullet lists or tables (use <h2>, <ul>, <li>, <table> if needed). 
            Do NOT include <html> or <body> tags, only the HTML for the content itself.
            Focus on identifying patterns, stock levels relative to previous month demand, and provide recommendations for inventory management.
            Here is the data:\n\n".$invText."\n".$salesText;
        }

        // --- YEARLY_ANALYSIS ---
        if ($prompt === 'YEARLY_ANALYSIS') {
            // Determine current academic year (August to May)
            $currentYear = date('Y');
            $currentMonth = date('n');
            
            // If current month is June or July, use previous academic year
            if ($currentMonth >= 6 && $currentMonth <= 7) {
                $yearStart = ($currentYear - 1) . '-08-01';
                $yearEnd = $currentYear . '-05-31';
            } else {
                // If after August, current academic year started last August
                if ($currentMonth >= 8) {
                    $yearStart = $currentYear . '-08-01';
                    $yearEnd = ($currentYear + 1) . '-05-31';
                } else {
                    // If January-May, academic year started previous August
                    $yearStart = ($currentYear - 1) . '-08-01';
                    $yearEnd = $currentYear . '-05-31';
                }
            }

            // Get yearly orders summary
            $yearlySql = "
                SELECT 
                    i.name,
                    i.product_type,
                    r.status,
                    COUNT(r.request_id) as request_count,
                    SUM(ci.amount) as total_amount,
                    MONTH(r.request_date) as month
                FROM tbl_requests r
                JOIN tbl_cart_items ci ON r.cart_id = ci.cart_id
                JOIN tbl_inventory i ON ci.product_id = i.product_id
                WHERE r.request_date BETWEEN ? AND ?
                GROUP BY i.name, i.product_type, r.status, MONTH(r.request_date)
                ORDER BY month, total_amount DESC
            ";
            $stmt = $pdo->prepare($yearlySql);
            $stmt->execute([$yearStart, $yearEnd]);
            $yearlyRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $yearlyText = "Academic Year Analysis ($yearStart to $yearEnd):\n\n";
            
            if (!empty($yearlyRows)) {
                $currentMonth = null;
                foreach ($yearlyRows as $r) {
                    $monthName = date('F', mktime(0, 0, 0, $r['month'], 1));
                    if ($currentMonth !== $r['month']) {
                        $currentMonth = $r['month'];
                        $yearlyText .= "\n$monthName:\n";
                    }
                    $yearlyText .= "- {$r['name']} ({$r['product_type']}): {$r['total_amount']} units, {$r['request_count']} requests, Status: {$r['status']}\n";
                }
            } else {
                $yearlyText .= "No orders in this academic year period.\n";
            }

            // Get top products for the year
            $topProductsSql = "
                SELECT 
                    i.name,
                    i.product_type,
                    SUM(ci.amount) as total_units,
                    COUNT(r.request_id) as total_requests
                FROM tbl_requests r
                JOIN tbl_cart_items ci ON r.cart_id = ci.cart_id
                JOIN tbl_inventory i ON ci.product_id = i.product_id
                WHERE r.request_date BETWEEN ? AND ?
                GROUP BY i.name, i.product_type
                ORDER BY total_units DESC
                LIMIT 10
            ";
            $stmt = $pdo->prepare($topProductsSql);
            $stmt->execute([$yearStart, $yearEnd]);
            $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $topProductsText = "\nTop Products (Academic Year):\n";
            if (!empty($topProducts)) {
                foreach ($topProducts as $product) {
                    $topProductsText .= "- {$product['name']}: {$product['total_units']} units across {$product['total_requests']} requests\n";
                }
            } else {
                $topProductsText .= "No product data available.\n";
            }

            $prompt = "Provide a comprehensive analysis of the academic year (August to May) order patterns and trends.
            Analyze monthly distribution, popular products, usage patterns across the academic year, and provide insights for long-term inventory planning.
            Identify seasonal trends, peak usage periods, and make recommendations for academic year inventory strategy.
            Return the answer formatted in valid HTML with clear headings and bullet lists or tables (use <h2>, <ul>, <li>, <table> if needed). 
            Do NOT include <html> or <body> tags, only the HTML for the content itself.
            Here is the data:\n\n".$yearlyText."\n".$topProductsText;
        }

        // --- Gemini API call ---
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$GEMINI_API_KEY}";

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        // WAMP SSL bypass
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            header('Content-Type: application/json');
            echo json_encode(["answer" => "cURL error: $error"]);
            exit;
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        if (isset($decoded['error'])) {
            header('Content-Type: application/json');
            echo json_encode(["answer" => "Gemini error: ".$decoded['error']['message']]);
            exit;
        }

        $answer = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? 'Error retrieving Gemini response.';

        header('Content-Type: application/json');
        echo json_encode(["answer" => $answer]);
        exit;
    }

    // Get saved reports from database
    $savedReports = getSavedReports($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Report</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" type="text/css"  href="resource/css/logs.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
<!-- Include jsPDF for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<!-- Include html2canvas for better PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>

<body class="has-sidebar">
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
        <a class="nav-link text-white" href="s-user-logs.php">User Logs</a>
        </li>
        <li class="nav-item">
        <a class="nav-link text-white" href="s-admin-logs.php">Admin Logs</a>
        </li>
        <li class="nav-item">
        <a class="nav-link text-white" href="s-holiday-management.php">Holiday Management</a>
        </li>
        <li class="nav-item">
        <a class="nav-link text-white active" aria-current="page"  href="s-ai.php">AI Report</a>
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

<!-- Toggle Sidebar Button -->
<div class="toggle-sidebar" onclick="toggleSidebar()">
    <i class="fas fa-chevron-left" id="sidebar-toggle-icon"></i>
</div>

<!-- Saved Reports Sidebar -->
<div class="saved-reports-sidebar">
    <div class="p-3 border-bottom">
        <h5 class="mb-3 mt-3"><i class="fas fa-save me-2"></i>Saved Reports</h5>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <small class="text-muted"><?php echo count($savedReports); ?> reports saved</small>
            <?php if (!empty($savedReports)): ?>
                <button class="btn btn-sm btn-outline-danger" onclick="clearAllReports()">
                    <i class="fas fa-trash me-1"></i>Clear All
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="p-3">
        <?php if (empty($savedReports)): ?>
            <div class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-3"></i>
                <p>No saved reports yet</p>
                <small>Generate and save AI reports to see them here</small>
            </div>
        <?php else: ?>
            <div class="saved-reports-list">
                <?php foreach ($savedReports as $report): ?>
                    <div class="saved-report-item" data-report-id="<?php echo $report['report_id']; ?>">
                        <div class="report-title"><?php echo htmlspecialchars($report['title']); ?></div>
                        <div class="report-date">
                            <i class="far fa-clock me-1"></i>
                            <?php echo date('M j, Y g:i A', strtotime($report['created_at'])); ?>
                        </div>
                        <?php if ($report['date_range_start'] && $report['date_range_end']): ?>
                            <div class="report-date-range">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('M j, Y', strtotime($report['date_range_start'])) . ' - ' . date('M j, Y', strtotime($report['date_range_end'])); ?>
                            </div>
                        <?php endif; ?>
                        <div class="report-type badge bg-secondary mb-2">
                            <?php 
                            $typeLabels = [
                                'current_inventory' => 'Current Inventory Summary',
                                'previous_month' => 'Previous Month Analysis',
                                'yearly_analysis' => 'Yearly Analysis',
                                'custom_range' => 'Custom Date Range',
                                'stat_summary' => 'Statistical Summary'
                            ];
                            echo $typeLabels[$report['report_type']] ?? ucfirst($report['report_type']);
                            ?>
                        </div>
                        <div class="report-preview mb-2">
                            <?php 
                            $preview = strip_tags($report['content']);
                            echo strlen($preview) > 100 ? substr($preview, 0, 100) . '...' : $preview;
                            ?>
                        </div>
                        <div class="report-actions">
                            <button class="btn btn-sm btn-primary" onclick="loadSavedReport('<?php echo $report['report_id']; ?>')">
                                <i class="fas fa-eye me-1"></i>View
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="exportReport('<?php echo $report['report_id']; ?>', 'pdf')">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteReport('<?php echo $report['report_id']; ?>')">
                                <i class="fas fa-trash me-1"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<main class="main-content">
    <!-- AI Report Section -->
    <section class="container my-5">
        <div class="card shadow table-container">
            <div class="logs-header card-header table-header">
                <h3 class="card-title mb-0"><i class="fas fa-robot me-2"></i>AI Reporting & Analysis</h3>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-12">
                        <p class="lead mb-4">Get AI-powered insights and analysis of your inventory and sales data.</p>
                        
                        <div class="d-flex flex-wrap gap-3 mb-4">
                            <button class="btn btn-order btn-lg" onclick="sendPrompt('CURRENT_INVENTORY_SUMMARY')">
                                <i class="fas fa-boxes me-2"></i>Current Inventory Summary
                            </button>
                            <button class="btn btn-month btn-lg" onclick="sendPrompt('PREVIOUS_MONTH_ANALYSIS')">
                                <i class="fas fa-chart-line me-2"></i>Previous Month Analysis
                            </button>
                            <button class="btn btn-previous btn-lg" onclick="sendPrompt('YEARLY_ANALYSIS')">
                                <i class="fas fa-calendar-alt me-2"></i>Yearly Analysis
                            </button>
                            <button class="btn btn-custom btn-lg" onclick="showCustomPromptModal()">
                                <i class="fas fa-filter me-2"></i>Custom Date Range
                            </button>
                        </div>  
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="logs-header card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><i class="fas fa-comment-dots me-2"></i>AI Response</h5>
                                <button class="btn btn-sm btn-success" id="save-report-btn" style="display: <?php echo $viewingReport ? 'block' : 'none'; ?>;" onclick="showSaveReportModal()">
                                    <i class="fas fa-save me-1"></i>Save Report
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="output" class="ai-output">
                                    <?php if ($viewingReport): ?>
                                        <?php echo $currentReportContent; ?>
                                    <?php else: ?>
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-robot fa-3x mb-3"></i>
                                            <p>Select an option above to generate AI analysis</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Save Report Modal -->
<div class="modal fade save-report-modal" id="saveReportModal" tabindex="-1" aria-labelledby="saveReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveReportModalLabel">
                    <i class="fas fa-save me-2"></i>Save Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="saveReportForm">
                    <div class="mb-3">
                        <label for="reportTitle" class="form-label">Report Title</label>
                        <input type="text" class="form-control" id="reportTitle" required 
                            placeholder="Enter a descriptive title for this report">
                    </div>
                    <div class="mb-3">
                        <label for="reportType" class="form-label">Report Type</label>
                        <select class="form-select" id="reportType">
                            <option value="current_inventory">Current Inventory Summary</option>
                            <option value="previous_month">Previous Month Analysis</option>
                            <option value="yearly_analysis">Yearly Analysis</option>
                            <option value="custom_range">Custom Date Range</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Preview</label>
                        <div class="border p-2 bg-light" style="max-height: 150px; overflow-y: auto; font-size: 0.9rem;" id="reportPreview">
                            <!-- Preview will be inserted here -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCurrentReport()">
                    <i class="fas fa-save me-1"></i>Save Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Prompt Modal -->
<div class="modal fade custom-prompt-modal" id="customPromptModal" tabindex="-1" aria-labelledby="customPromptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customPromptModalLabel">
                    <i class="fas fa-calendar-alt me-2"></i>Custom Date Range Analysis
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-4">Select a date range to analyze inventory and order data for that specific period.</p>
                
                <div class="date-range-inputs">
                    <div class="date-input-group">
                        <label for="dateFrom" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="dateFrom" required>
                    </div>
                    <div class="date-input-group">
                        <label for="dateTo" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="dateTo" required>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <small>
                        <i class="fas fa-info-circle me-2"></i>
                        This will analyze orders, inventory status, and provide recommendations for the selected date range.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitCustomPrompt()">
                    <i class="fas fa-chart-bar me-1"></i>Generate Analysis
                </button>
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
<script src="resource/js/ai-reports.js"></script>

</body>
</html>