<?php
/**
 * Visit API Endpoint
 * 
 * Handles visit-related requests for creating visits when recording vital signs
 */

// Start output buffering to catch any unexpected output
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/api_errors.log');

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
require_once __DIR__ . '/config/database.php';

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

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get user information
$user = $_SESSION['user'];
$user_hospital_id = $user['hospital_id'];

switch ($method) {
    case 'POST':
        // Create new visit for vital signs recording
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validate required fields
        if (!isset($data['patient_id']) || !isset($data['visit_type'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Patient ID and visit type are required'
            ]);
            exit;
        }
        
        try {
            // Generate unique visit number
            $visit_number = 'V' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Check if visit number already exists
            $check_query = "SELECT id FROM visits WHERE visit_number = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->execute([$visit_number]);
            
            // If exists, generate new one
            while ($stmt->rowCount() > 0) {
                $visit_number = 'V' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $stmt->execute([$visit_number]);
            }
            
            // Create visit
            $query = "INSERT INTO visits (
                        hospital_id, patient_id, visit_number, visit_date, visit_type,
                        department_id, chief_complaint, presenting_complaint,
                        history_of_present_illness, past_medical_history, physical_examination, 
                        status, attending_doctor, priority, created_by
                      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([
                $user_hospital_id,
                $data['patient_id'],
                $visit_number,
                $data['visit_date'] ?? date('Y-m-d H:i:s'),
                $data['visit_type'] ?? 'OPD',
                $user['department_id'] ?? null,
                $data['chief_complaint'] ?? '',
                $data['presenting_complaint'] ?? '',
                $data['history_present_illness'] ?? '',
                $data['past_medical_history'] ?? '',
                $data['physical_examination'] ?? '',
                'In Progress',
                $data['attending_doctor'] ?? $user['id'],
                $data['priority'] ?? 'Normal',
                $user['id']
            ]);
            
            if ($result) {
                $visit_id = $conn->lastInsertId();
                
                // Get the created visit details
                $select_query = "SELECT v.*, p.first_name, p.last_name, p.nhis_number
                                FROM visits v
                                JOIN patients p ON v.patient_id = p.id
                                WHERE v.id = ?";
                $stmt = $conn->prepare($select_query);
                $stmt->execute([$visit_id]);
                $visit = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Clean output buffer before sending response
                if (ob_get_level()) {
                    ob_clean();
                }
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Visit created successfully',
                    'data' => [
                        'id' => $visit_id,
                        'visit_number' => $visit_number,
                        'patient_name' => $visit['first_name'] . ' ' . $visit['last_name'],
                        'nhis_number' => $visit['nhis_number'],
                        'visit_date' => $visit['visit_date'],
                        'visit_type' => $visit['visit_type'],
                        'status' => $visit['status']
                    ]
                ]);
            } else {
                throw new Exception('Failed to create visit');
            }
            
        } catch (Exception $e) {
            // Clean output buffer before sending error response
            if (ob_get_level()) {
                ob_clean();
            }
            
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Error creating visit: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'GET':
        // Get patient's active visit or recent visits
        $patient_id = $_GET['patient_id'] ?? null;
        
        if (!$patient_id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Patient ID is required'
            ]);
            exit;
        }
        
        try {
            // Check for active visit first
            $query = "SELECT v.*, p.first_name, p.last_name, p.nhis_number
                      FROM visits v
                      JOIN patients p ON v.patient_id = p.id
                      WHERE v.patient_id = ? AND v.status IN ('Waiting', 'In Progress')
                      AND v.hospital_id = ?
                      ORDER BY v.visit_date DESC
                      LIMIT 1";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([$patient_id, $user_hospital_id]);
            
            if ($stmt->rowCount() > 0) {
                $visit = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Clean output buffer before sending response
                if (ob_get_level()) {
                    ob_clean();
                }
                
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'id' => $visit['id'],
                        'visit_number' => $visit['visit_number'],
                        'patient_name' => $visit['first_name'] . ' ' . $visit['last_name'],
                        'nhis_number' => $visit['nhis_number'],
                        'visit_date' => $visit['visit_date'],
                        'visit_type' => $visit['visit_type'],
                        'status' => $visit['status'],
                        'chief_complaint' => $visit['chief_complaint'],
                        'presenting_complaint' => $visit['presenting_complaint']
                    ]
                ]);
            } else {
                // Clean output buffer before sending response
                if (ob_get_level()) {
                    ob_clean();
                }
                
                // No active visit found, return message
                echo json_encode([
                    'status' => 'success',
                    'message' => 'No active visit found for this patient',
                    'data' => null
                ]);
            }
            
        } catch (Exception $e) {
            // Clean output buffer before sending error response
            if (ob_get_level()) {
                ob_clean();
            }
            
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Error retrieving visit: ' . $e->getMessage()
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed'
        ]);
        break;
}

// Clean up output buffering
if (ob_get_level()) {
    ob_end_flush();
}
?>