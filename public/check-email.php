<?php
/**
 * Simple Proxy Bridge to Mailpit
 * Because Nginx/Firewall blocks direct access to port 8025
 */

$targetUrl = 'http://127.0.0.1:8025';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo 'Error connecting to Mailpit: ' . curl_error($ch);
} else {
    http_response_code($httpCode);
    echo $response;
}

curl_close($ch);
