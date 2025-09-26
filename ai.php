<?php // from Google AI Studio

session_start();
require_once 'resource/php/init.php';

if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php');
    exit();
}

$config = new config();
$pdo = $config->con();   // PDO connection

$GEMINI_API_KEY = 'AIzaSyB8JO8mY6M0osBW62Q8XR1eaiYY7psL1VM'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    $prompt = trim($postData['prompt'] ?? '');

    // === STAT SUMMARY ===
    if ($prompt === 'STAT_SUMMARY') {
        // Dates for current month
        $firstDay = date('Y-m-01');
        $lastDay  = date('Y-m-t');

        // Prepared statement to get total ordered per product
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

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$firstDay, $lastDay]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(["answer" => "DB error: ".$e->getMessage()]);
            exit;
        }

        // Build text for Gemini
        $summaryText = "Here are the products ordered (completed status) from $firstDay to $lastDay:\n\n";
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $summaryText .= "- {$row['name']}: {$row['total_amount']} units ordered\n";
            }
        } else {
            $summaryText .= "(No completed orders this month)";
        }

        // Replace prompt with actual text to send to Gemini
        $prompt = "Provide a clear statistical summary of the following data:\n\n".$summaryText;
    }

    // === STOCK ANALYSIS ===
    if ($prompt === 'STOCK_ANALYSIS') {
        // 1. Fetch inventory
        $invSql = "SELECT name, stock, measure_unit, product_type FROM tbl_inventory";
        try {
            $invStmt = $pdo->query($invSql);
            $inventory = $invStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(["answer" => "DB error (inventory): ".$e->getMessage()]);
            exit;
        }

        // 2. Fetch current month sales summary
        $firstDay = date('Y-m-01');
        $lastDay  = date('Y-m-t');

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

        try {
            $salesStmt = $pdo->prepare($salesSql);
            $salesStmt->execute([$firstDay, $lastDay]);
            $sales = $salesStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(["answer" => "DB error (sales): ".$e->getMessage()]);
            exit;
        }

        // 3. Build inventory text
        $invText = "Inventory (current stock):\n";
        foreach ($inventory as $inv) {
            $invText .= "- {$inv['name']} ({$inv['measure_unit']}): {$inv['stock']} units [{$inv['product_type']}]\n";
        }

        // 4. Build sales text
        $salesText = "Monthly Completed Orders ($firstDay to $lastDay):\n";
        if (!empty($sales)) {
            foreach ($sales as $row) {
                $salesText .= "- {$row['name']}: {$row['total_amount']} ordered\n";
            }
        } else {
            $salesText .= "(No completed orders this month)\n";
        }

        // 5. Create analysis prompt for Gemini
        $prompt = "Analyze the following inventory and recent sales data. "
                ."Identify items that are nearing out of stock and predict which items "
                ."might run out soon based on recent demand:\n\n"
                .$invText."\n".$salesText;
    }

    // --- Gemini API call ---
    $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$GEMINI_API_KEY";

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
  <title>AI Reporting</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f8f8f8; }
    button { margin: 5px; padding: 10px 15px; cursor: pointer; }
    #output { margin-top: 20px; padding: 10px; border: 1px solid #ccc; background: #fff; min-height: 100px; }
  </style>
</head>
<body>
  <h1>AI Reporting</h1>

  <button onclick="sendPrompt('STAT_SUMMARY')">Monthly Product Summary</button>
  <button onclick="sendPrompt('STOCK_ANALYSIS')">Inventory Stock Analysis</button>

  <div id="output">AI response will appear here...</div>

  <script>
    async function sendPrompt(prompt) {
      document.getElementById('output').innerText = 'Loading...';

      const res = await fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prompt })
      });

      const data = await res.json();
      document.getElementById('output').innerText = data.answer || 'Error getting response.';
    }
  </script>
</body>
</html>
