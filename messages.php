<?php 
include 'config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];

// Get current student
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$current_student = $stmt->fetch();

// Check subscription
if (!$current_student['is_subscribed']) {
    header('Location: subscribe.php');
    exit();
}

// Get conversations
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        CASE 
            WHEN sender_id = ? THEN receiver_id 
            ELSE sender_id 
        END as other_user_id,
        MAX(created_at) as last_message_time
    FROM messages 
    WHERE sender_id = ? OR receiver_id = ?
    GROUP BY other_user_id
    ORDER BY last_message_time DESC
");
$stmt->execute([$student_id, $student_id, $student_id]);
$conversations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - CUT Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-envelope"></i> Messages</h5>
                    </div>
                    <div class="card-body">
                        <?php if(count($conversations) > 0): ?>
                            <?php foreach($conversations as $conv): ?>
                                <?php
                                $other = $pdo->prepare("SELECT * FROM students WHERE id = ?");
                                $other->execute([$conv['other_user_id']]);
                                $other_user = $other->fetch();
                                
                                // Get unread count
                                $unread = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND sender_id = ? AND is_read = 0");
                                $unread->execute([$student_id, $conv['other_user_id']]);
                                $unread_count = $unread->fetchColumn();
                                ?>
                                <a href="chat.php?user=<?php echo $conv['other_user_id']; ?>" class="text-decoration-none">
                                    <div class="card mb-2 hover-card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <img src="uploads/<?php echo $other_user['profile_image'] ?: 'default.png'; ?>" 
                                                     width="50" height="50" class="rounded-circle me-3">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0"><?php echo $other_user['name'] . ' ' . $other_user['surname']; ?></h6>
                                                    <small class="text-muted"><?php echo $other_user['course']; ?></small>
                                                </div>
                                                <?php if($unread_count > 0): ?>
                                                    <span class="badge bg-danger rounded-pill"><?php echo $unread_count; ?></span>
                                                <?php endif; ?>
                                                <i class="fas fa-chevron-right text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-muted">No messages yet. Start a conversation with someone!</p>
                            <div class="text-center">
                                <a href="dashboard.php" class="btn btn-primary">Find Connections</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .hover-card:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
            transition: all 0.3s;
        }
    </style>
</body>
</html>