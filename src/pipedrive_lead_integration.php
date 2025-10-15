<?php 

include 'helpers.php';

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

$domain = "nettbureaucase";
$apiToken = $_ENV['PIPEDRIVE_API_TOKEN'] ?? null;

$url = "https://{$domain}.pipedrive.com/api/v2/organizations?api_token={$apiToken}";

function createOrganization($data, $url) {
    $orgName = $data['contact_type'] . ' - ' . $data['name'];

    $payload = ['name' => $orgName];

    $response = sendPostRequest($url, $payload);

    if (isset($response['data']['id'])) {
        return (int)$response['data']['id'];
    }
    return null;
}

function createPerson($data, $url, $orgId) {
    $personName = $data['name'];
    $payload = ['name'=> $personName,
                'orgId'=> $orgId];
}

echo "Response preview:\n";

//$orgId = createOrganization($data, $url);
//echo "orgId = " . ($orgId ?? 'null') . PHP_EOL;
$id = 180;
$url2 = "https://{$domain}.pipedrive.com/api/v2/organizations/{$id}?api_token={$apiToken}";

$data = sendGetRequest($url2);
print_r(array_slice($data, 0, 2));

