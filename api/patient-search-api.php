<?php
/**
 * Patient Search API Endpoint
 * 
 * Handles patient search requests for vital signs and other medical forms
 */

// Set response headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
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

// Get user's hospital ID for security
$user_hospital_id = $_SESSION['user']['hospital_id'] ?? null;

// Process request
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $search_term = $_GET['search'] ?? '';
    
    if (strlen($search_term) < 3) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Search term must be at least 3 characters'
        ]);
        exit;
    }
    
    // Search for patients by NHIS number or name
    $query = "SELECT p.*, 
                     CONCAT(p.first_name, ' ', COALESCE(p.other_names, ''), ' ', p.last_name) as full_name,
                     TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age_years,
                     (SELECT MAX(v.visit_date) FROM visits v WHERE v.patient_id = p.id) as last_visit_date,
                     (SELECT COUNT(*) FROM visits v WHERE v.patient_id = p.id) as visit_count
              FROM patients p
              WHERE p.is_active = 1";
    
    $params = [];
    
    // Add hospital filter if user is not superadmin
    if ($user_hospital_id && $_SESSION['user']['role'] !== 'superadmin') {
        $query .= " AND p.hospital_id = ?";
        $params[] = $user_hospital_id;
    }
    
    // Add search conditions
    $query .= " AND (p.nhis_number LIKE ? 
                OR p.patient_number LIKE ?
                OR CONCAT(p.first_name, ' ', COALESCE(p.other_names, ''), ' ', p.last_name) LIKE ?
                OR p.phone LIKE ?)";
    
    $search_param = '%' . $search_term . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    
    $query .= " ORDER BY p.last_name, p.first_name LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    
    $patients = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Get latest vital signs if available
        $vitals_query = "SELECT vs.temperature, vs.blood_pressure_systolic, vs.blood_pressure_diastolic, 
                                vs.pulse_rate, vs.recorded_at
                         FROM vital_signs vs
                         JOIN visits v ON vs.visit_id = v.id
                         WHERE v.patient_id = ?
                         ORDER BY vs.recorded_at DESC
                         LIMIT 1";
        
        $vitals_stmt = $conn->prepare($vitals_query);
        $vitals_stmt->execute([$row['id']]);
        $latest_vitals = $vitals_stmt->fetch(PDO::FETCH_ASSOC);
        
        $patients[] = [
            'id' => $row['id'],
            'patient_number' => $row['patient_number'],
            'nhis_number' => $row['nhis_number'],
            'full_name' => $row['full_name'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'other_names' => $row['other_names'],
            'gender' => $row['gender'],
            'age' => $row['age_years'] . ' years',
            'date_of_birth' => $row['date_of_birth'],
            'phone' => $row['phone'],
            'blood_group' => $row['blood_group'],
            'allergies' => $row['allergies'],
            'last_visit_date' => $row['last_visit_date'],
            'visit_count' => $row['visit_count'],
            'latest_vitals' => $latest_vitals ? [
                'temperature' => $latest_vitals['temperature'],
                'systolic' => $latest_vitals['blood_pressure_systolic'],
                'diastolic' => $latest_vitals['blood_pressure_diastolic'],
                'pulse' => $latest_vitals['pulse_rate'],
                'date' => $latest_vitals['recorded_at']
            ] : null
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $patients,
        'count' => count($patients)
    ]);
    
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>