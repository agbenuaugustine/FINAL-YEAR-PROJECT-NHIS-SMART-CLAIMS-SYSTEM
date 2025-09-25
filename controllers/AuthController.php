<?php
/**
 * Authentication Controller
 * 
 * Handles user authentication operations
 */

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/JwtHandler.php';
require_once __DIR__ . '/../utils/Mailer.php';

class AuthController {
    // Database connection and user model
    private $conn;
    private $user;
    private $jwt;
    private $mailer;
    
    /**
     * Constructor
     */
    public function __construct() {
        try {
            // Get database connection
            $database = new Database();
            $this->conn = $database->getConnection();
            
            if (!$this->conn) {
                throw new Exception('Database connection failed');
            }
            
            // Initialize user model
            $this->user = new User($this->conn);
            
            // Initialize JWT handler
            $this->jwt = new JwtHandler();
            
            // Initialize Mailer
            $this->mailer = new Mailer();
        } catch (Exception $e) {
            error_log("AuthController initialization error: " . $e->getMessage());
            throw new Exception('Authentication system initialization failed');
        }
    }
    
    /**
     * Login method
     * 
     * @return array Response with status and data
     */
    public function login() {
        try {
            // Get posted data
            $input = file_get_contents("php://input");
            error_log("AuthController login input: " . $input);
            
            if ($input === false || empty(trim($input))) {
                return [
                    'status' => 'error',
                    'message' => 'No input data received'
                ];
            }
            
            $data = json_decode($input);
            
            // Check for JSON parsing errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON parse error: " . json_last_error_msg() . " - Input: " . $input);
                return [
                    'status' => 'error',
                    'message' => 'Invalid request format'
                ];
            }
            
            if ($data === null) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid request data'
                ];
            }
        } catch (Exception $e) {
            error_log("Login input processing error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Request processing failed'
            ];
        }
        
        // Validate input - handle both object and array formats
        $username = null;
        $password = null;
        
        if (is_object($data)) {
            $username = isset($data->username) ? $data->username : null;
            $password = isset($data->password) ? $data->password : null;
        } elseif (is_array($data)) {
            $username = isset($data['username']) ? $data['username'] : null;
            $password = isset($data['password']) ? $data['password'] : null;
        }
        
        if (empty($username) || empty($password)) {
            error_log("Missing credentials - Username: " . ($username ? 'provided' : 'missing') . 
                      ", Password: " . ($password ? 'provided' : 'missing'));
            return [
                'status' => 'error',
                'message' => 'Username and password are required'
            ];
        }
        
        try {
            // Set user property values
            $this->user->username = $username;
            $userPassword = $password;
            
            // Check if user exists
            if ($this->user->findByUsername($this->user->username)) {
            error_log("User found: " . $this->user->username);
            
            // Verify password
            $password_match = password_verify($userPassword, $this->user->password);
            error_log("Password verification result: " . ($password_match ? 'success' : 'failed'));
            
            if ($password_match) {
                // Check if user is active
                error_log("User active status: " . ($this->user->is_active ? 'active' : 'inactive') . " (value: " . $this->user->is_active . ", type: " . gettype($this->user->is_active) . ")");
                
                // Double-check is_active value directly from database
                $stmt = $this->conn->prepare("SELECT is_active FROM users WHERE id = ?");
                $stmt->bindParam(1, $this->user->id);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Direct DB check - is_active: " . $row['is_active'] . " (type: " . gettype($row['is_active']) . ")");
                
                if (!$this->user->is_active && $this->user->is_active !== '1') {
                    return [
                        'status' => 'error',
                        'message' => 'Your account is inactive. Please contact an administrator.'
                    ];
                }
                
                // Update last login
                $this->user->updateLastLogin();
                
                // Generate JWT token
                $token = $this->jwt->generateToken([
                    'id' => $this->user->id,
                    'username' => $this->user->username,
                    'role' => $this->user->role
                ]);
                
                // Get hospital and department information
                $hospital_info = null;
                $department_info = null;
                
                if ($this->user->hospital_id) {
                    $stmt = $this->conn->prepare("SELECT hospital_name, hospital_code FROM hospitals WHERE id = ?");
                    $stmt->bindParam(1, $this->user->hospital_id);
                    $stmt->execute();
                    $hospital_info = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                
                if ($this->user->department_id) {
                    $stmt = $this->conn->prepare("SELECT department_name, department_code FROM departments WHERE id = ?");
                    $stmt->bindParam(1, $this->user->department_id);
                    $stmt->execute();
                    $department_info = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                
                // Return success with token and user data
                return [
                    'status' => 'success',
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => [
                        'id' => $this->user->id,
                        'username' => $this->user->username,
                        'email' => $this->user->email,
                        'full_name' => $this->user->full_name,
                        'role' => $this->user->role,
                        'hospital_id' => $this->user->hospital_id,
                        'department_id' => $this->user->department_id,
                        'hospital' => $hospital_info,
                        'department' => $department_info,
                        'is_active' => $this->user->is_active
                    ]
                ];
            } else {
                // Password is incorrect
                return [
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ];
            }
        } else {
            // User not found
            return [
                'status' => 'error',
                'message' => 'Invalid credentials'
            ];
        }
        } catch (Exception $e) {
            error_log("Login processing error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Login processing failed'
            ];
        }
    }
    
    /**
     * Register method
     * 
     * @return array Response with status and data
     */
    public function register() {
        try {
            // Get posted data
            $input = file_get_contents("php://input");
            error_log("Register input data: " . $input);
            
            $data = json_decode($input);
            
            // Check for JSON parsing errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON parse error in register: " . json_last_error_msg());
                return [
                    'status' => 'error',
                    'message' => 'Invalid JSON data: ' . json_last_error_msg()
                ];
            }
            
            // Validate input
            if (
                empty($data->username) || 
                empty($data->password) || 
                empty($data->email) || 
                empty($data->full_name) || 
                empty($data->role)
            ) {
                error_log("Missing required fields in registration");
                return [
                    'status' => 'error',
                    'message' => 'All fields are required'
                ];
            }
            
            // Set user property values
            $this->user->username = $data->username;
            $this->user->password = $data->password;
            $this->user->email = $data->email;
            $this->user->full_name = $data->full_name;
            $this->user->role = $data->role;
            
            // Handle department and metadata
            if (isset($data->department)) {
                $this->user->department = $data->department;
            } elseif (isset($data->metadata)) {
                // Store metadata as JSON in the department field
                $this->user->department = json_encode($data->metadata);
            } else {
                $this->user->department = null;
            }
            
            // Set is_active to false by default - admin must approve
            $this->user->is_active = false;
            
            // Check if username already exists
            $temp_user = new User($this->conn);
            if ($temp_user->findByUsername($this->user->username)) {
                error_log("Username already exists: " . $this->user->username);
                return [
                    'status' => 'error',
                    'message' => 'Username already exists'
                ];
            }
            
            // Create the user
            if ($this->user->create()) {
                error_log("User created successfully: " . $this->user->username);
                
                // Send registration emails only if mailer is available
                if ($this->mailer !== null) {
                    try {
                        error_log("Attempting to send registration emails");
                        $emailResult = $this->mailer->sendRegistrationEmails(
                            $this->user->email,
                            $this->user->full_name,
                            $this->user->username
                        );
                        error_log("Email sending result: " . ($emailResult ? "success" : "failed"));
                    } catch (Exception $e) {
                        error_log("Failed to send registration emails: " . $e->getMessage());
                    }
                } else {
                    error_log("Email sending skipped - mailer not initialized");
                }
                
                return [
                    'status' => 'success',
                    'message' => 'Registration successful! Your account is pending approval by an administrator.',
                    'user' => [
                        'id' => $this->user->id,
                        'username' => $this->user->username,
                        'email' => $this->user->email,
                        'full_name' => $this->user->full_name,
                        'role' => $this->user->role,
                        'department' => $this->user->department
                    ]
                ];
            } else {
                error_log("Failed to create user: " . $this->user->username);
                return [
                    'status' => 'error',
                    'message' => 'Unable to create user'
                ];
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An error occurred during registration: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate token method
     * 
     * @return array Response with status and data
     */
    public function validateToken() {
        // Get all headers
        $headers = getallheaders();
        
        // Get authorization header
        $authHeader = $headers['Authorization'] ?? '';
        
        // Check if token exists
        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return [
                'status' => 'error',
                'message' => 'Token not provided'
            ];
        }
        
        // Get token
        $token = $matches[1];
        
        // Validate token
        $decoded = $this->jwt->validateToken($token);
        
        if ($decoded) {
            // Token is valid
            return [
                'status' => 'success',
                'message' => 'Token is valid',
                'user' => $decoded
            ];
        } else {
            // Token is invalid
            return [
                'status' => 'error',
                'message' => 'Invalid or expired token'
            ];
        }
    }
}
?>