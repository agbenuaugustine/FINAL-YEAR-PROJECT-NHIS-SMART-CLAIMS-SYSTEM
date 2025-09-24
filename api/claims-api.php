<?php
/**
 * Claims Processing API
 * Handles NHIS claims generation, submission, and tracking
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config/database.php';
require_once 'controllers/ClaimsController.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    // Log the incoming request
    error_log("Claims API Request: " . $method . " " . $_SERVER['REQUEST_URI'] . " Action: " . $action);
    
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    $controller = new ClaimsController($db);
    
    if (empty($action)) {
        throw new Exception('No action specified');
    }
    
    error_log("Claims API Action: $action");
    
    switch ($action) {
        case 'test':
            // Simple test endpoint
            echo json_encode([
                'status' => 'success',
                'message' => 'API is working',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'get_claimable_consultations':
            // Get consultations that can be claimed
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';
            $status = $_GET['status'] ?? '';
            $department = $_GET['department'] ?? '';
            
            error_log("Claims API: Getting claimable consultations with params - dateFrom: $dateFrom, dateTo: $dateTo, status: $status, department: $department");
            
            $result = $controller->getClaimableConsultations($dateFrom, $dateTo, $status, $department);
            error_log("Claims API: Result status = " . $result['status']);
            
            echo json_encode($result);
            break;
            
        case 'search_consultations':
            // Search consultations for claims
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $query = $_GET['q'] ?? '';
            if (empty($query)) {
                throw new Exception('Search query is required');
            }
            
            $result = $controller->searchConsultations($query);
            echo json_encode($result);
            break;
            
        case 'get_consultation_details':
            // Get detailed consultation information for claims
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $visitId = $_GET['visit_id'] ?? '';
            if (empty($visitId)) {
                throw new Exception('Visit ID is required');
            }
            
            $result = $controller->getConsultationDetails($visitId);
            echo json_encode($result);
            break;
            
        case 'verify_nhis_eligibility':
            // Verify patient NHIS eligibility
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['nhis_number'])) {
                throw new Exception('NHIS number is required');
            }
            
            $result = $controller->verifyNHISEligibility($input);
            echo json_encode($result);
            break;
            
        case 'calculate_claim_amount':
            // Calculate claim amount based on NHIA tariffs
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['visit_id'])) {
                throw new Exception('Visit ID is required');
            }
            
            $result = $controller->calculateClaimAmount($input);
            echo json_encode($result);
            break;
            
        case 'generate_claim':
            // Generate NHIS claim
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $result = $controller->generateClaim($input);
            echo json_encode($result);
            break;
            
        case 'submit_claim':
            // Submit claim to NHIA
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $result = $controller->submitClaim($input);
            echo json_encode($result);
            break;
            
        case 'get_claims':
            // Get claims list
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $status = $_GET['status'] ?? '';
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            
            $result = $controller->getClaims($status, $dateFrom, $dateTo, $limit, $offset);
            echo json_encode($result);
            break;
            
        case 'get_claim_details':
            // Get claim details
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $claimId = $_GET['claim_id'] ?? '';
            if (empty($claimId)) {
                throw new Exception('Claim ID is required');
            }
            
            $result = $controller->getClaimDetails($claimId);
            echo json_encode($result);
            break;
            
        case 'update_claim_status':
            // Update claim status
            if ($method !== 'PUT') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $result = $controller->updateClaimStatus($input);
            echo json_encode($result);
            break;
            
        case 'get_claims_analytics':
            // Get claims analytics data
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $period = $_GET['period'] ?? 'month';
            $result = $controller->getClaimsAnalytics($period);
            echo json_encode($result);
            break;
            
        case 'bulk_process_claims':
            // Process multiple claims at once
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $result = $controller->bulkProcessClaims($input);
            echo json_encode($result);
            break;
            
        case 'export_claims':
            // Export claims to PDF/Excel
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $result = $controller->exportClaims($input);
            echo json_encode($result);
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>