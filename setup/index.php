<?php
/**
 * Setup Wizard for Digital Library System
 * This file handles the initial setup of the application
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base path
$basePath = '/Library_System/';

// Define setup steps
$steps = [
    1 => 'Welcome',
    2 => 'Requirements Check',
    3 => 'Database Configuration',
    4 => 'Admin Account',
    5 => 'Finalize'
];

// Get current step
$currentStep = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if ($currentStep < 1 || $currentStep > count($steps)) {
    $currentStep = 1;
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($currentStep) {
        case 3:
            // Database configuration
            $dbHost = $_POST['db_host'] ?? '';
            $dbName = $_POST['db_name'] ?? '';
            $dbUser = $_POST['db_user'] ?? '';
            $dbPass = $_POST['db_pass'] ?? '';

            // Validate database connection
            try {
                $pdo = new PDO(
                    "mysql:host=$dbHost",
                    $dbUser,
                    $dbPass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );

                // Check if database exists, create if not
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                // Save database configuration
                $configContent = "<?php\nreturn [\n    'db' => [\n        'host' => '$dbHost',\n        'name' => '$dbName',\n        'user' => '$dbUser',\n        'pass' => '$dbPass'\n    ],\n    'app' => [\n        'base_url' => '$basePath'\n    ]\n];";

                // Create config directory if it doesn't exist
                if (!is_dir('../config')) {
                    mkdir('../config', 0755, true);
                }

                // Save config file
                file_put_contents('../config/xampp.php', $configContent);

                // Redirect to next step
                header("Location: {$basePath}setup/?step=4");
                exit;
            } catch (PDOException $e) {
                $_SESSION['setup_error'] = "Database connection failed: " . $e->getMessage();
                header("Location: {$basePath}setup/?step=3");
                exit;
            }
            break;

        case 4:
            // Admin account
            $adminEmail = $_POST['admin_email'] ?? '';
            $adminPassword = $_POST['admin_password'] ?? '';
            $adminConfirmPassword = $_POST['admin_confirm_password'] ?? '';
            $adminName = $_POST['admin_name'] ?? '';

            // Validate input
            if (empty($adminEmail) || empty($adminPassword) || empty($adminName)) {
                $_SESSION['setup_error'] = "All fields are required";
                header("Location: {$basePath}setup/?step=4");
                exit;
            }

            if ($adminPassword !== $adminConfirmPassword) {
                $_SESSION['setup_error'] = "Passwords do not match";
                header("Location: {$basePath}setup/?step=4");
                exit;
            }

            if (strlen($adminPassword) < 6) {
                $_SESSION['setup_error'] = "Password must be at least 6 characters";
                header("Location: {$basePath}setup/?step=4");
                exit;
            }

            // Save admin credentials in session for display in the final step
            $_SESSION['admin_email'] = $adminEmail;
            $_SESSION['admin_password'] = $adminPassword;

            // Load database configuration
            $config = require_once '../config/xampp.php';

            try {
                // Connect to database
                $pdo = new PDO(
                    "mysql:host={$config['db']['host']};dbname={$config['db']['name']}",
                    $config['db']['user'],
                    $config['db']['pass'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );

                // Create tables
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `users` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `email` VARCHAR(255) NOT NULL UNIQUE,
                        `password` VARCHAR(255) NOT NULL,
                        `full_name` VARCHAR(255) NOT NULL,
                        `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
                        `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
                        `subscription_end` DATE NULL,
                        `upload_limit` INT DEFAULT 5,
                        `receipt_path` VARCHAR(255) NULL,
                        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                    CREATE TABLE IF NOT EXISTS `categories` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `name` VARCHAR(255) NOT NULL,
                        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                    CREATE TABLE IF NOT EXISTS `books` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `title` VARCHAR(255) NOT NULL,
                        `author` VARCHAR(255) NOT NULL,
                        `file_path` VARCHAR(255) NOT NULL,
                        `category_id` INT,
                        `uploaded_by` INT NOT NULL,
                        `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
                        FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                    CREATE TABLE IF NOT EXISTS `downloads` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `book_id` INT NOT NULL,
                        `user_id` INT NOT NULL,
                        `downloaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE,
                        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");

                // Insert default categories
                $pdo->exec("
                    INSERT INTO `categories` (`name`) VALUES
                    ('Fiction'),
                    ('Non-Fiction'),
                    ('Science'),
                    ('Technology'),
                    ('History'),
                    ('Biography'),
                    ('Business'),
                    ('Self-Help'),
                    ('Education')
                ");

                // Insert admin user
                $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
                $oneYearFromNow = date('Y-m-d', strtotime('+1 year'));

                $stmt = $pdo->prepare("
                    INSERT INTO `users` (`email`, `password`, `full_name`, `role`, `status`, `subscription_end`, `upload_limit`)
                    VALUES (?, ?, ?, 'admin', 'approved', ?, 999)
                ");
                $stmt->execute([$adminEmail, $hashedPassword, $adminName, $oneYearFromNow]);

                // Create necessary directories
                $directories = [
                    '../public/uploads/books',
                    '../public/uploads/receipts',
                    '../public/assets/img'
                ];

                foreach ($directories as $dir) {
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                }

                // Redirect to next step
                header("Location: {$basePath}setup/?step=5");
                exit;
            } catch (PDOException $e) {
                $_SESSION['setup_error'] = "Database error: " . $e->getMessage();
                header("Location: {$basePath}setup/?step=4");
                exit;
            } catch (Exception $e) {
                $_SESSION['setup_error'] = "Error: " . $e->getMessage();
                header("Location: {$basePath}setup/?step=4");
                exit;
            }
            break;

        case 5:
            // Finalize setup
            // Create setup_complete.php file
            $setupCompleteContent = "<?php\n// Setup completed on " . date('Y-m-d H:i:s') . "\nreturn true;";
            file_put_contents('../config/setup_complete.php', $setupCompleteContent);

            // Redirect to home page
            header("Location: {$basePath}");
            exit;
            break;
    }
}

// Function to check system requirements
function checkRequirements() {
    $requirements = [
        'PHP Version' => [
            'required' => '7.4.0',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '7.4.0', '>=')
        ],
        'PDO Extension' => [
            'required' => 'Enabled',
            'current' => extension_loaded('pdo') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('pdo')
        ],
        'PDO MySQL Extension' => [
            'required' => 'Enabled',
            'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('pdo_mysql')
        ],
        'JSON Extension' => [
            'required' => 'Enabled',
            'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('json')
        ],
        'Fileinfo Extension' => [
            'required' => 'Enabled',
            'current' => extension_loaded('fileinfo') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('fileinfo')
        ],
        'Config Directory Writable' => [
            'required' => 'Writable',
            'current' => is_writable('../config') || is_writable('..') ? 'Writable' : 'Not Writable',
            'status' => is_writable('../config') || is_writable('..')
        ],
        'Public Directory Writable' => [
            'required' => 'Writable',
            'current' => is_writable('../public') || is_writable('..') ? 'Writable' : 'Not Writable',
            'status' => is_writable('../public') || is_writable('..')
        ]
    ];

    $allPassed = true;
    foreach ($requirements as $req) {
        if (!$req['status']) {
            $allPassed = false;
            break;
        }
    }

    return [
        'requirements' => $requirements,
        'passed' => $allPassed
    ];
}

// Get error message if any
$errorMessage = $_SESSION['setup_error'] ?? '';
unset($_SESSION['setup_error']);

// HTML header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Library System - Setup</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 2rem;
        }
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .setup-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        .setup-steps::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #dee2e6;
            z-index: 1;
        }
        .step {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #fff;
            border: 2px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }
        .step.active {
            background-color: #4e73df;
            border-color: #4e73df;
            color: #fff;
        }
        .step.completed {
            background-color: #1cc88a;
            border-color: #1cc88a;
            color: #fff;
        }
        .step-label {
            position: absolute;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            font-size: 0.8rem;
        }
        .setup-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 2rem;
        }
        .requirements-table .status-icon {
            font-size: 1.2rem;
        }
        .requirements-table .passed {
            color: #1cc88a;
        }
        .requirements-table .failed {
            color: #e74a3b;
        }
    </style>
</head>
<body>
    <div class="container setup-container">
        <div class="setup-header">
            <h1><i class="fas fa-book-open me-2"></i> Digital Library System</h1>
            <p class="lead">Setup Wizard</p>
        </div>

        <div class="setup-steps">
            <?php foreach ($steps as $stepNum => $stepName): ?>
                <div class="position-relative">
                    <div class="step <?php echo $stepNum < $currentStep ? 'completed' : ($stepNum === $currentStep ? 'active' : ''); ?>">
                        <?php if ($stepNum < $currentStep): ?>
                            <i class="fas fa-check"></i>
                        <?php else: ?>
                            <?php echo $stepNum; ?>
                        <?php endif; ?>
                    </div>
                    <div class="step-label"><?php echo $stepName; ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger mb-4">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <div class="setup-card">
            <?php
            switch ($currentStep) {
                case 1: // Welcome
            ?>
                    <h2 class="mb-4">Welcome to Digital Library System</h2>
                    <p>Thank you for choosing Digital Library System. This setup wizard will guide you through the installation process.</p>
                    <p>Before proceeding, please make sure you have the following information ready:</p>
                    <ul>
                        <li>Database server details (host, username, password)</li>
                        <li>Admin account details</li>
                    </ul>
                    <p>Click "Next" to begin the setup process.</p>
                    <div class="d-flex justify-content-end mt-4">
                        <a href="?step=2" class="btn btn-primary">
                            Next <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
            <?php
                    break;
                case 2: // Requirements Check
            ?>
                    <h2 class="mb-4">System Requirements Check</h2>
                    <p>The system will now check if your server meets the requirements for running Digital Library System.</p>

                    <?php $requirementsCheck = checkRequirements(); ?>

                    <div class="table-responsive mt-4">
                        <table class="table requirements-table">
                            <thead>
                                <tr>
                                    <th>Requirement</th>
                                    <th>Required</th>
                                    <th>Current</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requirementsCheck['requirements'] as $name => $req): ?>
                                    <tr>
                                        <td><?php echo $name; ?></td>
                                        <td><?php echo $req['required']; ?></td>
                                        <td><?php echo $req['current']; ?></td>
                                        <td>
                                            <?php if ($req['status']): ?>
                                                <i class="fas fa-check-circle status-icon passed"></i>
                                            <?php else: ?>
                                                <i class="fas fa-times-circle status-icon failed"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="?step=1" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        <?php if ($requirementsCheck['passed']): ?>
                            <a href="?step=3" class="btn btn-primary">
                                Next <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-primary" disabled>
                                Next <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                        <?php endif; ?>
                    </div>
            <?php
                    break;
                case 3: // Database Configuration
            ?>
                    <h2 class="mb-4">Database Configuration</h2>
                    <p>Please enter your database connection details below.</p>

                    <form action="?step=3" method="post">
                        <div class="mb-3">
                            <label for="db_host" class="form-label">Database Host</label>
                            <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                        </div>

                        <div class="mb-3">
                            <label for="db_name" class="form-label">Database Name</label>
                            <input type="text" class="form-control" id="db_name" name="db_name" value="library_system" required>
                        </div>

                        <div class="mb-3">
                            <label for="db_user" class="form-label">Database Username</label>
                            <input type="text" class="form-control" id="db_user" name="db_user" value="root" required>
                        </div>

                        <div class="mb-3">
                            <label for="db_pass" class="form-label">Database Password</label>
                            <input type="password" class="form-control" id="db_pass" name="db_pass" value="">
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="?step=2" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Next <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </form>
            <?php
                    break;
                case 4: // Admin Account
            ?>
                    <h2 class="mb-4">Admin Account</h2>
                    <p>Please create an administrator account for the Digital Library System.</p>

                    <form action="?step=4" method="post">
                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" value="admin@library.local" required>
                        </div>

                        <div class="mb-3">
                            <label for="admin_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="admin_name" name="admin_name" value="System Administrator" required>
                        </div>

                        <div class="mb-3">
                            <label for="admin_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="admin_password" name="admin_password" value="Admin123" required>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>

                        <div class="mb-3">
                            <label for="admin_confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="admin_confirm_password" name="admin_confirm_password" value="Admin123" required>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="?step=3" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Next <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </form>
            <?php
                    break;
                case 5: // Finalize
                    // Get admin credentials from session if available
                    $adminEmail = $_SESSION['admin_email'] ?? 'admin@library.local';
                    $adminPassword = $_SESSION['admin_password'] ?? 'Admin123';
            ?>
                    <h2 class="mb-4">Setup Complete</h2>
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    <p>Congratulations! The Digital Library System has been successfully installed.</p>

                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>Admin Account Details</h5>
                        <p>Please save these credentials for future reference:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($adminEmail); ?>" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard(this.parentNode.querySelector('input'))">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($adminPassword); ?>" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard(this.parentNode.querySelector('input'))">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p>You can now log in using the administrator account shown above.</p>

                    <form action="?step=5" method="post">
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-home me-1"></i> Go to Homepage
                            </button>
                        </div>
                    </form>

                    <script>
                        function copyToClipboard(input) {
                            input.select();
                            document.execCommand('copy');

                            // Show tooltip
                            const button = input.nextElementSibling;
                            const originalHTML = button.innerHTML;
                            button.innerHTML = '<i class="fas fa-check"></i> Copied!';

                            setTimeout(() => {
                                button.innerHTML = originalHTML;
                            }, 2000);
                        }
                    </script>
            <?php
                    break;
            }
            ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
