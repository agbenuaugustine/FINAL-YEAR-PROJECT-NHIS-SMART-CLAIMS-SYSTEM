<?php
/**
 * Simple Test API to verify connection and database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Test database connection
    require_once 'config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Test basic query
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Test required tables
    $tables = [];
    $requiredTables = ['visits', 'patients', 'users'];
    
    foreach ($requiredTables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            $tables[$table] = [
                'exists' => true,
                'count' => $count['count']
            ];
        } catch (Exception $e) {
            $tables[$table] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Test sample claimable consultation query
    $claimableQuery = "SELECT v.id as visit_id, 
                              CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, '')) as full_name,
                              p.nhis_number, v.visit_date, v.status
                       FROM visits v
                       JOIN patients p ON v.patient_id = p.id
                       WHERE (v.status = 'Completed' OR v.status = 'completed')
                       AND (p.nhis_number IS NOT NULL AND p.nhis_number != '' AND p.nhis_number != 'N/A')
                       LIMIT 5";
    
    $stmt = $db->prepare($claimableQuery);
    $stmt->execute();
    $sampleConsultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'API and database are working',
        'timestamp' => date('Y-m-d H:i:s'),
        'database_connection' => 'OK',
        'tables' => $tables,
        'sample_consultations' => $sampleConsultations,
        'sample_count' => count($sampleConsultations)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>