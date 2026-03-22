<?php include 'config.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_number = $_POST['student_number'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $course = $_POST['course'];
    
    // Check if student already exists
    $check = $pdo->prepare("SELECT * FROM students WHERE student_number = ? OR email = ?");
    $check->execute([$student_number, $email]);
    
    if($check->rowCount() > 0) {
        $error = "Student number or email already registered!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO students (student_number, name, surname, email, course, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
        
        if($stmt->execute([$student_number, $name, $surname, $email, $course, $password])) {
            $success = "Registration successful! Please login.";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CUT Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user-plus"></i> Create Your Account</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Student Number</label>
                                    <input type="text" name="student_number" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Course/Programme</label>
                                    <input type="text" name="course" class="form-control" placeholder="e.g., Computer Science" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Surname</label>
                                    <input type="text" name="surname" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                        <hr>
                        <p class="text-center mb-0">Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>