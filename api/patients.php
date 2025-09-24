<?php
/**
 * Patients API Endpoint
 * 
 * Handles patient-related requests
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
require_once __DIR__ . '/controllers/PatientController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create patient controller
$controller = new PatientController();

// Get request method and parameters
$method = $_SERVER['REQUEST_METHOD'];
$params = [];

// Parse query parameters
if (isset($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $params);
}

// Get patient ID from URL if present
$patientId = null;
$requestUri = $_SERVER['REQUEST_URI'];
if (preg_match('/patients\/(\d+)/', $requestUri, $matches)) {
    $patientId = $matches[1];
}

// Process request based on method
switch ($method) {
    case 'GET':
        if ($patientId) {
            // Get single patient
            $result = $controller->getPatient($patientId);
        } elseif (isset($params['search'])) {
            // Search patients
            $result = $controller->searchPatients($params['search']);
        } else {
            // Get all patients
            $result = $controller->getPatients($params);
        }
        break;
        
    case 'POST':
        // Get request body
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Create patient
        $result = $controller->createPatient($data);
        break;
        
    case 'PUT':
        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID is required']);
            exit;
        }
        
        // Get request body
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Update patient
        $result = $controller->updatePatient($patientId, $data);
        break;
        
    case 'DELETE':
        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID is required']);
            exit;
        }
        
        // Delete patient
        $result = $controller->deletePatient($patientId);
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