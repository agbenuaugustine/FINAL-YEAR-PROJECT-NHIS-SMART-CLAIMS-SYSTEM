<?php
/**
 * Diagnosis & Medication API
 * 
 * Handles diagnosis recording, ICD-10 code management, and prescription functionality
 */

// Start output buffering to catch any unexpected output
ob_start();

// Suppress all errors from being displayed (but still log them)
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/DiagnosisController.php';
require_once __DIR__ . '/controllers/MedicationController.php';
require_once __DIR__ . '/controllers/PrescriptionController.php';
require_once __DIR__ . '/controllers/PatientController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Get user information
$user = $_SESSION['user'];
$user_hospital_id = $user['hospital_id'];

try {
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    // Initialize controllers with error checking
    if (!class_exists('DiagnosisController')) {
        throw new Exception('DiagnosisController class not found');
    }
    if (!class_exists('PrescriptionController')) {
        throw new Exception('PrescriptionController class not found');
    }
    if (!class_exists('MedicationController')) {
        throw new Exception('MedicationController class not found');
    }
    if (!class_exists('PatientController')) {
        throw new Exception('PatientController class not found');
    }
    
    $diagnosisController = new DiagnosisController();
    $medicationController = new MedicationController();
    $prescriptionController = new PrescriptionController();
    $patientController = new PatientController();
    
    switch ($action) {
        
        case 'search_patients':
            // Search for patients
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $searchTerm = $_GET['q'] ?? '';
            if (empty($searchTerm)) {
                throw new Exception('Search term is required');
            }
            
            $result = $patientController->searchPatients($searchTerm, $user_hospital_id);
            echo json_encode($result);
            break;
        
        case 'search_icd10':
            // Search ICD-10 codes
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $query = $_GET['query'] ?? '';
            $limit = $_GET['limit'] ?? 20;
            
            if (empty($query)) {
                echo json_encode([
                    'status' => 'success',
                    'data' => []
                ]);
                break;
            }
            
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                $sql = "SELECT id, description, category, subcategory 
                        FROM icd10_codes 
                        WHERE (id LIKE ? OR description LIKE ?) 
                        AND is_active = TRUE 
                        ORDER BY 
                            CASE 
                                WHEN id LIKE ? THEN 1
                                WHEN description LIKE ? THEN 2
                                ELSE 3
                            END,
                            id
                        LIMIT ?";
                
                $stmt = $conn->prepare($sql);
                $searchTerm = '%' . $query . '%';
                $exactSearch = $query . '%';
                $stmt->bindParam(1, $searchTerm);
                $stmt->bindParam(2, $searchTerm);
                $stmt->bindParam(3, $exactSearch);
                $stmt->bindParam(4, $exactSearch);
                $stmt->bindParam(5, $limit, PDO::PARAM_INT);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'status' => 'success',
                    'data' => $results
                ]);
                
            } catch (Exception $e) {
                throw new Exception('Failed to search ICD-10 codes: ' . $e->getMessage());
            }
            break;
            
        case 'search_medications':
            // Search medications
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $query = $_GET['query'] ?? '';
            $limit = $_GET['limit'] ?? 20;
            
            if (empty($query)) {
                echo json_encode([
                    'status' => 'success',
                    'data' => []
                ]);
                break;
            }
            
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                $sql = "SELECT id, name, generic_name, brand_name, dosage_form, 
                               strength, unit_price, drug_class, nhis_covered
                        FROM medications 
                        WHERE (name LIKE ? OR generic_name LIKE ? OR brand_name LIKE ?) 
                        AND is_active = TRUE 
                        AND nhis_covered = TRUE
                        ORDER BY 
                            CASE 
                                WHEN name LIKE ? THEN 1
                                WHEN generic_name LIKE ? THEN 2
                                WHEN brand_name LIKE ? THEN 3
                                ELSE 4
                            END,
                            name
                        LIMIT ?";
                
                $stmt = $conn->prepare($sql);
                $searchTerm = '%' . $query . '%';
                $exactSearch = $query . '%';
                $stmt->bindParam(1, $searchTerm);
                $stmt->bindParam(2, $searchTerm);
                $stmt->bindParam(3, $searchTerm);
                $stmt->bindParam(4, $exactSearch);
                $stmt->bindParam(5, $exactSearch);
                $stmt->bindParam(6, $exactSearch);
                $stmt->bindParam(7, $limit, PDO::PARAM_INT);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'status' => 'success',
                    'data' => $results
                ]);
                
            } catch (Exception $e) {
                throw new Exception('Failed to search medications: ' . $e->getMessage());
            }
            break;
            
        case 'save_diagnosis':
            // Save patient diagnosis
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) {
                throw new Exception('Invalid JSON data');
            }
            
            // Validate required fields
            $required = ['visit_id', 'icd10_code', 'diagnosis_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("$field is required");
                }
            }
            
            // Add user info
            $data['diagnosed_by'] = $user['id'];
            
            try {
                $result = $diagnosisController->saveDiagnosis($data);
                echo json_encode($result);
            } catch (Exception $e) {
                error_log("DiagnosisController error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save diagnosis: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'save_prescription':
            // Save medication prescription
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) {
                throw new Exception('Invalid JSON data');
            }
            
            // Validate required fields
            $required = ['visit_id', 'medication_id', 'dosage', 'frequency', 'duration', 'quantity'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("$field is required");
                }
            }
            
            // Add user info
            $data['prescribed_by'] = $user['id'];
            
            $result = $prescriptionController->savePrescription($data);
            echo json_encode($result);
            break;
            
        case 'finalize_consultation':
            // Simple finalize - just update visit status
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) {
                throw new Exception('Invalid JSON data');
            }
            
            // Validate required fields
            if (empty($data['visit_id'])) {
                throw new Exception('Visit ID is required');
            }
            
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                // Clean any output that might have been generated
                if (ob_get_level()) {
                    ob_clean();
                }
                
                // Update visit status to completed
                $updateVisit = "UPDATE visits SET status = 'Completed', updated_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($updateVisit);
                $stmt->bindParam(1, $data['visit_id']);
                
                if ($stmt->execute()) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Consultation finalized successfully',
                        'data' => [
                            'visit_id' => $data['visit_id'],
                            'visit_status' => 'Completed'
                        ]
                    ]);
                } else {
                    throw new Exception('Failed to update visit status');
                }
                
            } catch (Exception $e) {
                throw new Exception('Failed to finalize consultation: ' . $e->getMessage());
            }
            break;
            
        case 'get_patient_diagnoses':
            // Get patient's diagnosis history
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $patientId = $_GET['patient_id'] ?? '';
            $limit = $_GET['limit'] ?? 10;
            
            if (empty($patientId)) {
                throw new Exception('Patient ID is required');
            }
            
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                $sql = "SELECT d.*, icd.description as diagnosis_description, 
                               v.visit_date, u.full_name as diagnosed_by_name
                        FROM diagnoses d
                        JOIN visits v ON d.visit_id = v.id
                        JOIN icd10_codes icd ON d.icd10_code = icd.id
                        JOIN users u ON d.diagnosed_by = u.id
                        WHERE v.patient_id = ? AND v.hospital_id = ?
                        ORDER BY d.diagnosed_at DESC
                        LIMIT ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(1, $patientId);
                $stmt->bindParam(2, $user_hospital_id);
                $stmt->bindParam(3, $limit, PDO::PARAM_INT);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'status' => 'success',
                    'data' => $results
                ]);
                
            } catch (Exception $e) {
                throw new Exception('Failed to get patient diagnoses: ' . $e->getMessage());
            }
            break;

        case 'get_recent_consultations':
            // Get recent consultations with diagnosis and medication info
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $limit = $_GET['limit'] ?? 10;
            
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                $sql = "SELECT DISTINCT v.id as visit_id, v.visit_date, v.status,
                               CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                               p.nhis_number,
                               d.icd10_code,
                               icd.description as primary_diagnosis,
                               COUNT(DISTINCT pr.id) as medication_count,
                               u.full_name as physician_name
                        FROM visits v
                        JOIN patients p ON v.patient_id = p.id
                        LEFT JOIN diagnoses d ON v.id = d.visit_id
                        LEFT JOIN icd10_codes icd ON d.icd10_code = icd.id
                        LEFT JOIN prescriptions pr ON v.id = pr.visit_id
                        JOIN users u ON v.created_by = u.id
                        WHERE v.hospital_id = ?
                        GROUP BY v.id, v.visit_date, v.status, p.first_name, p.last_name, 
                                p.nhis_number, d.icd10_code, icd.description, u.full_name
                        ORDER BY v.visit_date DESC
                        LIMIT ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(1, $user_hospital_id);
                $stmt->bindParam(2, $limit, PDO::PARAM_INT);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'status' => 'success',
                    'data' => $results
                ]);
                
            } catch (Exception $e) {
                throw new Exception('Failed to get recent consultations: ' . $e->getMessage());
            }
            break;

        case 'get_consultation_diagnoses':
            // Get diagnoses for a specific consultation/visit
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $visitId = $_GET['visit_id'] ?? '';
            if (empty($visitId)) {
                throw new Exception('Visit ID is required');
            }
            
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                $sql = "SELECT d.*, icd.description as diagnosis_description, 
                               u.full_name as diagnosed_by_name
                        FROM diagnoses d
                        JOIN icd10_codes icd ON d.icd10_code = icd.id
                        JOIN users u ON d.diagnosed_by = u.id
                        WHERE d.visit_id = ?
                        ORDER BY d.diagnosed_at DESC";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(1, $visitId);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'status' => 'success',
                    'data' => $results
                ]);
                
            } catch (Exception $e) {
                throw new Exception('Failed to get consultation diagnoses: ' . $e->getMessage());
            }
            break;

        case 'get_consultation_prescriptions':
            // Get prescriptions for a specific consultation/visit
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $visitId = $_GET['visit_id'] ?? '';
            if (empty($visitId)) {
                throw new Exception('Visit ID is required');
            }
            
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                $sql = "SELECT p.*, m.name as medication_name, m.generic_name, 
                               m.strength, m.dosage_form, u.full_name as prescribed_by_name
                        FROM prescriptions p
                        JOIN medications m ON p.medication_id = m.id
                        JOIN users u ON p.prescribed_by = u.id
                        WHERE p.visit_id = ?
                        ORDER BY p.prescribed_at DESC";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(1, $visitId);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'status' => 'success',
                    'data' => $results
                ]);
                
            } catch (Exception $e) {
                throw new Exception('Failed to get consultation prescriptions: ' . $e->getMessage());
            }
            break;
            
        case 'get_patient_prescriptions':
            // Get patient's prescription history
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $patientId = $_GET['patient_id'] ?? '';
            $limit = $_GET['limit'] ?? 10;
            
            if (empty($patientId)) {
                throw new Exception('Patient ID is required');
            }
            
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                $sql = "SELECT p.*, m.name as medication_name, m.generic_name,
                               v.visit_date, u.full_name as prescribed_by_name
                        FROM prescriptions p
                        JOIN visits v ON p.visit_id = v.id
                        JOIN medications m ON p.medication_id = m.id
                        JOIN users u ON p.prescribed_by = u.id
                        WHERE v.patient_id = ? AND v.hospital_id = ?
                        ORDER BY p.prescribed_at DESC
                        LIMIT ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(1, $patientId);
                $stmt->bindParam(2, $user_hospital_id);
                $stmt->bindParam(3, $limit, PDO::PARAM_INT);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'status' => 'success',
                    'data' => $results
                ]);
                
            } catch (Exception $e) {
                throw new Exception('Failed to get patient prescriptions: ' . $e->getMessage());
            }
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
    
} catch (Exception $e) {
    // Clean any output buffer before sending error response
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (Throwable $t) {
    // Clean any output buffer before sending error response
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error: ' . $t->getMessage()
    ]);
} finally {
    // End output buffering and send response
    if (ob_get_level()) {
        ob_end_flush();
    }
}
?>