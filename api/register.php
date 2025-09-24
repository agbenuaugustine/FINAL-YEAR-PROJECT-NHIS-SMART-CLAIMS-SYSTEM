<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Register API Endpoint
 * 
 * Handles user registration requests
 */

// Set response headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include controller
require_once 'controllers/AuthController.php';

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Create auth controller
$auth = new AuthController();

// Log the incoming request data
$input = file_get_contents("php://input");
error_log("Register API - Received data: " . $input);

// Process registration
$result = $auth->register();

// Log the result
error_log("Register API - Result: " . json_encode($result));

// Set response code
if ($result['status'] === 'success') {
    http_response_code(201);
} else {
    http_response_code(400);
}

// Return response
echo json_encode($result);
?>