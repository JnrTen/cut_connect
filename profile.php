<?php 
include 'config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $course = $_POST['course'];
    $bio = $_POST['bio'];
    $looking_for = $_POST['looking_for'];
    $phone = $_POST['phone'];
    
    $update = $pdo->prepare("UPDATE students SET name = ?, surname = ?, course = ?, bio = ?, looking_for = ?, phone = ? WHERE id = ?");
    $update->execute([$name, $surname, $course, $bio, $looking_for, $phone, $_SESSION['student_id']]);
    
    $success = "Profile updated successfully!";
    
    // Refresh student data
    $stmt->execute([$_SESSION['student_id']]);
    $student = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CUT Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-users"></i> CUT Connect
            </a>
            <a href="dashboard.php" class="btn btn-outline-light">← Back to Dashboard</a>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user-edit"></i> Edit Profile</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo $student['name']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Surname</label>
                                    <input type="text" name="surname" class="form-control" value="<?php echo $student['surname']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Student Number</label>
                                <input type="text" class="form-control" value="<?php echo $student['student_number']; ?>" readonly>
                                <small class="text-muted">Student number cannot be changed</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo $student['email']; ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Course/Programme</label>
                                <input type="text" name="course" class="form-control" value="<?php echo $student['course']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo $student['phone']; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Bio / About Me</label>
                                <textarea name="bio" class="form-control" rows="4"><?php echo $student['bio']; ?></textarea>
                                <small class="text-muted">Tell others about yourself, your interests, and what you're looking for</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">I'm looking for...</label>
                                <select name="looking_for" class="form-control">
                                    <option value="Friendship" <?php echo $student['looking_for'] == 'Friendship' ? 'selected' : ''; ?>>Friendship</option>
                                    <option value="Study Partner" <?php echo $student['looking_for'] == 'Study Partner' ? 'selected' : ''; ?>>Study Partner</option>
                                    <option value="Networking" <?php echo $student['looking_for'] == 'Networking' ? 'selected' : ''; ?>>Networking</option>
                                    <option value="Dating" <?php echo $student['looking_for'] == 'Dating' ? 'selected' : ''; ?>>Dating</option>
                                    <option value="Open to anything" <?php echo $student['looking_for'] == 'Open to anything' ? 'selected' : ''; ?>>Open to anything</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>