<?php
// Debug script to check visits table structure
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get table structure
    $stmt = $conn->prepare("DESCRIBE visits");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Visits Table Structure:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Also check if visits table exists
    $stmt = $conn->prepare("SHOW TABLES LIKE 'visits'");
    $stmt->execute();
    $exists = $stmt->rowCount() > 0;
    
    echo "<h3>Table exists: " . ($exists ? "YES" : "NO") . "</h3>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>