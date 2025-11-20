<?php
/**
 * Generate VAPID Keys for Web Push Notifications
 * 
 * This script generates VAPID (Voluntary Application Server Identification) keys
 * for Web Push Notifications.
 * 
 * Usage: php generate_vapid_keys.php
 * 
 * Alternative methods:
 * 1. Node.js: npx web-push generate-vapid-keys
 * 2. Online: https://tools.reactpwa.com/vapid
 * 3. Python: pip install py-vapid && python -c "from py_vapid import Vapid01; v=Vapid01(); v.generate_keys(); print('Public:', v.public_key); print('Private:', v.private_key)"
 */

// Check if OpenSSL is available
if (!function_exists('openssl_pkey_new')) {
    die("Error: OpenSSL extension is required to generate VAPID keys.\n");
}

echo "Generating VAPID keys for Web Push Notifications...\n\n";

// Generate EC key pair for prime256v1 curve
$config = [
    "private_key_type" => OPENSSL_KEYTYPE_EC,
    "curve_name" => "prime256v1"
];

$privateKey = openssl_pkey_new($config);

if (!$privateKey) {
    $error = openssl_error_string();
    die("Error: Failed to generate private key. $error\n");
}

// Get private key details
$privateKeyDetails = openssl_pkey_get_details($privateKey);
if (!$privateKeyDetails || !isset($privateKeyDetails['ec'])) {
    die("Error: Failed to get private key details.\n");
}

// Extract private key (d value)
$privateKeyD = $privateKeyDetails['ec']['d'];
if (!$privateKeyD) {
    die("Error: Failed to extract private key.\n");
}

// Extract public key coordinates
if (!isset($privateKeyDetails['ec']['x']) || !isset($privateKeyDetails['ec']['y'])) {
    die("Error: Failed to extract public key coordinates.\n");
}

$x = $privateKeyDetails['ec']['x'];
$y = $privateKeyDetails['ec']['y'];

// Convert to base64url format (for VAPID)
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// VAPID public key format: 0x04 (uncompressed) + X (32 bytes) + Y (32 bytes)
$publicKeyBytes = "\x04" . $x . $y;
$publicKeyBase64 = base64url_encode($publicKeyBytes);

// VAPID private key: just the d value
$privateKeyBase64 = base64url_encode($privateKeyD);

echo "✅ VAPID Keys Generated Successfully!\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "PUBLIC KEY (Use this in your JavaScript code):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo $publicKeyBase64 . "\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "PRIVATE KEY (Keep this SECRET! Use on server only):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo $privateKeyBase64 . "\n\n";

echo "⚠️  IMPORTANT:\n";
echo "   - Keep the PRIVATE KEY secret and secure\n";
echo "   - Never commit the private key to version control\n";
echo "   - Use the PUBLIC KEY in your client-side JavaScript\n";
echo "   - Use the PRIVATE KEY on your server for sending push notifications\n\n";

echo "📝 Next Steps:\n";
echo "   1. Copy the PUBLIC KEY above\n";
echo "   2. Open app/Views/layouts/main.php\n";
echo "   3. Find the getVapidPublicKey() function\n";
echo "   4. Replace the placeholder with your PUBLIC KEY\n";
echo "   5. Store the PRIVATE KEY securely (e.g., in .env file)\n";
echo "   6. Use the PRIVATE KEY in your server-side push notification code\n\n";

echo "💡 Alternative Methods to Generate Keys:\n";
echo "   - Node.js: npx web-push generate-vapid-keys\n";
echo "   - Online: https://tools.reactpwa.com/vapid\n";
echo "   - Python: pip install py-vapid\n\n";
