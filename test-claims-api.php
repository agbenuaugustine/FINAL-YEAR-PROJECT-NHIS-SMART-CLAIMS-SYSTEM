<?php
/**
 * Test script to debug claims API issues
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Claims API Debug Test</h2>";

// Test 1: Check if files exist
echo "<h3>1. File Existence Check</h3>";
$files = [
    'api/config/database.php',
    'api/controllers/ClaimsController.php',
    'api/claims-api.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    echo "- $file: " . (file_exists($path) ? "✓ EXISTS" : "✗ MISSING") . "<br>";
}

// Test 2: Database connection
echo "<h3>2. Database Connection Test</h3>";
try {
    require_once 'api/config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "✓ Database connection successful<br>";
    
    // Test basic query
    $stmt = $db->query("SELECT COUNT(*) as count FROM visits");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Visits table accessible - Found {$result['count']} visits<br>";
    
    // Test patients table
    $stmt = $db->query("SELECT COUNT(*) as count FROM patients WHERE nhis_number IS NOT NULL AND nhis_number != ''");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Patients with NHIS numbers: {$result['count']}<br>";
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Controller instantiation
echo "<h3>3. Controller Test</h3>";
try {
    require_once 'api/controllers/ClaimsController.php';
    $controller = new ClaimsController($db);
    echo "✓ ClaimsController instantiated successfully<br>";
    
    // Test getClaimableConsultations method
    echo "<h4>Testing getClaimableConsultations method:</h4>";
    $result = $controller->getClaimableConsultations();
    echo "Status: " . $result['status'] . "<br>";
    
    if ($result['status'] === 'success') {
        $count = count($result['data']);
        echo "✓ Found $count claimable consultations<br>";
        
        if ($count > 0) {
            echo "<h5>Sample data:</h5>";
            echo "<pre>" . print_r(array_slice($result['data'], 0, 3), true) . "</pre>";
        }
    } else {
        echo "✗ Error: " . $result['message'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "✗ Controller error: " . $e->getMessage() . "<br>";
}

// Test 4: API endpoint simulation
echo "<h3>4. API Endpoint Simulation</h3>";
try {
    $_GET['action'] = 'get_claimable_consultations';
    
    ob_start();
    require 'api/claims-api.php';
    $output = ob_get_clean();
    
    echo "API Response:<br>";
    echo "<pre>$output</pre>";
    
} catch (Exception $e) {
    echo "✗ API simulation error: " . $e->getMessage() . "<br>";
}

// Test 5: Check visit data structure
echo "<h3>5. Visit Data Structure Check</h3>";
try {
    $stmt = $db->query("SHOW COLUMNS FROM visits");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Visits table columns:<br>";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})<br>";
    }
    
    // Sample visit data
    $stmt = $db->query("SELECT v.*, p.first_name, p.last_name, p.nhis_number 
                       FROM visits v 
                       JOIN patients p ON v.patient_id = p.id 
                       WHERE p.nhis_number IS NOT NULL 
                       LIMIT 3");
    $visits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h5>Sample visit data:</h5>";
    foreach ($visits as $visit) {
        echo "Visit ID: {$visit['id']}, Patient: {$visit['first_name']} {$visit['last_name']}, NHIS: {$visit['nhis_number']}, Status: {$visit['status']}<br>";
    }
    
} catch (Exception $e) {
    echo "✗ Data structure error: " . $e->getMessage() . "<br>";
}

?>