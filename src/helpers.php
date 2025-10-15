<?php

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

function contactType($typeData):?int {
   switch ($typeData) {
    case 'Privat':
        return 27;
    case 'Borettslag':
        return 28;
    case 'Bedrift':
        return 29;
    default:
        return null;
    }
}

function housingType($typeData): ?int {
    switch ($typeData) {
        case 'Enebolig':     return 30;
        case 'Leilighet':    return 31;
        case 'Tomannsbolig': return 32;
        case 'Rekkehus':     return 33;
        case 'Hytte':        return 34;
        case 'Annet':        return 35;
        default:             return null;
    }
}

function dealType($typeData): ?int
{
    switch ($typeData) {
        case 'Alle strømavtaler er aktuelle':
            return 42;
        case 'Fastpris':
            return 43;
        case 'Spotpris':
            return 44;
        case 'Kraftforvaltning':
            return 45;
        case 'Annen avtale/vet ikke':
            return 46;
        default:
            return null;
    }
}