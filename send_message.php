<?php 
include 'config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$to_id = $_GET['to'];
$student_id = $_SESSION['student_id'];

// Check subscription
$stmt = $pdo->prepare("SELECT is_subscribed FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$subscribed = $stmt->fetchColumn();

if (!$subscribed) {
    header('Location: subscribe.php');
    exit();
}

header("Location: chat.php?user=$to_id");
exit();
?>