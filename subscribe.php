<?php 
include 'config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch();

// PayNow Configuration
define('PAYNOW_ID', '23893');
define('PAYNOW_KEY', '1cf3a5ad-d64b-4bf3-813c-dc46ad37b4f8');
define('PAYNOW_RETURN_URL', 'http://localhost/cut_connect/payment_callback.php');
define('PAYNOW_RESULT_URL', 'http://localhost/cut_connect/payment_result.php');
define('PAYNOW_MERCHANT_EMAIL', 'mupashepherd@gmail.com');

// Function to create hash
function CreatePayNowHash($values, $integrationKey) {
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

// Function to initiate mobile money payment
function initiateMobilePayment($amount, $phone, $reference, $student_email) {
    $formatted_amount = number_format($amount, 2, '.', '');
    $additional_info = 'CUT Connect Premium Subscription';
    
    // Clean phone number
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if(substr($phone, 0, 1) == '0') {
        $phone = '263' . substr($phone, 1);
    } elseif(strlen($phone) == 9) {
        $phone = '263' . $phone;
    }
    
    // Parameters in alphabetical order
    $values = array(
        'additionalinfo' => $additional_info,
        'amount' => $formatted_amount,
        'authemail' => PAYNOW_MERCHANT_EMAIL,
        'id' => PAYNOW_ID,
        'phone' => $phone,
        'reference' => $reference,
        'resulturl' => PAYNOW_RESULT_URL,
        'returnurl' => PAYNOW_RETURN_URL,
        'status' => 'Message'
    );
    
    $hash = CreatePayNowHash($values, PAYNOW_KEY);
    $values['hash'] = $hash;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.paynow.co.zw/interface/initiatetransaction');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($values));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    parse_str($response, $output);
    return $output;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    $amount = 1.00;
    $reference = 'CUT_' . $student['id'] . '_' . time();
    $phone = $_POST['phone'];
    
    if(empty($phone)) {
        $error = "Please enter your mobile number";
    } else {
        try {
            // Create payment record
            $stmt = $pdo->prepare("INSERT INTO payments (student_id, paynow_reference, amount) VALUES (?, ?, ?)");
            $stmt->execute([$student['id'], $reference, $amount]);
            $payment_id = $pdo->lastInsertId();
            
            // Initiate payment
            $result = initiateMobilePayment($amount, $phone, $reference, $student['email']);
            
            if(isset($result['status']) && $result['status'] == 'Ok') {
                // Check if poll_url exists in response and if column exists
                if(isset($result['pollurl'])) {
                    // Check if poll_url column exists
                    try {
                        $update = $pdo->prepare("UPDATE payments SET poll_url = ? WHERE id = ?");
                        $update->execute([$result['pollurl'], $payment_id]);
                    } catch(PDOException $e) {
                        // Column doesn't exist, ignore (not critical)
                        error_log("Could not save poll_url: " . $e->getMessage());
                    }
                }
                
                $success_message = "✅ Payment request sent! Please check your phone and enter your PIN to complete the payment.";
                $payment_reference = $reference;
            } else {
                $error_message = $result['error'] ?? 'Unknown error';
                $error = "Payment initiation failed: " . $error_message;
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if(strlen($phone) == 12 && substr($phone, 0, 3) == '263') {
        return '0' . substr($phone, 3);
    }
    return $phone;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe - CUT Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .payment-option {
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-option:hover {
            border-color: #ff8c00;
            background: #fff8f0;
        }
        .payment-option.selected {
            border-color: #ff8c00;
            background: #fff8f0;
        }
        .phone-input {
            display: none;
            margin-top: 15px;
        }
        .phone-input.show {
            display: block;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .loading.show {
            display: block;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #ff8c00;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-users"></i> CUT Connect
            </a>
            <a href="dashboard.php" class="btn btn-outline-light">← Back</a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0"><i class="fas fa-gem"></i> Upgrade to Premium</h4>
                    </div>
                    <div class="card-body text-center">
                        <?php if($student['is_subscribed']): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle fa-2x"></i>
                                <h5>You are already a Premium Member!</h5>
                                <p>Your subscription expires: <?php echo date('F d, Y', strtotime($student['subscription_expiry'])); ?></p>
                            </div>
                            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                        <?php else: ?>
                            <?php if(isset($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <strong>Payment Failed</strong><br>
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if(isset($success_message)): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                    <h5>Payment Request Sent!</h5>
                                    <p><?php echo $success_message; ?></p>
                                    <hr>
                                    <div class="text-center">
                                        <i class="fas fa-mobile-alt fa-3x text-success mb-2"></i>
                                        <p><strong>Instructions:</strong></p>
                                        <ol class="text-start">
                                            <li>Check your phone for the payment request from PayNow</li>
                                            <li>Open the message and follow the prompts</li>
                                            <li>Enter your PIN to confirm payment</li>
                                            <li>Wait for confirmation</li>
                                        </ol>
                                        <p class="small text-muted">Reference: <?php echo $payment_reference; ?></p>
                                        <a href="check_payment.php?ref=<?php echo $payment_reference; ?>" class="btn btn-primary mt-2">
                                            Check Payment Status
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="pricing-card">
                                    <h2 class="text-warning">$1.00 USD</h2>
                                    <p class="text-muted">One-time payment for 1 year premium access</p>
                                    
                                    <div class="text-start mt-4">
                                        <h5><i class="fas fa-check-circle text-success"></i> Premium Benefits:</h5>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success"></i> View full student profiles</li>
                                            <li><i class="fas fa-check text-success"></i> Unlimited messaging</li>
                                            <li><i class="fas fa-check text-success"></i> Priority in search results</li>
                                            <li><i class="fas fa-check text-success"></i> Verified badge on profile</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <form method="POST" id="paymentForm">
                                            <div class="alert alert-info mb-3">
                                                <i class="fas fa-mobile-alt"></i> 
                                                <strong>Pay with Mobile Money</strong><br>
                                                <small>Enter your Ecocash or OneMoney number. You'll receive a payment request on your phone.</small>
                                            </div>
                                            
                                            <div class="payment-option" data-method="ecocash">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-mobile-alt fa-2x text-success me-3"></i>
                                                    <div class="flex-grow-1">
                                                        <strong>Ecocash</strong><br>
                                                        <small>Pay using your Ecocash wallet</small>
                                                    </div>
                                                    <i class="fas fa-check-circle text-success" style="display: none;"></i>
                                                </div>
                                            </div>
                                            
                                            <div class="payment-option" data-method="onemoney">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-mobile-alt fa-2x text-primary me-3"></i>
                                                    <div class="flex-grow-1">
                                                        <strong>OneMoney</strong><br>
                                                        <small>Pay using your OneMoney wallet</small>
                                                    </div>
                                                    <i class="fas fa-check-circle text-success" style="display: none;"></i>
                                                </div>
                                            </div>
                                            
                                            <div class="phone-input mt-3" id="phoneInput">
                                                <label class="form-label">Mobile Number</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">+263</span>
                                                    <input type="tel" name="phone" class="form-control" 
                                                           id="phoneNumber"
                                                           placeholder="77 123 4567" 
                                                           pattern="[0-9]{9}"
                                                           maxlength="9"
                                                           required>
                                                </div>
                                                <small class="text-muted">Enter your 9-digit mobile number (e.g., 771234567)</small>
                                            </div>
                                            
                                            <input type="hidden" name="payment_method" id="payment_method" value="">
                                            
                                            <div class="loading" id="loading">
                                                <div class="spinner"></div>
                                                <p class="mt-2">Sending payment request to your phone...</p>
                                                <p class="small text-muted">Please check your phone for the payment prompt</p>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-warning btn-lg w-100 mt-3" id="payBtn" disabled>
                                                <i class="fas fa-arrow-right"></i> Select Payment Method First
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let selectedMethod = '';
        
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(opt => {
                    opt.classList.remove('selected');
                    opt.querySelector('.fa-check-circle').style.display = 'none';
                });
                
                this.classList.add('selected');
                this.querySelector('.fa-check-circle').style.display = 'block';
                
                selectedMethod = this.dataset.method;
                document.getElementById('payment_method').value = selectedMethod;
                document.getElementById('phoneInput').classList.add('show');
                document.getElementById('payBtn').disabled = false;
                document.getElementById('payBtn').innerHTML = '<i class="fas fa-arrow-right"></i> Pay $1 USD with ' + 
                    (selectedMethod === 'ecocash' ? 'Ecocash' : 'OneMoney');
            });
        });
        
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            if (!selectedMethod) {
                e.preventDefault();
                alert('Please select a payment method');
                return false;
            }
            
            const phone = document.getElementById('phoneNumber').value;
            if (!phone || phone.length < 9) {
                e.preventDefault();
                alert('Please enter a valid 9-digit mobile number (e.g., 771234567)');
                return false;
            }
            
            document.getElementById('loading').classList.add('show');
            document.getElementById('payBtn').disabled = true;
            document.getElementById('payBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending request...';
            
            return true;
        });
        
        document.getElementById('phoneNumber').addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value.length > 9) value = value.slice(0, 9);
            this.value = value;
        });
    </script>
</body>
</html>