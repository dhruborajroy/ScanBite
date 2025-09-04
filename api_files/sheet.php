<?php
$apiKey = "AIzaSyDHWeOdKU0kSchVRNKU0ncBgZY7AwoIBxo"; //provided by google cloud https://www.youtube.com/watch?v=mVkOIDdw57w
$spreadsheetId = "1h2estY2spyAfO6nwlLixJYo6d8mjDxsCe-7iu5chFTc"; //spreadsheet id September
$range = "app!A1:AL"; // extend A→AL (to include 31 days + totals)

// API request
$url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/$range?key=$apiKey";

$response = file_get_contents($url);
$data = json_decode($response, true);

// First row is headers
$headers = $data['values'][0];
$rows = array_slice($data['values'], 1);

// Normalize row lengths (fill empty values)
$result = [];
foreach ($rows as $row) {
    $item = [];
    foreach ($headers as $i => $header) {
        $cleanHeader = trim($header);
        $item[$cleanHeader] = isset($row[$i]) ? $row[$i] : "";
    }
    $result[] = $item;
}

// Output clean JSON
header("Content-Type: application/json");
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
