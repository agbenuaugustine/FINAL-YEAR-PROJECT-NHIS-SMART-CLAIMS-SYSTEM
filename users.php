<?php
/**
 * Users API Endpoint
 * 
 * Handles user management operations
 */

// Include required files
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'utils/AuthMiddleware.php';

// Authenticate request (only admin can access)
$current_user = requireAuth(['admin']);

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user model
$user = new User($db);

// Handle different HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Check if ID is provided
        if (isset($_GET['id'])) {
            // Get single user
            $user->id = $_GET['id'];
            
            if ($user->readOne($user->id)) {
                // User found
                $user_data = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'full_name' => $user->full_name,
                    'role' => $user->role,
                    'department' => $user->department,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'last_login' => $user->last_login,
                    'is_active' => $user->is_active
                ];
                
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'data' => $user_data
                ]);
            } else {
                // User not found
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'User not found'
                ]);
            }
        } else {
            // Get all users
            $stmt = $user->read();
            $users = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[] = [
                    'id' => $row['id'],
                    'username' => $row['username'],
                    'email' => $row['email'],
                    'full_name' => $row['full_name'],
                    'role' => $row['role'],
                    'department' => $row['department'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'last_login' => $row['last_login'],
                    'is_active' => (bool)$row['is_active']
                ];
            }
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => $users
            ]);
        }
        break;
        
    case 'POST':
        // Create user
        $data = json_decode(file_get_contents("php://input"));
        
        // Validate input
        if (
            empty($data->username) || 
            empty($data->password) || 
            empty($data->email) || 
            empty($data->full_name) || 
            empty($data->role)
        ) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'All fields are required'
            ]);
            break;
        }
        
        // Set user property values
        $user->username = $data->username;
        $user->password = $data->password;
        $user->email = $data->email;
        $user->full_name = $data->full_name;
        $user->role = $data->role;
        $user->department = $data->department ?? null;
        $user->is_active = $data->is_active ?? true;
        
        // Check if username already exists
        $temp_user = new User($db);
        if ($temp_user->findByUsername($user->username)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Username already exists'
            ]);
            break;
        }
        
        // Create the user
        if ($user->create()) {
            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'full_name' => $user->full_name,
                    'role' => $user->role,
                    'department' => $user->department
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to create user'
            ]);
        }
        break;
        
    case 'PUT':
        // Update user
        $data = json_decode(file_get_contents("php://input"));
        
        // Validate input
        if (
            empty($data->id) || 
            empty($data->username) || 
            empty($data->email) || 
            empty($data->full_name) || 
            empty($data->role)
        ) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Required fields missing'
            ]);
            break;
        }
        
        // Set user property values
        $user->id = $data->id;
        $user->username = $data->username;
        $user->email = $data->email;
        $user->full_name = $data->full_name;
        $user->role = $data->role;
        $user->department = $data->department ?? null;
        $user->is_active = $data->is_active ?? true;
        
        // Set password if provided
        if (!empty($data->password)) {
            $user->password = $data->password;
        }
        
        // Update the user
        if ($user->update()) {
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'User updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to update user'
            ]);
        }
        break;
        
    case 'DELETE':
        // Delete user
        $data = json_decode(file_get_contents("php://input"));
        
        // Validate input
        if (empty($data->id)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'User ID is required'
            ]);
            break;
        }
        
        // Set user ID
        $user->id = $data->id;
        
        // Delete the user
        if ($user->delete()) {
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to delete user'
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed'
        ]);
}
?>