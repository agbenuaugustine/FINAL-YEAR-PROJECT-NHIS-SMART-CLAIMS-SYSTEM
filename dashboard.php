<?php
/**
 * Dashboard API Endpoint
 * 
 * Provides statistics and data for the dashboard
 * Updated for enhanced database schema with hospital management
 */

// Set response headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Visit.php';
require_once __DIR__ . '/models/Patient.php';
require_once __DIR__ . '/models/Claim.php';
require_once __DIR__ . '/controllers/VisitController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Initialize models
$visit = new Visit($conn);
$patient = new Patient($conn);
$claim = new Claim($conn);

// Initialize controllers
$visitController = new VisitController();

// Get dashboard data
try {
    // Get user information from session
    $user = $_SESSION['user'] ?? null;
    $role = $_SESSION['user']['role'] ?? 'user';
    $hospital_id = $_SESSION['user']['hospital_id'] ?? null;
    
    // Prepare base filters for hospital-specific data
    $hospital_filter = '';
    $hospital_params = [];
    
    if ($role !== 'superadmin' && $hospital_id) {
        $hospital_filter = " WHERE hospital_id = ?";
        $hospital_params = [$hospital_id];
    }
    
    // Get enhanced statistics
    $stats = [];
    
    // Get patient statistics
    $patientQuery = "SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today,
        COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as this_week,
        COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as this_month
        FROM patients" . $hospital_filter;
    
    $stmt = $conn->prepare($patientQuery);
    if (!empty($hospital_params)) {
        $stmt->execute($hospital_params);
    } else {
        $stmt->execute();
    }
    $patientStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get visit statistics
    $visitQuery = "SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN DATE(visit_date) = CURDATE() THEN 1 END) as today,
        COUNT(CASE WHEN DATE(visit_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as this_week,
        COUNT(CASE WHEN DATE(visit_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as this_month,
        COUNT(CASE WHEN status = 'Waiting' THEN 1 END) as waiting,
        COUNT(CASE WHEN status = 'In Progress' THEN 1 END) as in_progress,
        COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed
        FROM visits" . $hospital_filter;
    
    $stmt = $conn->prepare($visitQuery);
    if (!empty($hospital_params)) {
        $stmt->execute($hospital_params);
    } else {
        $stmt->execute();
    }
    $visitStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get claim statistics
    $claimQuery = "SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN status = 'Draft' THEN 1 END) as draft,
        COUNT(CASE WHEN status = 'Submitted' THEN 1 END) as submitted,
        COUNT(CASE WHEN status = 'Under Review' THEN 1 END) as under_review,
        COUNT(CASE WHEN status = 'Approved' THEN 1 END) as approved,
        COUNT(CASE WHEN status = 'Rejected' THEN 1 END) as rejected,
        COUNT(CASE WHEN status = 'Paid' THEN 1 END) as paid,
        COUNT(CASE WHEN status = 'Partially Paid' THEN 1 END) as partially_paid,
        SUM(total_amount) as total_amount,
        SUM(nhis_amount) as nhis_amount,
        AVG(CASE WHEN approval_date IS NOT NULL AND submission_date IS NOT NULL 
            THEN TIMESTAMPDIFF(HOUR, submission_date, approval_date) END) as avg_processing_time
        FROM claims" . $hospital_filter;
    
    $stmt = $conn->prepare($claimQuery);
    if (!empty($hospital_params)) {
        $stmt->execute($hospital_params);
    } else {
        $stmt->execute();
    }
    $claimStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get department statistics (if user has hospital access)
    $departmentStats = [];
    if ($hospital_id) {
        // OPD visits today
        $opdQuery = "SELECT COUNT(*) as count FROM visits v 
                     JOIN departments d ON v.department_id = d.id 
                     WHERE d.hospital_id = ? AND DATE(v.visit_date) = CURDATE() 
                     AND d.department_name LIKE '%OPD%'";
        $stmt = $conn->prepare($opdQuery);
        $stmt->execute([$hospital_id]);
        $departmentStats['opd'] = ['today' => $stmt->fetchColumn()];
        
        // Lab tests processed
        $labQuery = "SELECT COUNT(*) as count FROM lab_orders lo 
                     JOIN visits v ON lo.visit_id = v.id 
                     WHERE v.hospital_id = ? AND lo.status IN ('Completed', 'In Progress')";
        $stmt = $conn->prepare($labQuery);
        $stmt->execute([$hospital_id]);
        $departmentStats['lab'] = ['processed' => $stmt->fetchColumn()];
        
        // Pharmacy dispensed
        $pharmacyQuery = "SELECT COUNT(*) as count FROM prescriptions p 
                          JOIN visits v ON p.visit_id = v.id 
                          WHERE v.hospital_id = ? AND p.dispensed = 1";
        $stmt = $conn->prepare($pharmacyQuery);
        $stmt->execute([$hospital_id]);
        $departmentStats['pharmacy'] = ['dispensed' => $stmt->fetchColumn()];
    }
    
    // Get recent visits with enhanced data
    $recentVisitsQuery = "SELECT v.*, 
        CONCAT(p.first_name, ' ', p.last_name) as patient_name,
        p.nhis_number,
        h.hospital_name,
        d.department_name,
        CONCAT(u.full_name) as attending_doctor_name
        FROM visits v
        JOIN patients p ON v.patient_id = p.id
        LEFT JOIN hospitals h ON v.hospital_id = h.id
        LEFT JOIN departments d ON v.department_id = d.id
        LEFT JOIN users u ON v.attending_doctor = u.id" . 
        ($hospital_filter ? " WHERE v.hospital_id = ?" : "") . "
        ORDER BY v.visit_date DESC 
        LIMIT 5";
    
    $stmt = $conn->prepare($recentVisitsQuery);
    if (!empty($hospital_params)) {
        $stmt->execute($hospital_params);
    } else {
        $stmt->execute();
    }
    $recentVisits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process claim statistics for response
    $claimStatsByStatus = [
        'Draft' => ['count' => $claimStats['draft'] ?? 0],
        'Submitted' => ['count' => $claimStats['submitted'] ?? 0],
        'Under Review' => ['count' => $claimStats['under_review'] ?? 0],
        'Approved' => ['count' => $claimStats['approved'] ?? 0],
        'Rejected' => ['count' => $claimStats['rejected'] ?? 0],
        'Paid' => ['count' => $claimStats['paid'] ?? 0],
        'Partially Paid' => ['count' => $claimStats['partially_paid'] ?? 0]
    ];
    
    // Prepare response
    $response = [
        'status' => 'success',
        'data' => [
            'patients' => [
                'total' => (int)$patientStats['total'],
                'today' => (int)$patientStats['today'],
                'this_week' => (int)$patientStats['this_week'],
                'this_month' => (int)$patientStats['this_month']
            ],
            'visits' => [
                'total' => (int)$visitStats['total'],
                'today' => (int)$visitStats['today'],
                'this_week' => (int)$visitStats['this_week'],
                'this_month' => (int)$visitStats['this_month'],
                'waiting' => (int)$visitStats['waiting'],
                'in_progress' => (int)$visitStats['in_progress'],
                'completed' => (int)$visitStats['completed']
            ],
            'claims' => [
                'total' => (int)$claimStats['total'],
                'by_status' => $claimStatsByStatus,
                'total_amount' => (float)($claimStats['total_amount'] ?? 0),
                'nhis_amount' => (float)($claimStats['nhis_amount'] ?? 0),
                'avg_processing_time' => round((float)($claimStats['avg_processing_time'] ?? 0), 1),
                'this_month' => [
                    'count' => (int)($claimStats['approved'] ?? 0) + (int)($claimStats['paid'] ?? 0)
                ]
            ],
            'departments' => $departmentStats,
            'recent_visits' => $recentVisits,
            'user_context' => [
                'role' => $role,
                'hospital_id' => $hospital_id,
                'is_superadmin' => $role === 'superadmin'
            ]
        ]
    ];
    
    // Return response
    http_response_code(200);
    echo json_encode($response);
} catch (Exception $e) {
    // Handle errors
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>