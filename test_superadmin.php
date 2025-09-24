<?php
/**
 * Quick test for superadmin dashboard dependencies
 */

// Test database connection and table existence
require_once __DIR__ . '/api/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Testing Superadmin Dashboard Dependencies</h2>";
    
    // Test hospitals table
    echo "<h3>1. Testing Hospitals Table</h3>";
    $query = "DESCRIBE hospitals";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        echo "<p style='color: red;'>❌ Hospitals table not found</p>";
    } else {
        echo "<p style='color: green;'>✅ Hospitals table exists with columns:</p>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li>" . $column['Field'] . " (" . $column['Type'] . ")</li>";
        }
        echo "</ul>";
    }
    
    // Test users table
    echo "<h3>2. Testing Users Table</h3>";
    $query = "DESCRIBE users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        echo "<p style='color: red;'>❌ Users table not found</p>";
    } else {
        echo "<p style='color: green;'>✅ Users table exists</p>";
    }
    
    // Test patients table
    echo "<h3>3. Testing Patients Table</h3>";
    $query = "DESCRIBE patients";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        echo "<p style='color: red;'>❌ Patients table not found</p>";
    } else {
        echo "<p style='color: green;'>✅ Patients table exists</p>";
    }
    
    // Test sample data
    echo "<h3>4. Testing Sample Data</h3>";
    $query = "SELECT COUNT(*) as count FROM hospitals";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Hospitals in database: " . $result['count'] . "</p>";
    
    $query = "SELECT COUNT(*) as count FROM users WHERE role = 'superadmin'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Superadmin users: " . $result['count'] . "</p>";
    
    echo "<h3>5. Superadmin Dashboard URLs</h3>";
    echo "<p><a href='/api/access/superadmin/index.php'>Main Dashboard</a> (requires superadmin login)</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Note:</strong> If tables don't exist, you may need to run database setup scripts.</p>";
echo "<p><strong>Access:</strong> Login as superadmin and navigate to /api/access/superadmin/index.php</p>";
?>