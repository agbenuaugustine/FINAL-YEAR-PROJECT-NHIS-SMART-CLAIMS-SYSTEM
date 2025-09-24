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

// Include controller
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

// Get IDs from URL if present
$vitalSignsId = null;
$visitId = null;
$patientId = null;
$requestUri = $_SERVER['REQUEST_URI'];

if (preg_match('/vital-signs\/(\d+)/', $requestUri, $matches)) {
    $vitalSignsId = $matches[1];
} elseif (preg_match('/visits\/(\d+)\/vital-signs/', $requestUri, $matches)) {
    $visitId = $matches[1];
} elseif (preg_match('/patients\/(\d+)\/vital-signs/', $requestUri, $matches)) {
    $patientId = $matches[1];
}

// Process request based on method
switch ($method) {
    case 'GET':
        if ($vitalSignsId) {
            // Not implemented - would get a single vital signs record
            http_response_code(501);
            echo json_encode(['status' => 'error', 'message' => 'Not implemented']);
            exit;
        } elseif ($visitId) {
            // Get vital signs for a visit
            $result = $controller->getVisitVitalSigns($visitId);
        } elseif ($patientId) {
            // Get vital signs history for a patient
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $result = $controller->getPatientVitalSignsHistory($patientId, $limit);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Visit ID or patient ID is required']);
            exit;
        }
        break;
        
    case 'POST':
        // Get request body
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Record vital signs
        $result = $controller->recordVitalSigns($data);
        break;
        
    case 'PUT':
        if (!$vitalSignsId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Vital signs ID is required']);
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
            echo json_encode(['status' => 'error', 'message' => 'Vital signs ID is required']);
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