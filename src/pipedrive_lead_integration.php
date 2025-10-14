<?php

// Read the file test_data.json
$json = file_get_contents('tests/test_data.json');

if ($json === false) {
    die('Error reading the JSON file');
}

// Turn JSON text into PHP data
$data = json_decode($json, true);

if ($data === null) {
    die('Error decoding the JSON file');
}

// Retreive the API token
$envFile = '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Make an organization name
$name = $data['name'];
$contactType = $data['contact_type'];
$orgName = $contactType . " - " . $name;

$domain = "nettbureaucase";
$apiToken = $_ENV['PIPEDRIVE_API_TOKEN'] ?? null;

$url = "https://{$domain}.pipedrive.com/api/v2/organizations?api_token={$apiToken}";


function sendPostRequest($url, $payload) {
    $connect = curl_init($url);
    curl_setopt_array($connect, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload)]);

    $response = curl_exec($connect);
    $httpStatus = curl_getinfo($connect, CURLINFO_HTTP_CODE);
    curl_close($connect);

    $data = json_decode($response, true);
    return $data;
}

function sendGetRequest($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ]
]);
    // Execute the request
    $response = curl_exec($ch);
    // Check for errors
    if ($response === false) {
        die("cURL error: " . curl_error($ch));
    }
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    // Show HTTP status
    echo "HTTP Status: {$httpStatus}\n";
    // Decode JSON response
    $data = json_decode($response, true);
    return $data;
}

echo "Response preview:\n";
$data = sendGetRequest($url);
print_r(array_slice($data, 0, 2));