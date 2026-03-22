<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CUT Connect - Student Connection Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .hero { padding: 100px 0; color: white; }
        .btn-primary { background: #ff8c00; border: none; }
        .btn-primary:hover { background: #e67e00; }
        .feature-card { transition: transform 0.3s; }
        .feature-card:hover { transform: translateY(-10px); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-users"></i> CUT Connect
            </a>
            <div>
                <?php if(isset($_SESSION['student_id'])): ?>
                    <a href="dashboard.php" class="btn btn-outline-light">Dashboard</a>
                    <a href="logout.php" class="btn btn-light ms-2">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light">Login</a>
                    <a href="register.php" class="btn btn-light ms-2">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="hero text-center">
        <div class="container">
            <h1 class="display-4">Welcome to CUT Connect</h1>
            <p class="lead">Connect with fellow CUT students, find study partners, and make lifelong friends!</p>
            <?php if(!isset($_SESSION['student_id'])): ?>
                <a href="register.php" class="btn btn-primary btn-lg mt-3">Get Started Free</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-primary btn-lg mt-3">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container py-5">
        <div class="row text-center text-white">
            <div class="col-md-4 mb-4">
                <div class="card bg-dark text-white h-100 feature-card">
                    <div class="card-body">
                        <i class="fas fa-user-plus fa-3x mb-3"></i>
                        <h3>Free Registration</h3>
                        <p>Sign up for free and create your profile</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-dark text-white h-100 feature-card">
                    <div class="card-body">
                        <i class="fas fa-heart fa-3x mb-3"></i>
                        <h3>Smart Matching</h3>
                        <p>Find compatible study partners and friends</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-dark text-white h-100 feature-card">
                    <div class="card-body">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <h3>Premium Chat</h3>
                        <p>Subscribe to unlock unlimited messaging (R50/year)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>