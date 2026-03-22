<?php 
include 'config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

// Get current student
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch();

// Get suggested connections (other students)
$suggestions = $pdo->prepare("SELECT * FROM students WHERE id != ? ORDER BY RANDOM() LIMIT 10");
$suggestions->execute([$_SESSION['student_id']]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CUT Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-img { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #ff8c00; }
        .suggestion-card { cursor: pointer; transition: transform 0.2s; }
        .suggestion-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-users"></i> CUT Connect
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
                <a class="nav-link" href="messages.php">
                    <i class="fas fa-envelope"></i> Messages
                </a>
                <a class="nav-link" href="subscribe.php">
                    <i class="fas fa-gem"></i> Subscribe
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <!-- Profile Card -->
            <div class="col-md-4 mb-4">
                <div class="card shadow text-center">
                    <div class="card-body">
                        <img src="uploads/<?php echo $student['profile_image'] ?: 'default.png'; ?>" 
                             class="profile-img mb-3">
                        <h4><?php echo $student['name'] . ' ' . $student['surname']; ?></h4>
                        <p class="text-muted">
                            <i class="fas fa-graduation-cap"></i> <?php echo $student['course'] ?: 'Course not set'; ?>
                        </p>
                        <p class="small"><?php echo $student['bio'] ?: 'No bio yet. Click Edit Profile to add one!'; ?></p>
                        
                        <?php if($student['is_subscribed']): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-crown"></i> Premium Member
                            </span>
                            <p class="small text-muted mt-2">
                                Expires: <?php echo date('M d, Y', strtotime($student['subscription_expiry'])); ?>
                            </p>
                        <?php else: ?>
                            <a href="subscribe.php" class="btn btn-warning btn-sm">
                                <i class="fas fa-gem"></i> Subscribe (R50/year)
                            </a>
                        <?php endif; ?>
                        
                        <hr>
                        <a href="profile.php" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Suggestions -->
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-users"></i> Suggested Connections</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if($suggestions->rowCount() > 0): ?>
                                <?php while($suggestion = $suggestions->fetch()): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card suggestion-card" onclick="location.href='view_profile.php?id=<?php echo $suggestion['id']; ?>'">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <img src="uploads/<?php echo $suggestion['profile_image'] ?: 'default.png'; ?>" 
                                                         width="60" height="60" class="rounded-circle me-3">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0"><?php echo $suggestion['name'] . ' ' . $suggestion['surname']; ?></h6>
                                                        <small class="text-muted">
                                                            <i class="fas fa-graduation-cap"></i> <?php echo $suggestion['course'] ?: 'Student'; ?>
                                                        </small>
                                                        <div class="mt-1">
                                                            <small class="text-primary">
                                                                <?php echo $suggestion['looking_for'] ?: 'Open to connections'; ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <a href="view_profile.php?id=<?php echo $suggestion['id']; ?>" class="btn btn-sm btn-primary">
                                                        View
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-center text-muted">No other students found yet. Share the app with friends!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>