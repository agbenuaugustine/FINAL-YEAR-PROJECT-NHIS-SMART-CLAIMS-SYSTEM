<?php
/**
 * Vital Signs API Endpoint
 * 
 * Handles vital signs-related requests
 */

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

// Include required files
require_once __DIR__ . '/controllers/VitalSignsController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create vital signs controller
$controller = new VitalSignsController();

// Get request method and parameters
$method = $_SERVER['REQUEST_METHOD'];
$params = [];

// Parse query parameters
if (isset($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $params);
}

// Get vital signs ID from URL if present
$vitalSignsId = null;
$requestUri = $_SERVER['REQUEST_URI'];
if (preg_match('/vital-signs-api\/(\d+)/', $requestUri, $matches)) {
    $vitalSignsId = $matches[1];
}

// Process request based on method
switch ($method) {
    case 'GET':
        if ($vitalSignsId) {
            // Get single vital signs record
            $result = $controller->getVitalSigns($vitalSignsId);
        } elseif (isset($params['visit_id'])) {
            // Get vital signs by visit ID
            $result = $controller->getVitalSignsByVisit($params['visit_id']);
        } else {
            // Get all vital signs with pagination
            $result = $controller->getAllVitalSigns($params);
        }
        break;
        
    case 'POST':
        // Get request body
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Create vital signs record
        $result = $controller->createVitalSigns($data);
        break;
        
    case 'PUT':
        if (!$vitalSignsId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Vital Signs ID is required']);
            exit;
        }
        
        // Get request body
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Update vital signs
        $result = $controller->updateVitalSigns($vitalSignsId, $data);
        break;
        
    case 'DELETE':
        if (!$vitalSignsId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Vital Signs ID is required']);
            exit;
        }
        
        // Delete vital signs
        $result = $controller->deleteVitalSigns($vitalSignsId);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        exit;
}

// Set response code
if ($result['status'] === 'success') {
    http_response_code(200);
} else {
    http_response_code(400);
}

// Return response
echo json_encode($result);
?>