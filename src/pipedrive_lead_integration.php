<?php 

include 'helpers.php';


/**
 * Read test data from a specific file path.
 *
 * @param string $path Path to a JSON file with test data.
 * @return array       Decoded JSON as associative array.
 */
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


/**
 * Create or reuse an ORganization.
 * Searches v2 by exact name. If not found, creates it.
 *
 * @param array  $data     Input data.
 * @param string $apiToken Pipedrive API token.
 * @return int|null        Organization ID or null on failure.
 */
function createOrganization(array $data, string $apiToken): ?int {
    $orgName = '[' . ($data['contact_type'] ?? 'Ukjent') . '] ' . ($data['name'] ?? 'Ukjent');

    // Check if organization exists
    $checkUrl = "https://nettbureaucase.pipedrive.com/api/v2/organizations/search"
              . "?term=" . rawurlencode($orgName)
              . "&fields=name&exact_match=true&limit=1&api_token=$apiToken";

              
    // If this is true the organization exist
    $getResponse = sendGetRequest($checkUrl);
    if (!$getResponse) return null; 
    $id = $getResponse['data']['items'][0]['item']['id'] ?? null;
    if ($id) 
        return (int)$id;

    // Create a new organization
    $postUrl = "https://nettbureaucase.pipedrive.com/api/v2/organizations?api_token=$apiToken";
    $postResponse = sendPostRequest($postUrl, ['name' => $orgName]);
    if (!$postResponse) return null; 
    return isset($postResponse['data']['id']) ? (int)$postResponse['data']['id'] : null;
}

/**
 * Create or reuse a Person linked to an Organization.
 * Searches v2 by exact name. If not found, creates (v2) with optional emails/phones
 * and sets a custom contact_type field.
 *
 * @param array  $data     Input data.
 * @param int    $orgId    Organization ID to link.
 * @param string $apiToken Pipedrive API token.
 * @return int|null        Person ID or null on failure.
 */
function createPerson($data, $orgId, $apiToken):?int {

    $personName = $data['name'] ?? 'Ukjent';

    // 1) Check if person exists by exact name
    $checkUrl = "https://nettbureaucase.pipedrive.com/api/v2/persons/search"
              . "?term=" . rawurlencode($personName)
              . "&fields=name&exact_match=true&limit=1&api_token=$apiToken";

    $getResponse = sendGetRequest($checkUrl);
    if (!$getResponse) return null; 
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
    $postResponse = sendPostRequest($postUrl, $payload);
    if (!$postResponse) return null; 
    return isset($postResponse['data']['id']) ? (int)$postResponse['data']['id'] : null;
}

/**
 * Create or reuse a Lead linked to the Person and Organization.
 * Searches v2 by exact title "[LEAD] {name}". If not found, creates via v1.
 *
 * @param array  $data      Input data.
 * @param int    $personId  Person ID.
 * @param int    $orgId     Organization ID.
 * @param string $apiToken  Pipedrive API token.
 * @return string|null      Lead UUID string or null on failure.
 */
function createLead($data, $personId, $orgId, $apiToken): ?string {

    // Build a simple title (expects $data['title'])
    $title = '[LEAD] ' . trim($data['name'] ?? 'Ukjent');

    $checkUrl = "https://nettbureaucase.pipedrive.com/api/v2/leads/search"
              . "?term=" . rawurlencode($title)
              . "&fields=title&exact_match=true&limit=1"
              . "&api_token=" . $apiToken;

    $getResponse = sendGetRequest($checkUrl);
    if (!$getResponse) return null; 
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
    $postResponse = sendPostRequest($postUrl, $payload);
    if (!$postResponse) return null; 

    return $postResponse['data']['id'] ?? null;
}