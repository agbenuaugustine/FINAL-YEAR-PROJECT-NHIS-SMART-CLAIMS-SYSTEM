<?php
/**
 * Claims System Diagnostic Tool
 * Run this to check if everything is working properly
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

echo json_encode([
    'diagnosis' => 'starting',
    'timestamp' => date('Y-m-d H:i:s')
]);

try {
    $diagnosis = [];
    
    // Test 1: Check if files exist
    $diagnosis['files'] = [];
    $requiredFiles = [
        'config/database.php',
        'controllers/ClaimsController.php',
        'claims-api.php'
    ];
    
    foreach ($requiredFiles as $file) {
        $path = __DIR__ . '/' . $file;
        $diagnosis['files'][$file] = file_exists($path);
    }
    
    // Test 2: Database connection
    try {
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        $diagnosis['database'] = 'connected';
        
        // Test basic query
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch();
        $diagnosis['database_query'] = $result ? 'working' : 'failed';
        
    } catch (Exception $e) {
        $diagnosis['database'] = 'failed: ' . $e->getMessage();
    }
    
    // Test 3: Required tables
    $diagnosis['tables'] = [];
    if (isset($db)) {
        $tables = ['visits', 'patients', 'users', 'diagnoses', 'prescriptions'];
        foreach ($tables as $table) {
            try {
                $stmt = $db->query("SELECT COUNT(*) as count FROM `$table`");
                $result = $stmt->fetch();
                $diagnosis['tables'][$table] = $result['count'] . ' records';
            } catch (Exception $e) {
                $diagnosis['tables'][$table] = 'error: ' . $e->getMessage();
            }
        }
    }
    
    // Test 4: Claims Controller
    try {
        require_once 'controllers/ClaimsController.php';
        $controller = new ClaimsController($db);
        $diagnosis['controller'] = 'instantiated';
        
        // Test the method
        $result = $controller->getClaimableConsultations();
        $diagnosis['controller_method'] = $result['status'];
        $diagnosis['consultations_count'] = count($result['data'] ?? []);
        
    } catch (Exception $e) {
        $diagnosis['controller'] = 'failed: ' . $e->getMessage();
    }
    
    // Test 5: Sample data check
    if (isset($db)) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM visits v 
                               JOIN patients p ON v.patient_id = p.id 
                               WHERE v.status = 'Completed' 
                               AND p.nhis_number IS NOT NULL 
                               AND p.nhis_number != ''");
            $result = $stmt->fetch();
            $diagnosis['claimable_visits'] = $result['count'];
        } catch (Exception $e) {
            $diagnosis['claimable_visits'] = 'error: ' . $e->getMessage();
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'diagnosis' => $diagnosis,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>