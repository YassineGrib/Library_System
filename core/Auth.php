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
            unset($user['password_hash']);
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
        $passwordHash = password_hash($password, $config['security']['password_algo']);
        
        // Insert new user
        $this->db->query("INSERT INTO users (email, password_hash, full_name, receipt_path, status) 
                          VALUES (:email, :password_hash, :full_name, :receipt_path, 'pending')");
        $this->db->bind(':email', $email);
        $this->db->bind(':password_hash', $passwordHash);
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
        
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
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
}
