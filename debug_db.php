<?php
require_once 'api/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo 'Database connection: SUCCESS<br>';
    
    // Check if visits table exists
    $result = $conn->query('SHOW TABLES LIKE "visits"');
    if ($result->rowCount() > 0) {
        echo 'visits table: EXISTS<br>';
        
        // Check visits table structure
        $structure = $conn->query('DESCRIBE visits');
        echo 'visits table columns:<br>';
        while ($row = $structure->fetch(PDO::FETCH_ASSOC)) {
            echo '  - ' . $row['Field'] . ' (' . $row['Type'] . ')<br>';
        }
    } else {
        echo 'visits table: NOT EXISTS<br>';
    }
    
    // Check if service_orders table exists
    $result = $conn->query('SHOW TABLES LIKE "service_orders"');
    if ($result->rowCount() > 0) {
        echo 'service_orders table: EXISTS<br>';
    } else {
        echo 'service_orders table: NOT EXISTS<br>';
    }
    
    // Check if services table exists
    $result = $conn->query('SHOW TABLES LIKE "services"');
    if ($result->rowCount() > 0) {
        echo 'services table: EXISTS<br>';
    } else {
        echo 'services table: NOT EXISTS<br>';
    }
    
    // Check if patients table exists
    $result = $conn->query('SHOW TABLES LIKE "patients"');
    if ($result->rowCount() > 0) {
        echo 'patients table: EXISTS<br>';
    } else {
        echo 'patients table: NOT EXISTS<br>';
    }
    
    // Show all tables
    echo '<br>All tables in database:<br>';
    $result = $conn->query('SHOW TABLES');
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        echo '  - ' . $row[0] . '<br>';
    }
    
} catch (Exception $e) {
    echo 'Database error: ' . $e->getMessage() . '<br>';
}
?>