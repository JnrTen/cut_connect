<?php
// payment_callback.php - Handle PayNow redirect
include 'config.php';

$reference = $_GET['reference'] ?? '';

if($reference) {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE paynow_reference = ?");
    $stmt->execute([$reference]);
    $payment = $stmt->fetch();
    
    if($payment && $payment['status'] == 'pending') {
        // Activate subscription
        $update = $pdo->prepare("UPDATE students SET is_subscribed = 1, subscription_expiry = datetime('now', '+1 year') WHERE id = ?");
        $update->execute([$payment['student_id']]);
        
        $update = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = datetime('now') WHERE id = ?");
        $update->execute([$payment['id']]);
        
        $_SESSION['payment_success'] = "Payment successful! Your premium account is now active.";
    }
}

header('Location: dashboard.php');
exit();
?>