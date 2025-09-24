<?php
// Fix duplicate visit numbers and clean up database

require_once 'api/config/database.php';

echo "<h2>Visit Number Cleanup</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p>✅ Database connection successful</p>";
    
    // First, let's see what duplicates we have
    echo "<h3>Checking for duplicate visit numbers...</h3>";
    
    $duplicateQuery = "
        SELECT visit_number, COUNT(*) as count 
        FROM visits 
        WHERE visit_number IS NOT NULL 
        GROUP BY visit_number 
        HAVING COUNT(*) > 1
    ";
    
    $duplicates = $conn->query($duplicateQuery)->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicates) > 0) {
        echo "<p>Found " . count($duplicates) . " duplicate visit numbers:</p>";
        foreach ($duplicates as $dup) {
            echo "<p>- {$dup['visit_number']} (appears {$dup['count']} times)</p>";
        }
        
        // Fix duplicates by regenerating visit numbers
        echo "<h3>Fixing duplicate visit numbers...</h3>";
        
        // Get all visits with duplicate numbers
        foreach ($duplicates as $dup) {
            $visitNumber = $dup['visit_number'];
            
            // Get all visits with this number
            $visitsQuery = "SELECT id FROM visits WHERE visit_number = ? ORDER BY id";
            $visitsStmt = $conn->prepare($visitsQuery);
            $visitsStmt->execute([$visitNumber]);
            $visits = $visitsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Keep the first one, renumber the rest
            $first = true;
            foreach ($visits as $visit) {
                if ($first) {
                    $first = false;
                    continue; // Keep the first one as is
                }
                
                // Generate new unique number for this visit
                $newVisitNumber = generateUniqueVisitNumber($conn, $visit['id']);
                
                $updateQuery = "UPDATE visits SET visit_number = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([$newVisitNumber, $visit['id']]);
                
                echo "<p>Updated visit ID {$visit['id']} to new number: $newVisitNumber</p>";
            }
        }
        
    } else {
        echo "<p>✅ No duplicate visit numbers found</p>";
    }
    
    // Clean up any NULL visit numbers
    echo "<h3>Fixing NULL visit numbers...</h3>";
    
    $nullQuery = "SELECT id FROM visits WHERE visit_number IS NULL";
    $nullVisits = $conn->query($nullQuery)->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($nullVisits) > 0) {
        echo "<p>Found " . count($nullVisits) . " visits with NULL visit numbers</p>";
        
        foreach ($nullVisits as $visit) {
            $newVisitNumber = generateUniqueVisitNumber($conn, $visit['id']);
            
            $updateQuery = "UPDATE visits SET visit_number = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->execute([$newVisitNumber, $visit['id']]);
            
            echo "<p>Updated visit ID {$visit['id']} with new number: $newVisitNumber</p>";
        }
    } else {
        echo "<p>✅ No NULL visit numbers found</p>";
    }
    
    // Final status check
    echo "<h3>Final Status:</h3>";
    
    $totalVisits = $conn->query("SELECT COUNT(*) FROM visits")->fetchColumn();
    $uniqueVisitNumbers = $conn->query("SELECT COUNT(DISTINCT visit_number) FROM visits WHERE visit_number IS NOT NULL")->fetchColumn();
    $nullVisitNumbers = $conn->query("SELECT COUNT(*) FROM visits WHERE visit_number IS NULL")->fetchColumn();
    
    echo "<p>Total visits: $totalVisits</p>";
    echo "<p>Unique visit numbers: $uniqueVisitNumbers</p>";
    echo "<p>NULL visit numbers: $nullVisitNumbers</p>";
    
    if ($totalVisits == $uniqueVisitNumbers && $nullVisitNumbers == 0) {
        echo "<p>✅ All visit numbers are unique and valid!</p>";
    } else {
        echo "<p>❌ Still have issues with visit numbers</p>";
    }
    
    echo "<h3>✅ Visit number cleanup completed!</h3>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Helper function to generate unique visit numbers
function generateUniqueVisitNumber($conn, $visitId = null) {
    $maxAttempts = 10;
    $attempts = 0;
    
    while ($attempts < $maxAttempts) {
        // Get current year
        $year = date('Y');
        
        // Get the next sequential number for this year
        $query = "SELECT MAX(CAST(SUBSTRING(visit_number, 6) AS UNSIGNED)) as max_num 
                  FROM visits 
                  WHERE visit_number LIKE 'V{$year}%' 
                  AND visit_number IS NOT NULL";
        
        if ($visitId) {
            $query .= " AND id != $visitId";
        }
        
        $result = $conn->query($query);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $nextNum = ($row['max_num'] ?? 0) + 1;
        
        // Generate visit number: V + Year + 6-digit sequential number
        $visitNumber = 'V' . $year . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
        
        // Check if this number already exists
        $checkQuery = "SELECT COUNT(*) FROM visits WHERE visit_number = ?";
        if ($visitId) {
            $checkQuery .= " AND id != $visitId";
        }
        
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->execute([$visitNumber]);
        
        if ($checkStmt->fetchColumn() == 0) {
            return $visitNumber;
        }
        
        $attempts++;
        usleep(10000); // 10ms delay
    }
    
    // Fallback
    return 'V' . date('Y') . date('mdHis') . ($visitId ? $visitId : rand(100, 999));
}
?>