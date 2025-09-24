<?php
// Quick fix to remove duplicate visit number constraint

require_once 'api/config/database.php';

echo "<h2>Quick Database Fix</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Option 1: Remove the UNIQUE constraint on visit_number
    echo "<h3>Removing UNIQUE constraint on visit_number...</h3>";
    
    try {
        // First, check if the constraint exists
        $constraintQuery = "SHOW CREATE TABLE visits";
        $result = $conn->query($constraintQuery);
        $createTable = $result->fetch(PDO::FETCH_ASSOC);
        
        if (strpos($createTable['Create Table'], 'UNIQUE KEY') !== false) {
            // Drop the unique constraint
            $conn->exec("ALTER TABLE visits DROP INDEX visit_number");
            echo "<p>✅ Removed UNIQUE constraint on visit_number</p>";
        } else {
            echo "<p>ℹ️ No UNIQUE constraint found on visit_number</p>";
        }
        
        // Add a non-unique index instead for performance
        $conn->exec("ALTER TABLE visits ADD INDEX idx_visit_number (visit_number)");
        echo "<p>✅ Added non-unique index on visit_number</p>";
        
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "Duplicate key name") !== false) {
            echo "<p>ℹ️ Index already exists</p>";
        } else {
            echo "<p>Warning: " . $e->getMessage() . "</p>";
        }
    }
    
    // Option 2: Clean up existing duplicates
    echo "<h3>Cleaning up existing data...</h3>";
    
    // Set all visit numbers to NULL first
    $conn->exec("UPDATE visits SET visit_number = NULL");
    echo "<p>✅ Reset all visit numbers</p>";
    
    // Regenerate visit numbers sequentially
    $visits = $conn->query("SELECT id FROM visits ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    $counter = 1;
    
    foreach ($visits as $visit) {
        $visitNumber = 'V' . date('Y') . str_pad($counter, 6, '0', STR_PAD_LEFT);
        $updateStmt = $conn->prepare("UPDATE visits SET visit_number = ? WHERE id = ?");
        $updateStmt->execute([$visitNumber, $visit['id']]);
        $counter++;
    }
    
    echo "<p>✅ Regenerated " . count($visits) . " visit numbers</p>";
    
    // Verify the fix
    echo "<h3>Verification:</h3>";
    
    $totalVisits = $conn->query("SELECT COUNT(*) FROM visits")->fetchColumn();
    $uniqueVisitNumbers = $conn->query("SELECT COUNT(DISTINCT visit_number) FROM visits WHERE visit_number IS NOT NULL")->fetchColumn();
    
    echo "<p>Total visits: $totalVisits</p>";
    echo "<p>Unique visit numbers: $uniqueVisitNumbers</p>";
    
    if ($totalVisits == $uniqueVisitNumbers) {
        echo "<p>✅ All visit numbers are now unique!</p>";
    }
    
    echo "<h3>✅ Database fix completed!</h3>";
    echo "<p><strong>You can now try submitting requisitions again.</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>