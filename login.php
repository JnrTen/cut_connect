<?php 
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_number = $_POST['student_number'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_number = ?");
    $stmt->execute([$student_number]);
    $student = $stmt->fetch();
    
    if ($student && password_verify($password, $student['password_hash'])) {
        $_SESSION['student_id'] = $student['id'];
        
        // Update last login
        $update = $pdo->prepare("UPDATE students SET last_login = datetime('now') WHERE id = ?");
        $update->execute([$student['id']]);
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid student number or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CUT Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-sign-in-alt"></i> Student Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Student Number</label>
                                <input type="text" name="student_number" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <hr>
                        <p class="text-center mb-0">Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>