<?php
/**
 * Authentication Middleware
 * Handles session management and access control
 */

session_start();

class Auth {
    private static $instance = null;
    private $conn;
    private $user = null;
    private $requiredRole = null;
    private $allowedRoles = array();
    
    private function __construct() {
        // Get database connection
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // Initialize user from session if exists
        if (isset($_SESSION['user_id'])) {
            $this->loadUser($_SESSION['user_id']);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadUser($userId) {
        $query = "SELECT * FROM users WHERE id = ? AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $this->user = $result->fetch_assoc();
            // Update last activity
            $_SESSION['last_activity'] = time();
        } else {
            $this->logout();
        }
    }
    
    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = ? AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['last_activity'] = time();
                $_SESSION['role'] = $user['role'];
                
                // Update last login
                $updateQuery = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                
                // Log the login
                $this->logActivity('login', 'User logged in successfully');
                
                $this->user = $user;
                return true;
            }
        }
        
        // Log failed login attempt
        $this->logActivity('login_failed', 'Failed login attempt for username: ' . $username);
        return false;
    }
    
    public function logout() {
        if ($this->isLoggedIn()) {
            $this->logActivity('logout', 'User logged out');
        }
        
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy the session
        session_destroy();
        
        $this->user = null;
    }
    
    public function isLoggedIn() {
        // Check if user is logged in and session hasn't expired
        if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
            // Check if session has expired (30 minutes)
            if (time() - $_SESSION['last_activity'] > 1800) {
                $this->logout();
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ../login.php');
            exit();
        }
    }
    
    public function requireRole($role) {
        $this->requireLogin();
        if ($this->user['role'] !== $role) {
            header('Location: unauthorized.php');
            exit();
        }
    }
    
    public function requireAnyRole($roles) {
        $this->requireLogin();
        if (!in_array($this->user['role'], $roles)) {
            header('Location: unauthorized.php');
            exit();
        }
    }
    
    public function getUser() {
        return $this->user;
    }
    
    public function getUserId() {
        return $this->user ? $this->user['id'] : null;
    }
    
    public function getUserRole() {
        return $this->user ? $this->user['role'] : null;
    }
    
    private function logActivity($action, $details = '') {
        if (!$this->isLoggedIn()) {
            return;
        }
        
        $query = "INSERT INTO audit_logs (user_id, action, entity_type, details, ip_address, user_agent) 
                 VALUES (?, ?, 'auth', ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $stmt->bind_param("issss", $this->user['id'], $action, $details, $ip, $userAgent);
        $stmt->execute();
    }
    
    public function checkPermission($permission) {
        // Define role-based permissions
        $permissions = array(
            'admin' => array('*'), // Admin has all permissions
            'doctor' => array('view_patients', 'edit_patients', 'view_visits', 'edit_visits', 
                            'view_diagnoses', 'edit_diagnoses', 'view_prescriptions', 'edit_prescriptions'),
            'nurse' => array('view_patients', 'edit_patients', 'view_visits', 'edit_visits', 
                           'view_vitals', 'edit_vitals'),
            'pharmacist' => array('view_patients', 'view_prescriptions', 'edit_prescriptions'),
            'lab_technician' => array('view_patients', 'view_lab_orders', 'edit_lab_orders'),
            'claims_officer' => array('view_patients', 'view_claims', 'edit_claims'),
            'receptionist' => array('view_patients', 'edit_patients', 'view_visits', 'edit_visits')
        );
        
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $role = $this->user['role'];
        return isset($permissions[$role]) && 
               (in_array('*', $permissions[$role]) || in_array($permission, $permissions[$role]));
    }
}