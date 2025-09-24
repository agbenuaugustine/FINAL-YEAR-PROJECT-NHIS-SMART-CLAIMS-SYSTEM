<?php
/**
 * Debug script to test hospital approval
 */

// Start output buffering
ob_start();

// Include necessary files
require_once __DIR__ . '/api/config/database.php';

// Set content type
header('Content-Type: application/json');

try {
    echo json_encode(['status' => 'starting']);
    ob_flush();
    
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();
    
    echo json_encode(['status' => 'connected to database']);
    ob_flush();
    
    // Find a test hospital
    $stmt = $db->query("SELECT id, hospital_name, registration_status, is_active FROM hospitals WHERE registration_status = 'Pending' LIMIT 1");
    $hospital = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hospital) {
        // Create a test hospital if none exists
        $insertQuery = "INSERT INTO hospitals (hospital_name, hospital_code, primary_contact_person, primary_contact_email, primary_contact_phone, region, district, town_city, hospital_type, hospital_category, registration_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([
            'Test Hospital for Approval',
            'TEST001',
            'Test Manager',
            'test@hospital.com',
            '0123456789',
            'Greater Accra',
            'Accra',
            'Accra',
            'Private',
            'Clinic',
            'Pending'
        ]);
        
        $hospitalId = $db->lastInsertId();
        echo json_encode(['status' => 'created test hospital', 'id' => $hospitalId]);
    } else {
        $hospitalId = $hospital['id'];
        echo json_encode(['status' => 'found existing hospital', 'hospital' => $hospital]);
    }
    
    ob_flush();
    
    // Test the approval update
    $db->beginTransaction();
    
    // Update hospital
    $updateQuery = "UPDATE hospitals SET registration_status = 'Approved', is_active = 1, approval_date = NOW() WHERE id = ?";
    $stmt = $db->prepare($updateQuery);
    $result = $stmt->execute([$hospitalId]);
    
    if (!$result) {
        throw new Exception('Failed to update hospital: ' . implode(', ', $stmt->errorInfo()));
    }
    
    $affectedRows = $stmt->rowCount();
    echo json_encode(['status' => 'hospital updated', 'affected_rows' => $affectedRows]);
    ob_flush();
    
    // Update users
    $userQuery = "UPDATE users SET is_active = 1 WHERE hospital_id = ?";
    $userStmt = $db->prepare($userQuery);
    $userResult = $userStmt->execute([$hospitalId]);
    
    if (!$userResult) {
        echo json_encode(['status' => 'user update failed', 'error' => $userStmt->errorInfo()]);
    } else {
        $affectedUsers = $userStmt->rowCount();
        echo json_encode(['status' => 'users updated', 'affected_users' => $affectedUsers]);
    }
    
    ob_flush();
    
    // Commit transaction
    $db->commit();
    
    // Verify the update
    $verifyStmt = $db->prepare("SELECT id, hospital_name, registration_status, is_active, approval_date FROM hospitals WHERE id = ?");
    $verifyStmt->execute([$hospitalId]);
    $updatedHospital = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'updated_hospital' => $updatedHospital]);
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollback();
    }
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

ob_end_flush();
?>