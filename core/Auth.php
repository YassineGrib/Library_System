<?php
/**
 * Authentication and Session Management
 */
class Auth {
    private static $instance = null;
    private $db;
    private $user = null;

    /**
     * Constructor - initializes session and database connection
     */
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->db = Database::getInstance();

        // Check if user is already logged in
        if (isset($_SESSION['user_id'])) {
            $this->loadUser($_SESSION['user_id']);
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load user data from database
     */
    private function loadUser($userId) {
        $this->db->query("SELECT * FROM users WHERE id = :id");
        $this->db->bind(':id', $userId);
        $user = $this->db->single();

        if ($user) {
            // Remove sensitive data
            if (isset($user['password_hash'])) {
                unset($user['password_hash']);
            }
            if (isset($user['password'])) {
                unset($user['password']);
            }
            $this->user = $user;
        }
    }

    /**
     * Register a new user
     */
    public function register($email, $password, $fullName, $receiptPath = null) {
        // Check if email already exists
        $this->db->query("SELECT id FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        $existingUser = $this->db->single();

        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'Email already registered'
            ];
        }

        // Hash password
        $config = require_once __DIR__ . '/../config/xampp.php';
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $this->db->query("INSERT INTO users (email, password, full_name, receipt_path, status)
                          VALUES (:email, :password, :full_name, :receipt_path, 'pending')");
        $this->db->bind(':email', $email);
        $this->db->bind(':password', $passwordHash);
        $this->db->bind(':full_name', $fullName);
        $this->db->bind(':receipt_path', $receiptPath);

        if ($this->db->execute()) {
            return [
                'success' => true,
                'message' => 'Registration successful. Awaiting admin approval.',
                'user_id' => $this->db->lastInsertId()
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }

    /**
     * Login a user
     */
    public function login($email, $password) {
        $this->db->query("SELECT * FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        $user = $this->db->single();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }

        // Verify password - check both password_hash and password fields for compatibility
        if ((isset($user['password_hash']) && password_verify($password, $user['password_hash'])) ||
            (isset($user['password']) && password_verify($password, $user['password']))) {

            // Check if user is approved
            if ($user['status'] !== 'approved') {
                return [
                    'success' => false,
                    'message' => 'Your account is pending approval'
                ];
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];

            // Load user data
            $this->loadUser($user['id']);

            return [
                'success' => true,
                'message' => 'Login successful'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }
    }

    /**
     * Logout the current user
     */
    public function logout() {
        $this->user = null;
        unset($_SESSION['user_id']);
        session_destroy();
        return true;
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return $this->user !== null;
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin() {
        return $this->isLoggedIn() && isset($this->user['role']) && $this->user['role'] === 'admin';
    }

    /**
     * Get current user data
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Update user status (for admin)
     */
    public function updateUserStatus($userId, $status) {
        if (!$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Permission denied'
            ];
        }

        $this->db->query("UPDATE users SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $userId);

        if ($this->db->execute()) {
            return [
                'success' => true,
                'message' => 'User status updated successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update user status'
            ];
        }
    }
    /**
     * Check if user subscription is active
     */
    public function hasActiveSubscription() {
        if (!$this->isLoggedIn()) {
            return false;
        }

        // Admins always have active subscription
        if ($this->isAdmin()) {
            return true;
        }

        // Check if user has a subscription end date
        if (empty($this->user['subscription_end'])) {
            return false;
        }

        // Check if subscription is still valid
        $today = date('Y-m-d');
        return $this->user['subscription_end'] >= $today;
    }

    /**
     * Get remaining upload count for user
     */
    public function getRemainingUploads() {
        if (!$this->isLoggedIn() || !$this->hasActiveSubscription()) {
            return 0;
        }

        // Admins have unlimited uploads
        if ($this->isAdmin()) {
            return PHP_INT_MAX;
        }

        // Get upload limit
        $uploadLimit = isset($this->user['upload_limit']) ? (int)$this->user['upload_limit'] : 5;

        // Count existing uploads
        $this->db->query("SELECT COUNT(*) as count FROM books WHERE uploaded_by = :user_id");
        $this->db->bind(':user_id', $this->user['id']);
        $result = $this->db->single();

        $uploadCount = isset($result['count']) ? (int)$result['count'] : 0;

        return max(0, $uploadLimit - $uploadCount);
    }

    /**
     * Check if user can upload more books
     */
    public function canUploadBooks() {
        return $this->isLoggedIn() &&
               $this->user['status'] === 'approved' &&
               $this->hasActiveSubscription() &&
               $this->getRemainingUploads() > 0;
    }

    /**
     * Update user subscription end date (for admin)
     */
    public function updateSubscriptionEnd($userId, $endDate) {
        if (!$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Permission denied'
            ];
        }

        $this->db->query("UPDATE users SET subscription_end = :end_date WHERE id = :id");
        $this->db->bind(':end_date', $endDate);
        $this->db->bind(':id', $userId);

        if ($this->db->execute()) {
            return [
                'success' => true,
                'message' => 'Subscription updated successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update subscription'
            ];
        }
    }

    /**
     * Update user upload limit (for admin)
     */
    public function updateUploadLimit($userId, $limit) {
        if (!$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'Permission denied'
            ];
        }

        $this->db->query("UPDATE users SET upload_limit = :limit WHERE id = :id");
        $this->db->bind(':limit', $limit);
        $this->db->bind(':id', $userId);

        if ($this->db->execute()) {
            return [
                'success' => true,
                'message' => 'Upload limit updated successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update upload limit'
            ];
        }
    }
}