<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$apiKey = 'sk-hV3rJIrVaxzsLiq0FwEQ9RNCYBwvm1NcMXwkYhfpUuABSnds';

$url = 'https://api.stability.ai/v2beta/stable-image/generate/core';

echo "Testing connection to Stability AI API...\n";

// Build multipart form data
$boundary = uniqid();
$delimiter = '-------------' . $boundary;
$postData = "--$delimiter\r\n";
$postData .= "Content-Disposition: form-data; name=\"prompt\"\r\n\r\n";
$postData .= "A professional avatar of a person in business attire, photorealistic\r\n";
$postData .= "--$delimiter\r\n";
$postData .= "Content-Disposition: form-data; name=\"output_format\"\r\n\r\n";
$postData .= "png\r\n";
$postData .= "--$delimiter--\r\n";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        "Content-Type: multipart/form-data; boundary=$delimiter",
        "Accept: image/*"
    ],
    CURLOPT_VERBOSE => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "Connection Error (" . curl_errno($ch) . "): " . curl_error($ch) . "\n";
} else {
    echo "Connection response received! HTTP Code: $httpCode\n";
    // If response is PNG, save it; if JSON, print it
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    if (strpos($contentType, 'image/png') !== false) {
        file_put_contents('avatar.png', $response);
        echo "Image saved as avatar.png\n";
    } else {
        echo "Response:\n$response\n";
    }
}

curl_close($ch);