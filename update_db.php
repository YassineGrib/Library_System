<?php
/**
 * Database Update Script
 * This script updates the database structure
 */

// Load configuration
$config = require_once __DIR__ . '/config/xampp.php';

// Connect to MySQL
try {
    $pdo = new PDO(
        'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'],
        $config['db']['user'],
        $config['db']['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "Connected to MySQL successfully.<br>";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Add subscription_end field to users table
try {
    // Check if the column already exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `users` LIKE 'subscription_end'");
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        // Add the column
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `subscription_end` DATE NULL AFTER `status`");
        
        // Set default subscription end date for existing users (1 year from now)
        $oneYearFromNow = date('Y-m-d', strtotime('+1 year'));
        $pdo->exec("UPDATE `users` SET `subscription_end` = '$oneYearFromNow' WHERE `status` = 'approved'");
        
        echo "Added 'subscription_end' column to users table.<br>";
    } else {
        echo "Column 'subscription_end' already exists.<br>";
    }
} catch (PDOException $e) {
    echo "Error updating users table: " . $e->getMessage() . "<br>";
}

// Add upload_limit field to users table
try {
    // Check if the column already exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `users` LIKE 'upload_limit'");
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        // Add the column
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `upload_limit` INT DEFAULT 5 AFTER `subscription_end`");
        
        echo "Added 'upload_limit' column to users table.<br>";
    } else {
        echo "Column 'upload_limit' already exists.<br>";
    }
} catch (PDOException $e) {
    echo "Error updating users table: " . $e->getMessage() . "<br>";
}

echo "<br>Database update completed successfully!";
echo "<br><a href='{$config['app']['base_url']}'>Go to homepage</a>";
