<?php
/**
 * Test script for hospital actions
 */

// Include necessary files
require_once __DIR__ . '/api/config/database.php';

try {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();
    
    echo "=== Hospital Actions Test ===\n\n";
    
    // Check if hospitals table exists and has required columns
    echo "1. Checking hospitals table structure...\n";
    $result = $db->query("DESCRIBE hospitals");
    $columns = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }
    
    $requiredColumns = ['id', 'hospital_name', 'registration_status', 'is_active', 'approval_date'];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "✓ Column '$col' exists\n";
        } else {
            echo "✗ Column '$col' MISSING\n";
        }
    }
    
    // Check for sample hospitals
    echo "\n2. Checking for sample hospitals...\n";
    $stmt = $db->query("SELECT id, hospital_name, registration_status, is_active FROM hospitals LIMIT 5");
    $hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($hospitals)) {
        echo "✗ No hospitals found in database\n";
    } else {
        echo "✓ Found " . count($hospitals) . " hospitals:\n";
        foreach ($hospitals as $hospital) {
            echo "  - ID: {$hospital['id']}, Name: {$hospital['hospital_name']}, Status: {$hospital['registration_status']}, Active: " . ($hospital['is_active'] ? 'Yes' : 'No') . "\n";
        }
    }
    
    // Check users table
    echo "\n3. Checking users table...\n";
    $result = $db->query("DESCRIBE users");
    $userColumns = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $userColumns[] = $row['Field'];
    }
    
    $requiredUserColumns = ['id', 'hospital_id', 'is_active'];
    foreach ($requiredUserColumns as $col) {
        if (in_array($col, $userColumns)) {
            echo "✓ Users column '$col' exists\n";
        } else {
            echo "✗ Users column '$col' MISSING\n";
        }
    }
    
    // Check for sample users
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "✓ Found $userCount users in database\n";
    
    // Test a specific hospital update
    if (!empty($hospitals)) {
        $testHospital = $hospitals[0];
        echo "\n4. Testing hospital update for ID: {$testHospital['id']}\n";
        
        // Test update query
        $updateQuery = "UPDATE hospitals SET registration_status = 'Approved', is_active = 1, approval_date = NOW() WHERE id = ?";
        $stmt = $db->prepare($updateQuery);
        
        if ($stmt) {
            echo "✓ Update query prepared successfully\n";
            
            // Don't actually execute, just test preparation
            echo "✓ Would update hospital: {$testHospital['hospital_name']}\n";
            
            // Test user update query
            $userQuery = "UPDATE users SET is_active = 1 WHERE hospital_id = ?";
            $userStmt = $db->prepare($userQuery);
            
            if ($userStmt) {
                echo "✓ User update query prepared successfully\n";
            } else {
                echo "✗ Failed to prepare user update query\n";
            }
        } else {
            echo "✗ Failed to prepare update query\n";
        }
    }
    
    echo "\n=== Test Complete ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>