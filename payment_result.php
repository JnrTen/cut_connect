<?php
// payment_result.php - Handles PayNow result notification (webhook)
include 'config.php';

// Get POST data from PayNow
$reference = $_POST['reference'] ?? '';
$status = $_POST['status'] ?? '';
$amount = $_POST['amount'] ?? '';
$paynow_reference = $_POST['paynowreference'] ?? '';

// Log the incoming request for debugging
$log_data = date('Y-m-d H:i:s') . " - " . print_r($_POST, true) . "\n";
file_put_contents('paynow_log.txt', $log_data, FILE_APPEND);

if($reference && $status == 'Paid') {
    // Update payment status
    $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = datetime('now') WHERE paynow_reference = ?");
    $stmt->execute([$reference]);
    
    // Get payment details
    $stmt = $pdo->prepare("SELECT student_id FROM payments WHERE paynow_reference = ?");
    $stmt->execute([$reference]);
    $payment = $stmt->fetch();
    
    if($payment) {
        // Activate subscription
        $update = $pdo->prepare("UPDATE students SET is_subscribed = 1, subscription_expiry = datetime('now', '+1 year') WHERE id = ?");
        $update->execute([$payment['student_id']]);
        
        // Log success
        file_put_contents('paynow_log.txt', "Payment activated for student: " . $payment['student_id'] . "\n", FILE_APPEND);
    }
}

// Respond to PayNow
echo "OK";
?>