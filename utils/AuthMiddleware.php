<?php
/**
 * Authentication Middleware
 * 
 * Handles authentication for protected API endpoints
 */

require_once __DIR__ . '/JwtHandler.php';

class AuthMiddleware {
    private $jwt;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->jwt = new JwtHandler();
    }
    
    /**
     * Authenticate request
     * 
     * @return mixed User data if authenticated, false otherwise
     */
    public function authenticate() {
        // Get all headers
        $headers = getallheaders();
        
        // Get authorization header
        $authHeader = $headers['Authorization'] ?? '';
        
        // Check if token exists
        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return false;
        }
        
        // Get token
        $token = $matches[1];
        
        // Validate token
        $decoded = $this->jwt->validateToken($token);
        
        return $decoded;
    }
    
    /**
     * Check if user has required role
     * 
     * @param object $user User data
     * @param array $roles Allowed roles
     * @return bool True if user has required role, false otherwise
     */
    public function hasRole($user, $roles) {
        if (!$user || !isset($user->role)) {
            return false;
        }
        
        return in_array($user->role, $roles);
    }
}

/**
 * Helper function to require authentication
 * 
 * @param array $allowed_roles Roles allowed to access the endpoint
 * @return object User data
 */
function requireAuth($allowed_roles = []) {
    // Set response headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
    // Handle preflight request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    // Create auth middleware
    $auth = new AuthMiddleware();
    
    // Authenticate request
    $user = $auth->authenticate();
    
    // Check if authenticated
    if (!$user) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    
    // Check if user has required role
    if (!empty($allowed_roles) && !$auth->hasRole($user, $allowed_roles)) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
        exit;
    }
    
    return $user;
}
?>