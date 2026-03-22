<?php
// config.php - Updated with correct table structure
$database_file = __DIR__ . '/cut_connect.db';

try {
    $pdo = new PDO("sqlite:" . $database_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create students table
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_number TEXT UNIQUE,
        name TEXT,
        surname TEXT,
        email TEXT UNIQUE,
        phone TEXT,
        course TEXT,
        bio TEXT,
        profile_image TEXT DEFAULT 'default.png',
        looking_for TEXT,
        interests TEXT,
        password_hash TEXT,
        is_subscribed INTEGER DEFAULT 0,
        subscription_expiry TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME
    )");
    
    // Create payments table with ALL required columns
    $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER,
        paynow_reference TEXT UNIQUE,
        amount REAL,
        status TEXT DEFAULT 'pending',
        poll_url TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        paid_at DATETIME
    )");
    
    // Create messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sender_id INTEGER,
        receiver_id INTEGER,
        message TEXT,
        is_read INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    session_start();
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>