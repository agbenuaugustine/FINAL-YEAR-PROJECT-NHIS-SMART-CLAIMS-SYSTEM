<?php
/**
 * Debug Database - Check Recent Visits, Diagnoses, and Prescriptions
 */

require_once 'api/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<h2>🔍 Database Debug Report</h2>";
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .warning { background: #fff3cd; color: #856404; }
        .error { background: #f8d7da; color: #721c24; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>";
    
    // 1. Check recent visits
    echo "<div class='section'>";
    echo "<h3>📋 Recent Visits (Last 10)</h3>";
    $stmt = $conn->query("SELECT v.id, v.visit_date, v.visit_type, v.status, 
                                 CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                                 u.full_name as created_by
                          FROM visits v 
                          LEFT JOIN patients p ON v.patient_id = p.id 
                          LEFT JOIN users u ON v.created_by = u.id 
                          ORDER BY v.visit_date DESC LIMIT 10");
    $visits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($visits) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Date</th><th>Patient</th><th>Type</th><th>Status</th><th>Created By</th></tr>";
        foreach ($visits as $visit) {
            echo "<tr>";
            echo "<td>{$visit['id']}</td>";
            echo "<td>{$visit['visit_date']}</td>";
            echo "<td>{$visit['patient_name']}</td>";
            echo "<td>{$visit['visit_type']}</td>";
            echo "<td>{$visit['status']}</td>";
            echo "<td>{$visit['created_by']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No visits found</p>";
    }
    echo "</div>";
    
    // 2. Check recent diagnoses
    echo "<div class='section'>";
    echo "<h3>🩺 Recent Diagnoses (Last 10)</h3>";
    $stmt = $conn->query("SELECT d.id, d.visit_id, d.icd10_code, i.description, 
                                 d.diagnosis_type, d.diagnosed_at,
                                 u.full_name as diagnosed_by
                          FROM diagnoses d 
                          LEFT JOIN icd10_codes i ON d.icd10_code = i.id
                          LEFT JOIN users u ON d.diagnosed_by = u.id
                          ORDER BY d.diagnosed_at DESC LIMIT 10");
    $diagnoses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($diagnoses) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Visit ID</th><th>ICD-10</th><th>Description</th><th>Type</th><th>Date</th><th>By</th></tr>";
        foreach ($diagnoses as $diagnosis) {
            echo "<tr>";
            echo "<td>{$diagnosis['id']}</td>";
            echo "<td>{$diagnosis['visit_id']}</td>";
            echo "<td>{$diagnosis['icd10_code']}</td>";
            echo "<td>{$diagnosis['description']}</td>";
            echo "<td>{$diagnosis['diagnosis_type']}</td>";
            echo "<td>{$diagnosis['diagnosed_at']}</td>";
            echo "<td>{$diagnosis['diagnosed_by']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No diagnoses found</p>";
    }
    echo "</div>";
    
    // 3. Check recent prescriptions
    echo "<div class='section'>";
    echo "<h3>💊 Recent Prescriptions (Last 10)</h3>";
    $stmt = $conn->query("SELECT p.id, p.visit_id, m.name as medication_name, 
                                 p.dosage, p.frequency, p.duration, p.quantity,
                                 p.prescribed_at, u.full_name as prescribed_by
                          FROM prescriptions p 
                          LEFT JOIN medications m ON p.medication_id = m.id
                          LEFT JOIN users u ON p.prescribed_by = u.id
                          ORDER BY p.prescribed_at DESC LIMIT 10");
    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($prescriptions) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Visit ID</th><th>Medication</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Qty</th><th>Date</th><th>By</th></tr>";
        foreach ($prescriptions as $prescription) {
            echo "<tr>";
            echo "<td>{$prescription['id']}</td>";
            echo "<td>{$prescription['visit_id']}</td>";
            echo "<td>{$prescription['medication_name']}</td>";
            echo "<td>{$prescription['dosage']}</td>";
            echo "<td>{$prescription['frequency']}</td>";
            echo "<td>{$prescription['duration']}</td>";
            echo "<td>{$prescription['quantity']}</td>";
            echo "<td>{$prescription['prescribed_at']}</td>";
            echo "<td>{$prescription['prescribed_by']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No prescriptions found</p>";
    }
    echo "</div>";
    
    // 4. Check medications count
    echo "<div class='section'>";
    echo "<h3>💉 Medications Database</h3>";
    $stmt = $conn->query("SELECT COUNT(*) as total, 
                                 COUNT(CASE WHEN nhis_covered = 1 THEN 1 END) as nhis_covered
                          FROM medications");
    $medCount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($medCount['total'] > 0) {
        echo "<p class='success'>✅ Total medications: {$medCount['total']} (NHIS covered: {$medCount['nhis_covered']})</p>";
    } else {
        echo "<p class='error'>❌ No medications in database. Run populate_medications.php</p>";
    }
    echo "</div>";
    
    // 5. Check ICD-10 codes count
    echo "<div class='section'>";
    echo "<h3>📊 ICD-10 Codes</h3>";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM icd10_codes");
    $icdCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($icdCount > 0) {
        echo "<p class='success'>✅ ICD-10 codes available: {$icdCount}</p>";
    } else {
        echo "<p class='error'>❌ No ICD-10 codes found</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h3>🎯 Quick Actions</h3>";
    echo "<p><a href='setup_medications.html' style='color: #007bff;'>Setup Medications Database</a></p>";
    echo "<p><a href='api/access/diagnosis-medication.php' style='color: #007bff;'>Go to Diagnosis & Medication Page</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section error'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>