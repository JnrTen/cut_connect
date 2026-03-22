<?php
// admin_check_payments.php - Admin page to check payment status
include 'config.php';

// Simple admin check (you can add proper authentication later)
$admin_key = $_GET['key'] ?? '';
if($admin_key != 'cut_admin_2026') {
    die("Unauthorized access");
}

// Get all pending payments
$stmt = $pdo->prepare("SELECT * FROM payments WHERE status = 'pending' ORDER BY created_at DESC");
$stmt->execute();
$pending_payments = $stmt->fetchAll();

// Get all successful payments
$stmt = $pdo->prepare("SELECT * FROM payments WHERE status = 'paid' ORDER BY created_at DESC LIMIT 50");
$stmt->execute();
$paid_payments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Payment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1>Payment Management</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5>Pending Payments (<?php echo count($pending_payments); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>ID</th><th>Student ID</th><th>Reference</th><th>Amount</th><th>Created</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($pending_payments as $p): ?>
                                    <tr>
                                        <td><?php echo $p['id']; ?></td>
                                        <td><?php echo $p['student_id']; ?></td>
                                        <td><?php echo $p['paynow_reference']; ?></td>
                                        <td>$<?php echo $p['amount']; ?></td>
                                        <td><?php echo $p['created_at']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5>Successful Payments (<?php echo count($paid_payments); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>ID</th><th>Student ID</th><th>Amount</th><th>Paid At</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($paid_payments as $p): ?>
                                    <tr>
                                        <td><?php echo $p['id']; ?></td>
                                        <td><?php echo $p['student_id']; ?></td>
                                        <td>$<?php echo $p['amount']; ?></td>
                                        <td><?php echo $p['paid_at']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <h5>Total Revenue: $<?php 
                $stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'paid'");
                echo number_format($stmt->fetchColumn(), 2);
            ?></h5>
        </div>
    </div>
</body>
</html>