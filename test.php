<?php
// Disable error display
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering
ob_start();

// Clean any output
if (ob_get_level()) {
    ob_clean();
}

// Set JSON headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

try {
    // Test database connection
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Test user count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Clean output and return JSON
    if (ob_get_level()) {
        ob_clean();
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'System is working',
        'user_count' => $userCount,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Clean output and return error JSON
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'System error: ' . $e->getMessage()
    ]);
}
?>