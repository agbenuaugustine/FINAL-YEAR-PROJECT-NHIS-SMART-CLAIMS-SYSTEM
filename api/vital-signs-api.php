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
require_once __DIR__ . '/controllers/VisitController.php';
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

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Initialize controllers
$vitalSignsController = new VitalSignsController();
$visitController = new VisitController();
$patientController = new PatientController();

try {
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
        
        case 'create_visit':
            // Create a new visit for vital signs recording
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) {
                throw new Exception('Invalid JSON data');
            }
            
            // Add hospital_id and user info to visit data
            $data['hospital_id'] = $user_hospital_id;
            $data['created_by'] = $user['id'];
            $data['visit_type'] = 'OPD';
            $data['status'] = 'Waiting';
            
            $result = $visitController->createVisit($data);
            echo json_encode($result);
            break;
        
        case 'record_vitals':
            // Record vital signs for a visit
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) {
                throw new Exception('Invalid JSON data');
            }
            
            // Add user info
            $data['recorded_by'] = $user['id'];
            
            $result = $vitalSignsController->createVitalSigns($data);
            echo json_encode($result);
            break;
        
        case 'get_vitals':
            // Get vital signs by ID or by visit
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $vitalsId = $_GET['id'] ?? '';
            $visitId = $_GET['visit_id'] ?? '';
            
            if (!empty($vitalsId)) {
                // Get specific vital signs record
                $result = $vitalSignsController->getVitalSigns($vitalsId);
            } elseif (!empty($visitId)) {
                // Get vital signs for a visit
                $result = $vitalSignsController->getVitalSignsByVisit($visitId);
            } else {
                throw new Exception('Either vitals ID or visit ID is required');
            }
            
            echo json_encode($result);
            break;
        
        case 'create_visit':
            // Create a visit for the patient
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                throw new Exception('Invalid JSON data');
            }
            
            // Include visit controller
            require_once __DIR__ . '/controllers/VisitController.php';
            $visitController = new VisitController($conn);
            
            $result = $visitController->createVisit($data);
            echo json_encode($result);
            break;
            
        case 'get_patient_history':
            // Get vital signs history for a patient
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $patientId = $_GET['patient_id'] ?? '';
            $limit = $_GET['limit'] ?? 10;
            
            if (empty($patientId)) {
                throw new Exception('Patient ID is required');
            }
            
            $result = $vitalSignsController->getPatientVitalSignsHistory($patientId, $limit);
            echo json_encode($result);
            break;
        
        case 'check_existing_vitals':
            // Check if patient already has vitals recorded today
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $patientId = $_GET['patient_id'] ?? '';
            if (empty($patientId)) {
                throw new Exception('Patient ID is required');
            }
            
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                $query = "SELECT vs.*, v.visit_date, v.id as visit_id
                          FROM vital_signs vs
                          JOIN visits v ON vs.visit_id = v.id
                          WHERE v.patient_id = ? AND v.hospital_id = ? 
                          AND DATE(v.visit_date) = CURDATE()
                          ORDER BY vs.recorded_at DESC
                          LIMIT 1";
                
                $stmt = $conn->prepare($query);
                $stmt->bindParam(1, $patientId);
                $stmt->bindParam(2, $user_hospital_id);
                $stmt->execute();
                
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    echo json_encode([
                        'status' => 'success',
                        'exists' => true,
                        'data' => [
                            'id' => $existing['id'],
                            'visit_id' => $existing['visit_id'],
                            'temperature' => $existing['temperature'],
                            'blood_pressure_systolic' => $existing['blood_pressure_systolic'],
                            'blood_pressure_diastolic' => $existing['blood_pressure_diastolic'],
                            'pulse_rate' => $existing['pulse_rate'],
                            'respiratory_rate' => $existing['respiratory_rate'],
                            'weight' => $existing['weight'],
                            'height' => $existing['height'],
                            'oxygen_saturation' => $existing['oxygen_saturation'],
                            'pain_scale' => $existing['pain_scale'],
                            'notes' => $existing['notes'],
                            'recorded_at' => $existing['recorded_at']
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'success',
                        'exists' => false
                    ]);
                }
                
            } catch (Exception $e) {
                throw new Exception('Failed to check existing vitals: ' . $e->getMessage());
            }
            break;
        
        case 'recent_vitals':
            // Get recent vital signs records
            if ($method !== 'GET') {
                throw new Exception('Method not allowed');
            }
            
            $limit = $_GET['limit'] ?? 10;
            
            // Get recent vital signs from the database
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                $query = "SELECT vs.*, v.patient_id, 
                                 CONCAT(p.first_name, ' ', p.last_name) as patient_name, 
                                 p.nhis_number,
                                 u.full_name as recorder_name, 
                                 v.visit_date,
                                 CONCAT(vs.blood_pressure_systolic, '/', vs.blood_pressure_diastolic) as blood_pressure
                          FROM vital_signs vs
                          JOIN visits v ON vs.visit_id = v.id
                          JOIN patients p ON v.patient_id = p.id
                          LEFT JOIN users u ON vs.recorded_by = u.id
                          WHERE v.hospital_id = ?
                          ORDER BY vs.recorded_at DESC
                          LIMIT ?";
                
                $stmt = $conn->prepare($query);
                $stmt->bindParam(1, $user_hospital_id);
                $stmt->bindParam(2, $limit, PDO::PARAM_INT);
                $stmt->execute();
                
                $vitals = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $vitals[] = [
                        'id' => $row['id'],
                        'visit_id' => $row['visit_id'],
                        'patient_name' => $row['patient_name'],
                        'nhis_number' => $row['nhis_number'],
                        'temperature' => $row['temperature'],
                        'blood_pressure' => $row['blood_pressure'],
                        'pulse_rate' => $row['pulse_rate'],
                        'respiratory_rate' => $row['respiratory_rate'],
                        'oxygen_saturation' => $row['oxygen_saturation'],
                        'weight' => $row['weight'],
                        'height' => $row['height'],
                        'bmi' => $row['bmi'],
                        'recorder_name' => $row['recorder_name'],
                        'recorded_at' => $row['recorded_at'],
                        'visit_date' => $row['visit_date']
                    ];
                }
                
                echo json_encode([
                    'status' => 'success',
                    'data' => $vitals
                ]);
                
            } catch (Exception $e) {
                throw new Exception('Failed to fetch recent vitals: ' . $e->getMessage());
            }
            break;
        
        case 'update_vitals':
            // Update vital signs
            if ($method !== 'PUT') {
                throw new Exception('Method not allowed');
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) {
                throw new Exception('Invalid JSON data');
            }
            
            $vitalsId = $_GET['id'] ?? '';
            if (empty($vitalsId)) {
                throw new Exception('Vital signs ID is required');
            }
            
            $result = $vitalSignsController->updateVitalSigns($vitalsId, $data);
            echo json_encode($result);
            break;
        
        case 'delete_vitals':
            // Delete vital signs
            if ($method !== 'DELETE') {
                throw new Exception('Method not allowed');
            }
            
            $vitalsId = $_GET['id'] ?? '';
            if (empty($vitalsId)) {
                throw new Exception('Vital signs ID is required');
            }
            
            // You can implement delete functionality in the controller if needed
            echo json_encode([
                'status' => 'error',
                'message' => 'Delete functionality not implemented'
            ]);
            break;
        
        case 'test':
            // Test endpoint
            echo json_encode([
                'status' => 'success',
                'message' => 'Vital Signs API is working',
                'user' => $user['full_name'],
                'hospital_id' => $user_hospital_id
            ]);
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