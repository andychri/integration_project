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
    $payload = ['name'  => $data['name'],
                'orgId' => $orgId,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'custom_fields' => [
                    'contact_type' => contactType($data['contact_type'])
                ]];

    $resp = sendPostRequest($url, $payload);

    return isset($resp['data']['id']) ? (int)$resp['data']['id'] : null;
}

function createLead($data, $url, $orgId, $personId) {
    $payload = [
        'title'     => 'Lead ' . ($data['name']),
        'person_id' => $personId,
        'org_id'    => $orgId,
        'custom_fields' => [
            'housing_type'  => housingType($data['housing_type']),
            'property_size' => isset($data['property_size']) ? (int)$data['property_size'] : null,
            'deal_type'     => dealType($data['deal_type']),
            'comment'       => $data['comment'],
        ],
    ];

    $resp = sendPostRequest($url, $payload);
    if (!$resp) return null;

    return $resp['data']['id'] ?? null;
}

echo "Response preview:\n";

//$orgId = createOrganization($data, $url);
//echo "orgId = " . ($orgId ?? 'null') . PHP_EOL;
$id = 249;
$url2 = "https://{$domain}.pipedrive.com/api/v2/organizations/{$id}?api_token={$apiToken}";

$data = sendGetRequest($url2);
print_r(array_slice($data, 0, 2));

