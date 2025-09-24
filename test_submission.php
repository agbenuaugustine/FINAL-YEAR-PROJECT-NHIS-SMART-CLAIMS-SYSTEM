<?php
// Test submission endpoint directly
header('Content-Type: application/json');

// Include database connection
require_once 'api/config/database.php';

// Start session
session_start();

echo "<h2>Submission Test</h2>";

try {
    // Test database connection
    $database = new Database();
    $conn = $database->getConnection();
    echo "<p>✅ Database connection: SUCCESS</p>";
    
    // Test if tables exist
    $tables = ['visits', 'service_orders', 'services', 'patients'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "<p>✅ Table '$table': EXISTS</p>";
        } else {
            echo "<p>❌ Table '$table': NOT EXISTS</p>";
        }
    }
    
    // Test sample data
    $patientCount = $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $serviceCount = $conn->query("SELECT COUNT(*) FROM services")->fetchColumn();
    
    echo "<p>Patients in database: $patientCount</p>";
    echo "<p>Services in database: $serviceCount</p>";
    
    if ($patientCount > 0 && $serviceCount > 0) {
        // Get first patient and service
        $patient = $conn->query("SELECT * FROM patients LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $service = $conn->query("SELECT * FROM services LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Test Data:</h3>";
        echo "<p>Patient: " . $patient['first_name'] . " " . $patient['last_name'] . " (ID: " . $patient['id'] . ")</p>";
        echo "<p>Service: " . $service['name'] . " (ID: " . $service['id'] . ")</p>";
        
        // Test submission
        echo "<h3>Testing Submission:</h3>";
        
        $testData = [
            'patient_id' => $patient['id'],
            'visit_type' => 'OPD',
            'visit_date' => date('Y-m-d H:i:s'),
            'priority' => 'Routine',
            'chief_complaint' => 'Test submission',
            'services' => [
                [
                    'id' => $service['id'],
                    'tariff' => $service['nhis_tariff'] ?? $service['private_price'],
                    'notes' => 'Test service order'
                ]
            ]
        ];
        
        // Simulate the submission process
        try {
            $conn->beginTransaction();
            
            // Create visit record
            $visitQuery = "INSERT INTO visits 
                          (patient_id, visit_type, priority, chief_complaint, status, created_by, visit_date)
                          VALUES (?, ?, ?, ?, 'Waiting', ?, ?)";
            
            $visitStmt = $conn->prepare($visitQuery);
            $visitStmt->execute([
                $testData['patient_id'],
                $testData['visit_type'],
                $testData['priority'],
                $testData['chief_complaint'],
                1, // Default user ID
                $testData['visit_date']
            ]);
            
            $visitId = $conn->lastInsertId();
            
            // Generate visit number
            $visitNumber = 'V' . date('Y') . str_pad($visitId, 6, '0', STR_PAD_LEFT);
            $updateVisitQuery = "UPDATE visits SET visit_number = ? WHERE id = ?";
            $updateVisitStmt = $conn->prepare($updateVisitQuery);
            $updateVisitStmt->execute([$visitNumber, $visitId]);
            
            // Create service order
            $serviceOrderQuery = "INSERT INTO service_orders 
                                 (visit_id, service_id, notes, ordered_by, ordered_at)
                                 VALUES (?, ?, ?, ?, NOW())";
            $serviceOrderStmt = $conn->prepare($serviceOrderQuery);
            
            foreach ($testData['services'] as $serviceData) {
                $serviceOrderStmt->execute([
                    $visitId,
                    $serviceData['id'],
                    $serviceData['notes'],
                    1 // Default user ID
                ]);
            }
            
            $conn->commit();
            
            echo "<p>✅ Test submission: SUCCESS</p>";
            echo "<p>Visit ID: $visitId</p>";
            echo "<p>Visit Number: $visitNumber</p>";
            
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p>❌ Test submission: FAILED</p>";
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p>❌ Not enough test data. Please run setup_database.php first.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='setup_database.php'>Run Database Setup</a></p>";
echo "<p><a href='debug_db.php'>Debug Database</a></p>";
echo "<p><a href='api/services-api.php?action=test'>Test API Endpoint</a></p>";
?>