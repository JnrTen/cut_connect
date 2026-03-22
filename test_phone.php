<?php
// test_phone_payment.php - Test phone payment with debugging

$integration_id = '23893';
$integration_key = '1cf3a5ad-d64b-4bf3-813c-dc46ad37b4f8';
$merchant_email = 'mupashepherd@gmail.com';
$test_phone = '771234567'; // Test phone number

function CreateHash($values, $integrationKey) {
    $string = "";
    ksort($values);
    foreach($values as $key => $value) {
        if(strtoupper($key) != "HASH") {
            $string .= $value;
        }
    }
    $string .= $integrationKey;
    return strtoupper(hash("sha512", $string));
}

echo "<h2>Testing Mobile Money Payment</h2>";

$reference = 'TEST_PHONE_' . time();
$amount = '1.00';
$additional_info = 'CUT Connect Test';

// Clean phone
$phone = '263' . $test_phone;

// Parameters in alphabetical order
$values = array(
    'additionalinfo' => $additional_info,
    'amount' => $amount,
    'authemail' => $merchant_email,
    'id' => $integration_id,
    'phone' => $phone,
    'reference' => $reference,
    'resulturl' => 'http://localhost/cut_connect/payment_result.php',
    'returnurl' => 'http://localhost/cut_connect/payment_callback.php',
    'status' => 'Message'
);

echo "<strong>Parameters (Alphabetical Order):</strong><br>";
echo "<pre>";
print_r($values);
echo "</pre>";

$hash = CreateHash($values, $integration_key);
$values['hash'] = $hash;

echo "<strong>Generated Hash:</strong> " . $hash . "<br>";
echo "<strong>Hash Starts With:</strong> " . substr($hash, 0, 6) . "<br><br>";

// Send to PayNow
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.paynow.co.zw/interface/initiatetransaction');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($values));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
curl_close($ch);

parse_str($response, $output);

echo "<strong>PayNow Response:</strong><br>";
echo "<pre>";
print_r($output);
echo "</pre>";

if(isset($output['status']) && $output['status'] == 'Ok') {
    echo "<div style='color:green; padding:10px; background:#e8f5e9; border-radius:5px;'>";
    echo "✅ <strong>Payment request sent to phone: " . $test_phone . "</strong><br>";
    echo "Reference: " . $reference . "<br>";
    echo "Poll URL: " . ($output['pollurl'] ?? 'N/A') . "<br>";
    echo "</div>";
} else {
    echo "<div style='color:red; padding:10px; background:#ffebee; border-radius:5px;'>";
    echo "❌ <strong>Error:</strong> " . ($output['error'] ?? 'Unknown error');
    echo "</div>";
}
?>