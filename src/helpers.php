<?php


/**
 * Send a JSON POST request and return the decoded JSON response as an array.
 *
 * @param string $url     ENdpoint URL
 * @param array  $payload PHP array that will be JSON-encoded and sent as the body.
 * @return array|null     Decoded JSON array or null if something fails.
 */
function sendPostRequest($url, $payload) {
    $connect = curl_init($url);
    curl_setopt_array($connect, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload)]);

    $response = curl_exec($connect);
    if ($response === false) {
        die("cURL error: " . curl_error($connect));
    }
    $httpStatus = curl_getinfo($connect, CURLINFO_HTTP_CODE);
    curl_close($connect);
    return json_decode($response, true);
}

/**
 * Send a JSON GET request and return the decoded JSON response as an array.
 *
 * @param string $url     Endpoint URL
 * @return array|null     Decoded JSON (assoc array) or null if decoding fails
 */
function sendGetRequest($url) {
    $connect = curl_init($url);
    curl_setopt_array($connect, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"]]);

    $response = curl_exec($connect);
    if ($response === false) {
        die("cURL error: " . curl_error($connect));
    }
    $httpStatus = curl_getinfo($connect, CURLINFO_HTTP_CODE);
    curl_close($connect);
    return json_decode($response, true);
}

/**
 * Map contact type to its numeric option ID
 * @param mixed $typeData Readable string data from input
 * @return int|null The id option as an int or null if the contact type is unknown
 */
function contactType($typeData): ?int {
   switch ($typeData) {
    case 'Privat':     return 27;
    case 'Borettslag': return 28;
    case 'Bedrift':    return 29;
    default:           return null;
    }
}

/**
 * Map housing type to its numeric option ID
 * @param mixed $typeData Readable string data from input
 * @return int|null The id option as an int or null if the contact type is unknown
 */

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

/**
 * Map deal type to its numeric option ID
 * @param mixed $typeData Readable string data from input
 * @return int|null The id option as an int or null if the contact type is unknown
 */
function dealType($typeData): ?int {
    switch ($typeData) {
        case 'Alle strømavtaler er aktuelle': return 42;
        case 'Fastpris':                      return 43;
        case 'Spotpris':                      return 44;
        case 'Kraftforvaltning':              return 45;
        case 'Annen avtale/vet ikke':         return 46;
        default:                              return null;
    }
}