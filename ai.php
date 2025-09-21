<?php
require_once 'resource/php/class/config.php'; // loads $GEMINI_API_KEY
$GEMINI_API_KEY = 'AIzaSyB8JO8mY6M0osBW62Q8XR1eaiYY7psL1VM'; // from Google AI Studio

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    $prompt = trim($postData['prompt'] ?? '');

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

    // WAMP SSL quick fix
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
  <title>AI Reporting
  </title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f8f8f8; }
    button { margin: 5px; padding: 10px 15px; cursor: pointer; }
    #output { margin-top: 20px; padding: 10px; border: 1px solid #ccc; background: #fff; min-height: 100px; }
  </style>
</head>
<body>
  <h1>AI Reporting</h1>

  <button onclick="sendPrompt('Tell me a joke')">Joke</button>
  <button onclick="sendPrompt('Write a motivational quote')">Motivational Quote</button>

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
