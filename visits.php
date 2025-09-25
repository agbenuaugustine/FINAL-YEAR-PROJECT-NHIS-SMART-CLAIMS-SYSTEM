<?php
/**
 * Visits API Endpoint
 * 
 * Handles visit-related requests
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

// Include controller
require_once __DIR__ . '/controllers/VisitController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create visit controller
$controller = new VisitController();

// Get request method and parameters
$method = $_SERVER['REQUEST_METHOD'];
$params = [];

// Parse query parameters
if (isset($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $params);
}

// Get visit ID from URL if present
$visitId = null;
$patientId = null;
$requestUri = $_SERVER['REQUEST_URI'];

if (preg_match('/visits\/(\d+)/', $requestUri, $matches)) {
    $visitId = $matches[1];
} elseif (preg_match('/patients\/(\d+)\/visits/', $requestUri, $matches)) {
    $patientId = $matches[1];
}

// Process request based on method
switch ($method) {
    case 'GET':
        if ($visitId) {
            // Get single visit
            $result = $controller->getVisit($visitId);
        } elseif ($patientId) {
            // Get patient visits
            $result = $controller->getPatientVisits($patientId);
        } elseif (isset($params['recent'])) {
            // Get recent visits
            $limit = isset($params['limit']) ? (int)$params['limit'] : 5;
            $result = $controller->getRecentVisits($limit);
        } elseif (isset($params['stats'])) {
            // Get visit statistics
            $result = $controller->getStatistics();
        } else {
            // Get all visits
            $result = $controller->getVisits($params);
        }
        break;
        
    case 'POST':
        // Get request body
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Create visit
        $result = $controller->createVisit($data);
        break;
        
    case 'PUT':
        if (!$visitId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Visit ID is required']);
            exit;
        }
        
        // Get request body
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Update visit
        $result = $controller->updateVisit($visitId, $data);
        break;
        
    case 'DELETE':
        if (!$visitId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Visit ID is required']);
            exit;
        }
        
        // Delete visit
        $result = $controller->deleteVisit($visitId);
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