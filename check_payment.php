<?php
// check_payment.php - Check if payment was completed
include 'config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$reference = $_GET['ref'] ?? '';
$student_id = $_SESSION['student_id'];

// Get payment record
$stmt = $pdo->prepare("SELECT * FROM payments WHERE paynow_reference = ? AND student_id = ?");
$stmt->execute([$reference, $student_id]);
$payment = $stmt->fetch();

if(!$payment) {
    header('Location: subscribe.php');
    exit();
}

// Check payment status with PayNow
if($payment['status'] == 'pending' && $payment['poll_url']) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $payment['poll_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    parse_str($response, $output);
    
    if(isset($output['status']) && $output['status'] == 'Paid') {
        // Update payment status
        $update = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = datetime('now') WHERE id = ?");
        $update->execute([$payment['id']]);
        
        // Activate subscription
        $update = $pdo->prepare("UPDATE students SET is_subscribed = 1, subscription_expiry = datetime('now', '+1 year') WHERE id = ?");
        $update->execute([$student_id]);
        
        $_SESSION['payment_success'] = "Payment successful! Your premium account is now active.";
        header('Location: dashboard.php');
        exit();
    }
}

// Auto-refresh every 5 seconds to check if payment completed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checking Payment - CUT Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #ff8c00;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <meta http-equiv="refresh" content="5">
</head>
<body>
    <div class="container py-5 text-center">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body py-5">
                        <i class="fas fa-clock fa-4x text-warning mb-3"></i>
                        <h3>Waiting for Payment Confirmation</h3>
                        <p>Please check your phone and enter your PIN to complete the payment.</p>
                        <div class="spinner"></div>
                        <p class="mt-3 text-muted">
                            <small>Reference: <?php echo htmlspecialchars($reference); ?></small><br>
                            <small>This page will refresh automatically when payment is confirmed.</small>
                        </p>
                        <a href="dashboard.php" class="btn btn-secondary mt-3">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>