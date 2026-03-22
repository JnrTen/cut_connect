<?php 
include 'config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$view_id = $_GET['id'];
$current_student_id = $_SESSION['student_id'];

// Get the profile to view
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$view_id]);
$profile = $stmt->fetch();

if(!$profile) {
    header('Location: dashboard.php');
    exit();
}

// Get current student (for subscription check)
$stmt2 = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt2->execute([$current_student_id]);
$current_student = $stmt2->fetch();

$is_subscribed = $current_student['is_subscribed'];
$can_view_full = $is_subscribed || ($view_id == $current_student_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $profile['name'] . ' ' . $profile['surname']; ?> - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-img-large { width: 200px; height: 200px; object-fit: cover; border-radius: 50%; border: 4px solid #ff8c00; }
        .locked-content { opacity: 0.5; filter: blur(3px); user-select: none; }
        .blur-text { color: transparent; text-shadow: 0 0 8px rgba(0,0,0,0.5); }
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

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <img src="uploads/<?php echo $profile['profile_image'] ?: 'default.png'; ?>" 
                             class="profile-img-large mb-4">
                        
                        <h2><?php echo $profile['name'] . ' ' . $profile['surname']; ?></h2>
                        <p class="text-muted">
                            <i class="fas fa-graduation-cap"></i> <?php echo $profile['course'] ?: 'Student'; ?>
                        </p>
                        
                        <?php if($can_view_full): ?>
                            <!-- Full Profile View (Subscribed or own profile) -->
                            <div class="text-start mt-4">
                                <h5><i class="fas fa-info-circle"></i> About</h5>
                                <p><?php echo $profile['bio'] ?: 'No bio yet.'; ?></p>
                                
                                <h5 class="mt-3"><i class="fas fa-heart"></i> Looking for</h5>
                                <p><?php echo $profile['looking_for'] ?: 'Open to connections'; ?></p>
                                
                                <?php if($profile['phone']): ?>
                                    <h5 class="mt-3"><i class="fas fa-phone"></i> Contact</h5>
                                    <p><?php echo $profile['phone']; ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($view_id != $current_student_id): ?>
                                <a href="send_message.php?to=<?php echo $profile['id']; ?>" class="btn btn-primary mt-3">
                                    <i class="fas fa-envelope"></i> Send Message
                                </a>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <!-- Locked Profile View (Requires Subscription) -->
                            <div class="alert alert-warning mt-4">
                                <i class="fas fa-lock fa-2x mb-2"></i>
                                <h5>🔒 Premium Content Locked</h5>
                                <p>Subscribe for just <strong>$1 USD</strong> to unlock full profiles and start messaging!</p>
                                <a href="subscribe.php" class="btn btn-warning btn-lg">
                                    <i class="fas fa-gem"></i> Subscribe Now - $1 USD
                                </a>
                            </div>
                            
                            <div class="text-start mt-3">
                                <div class="locked-content">
                                    <h5><i class="fas fa-info-circle"></i> About</h5>
                                    <p class="blur-text">Subscribe to view bio and contact details</p>
                                    
                                    <h5 class="mt-3"><i class="fas fa-heart"></i> Looking for</h5>
                                    <p class="blur-text">Subscribe to see what they're looking for</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>