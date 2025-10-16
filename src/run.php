<?php
// run_tests.php — minimal runner

require 'pipedrive_lead_integration.php'; // <-- change if your functions live in another file

// load test data
$data = getTestData();

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

$personPostUrl = "https://nettbureaucase.pipedrive.com/api/v2/persons?api_token={$token}";
$leadPostUrl   = "https://nettbureaucase.pipedrive.com/api/v1/leads?api_token={$token}";

// Run once
$o1 = createOrganization($data, $token);
$p1 = $o1 ? createPerson($data, $personPostUrl, $o1, $token) : null;
$l1 = ($p1 && $o1) ? createLead($data, $leadPostUrl, $p1, $o1, $token) : null;

// run again to test duplication
$o2 = createOrganization($data, $token);
$p2 = $o2 ? createPerson($data, $personPostUrl, $o2, $token) : null;
$l2 = ($p2 && $o2) ? createLead($data, $leadPostUrl, $p2, $o2, $token) : null;

// Print
$pass = ($o1 && $o1===$o2) && ($p1 && $p1===$p2) && ($l1 && $l1===$l2);
echo $pass ? "PASS\n" : "FAIL\n";
echo "run1: org=$o1 person=$p1 lead=$l1\n";
echo "run2: org=$o2 person=$p2 lead=$l2\n";