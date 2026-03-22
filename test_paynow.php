<?php
// test_paynow.php - Full debug to see what PayNow returns

function CreateHash($values, $IntegrationKey) {
    $string = "";
    
    // Sort keys alphabetically
    ksort($values);
    
    // Concatenate all values EXCEPT 'hash'
    foreach($values as $key => $value) {
        if(strtoupper($key) != "HASH") {
            $string .= $value;
        }
    }
    
    // Append the integration key
    $string .= $IntegrationKey;
    
    // Generate SHA512 hash and convert to uppercase
    $hash = hash("sha512", $string);
    
    return strtoupper($hash);
}

$integration_id = '23893';
$integration_key = '1cf3a5ad-d64b-4bf3-813c-dc46ad37b4f8';
$return_url = 'http://localhost/cut_connect/payment_callback.php';
$result_url = 'http://localhost/cut_connect/payment_result.php';

// Your registered PayNow email
$merchant_email = 'mupashepherd@gmail.com';

echo "<h2>Testing PayNow Integration - Full Debug</h2>";
echo "Integration ID: $integration_id<br>";
echo "Registered Email: $merchant_email<br><br>";

// Create test payment
$reference = 'CUT_' . time();
$amount = '1.00';

$values = array(
    'additionalinfo' => 'CUT Connect Premium Subscription',
    'amount' => $amount,
    'authemail' => $merchant_email,
    'id' => $integration_id,
    'reference' => $reference,
    'resulturl' => $result_url,
    'returnurl' => $return_url,
    'status' => 'Message'
);

// Generate hash
$hash = CreateHash($values, $integration_key);
$values['hash'] = $hash;

echo "<strong>Request Data:</strong><br>";
echo "<pre>";
print_r($values);
echo "</pre>";

// Send to PayNow
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.paynow.co.zw/interface/initiatetransaction');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($values));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<strong>HTTP Response Code:</strong> $http_code<br>";
echo "<strong>Raw Response:</strong><br><pre>" . htmlspecialchars($response) . "</pre><br>";

// Parse response
parse_str($response, $output);

echo "<strong>Parsed Response:</strong><br>";
echo "<pre>";
print_r($output);
echo "</pre>";

if(isset($output['status']) && $output['status'] == 'Ok') {
    echo "<div style='color:green; padding:10px; background:#e8f5e9; border-radius:5px;'>";
    echo "✅ <strong>PayNow connection successful!</strong><br>";
    
    if(isset($output['redirecturl']) && !empty($output['redirecturl'])) {
        echo "Redirect URL: " . $output['redirecturl'] . "<br>";
        echo "<a href='" . $output['redirecturl'] . "' target='_blank' class='btn btn-success'>Click here to go to PayNow Payment Page</a>";
    } elseif(isset($output['browserurl']) && !empty($output['browserurl'])) {
        echo "Browser URL: " . $output['browserurl'] . "<br>";
        echo "<a href='" . $output['browserurl'] . "' target='_blank' class='btn btn-success'>Click here to go to PayNow Payment Page</a>";
    } elseif(isset($output['pollurl']) && !empty($output['pollurl'])) {
        echo "Poll URL: " . $output['pollurl'] . "<br>";
        echo "You may need to poll this URL to get the redirect URL.";
    } else {
        echo "No redirect URL found. Full response above.";
    }
    echo "</div>";
} else {
    echo "<div style='color:red; padding:10px; background:#ffebee; border-radius:5px;'>";
    echo "❌ <strong>PayNow connection failed!</strong><br>";
    echo "Error: " . ($output['error'] ?? 'Unknown error');
    echo "</div>";
}
?>