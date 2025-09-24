<?php
require_once 'api/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Show all tables
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Available Tables:</h2>";
    foreach ($tables as $table) {
        echo "<h3>$table</h3>";
        
        // Show table structure
        $stmt = $db->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='margin-bottom: 20px;'>";
        echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show sample data
        $stmt = $db->query("SELECT * FROM $table LIMIT 3");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($data)) {
            echo "<h4>Sample Data:</h4>";
            echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        }
        
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>