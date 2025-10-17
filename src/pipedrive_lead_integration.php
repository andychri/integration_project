<?php 

include 'helpers.php';


function getTestData() {
    $json = file_get_contents('tests/test_data.json');
    if ($json === false) {
        die('Error reading the JSON file');
    }

    $data = json_decode($json, true);

    if ($data === null) {
        die('Error decoding the JSON file');
    }
    return $data;
}

function getTestDataFrom(string $path): array {
    if (!is_file($path)) {
        fwrite(STDERR, "Missing/invalid $path\n");
        exit(1);
    }
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        fwrite(STDERR, "Error decoding $path\n");
        exit(1);
    }
    return $data;
}

$data = getTestData();

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

$apiToken = $_ENV['PIPEDRIVE_API_TOKEN'] ?? null;

$url = "https://nettbureaucase.pipedrive.com/api/v2/organizations?api_token={$apiToken}";


/**
 * Summary of createOrganization
 * @param array $data
 * @param string $domain
 * @param string $apiToken
 * @return int|null
 */
function createOrganization(array $data, string $apiToken): ?int {
    $orgName = '[' . ($data['contact_type'] ?? 'Ukjent') . '] ' . ($data['name'] ?? 'Ukjent');

    // Check if organization exists
    $checkUrl = "https://nettbureaucase.pipedrive.com/api/v2/organizations/search"
              . "?term=" . rawurlencode($orgName)
              . "&fields=name&exact_match=true&limit=1&api_token=$apiToken";

              
    // If this is true the organization exist
    $getResponse = sendGetRequest($checkUrl);
    $id = $getResponse['data']['items'][0]['item']['id'] ?? null;
    if ($id) 
        return (int)$id;

    // Create a new organization
    $postUrl = "https://nettbureaucase.pipedrive.com/api/v2/organizations?api_token=$apiToken";
    $postResponse = sendPostRequest($postUrl, ['name' => $orgName]);
    return isset($postResponse['data']['id']) ? (int)$postResponse['data']['id'] : null;
}

/**
 * Summary of createPerson
 * @param mixed $data
 * @param mixed $url
 * @param mixed $orgId
 * @return int|null
 */
function createPerson($data, $url, $orgId, $apiToken) {

    $personName = $data['name'] ?? 'Ukjent';

    // 1) Check if person exists by exact name
    $checkUrl = "https://nettbureaucase.pipedrive.com/api/v2/persons/search"
              . "?term=" . rawurlencode($personName)
              . "&fields=name&exact_match=true&limit=1&api_token=$apiToken";

    $getResponse = sendGetRequest($checkUrl);
    $id = $getResponse['data']['items'][0]['item']['id'] ?? null;
    if ($id) return (int)$id;
    
    // Emails and phones are read as arrays with multiple items in v2 of PipeDrive.
    $emails = [];
    if (!empty($data['email'])) {
        $emails[] = ['value'   => $data['email'], 
                     'label'   => 'work', 
                     'primary' => true];
    }
    $phones = [];
    if (!empty($data['phone'])) {
        $phones[] = ['value'   => (string)$data['phone'], 
                     'label'   => 'mobile', 
                     'primary' => true];
    }

    $payload = [
        'name'   => $data['name'] ?? 'Ukjent',
        'org_id' => $orgId,
        'emails' => $emails,
        'phones' => $phones,
        'custom_fields' => [
            'c0b071d74d13386af76f5681194fd8cd793e6020' => contactType($data['contact_type'] ?? null), // 27/28/29
        ],
    ];


    $postUrl = "https://nettbureaucase.pipedrive.com/api/v2/persons?api_token=$apiToken";
    $resp = sendPostRequest($postUrl, $payload);
    return isset($resp['data']['id']) ? (int)$resp['data']['id'] : null;
}

/**
 * Summary of createLead
 * @param mixed $data
 * @param mixed $url
 * @param mixed $personId
 * @param mixed $orgId
 */
function createLead($data, $url, $personId, $orgId, $apiToken) {

    // Build a simple title (expects $data['title'])
    $title = '[LEAD] ' . trim($data['name'] ?? 'Ukjent');

    $checkUrl = "https://nettbureaucase.pipedrive.com/api/v2/leads/search"
              . "?term=" . rawurlencode($title)
              . "&fields=title&exact_match=true&limit=1"
              . "&api_token=" . $apiToken;

    $getResponse = sendGetRequest($checkUrl);
    $existingId = $getResponse['data']['items'][0]['item']['id'] ?? null;
    if ($existingId) {
        return (string)$existingId;
    }

    $payload = [
        'title'         => '[LEAD] ' . ($data['name'] ?? 'Ukjent'),
        'person_id'     => $personId,
        'organization_id'        => $orgId, // or organization_id if your tenant expects that
        '35c4e320a6dee7094535c0fe65fd9e748754a171' => housingType($data['housing_type'] ?? null),
        '533158ca6c8a97cc1207b273d5802bd4a074f887' => isset($data['property_size']) ? (int)$data['property_size'] : null,
        '761dd27362225e433e1011b3bd4389a48ae4a412' => dealType($data['deal_type'] ?? null),
        '1fe6a0769bd867d36c25892576862e9b423302f3' => $data['comment'] ?? null
    ];

    $postUrl  = "https://nettbureaucase.pipedrive.com/api/v1/leads?api_token={$apiToken}";
    $resp = sendPostRequest($postUrl, $payload);

    // keep your existing return
    return $resp['data']['id'] ?? null;
}