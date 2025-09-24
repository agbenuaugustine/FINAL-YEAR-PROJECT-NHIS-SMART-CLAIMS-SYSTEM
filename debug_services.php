<?php
// Debug services table structure and data
require_once __DIR__ . '/api/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<h1>Services Table Debug</h1>";
    
    // Check if services table exists
    $checkQuery = "SHOW TABLES LIKE 'services'";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        echo "<h2>✅ Services table exists</h2>";
        
        // Show table structure
        echo "<h3>Table Structure:</h3>";
        $columnsQuery = "SHOW COLUMNS FROM services";
        $columnsStmt = $conn->prepare($columnsQuery);
        $columnsStmt->execute();
        $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show data count
        $countQuery = "SELECT COUNT(*) FROM services";
        $countStmt = $conn->prepare($countQuery);
        $countStmt->execute();
        $count = $countStmt->fetchColumn();
        
        echo "<h3>Total Services: $count</h3>";
        
        // Show sample data
        if ($count > 0) {
            echo "<h3>Sample Data (first 10 records):</h3>";
            $dataQuery = "SELECT * FROM services LIMIT 10";
            $dataStmt = $conn->prepare($dataQuery);
            $dataStmt->execute();
            $services = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($services) {
                echo "<table border='1' cellpadding='5' style='font-size: 12px;'>";
                echo "<tr>";
                foreach (array_keys($services[0]) as $header) {
                    echo "<th>" . htmlspecialchars($header) . "</th>";
                }
                echo "</tr>";
                
                foreach ($services as $service) {
                    echo "<tr>";
                    foreach ($service as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
                
                // Check specifically for price-related columns
                echo "<h3>Price Analysis:</h3>";
                $priceQuery = "SELECT id, name, nhis_tariff, private_price, nhis_covered FROM services LIMIT 10";
                $priceStmt = $conn->prepare($priceQuery);
                $priceStmt->execute();
                $priceData = $priceStmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>Name</th><th>NHIS Tariff</th><th>Private Price</th><th>NHIS Covered</th></tr>";
                foreach ($priceData as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nhis_tariff']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['private_price']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nhis_covered']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        
    } else {
        echo "<h2>❌ Services table does not exist</h2>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error: " . $e->getMessage() . "</h2>";
}
?>