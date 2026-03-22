<?php
// fix_database_complete.php - Run this once to fix all database issues
include 'config.php';

try {
    // Check payments table structure
    $result = $pdo->query("PRAGMA table_info(payments)");
    $columns = [];
    while($row = $result->fetch()) {
        $columns[] = $row['name'];
    }
    
    $added = false;
    
    if(!in_array('poll_url', $columns)) {
        $pdo->exec("ALTER TABLE payments ADD COLUMN poll_url TEXT");
        echo "✅ Added poll_url column<br>";
        $added = true;
    }
    
    if(!in_array('paid_at', $columns)) {
        $pdo->exec("ALTER TABLE payments ADD COLUMN paid_at DATETIME");
        echo "✅ Added paid_at column<br>";
        $added = true;
    }
    
    if(!$added) {
        echo "✅ All columns already exist<br>";
    }
    
    // Fix any pending test payments
    $update = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = datetime('now') WHERE status = 'pending' AND student_id IN (SELECT id FROM students WHERE is_subscribed = 1)");
    $update->execute();
    
    echo "<br>✅ Database fixed successfully!<br>";
    echo "<a href='dashboard.php'>Go to Dashboard</a>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>