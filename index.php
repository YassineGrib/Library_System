<?php
/**
 * Main Entry Point
 * Simple router for the Digital Library System
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Setup check is now handled in Database.php

// Load core classes
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Localization.php';
require_once __DIR__ . '/core/helpers.php';

// Initialize auth
$auth = Auth::getInstance();

// Initialize localization
$localization = Localization::getInstance();

// Check for language change
if (isset($_GET['lang']) && in_array($_GET['lang'], $localization->getSupportedLanguages())) {
    $localization->setLanguage($_GET['lang']);

    // Get the current URL without the lang parameter
    $currentUrl = $_SERVER['REQUEST_URI'];
    $urlParts = parse_url($currentUrl);

    if (isset($urlParts['query'])) {
        parse_str($urlParts['query'], $queryParams);
        unset($queryParams['lang']);

        // Rebuild URL without lang parameter
        $newUrl = $urlParts['path'];
        if (!empty($queryParams)) {
            $newUrl .= '?' . http_build_query($queryParams);
        }
    } else {
        $newUrl = $urlParts['path'];
    }

    // Redirect to the same page without the lang parameter
    header('Location: ' . $newUrl);
    exit;
}

// Use the current_path() function from helpers.php
$uri = '/' . current_path();

// Debug information
error_log("Current path: " . $uri);

// Default to home page if URI is empty
if ($uri === '') {
    $uri = '/';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug information
    error_log("POST URI: " . $uri);

    switch ($uri) {
        case '/login':
            // Process login form
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            $result = $auth->login($email, $password);

            if ($result['success']) {
                redirect('');
            } else {
                $_SESSION['error'] = $result['message'];
                redirect('login');
            }
            break;

        case '/register':
            // Process registration form
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            $fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);

            // Handle file upload
            $receiptPath = null;
            if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/public/uploads/receipts/';

                // Generate unique filename
                $filename = uniqid() . '_' . basename($_FILES['receipt']['name']);
                $uploadFile = $uploadDir . $filename;

                // Check file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                if (!in_array($_FILES['receipt']['type'], $allowedTypes)) {
                    $_SESSION['error'] = $localization->t('file_type');
                    redirect('register');
                }

                // Move uploaded file
                if (move_uploaded_file($_FILES['receipt']['tmp_name'], $uploadFile)) {
                    $receiptPath = 'receipts/' . $filename;
                } else {
                    $_SESSION['error'] = 'Failed to upload file';
                    redirect('register');
                }
            }

            $result = $auth->register($email, $password, $fullName, $receiptPath);

            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
                redirect('login');
            } else {
                $_SESSION['error'] = $result['message'];
                redirect('register');
            }
            break;

        case '/admin/users/update-status':
            // Process user status update
            if (!$auth->isAdmin()) {
                redirect('');
            }

            $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

            $result = $auth->updateUserStatus($userId, $status);

            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = $result['message'];
            }

            redirect('admin/users');
            break;

        case '/books/upload':
            // Process user book upload
            if (!$auth->canUploadBooks()) {
                $_SESSION['error'] = $localization->t('permission_denied');
                redirect('profile');
            }

            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
            $author = filter_input(INPUT_POST, 'author', FILTER_SANITIZE_STRING);
            $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);

            // Handle file upload
            $filePath = null;
            if (isset($_FILES['book_file']) && $_FILES['book_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/public/uploads/books/';

                // Generate unique filename
                $filename = uniqid() . '_' . basename($_FILES['book_file']['name']);
                $uploadFile = $uploadDir . $filename;

                // Check file type
                $allowedTypes = ['application/pdf'];
                if (!in_array($_FILES['book_file']['type'], $allowedTypes)) {
                    $_SESSION['error'] = $localization->t('file_type');
                    redirect('profile');
                }

                // Move uploaded file
                if (move_uploaded_file($_FILES['book_file']['tmp_name'], $uploadFile)) {
                    $filePath = $filename;
                } else {
                    $_SESSION['error'] = 'Failed to upload file';
                    redirect('profile');
                }
            } else {
                $_SESSION['error'] = 'No file uploaded';
                redirect('profile');
            }

            // Insert book
            $db = Database::getInstance();
            $db->query("INSERT INTO books (title, author, file_path, category_id, uploaded_by)
                        VALUES (:title, :author, :file_path, :category_id, :uploaded_by)");
            $db->bind(':title', $title);
            $db->bind(':author', $author);
            $db->bind(':file_path', $filePath);
            $db->bind(':category_id', $categoryId);
            $db->bind(':uploaded_by', $auth->getUser()['id']);

            if ($db->execute()) {
                $_SESSION['success'] = $localization->t('book_added');
            } else {
                $_SESSION['error'] = 'Failed to add book';
            }

            redirect('profile');
            break;

        case '/admin/books':
            // Process admin book upload
            if (!$auth->isAdmin()) {
                redirect('');
            }

            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
            $author = filter_input(INPUT_POST, 'author', FILTER_SANITIZE_STRING);
            $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);

            // Handle file upload
            $filePath = null;
            if (isset($_FILES['book_file']) && $_FILES['book_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/public/uploads/books/';

                // Generate unique filename
                $filename = uniqid() . '_' . basename($_FILES['book_file']['name']);
                $uploadFile = $uploadDir . $filename;

                // Check file type
                $allowedTypes = ['application/pdf'];
                if (!in_array($_FILES['book_file']['type'], $allowedTypes)) {
                    $_SESSION['error'] = $localization->t('file_type');
                    redirect('admin/books');
                }

                // Move uploaded file
                if (move_uploaded_file($_FILES['book_file']['tmp_name'], $uploadFile)) {
                    $filePath = $filename;
                } else {
                    $_SESSION['error'] = 'Failed to upload file';
                    redirect('admin/books');
                }
            } else {
                $_SESSION['error'] = 'No file uploaded';
                redirect('admin/books');
            }

            // Insert book
            $db = Database::getInstance();
            $db->query("INSERT INTO books (title, author, file_path, category_id, uploaded_by)
                        VALUES (:title, :author, :file_path, :category_id, :uploaded_by)");
            $db->bind(':title', $title);
            $db->bind(':author', $author);
            $db->bind(':file_path', $filePath);
            $db->bind(':category_id', $categoryId);
            $db->bind(':uploaded_by', $auth->getUser()['id']);

            if ($db->execute()) {
                $_SESSION['success'] = $localization->t('book_added');
            } else {
                $_SESSION['error'] = 'Failed to add book';
            }

            redirect('admin/books');
            break;

        default:
            // Unknown form submission
            redirect('');
            break;
    }
}

// Simple router for GET requests
switch ($uri) {
    case '/':
        // Home page
        include __DIR__ . '/views/home.php';
        break;

    case '/login':
        // Login page
        if ($auth->isLoggedIn()) {
            redirect('');
        }
        include __DIR__ . '/views/login.php';
        break;

    case '/register':
        // Register page
        if ($auth->isLoggedIn()) {
            redirect('');
        }
        include __DIR__ . '/views/register.php';
        break;

    case '/logout':
        // Logout
        $auth->logout();
        redirect('');
        break;

    case '/books':
        // Books page
        include __DIR__ . '/views/books.php';
        break;

    case '/profile':
        // Profile page
        if (!$auth->isLoggedIn()) {
            redirect('login');
        }
        include __DIR__ . '/views/profile.php';
        break;

    case '/admin':
        // Admin dashboard
        if (!$auth->isAdmin()) {
            redirect('');
        }
        include __DIR__ . '/admin/dashboard.php';
        break;

    case '/admin/users':
        // Admin users
        if (!$auth->isAdmin()) {
            redirect('');
        }
        include __DIR__ . '/admin/users.php';
        break;

    case '/admin/books':
        // Admin books
        if (!$auth->isAdmin()) {
            redirect('');
        }

        // Handle book deletion
        if (isset($_GET['delete'])) {
            $bookId = filter_input(INPUT_GET, 'delete', FILTER_SANITIZE_NUMBER_INT);

            // Get book file path
            $db = Database::getInstance();
            $db->query("SELECT file_path FROM books WHERE id = :id");
            $db->bind(':id', $bookId);
            $book = $db->single();

            if ($book) {
                // Delete file
                $filePath = __DIR__ . '/public/uploads/books/' . $book['file_path'];

                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                // Delete from database
                $db->query("DELETE FROM books WHERE id = :id");
                $db->bind(':id', $bookId);

                if ($db->execute()) {
                    $_SESSION['success'] = $localization->t('book_deleted');
                } else {
                    $_SESSION['error'] = 'Failed to delete book';
                }
            }

            redirect('admin/books');
        }

        include __DIR__ . '/admin/books.php';
        break;

    default:
        // Check if it's a book view page
        if (preg_match('#^/books/view/(\d+)$#', $uri, $matches)) {
            $id = $matches[1];
            include __DIR__ . '/views/book_view.php';
        } else {
            // 404 page
            header("HTTP/1.0 404 Not Found");
            include __DIR__ . '/views/404.php';
        }
        break;
}
