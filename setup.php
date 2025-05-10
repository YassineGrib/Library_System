<?php
/**
 * Database Setup Script
 * This script initializes the database and creates the necessary tables
 */

// Load configuration
$config = require_once __DIR__ . '/config/xampp.php';

// Connect to MySQL without selecting a database
try {
    $pdo = new PDO(
        'mysql:host=' . $config['db']['host'],
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

// Create database if it doesn't exist
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db']['name']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '{$config['db']['name']}' created or already exists.<br>";
} catch (PDOException $e) {
    die("Error creating database: " . $e->getMessage());
}

// Select the database
try {
    $pdo->exec("USE `{$config['db']['name']}`");
    echo "Database '{$config['db']['name']}' selected.<br>";
} catch (PDOException $e) {
    die("Error selecting database: " . $e->getMessage());
}

// Create users table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(255) UNIQUE NOT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `full_name` VARCHAR(100) NOT NULL,
            `receipt_path` VARCHAR(255),
            `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
            `role` ENUM('user','admin') DEFAULT 'user',
            `preferred_lang` CHAR(2) DEFAULT 'en',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'users' created or already exists.<br>";
} catch (PDOException $e) {
    die("Error creating users table: " . $e->getMessage());
}

// Create categories table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'categories' created or already exists.<br>";
} catch (PDOException $e) {
    die("Error creating categories table: " . $e->getMessage());
}

// Create books table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `books` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `author` VARCHAR(100) NOT NULL,
            `file_path` VARCHAR(255) NOT NULL,
            `category_id` INT,
            `uploaded_by` INT,
            `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FULLTEXT INDEX `ft_search` (`title`, `author`),
            FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table 'books' created or already exists.<br>";
} catch (PDOException $e) {
    die("Error creating books table: " . $e->getMessage());
}

// Insert default categories
try {
    $categories = [
        'Fiction',
        'Non-Fiction',
        'Science',
        'Technology',
        'History',
        'Biography',
        'Self-Help',
        'Business',
        'Education',
        'Reference'
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO `categories` (`name`) VALUES (?)");
    
    foreach ($categories as $category) {
        $stmt->execute([$category]);
    }
    
    echo "Default categories inserted.<br>";
} catch (PDOException $e) {
    echo "Error inserting default categories: " . $e->getMessage() . "<br>";
}

// Create admin user if it doesn't exist
try {
    $stmt = $pdo->prepare("SELECT id FROM `users` WHERE email = 'admin@library.local'");
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        $passwordHash = password_hash('Admin123', $config['security']['password_algo']);
        
        $stmt = $pdo->prepare("
            INSERT INTO `users` 
            (`email`, `password_hash`, `full_name`, `status`, `role`) 
            VALUES 
            ('admin@library.local', :password_hash, 'System Administrator', 'approved', 'admin')
        ");
        
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->execute();
        
        echo "Admin user created.<br>";
    } else {
        echo "Admin user already exists.<br>";
    }
} catch (PDOException $e) {
    echo "Error creating admin user: " . $e->getMessage() . "<br>";
}

echo "<br>Setup completed successfully!";
echo "<br><a href='{$config['app']['base_url']}'>Go to homepage</a>";
