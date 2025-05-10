<?php
/**
 * Database and application configuration for XAMPP environment
 */
return [
    'db' => [
        'host' => 'localhost',
        'user' => 'root',      // XAMPP default
        'pass' => '',          // XAMPP default
        'name' => 'library_db'
    ],
    'security' => [
        'password_algo' => PASSWORD_ARGON2ID,
        'upload_dir' => __DIR__ . '/../public/uploads/'
    ],
    'app' => [
        'base_url' => 'http://localhost/Library_System/',
        'default_lang' => 'en'
    ]
];
