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
$GEMINI_API_KEY = $config->getGeminiKey();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    $prompt = trim($postData['prompt'] ?? '');

    // --- STAT_SUMMARY ---
    if ($prompt === 'STAT_SUMMARY') {
        $firstDay = date('Y-m-01');
        $lastDay  = date('Y-m-t');

        $sql = "
            SELECT i.name,
                   SUM(ci.amount) as total_amount
            FROM tbl_requests r
            JOIN tbl_cart_items ci ON r.cart_id = ci.cart_id
            JOIN tbl_inventory i ON ci.product_id = i.product_id
            WHERE r.status = 'completed'
              AND r.request_date BETWEEN ? AND ?
            GROUP BY i.name
            ORDER BY total_amount DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$firstDay, $lastDay]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summaryText = "Here are the products ordered (completed status) from $firstDay to $lastDay:\n\n";
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $summaryText .= "- {$row['name']}: {$row['total_amount']} units ordered\n";
            }
        } else {
            $summaryText .= "(No completed orders this month)";
        }

        $prompt = "Provide a clear statistical summary of the following data. 
Return the answer formatted in valid HTML with headings, bullet lists or tables (use <h2>, <ul>, <li>, <table> if needed). 
Do NOT include <html> or <body> tags, only the HTML for the content itself:\n\n".$summaryText;
    }

    // --- STOCK_ANALYSIS ---
    if ($prompt === 'STOCK_ANALYSIS') {
        $firstDay = date('Y-m-01');
        $lastDay  = date('Y-m-t');

        $invSql = "SELECT name, stock, measure_unit, product_type FROM tbl_inventory";
        $invRows = $pdo->query($invSql)->fetchAll(PDO::FETCH_ASSOC);

        $invText = "Inventory data:\n";
        foreach ($invRows as $r) {
            $invText .= "- {$r['name']} ({$r['product_type']}): {$r['stock']} {$r['measure_unit']}\n";
        }

        $salesSql = "
            SELECT i.name,
                   SUM(ci.amount) as total_amount
            FROM tbl_requests r
            JOIN tbl_cart_items ci ON r.cart_id = ci.cart_id
            JOIN tbl_inventory i ON ci.product_id = i.product_id
            WHERE r.status = 'completed'
              AND r.request_date BETWEEN ? AND ?
            GROUP BY i.name
            ORDER BY total_amount DESC
        ";
        $stmt = $pdo->prepare($salesSql);
        $stmt->execute([$firstDay, $lastDay]);
        $salesRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $salesText = "Sales data ($firstDay to $lastDay):\n";
        if (!empty($salesRows)) {
            foreach ($salesRows as $r) {
                $salesText .= "- {$r['name']}: {$r['total_amount']} units ordered\n";
            }
        } else {
            $salesText .= "(No completed orders this month)\n";
        }

        $prompt = "Analyze the following inventory and recent sales data. 
Return the answer formatted in valid HTML with clear headings and bullet lists or tables (use <h2>, <ul>, <li>, <table> if needed). 
Do NOT include <html> or <body> tags, only the HTML for the content itself. 
Here is the data:\n\n".$invText."\n".$salesText;
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
                   SUM(ci.amount) as total_amount
            FROM tbl_requests r
            JOIN tbl_cart_items ci ON r.cart_id = ci.cart_id
            JOIN tbl_inventory i ON ci.product_id = i.product_id
            WHERE r.status = 'completed'
              AND r.request_date BETWEEN ? AND ?
            GROUP BY i.name
            ORDER BY total_amount DESC
        ";
        $stmt = $pdo->prepare($salesSql);
        $stmt->execute([$firstDay, $lastDay]);
        $salesRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $salesText = "Previous Month Sales data ($monthName):\n";
        if (!empty($salesRows)) {
            foreach ($salesRows as $r) {
                $salesText .= "- {$r['name']}: {$r['total_amount']} units ordered\n";
            }
        } else {
            $salesText .= "(No completed orders in $monthName)\n";
        }

        $prompt = "Analyze the following current inventory data compared to previous month sales. 
Return the answer formatted in valid HTML with clear headings and bullet lists or tables (use <h2>, <ul>, <li>, <table> if needed). 
Do NOT include <html> or <body> tags, only the HTML for the content itself. 
Focus on identifying patterns, stock levels relative to previous month demand, and provide recommendations for inventory management.
Here is the data:\n\n".$invText."\n".$salesText;
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
          <a class="nav-link text-white" href="admin-logs.php">Admin Logs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="account-management.php">Account Management</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="holiday-management.php">Holiday Management</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active text-white" aria-current="page" href="ai.php">AI Report</a>
          <li class="nav-item">
          <a class="nav-link  text-white" href="logout.php">Log out</a>
        </li>
        </li>
      </ul>
    </div>
</nav>

<main>
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
                            <button class="btn btn-order btn-lg" onclick="sendPrompt('STAT_SUMMARY')">
                                <i class="fas fa-chart-bar me-2"></i>Current Order Summary
                            </button>
                            <button class="btn btn-month btn-lg" onclick="sendPrompt('STOCK_ANALYSIS')">
                                <i class="fas fa-boxes me-2"></i>Current Month Analysis
                            </button>
                            <button class="btn btn-previous btn-lg" onclick="sendPrompt('PREVIOUS_MONTH_ANALYSIS')">
                                <i class="fas fa-chart-line me-2"></i>Previous Month Analysis
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="logs-header card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-comment-dots me-2"></i>AI Response</h5>
                            </div>
                            <div class="card-body">
                                <div id="output" class="ai-output">
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-robot fa-3x mb-3"></i>
                                        <p>Select an option above to generate AI analysis</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

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
async function sendPrompt(prompt) {
    const output = document.getElementById('output');
    output.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Generating AI analysis...</p>
        </div>
    `;

    try {
        const res = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt })
        });

        const data = await res.json();
        
        if (data.answer) {
            output.innerHTML = data.answer;
        } else {
            output.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error getting response from AI.
                </div>
            `;
        }
    } catch (error) {
        output.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Network error: ${error.message}
            </div>
        `;
    }
}
</script>

</body>
</html>