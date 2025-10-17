<?php
// run_tests

require 'pipedrive_lead_integration.php';

$testPath = $argv[1] ?? getenv('TEST_DATA') ?? 'tests/test_data.json';
// load test data
$data = getTestDataFrom($testPath);

/// Retreive the API token
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

$token = $_ENV['PIPEDRIVE_API_TOKEN'] ?? null;

// Run once
$o1 = createOrganization($data, $token);
$p1 = $o1 ? createPerson($data, $o1, $token) : null;
$l1 = ($p1 && $o1) ? createLead($data, $p1, $o1, $token) : null;

// Gives the data som chance to be posted on the API. If not the function may not be able to find the org,user,lead in the search.
sleep(2);

// run again to test duplication
$o2 = createOrganization($data, $token);
$p2 = $o2 ? createPerson($data, $o2, $token) : null;
$l2 = ($p2 && $o2) ? createLead($data, $p2, $o2, $token) : null;

// Print
$pass = ($o1 && $o1===$o2) && ($p1 && $p1===$p2) && ($l1 && $l1===$l2);
echo $pass ? "PASS\n" : "FAIL\n";
echo "run1: org=$o1 person=$p1 lead=$l1\n";
echo "run2: org=$o2 person=$p2 lead=$l2\n";

$orgRaw  = $o1 ? sendGetRequest("https://nettbureaucase.pipedrive.com/api/v2/organizations/$o1?api_token=$token") : null;
$persRaw = $p1 ? sendGetRequest("https://nettbureaucase.pipedrive.com/api/v2/persons/$p1?api_token=$token")       : null;
// lead detail is v1
$leadRaw = $l1 ? sendGetRequest("https://nettbureaucase.pipedrive.com/api/v1/leads/$l1?api_token=$token")          : null;

echo "\nDisplay response\n";
echo "Organization:\n";
echo json_encode($orgRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "Person:\n";
echo json_encode($persRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "Lead:\n";
echo json_encode($leadRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";