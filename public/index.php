<?php
/**
 * Front Controller
 * Entry point for all requests
 */

// Load core classes
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Localization.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize auth
$auth = Auth::getInstance();

// Initialize localization
$localization = Localization::getInstance();

// Check for language change
if (isset($_GET['lang']) && in_array($_GET['lang'], $localization->getSupportedLanguages())) {
    $localization->setLanguage($_GET['lang']);
}

// Initialize router
$router = Router::getInstance();

// Get base URL for redirects
$config = require_once __DIR__ . '/../config/xampp.php';
$baseUrl = '';
if (isset($config['app']) && isset($config['app']['base_url'])) {
    $baseUrl = $config['app']['base_url'];
}

// Define routes
$router->get('/', function() use ($auth, $localization, $baseUrl) {
    // Home page
    include __DIR__ . '/../views/home.php';
});

// Test route
$router->get('/test', function() {
    echo "Test route is working!";
    echo "<pre>";
    echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
    echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
    echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
    echo "</pre>";
});

$router->get('/login', function() use ($auth, $localization, $baseUrl) {
    // Redirect if already logged in
    if ($auth->isLoggedIn()) {
        header('Location: ' . $baseUrl);
        exit;
    }

    include __DIR__ . '/../views/login.php';
});

$router->post('/login', function() use ($auth, $localization, $baseUrl) {
    // Redirect if already logged in
    if ($auth->isLoggedIn()) {
        header('Location: ' . $baseUrl);
        exit;
    }

    // Process login form
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    $result = $auth->login($email, $password);

    if ($result['success']) {
        // Redirect to home page
        header('Location: ' . $baseUrl);
        exit;
    } else {
        // Show error message
        $_SESSION['error'] = $result['message'];
        header('Location: ' . $baseUrl . 'login');
        exit;
    }
});

$router->get('/register', function() use ($auth, $localization, $baseUrl) {
    // Redirect if already logged in
    if ($auth->isLoggedIn()) {
        header('Location: ' . $baseUrl);
        exit;
    }

    include __DIR__ . '/../views/register.php';
});

$router->post('/register', function() use ($auth, $localization, $baseUrl) {
    // Redirect if already logged in
    if ($auth->isLoggedIn()) {
        header('Location: ' . $baseUrl);
        exit;
    }

    // Process registration form
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);

    // Handle file upload
    $receiptPath = null;
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $config = require_once __DIR__ . '/../config/xampp.php';
        $uploadDir = $config['security']['upload_dir'] . 'receipts/';

        // Generate unique filename
        $filename = uniqid() . '_' . basename($_FILES['receipt']['name']);
        $uploadFile = $uploadDir . $filename;

        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($_FILES['receipt']['type'], $allowedTypes)) {
            $_SESSION['error'] = $localization->t('file_type');
            header('Location: ' . $baseUrl . 'register');
            exit;
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $uploadFile)) {
            $receiptPath = 'receipts/' . $filename;
        } else {
            $_SESSION['error'] = 'Failed to upload file';
            header('Location: ' . $baseUrl . 'register');
            exit;
        }
    }

    $result = $auth->register($email, $password, $fullName, $receiptPath);

    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: ' . $baseUrl . 'login');
        exit;
    } else {
        $_SESSION['error'] = $result['message'];
        header('Location: ' . $baseUrl . 'register');
        exit;
    }
});

$router->get('/logout', function() use ($auth, $baseUrl) {
    $auth->logout();
    header('Location: ' . $baseUrl);
    exit;
});

$router->get('/books', function() use ($auth, $localization, $baseUrl) {
    // List all books
    include __DIR__ . '/../views/books.php';
});

$router->get('/books/view/:id', function($id) use ($auth, $localization, $baseUrl) {
    // View a book
    include __DIR__ . '/../views/book_view.php';
});

$router->get('/profile', function() use ($auth, $localization, $baseUrl) {
    // Redirect if not logged in
    if (!$auth->isLoggedIn()) {
        header('Location: ' . $baseUrl . 'login');
        exit;
    }

    include __DIR__ . '/../views/profile.php';
});

// Admin routes
$router->get('/admin', function() use ($auth, $localization, $baseUrl) {
    // Redirect if not admin
    if (!$auth->isAdmin()) {
        header('Location: ' . $baseUrl);
        exit;
    }

    include __DIR__ . '/../admin/dashboard.php';
});

$router->get('/admin/users', function() use ($auth, $localization, $baseUrl) {
    // Redirect if not admin
    if (!$auth->isAdmin()) {
        header('Location: ' . $baseUrl);
        exit;
    }

    include __DIR__ . '/../admin/users.php';
});

$router->post('/admin/users/update-status', function() use ($auth, $localization, $baseUrl) {
    // Redirect if not admin
    if (!$auth->isAdmin()) {
        header('Location: ' . $baseUrl);
        exit;
    }

    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    $result = $auth->updateUserStatus($userId, $status);

    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }

    header('Location: ' . $baseUrl . 'admin/users');
    exit;
});

$router->get('/admin/books', function() use ($auth, $localization, $baseUrl) {
    // Redirect if not admin
    if (!$auth->isAdmin()) {
        header('Location: ' . $baseUrl);
        exit;
    }

    include __DIR__ . '/../admin/books.php';
});

// 404 handler
$router->notFound(function() use ($auth, $localization, $baseUrl) {
    header("HTTP/1.0 404 Not Found");
    include __DIR__ . '/../views/404.php';
});

// Resolve the route
$router->resolve();
