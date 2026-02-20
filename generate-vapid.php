<?php

// Generate VAPID keys using pure PHP OpenSSL
$curve_name = 'prime256v1'; // P-256 curve

// Generate private key
$privateKey = openssl_pkey_new([
    'curve_name' => $curve_name,
    'private_key_type' => OPENSSL_KEYTYPE_EC,
]);

if ($privateKey === false) {
    die('Failed to generate private key: ' . openssl_error_string());
}

// Export private key
openssl_pkey_export($privateKey, $privateKeyPem);

// Get public key details
$publicKeyDetails = openssl_pkey_get_details($privateKey);
$publicKeyPem = $publicKeyDetails['key'];

// Extract raw keys
$privateKeyRaw = openssl_pkey_get_private($privateKeyPem);
$privateKeyDetails = openssl_pkey_get_details($privateKeyRaw);
$privateKeyBytes = $privateKeyDetails['ec']['d'];

$publicKeyRaw = openssl_pkey_get_public($publicKeyPem);
$publicKeyDetails = openssl_pkey_get_details($publicKeyRaw);
$publicKeyBytes = substr($publicKeyDetails['ec']['pub']['key'], -64);

// Convert to Base64 URL-safe format
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$publicKeyBase64 = base64UrlEncode($publicKeyBytes);
$privateKeyBase64 = base64UrlEncode($privateKeyBytes);

echo "======================================" . PHP_EOL;
echo "VAPID KEYS GENERATED SUCCESSFULLY" . PHP_EOL;
echo "======================================" . PHP_EOL;
echo PHP_EOL;
echo "Public Key: " . $publicKeyBase64 . PHP_EOL;
echo "Private Key: " . $privateKeyBase64 . PHP_EOL;
echo PHP_EOL;
echo "Add to your .env file:" . PHP_EOL;
echo "VAPID_PUBLIC_KEY=" . $publicKeyBase64 . PHP_EOL;
echo "VAPID_PRIVATE_KEY=" . $privateKeyBase64 . PHP_EOL;
echo PHP_EOL;
echo "======================================" . PHP_EOL;