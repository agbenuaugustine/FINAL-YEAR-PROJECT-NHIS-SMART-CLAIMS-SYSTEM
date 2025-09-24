<?php
// Direct API test to see what's causing the 500 error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>API Direct Test</h2>";

// Simulate the exact same request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'create_requisition';

// Sample test data
$testData = [
    'patient_id' => 1,
    'visit_type' => 'OPD',
    'visit_date' => date('Y-m-d H:i:s'),
    'priority' => 'Routine',
    'chief_complaint' => 'Test submission',
    'services' => [
        [
            'id' => 1,
            'tariff' => 25.00,
            'notes' => 'Test service'
        ]
    ]
];

// Simulate POST data
$_POST = $testData;
file_put_contents('php://input', json_encode($testData));

echo "<h3>Test Data:</h3>";
echo "<pre>" . print_r($testData, true) . "</pre>";

echo "<h3>API Response:</h3>";

try {
    // Capture output
    ob_start();
    
    // Include the API file
    include 'api/services-api.php';
    
    $output = ob_get_clean();
    
    echo "<pre>$output</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Also test database setup
echo "<hr><h3>Database Check:</h3>";

try {
    require_once 'api/config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check tables
    $tables = ['patients', 'services', 'visits', 'service_orders'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            $count = $conn->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "<p>✅ $table: EXISTS ($count records)</p>";
        } else {
            echo "<p>❌ $table: NOT EXISTS</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
}
?>