<?php
// Emergency database fix script
require_once 'api/config/database.php';

echo "<h2>Emergency Database Fix</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Create all required tables
    echo "<h3>Creating Required Tables...</h3>";
    
    // 1. Users table (for user management)
    $createUsersTable = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'doctor', 'nurse', 'pharmacist', 'lab_technician', 'claims_officer', 'receptionist') NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($createUsersTable);
    echo "<p>✅ Users table created/exists</p>";
    
    // 2. Patients table
    $createPatientsTable = "
    CREATE TABLE IF NOT EXISTS patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nhis_number VARCHAR(20) UNIQUE,
        nhis_expiry DATE,
        membership_type ENUM('SSNIT_Contributor', 'SSNIT_Pensioner', 'Informal_Sector', 'Indigent', 'Under_18', '70_Above', 'Pregnant_Women'),
        policy_status ENUM('Active', 'Expired', 'Suspended', 'Pending') DEFAULT 'Pending',
        title ENUM('Mr', 'Mrs', 'Miss', 'Dr', 'Prof', 'Rev'),
        first_name VARCHAR(50) NOT NULL,
        middle_name VARCHAR(50),
        last_name VARCHAR(50) NOT NULL,
        date_of_birth DATE NOT NULL,
        gender ENUM('Male', 'Female') NOT NULL,
        phone_primary VARCHAR(15),
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($createPatientsTable);
    echo "<p>✅ Patients table created/exists</p>";
    
    // 3. Services table
    $createServicesTable = "
    CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) NOT NULL UNIQUE,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        category ENUM('OPD', 'Laboratory', 'Pharmacy', 'Imaging', 'Procedure', 'Consultation') NOT NULL,
        subcategory VARCHAR(50),
        nhis_covered BOOLEAN DEFAULT FALSE,
        nhis_tariff DECIMAL(10,2),
        private_price DECIMAL(10,2) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($createServicesTable);
    echo "<p>✅ Services table created/exists</p>";
    
    // 4. Visits table
    $createVisitsTable = "
    CREATE TABLE IF NOT EXISTS visits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        visit_number VARCHAR(20) UNIQUE,
        visit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        visit_type ENUM('OPD', 'Emergency', 'Follow-up', 'Referral', 'Admission') NOT NULL,
        priority ENUM('Routine', 'Urgent', 'Emergency') DEFAULT 'Routine',
        chief_complaint TEXT,
        status ENUM('Waiting', 'In Progress', 'Completed', 'Cancelled', 'No Show') DEFAULT 'Waiting',
        created_by INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($createVisitsTable);
    echo "<p>✅ Visits table created/exists</p>";
    
    // 5. Service Orders table
    $createServiceOrdersTable = "
    CREATE TABLE IF NOT EXISTS service_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visit_id INT NOT NULL,
        service_id INT NOT NULL,
        status ENUM('Ordered', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Ordered',
        notes TEXT,
        ordered_by INT NOT NULL DEFAULT 1,
        ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($createServiceOrdersTable);
    echo "<p>✅ Service Orders table created/exists</p>";
    
    // Insert default admin user if users table is empty
    $userCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount == 0) {
        $conn->exec("INSERT INTO users (username, password, email, full_name, role) VALUES 
                    ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin@smartclaims.com', 'System Administrator', 'admin')");
        echo "<p>✅ Default admin user created</p>";
    }
    
    // Insert sample patient if patients table is empty
    $patientCount = $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    if ($patientCount == 0) {
        $conn->exec("INSERT INTO patients (nhis_number, first_name, last_name, date_of_birth, gender, phone_primary, policy_status) VALUES 
                    ('NHIS123456789', 'John', 'Doe', '1990-01-01', 'Male', '0244123456', 'Active'),
                    ('NHIS987654321', 'Jane', 'Smith', '1985-05-15', 'Female', '0244654321', 'Active')");
        echo "<p>✅ Sample patients created</p>";
    }
    
    // Insert sample services if services table is empty
    $serviceCount = $conn->query("SELECT COUNT(*) FROM services")->fetchColumn();
    if ($serviceCount == 0) {
        $sampleServices = [
            ['OPD001', 'General Consultation', 'General medical consultation', 'OPD', 'Consultation', 1, 25.00, 50.00],
            ['OPD002', 'Specialist Consultation', 'Specialist medical consultation', 'OPD', 'Consultation', 1, 45.00, 80.00],
            ['LAB001', 'Full Blood Count (FBC)', 'Complete blood count test', 'Laboratory', 'Hematology', 1, 35.00, 60.00],
            ['LAB002', 'Malaria Test (RDT)', 'Rapid diagnostic test for malaria', 'Laboratory', 'Parasitology', 1, 15.00, 30.00],
            ['PHARM001', 'Paracetamol 500mg', 'Pain relief medication', 'Pharmacy', 'Analgesics', 1, 5.00, 10.00],
            ['PHARM002', 'Ibuprofen 400mg', 'Anti-inflammatory medication', 'Pharmacy', 'Analgesics', 1, 8.00, 15.00]
        ];
        
        $insertService = $conn->prepare("
            INSERT INTO services (code, name, description, category, subcategory, nhis_covered, nhis_tariff, private_price)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sampleServices as $service) {
            $insertService->execute($service);
        }
        echo "<p>✅ Sample services created</p>";
    }
    
    echo "<hr>";
    echo "<h3>Final Status:</h3>";
    
    // Check final counts
    $userCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $patientCount = $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $serviceCount = $conn->query("SELECT COUNT(*) FROM services")->fetchColumn();
    
    echo "<p>Users: $userCount</p>";
    echo "<p>Patients: $patientCount</p>";
    echo "<p>Services: $serviceCount</p>";
    
    echo "<h3>✅ Database setup completed successfully!</h3>";
    echo "<p><strong>You can now try submitting a requisition again.</strong></p>";
    
    // Test a sample submission
    echo "<hr><h3>Testing Sample Submission:</h3>";
    
    try {
        // Get first patient and service for testing
        $patient = $conn->query("SELECT * FROM patients LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $service = $conn->query("SELECT * FROM services LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        
        if ($patient && $service) {
            echo "<p>Testing with Patient: {$patient['first_name']} {$patient['last_name']} (ID: {$patient['id']})</p>";
            echo "<p>Testing with Service: {$service['name']} (ID: {$service['id']})</p>";
            
            // Create a test visit
            $visitQuery = "INSERT INTO visits (patient_id, visit_type, priority, chief_complaint, status, created_by, visit_date) VALUES (?, ?, ?, ?, 'Waiting', ?, ?)";
            $visitStmt = $conn->prepare($visitQuery);
            
            $patientId = $patient['id'];
            $visitType = 'OPD';
            $priority = 'Routine';
            $chiefComplaint = 'Test submission';
            $createdBy = 1;
            $visitDate = date('Y-m-d H:i:s');
            
            $visitStmt->bindParam(1, $patientId);
            $visitStmt->bindParam(2, $visitType);
            $visitStmt->bindParam(3, $priority);
            $visitStmt->bindParam(4, $chiefComplaint);
            $visitStmt->bindParam(5, $createdBy);
            $visitStmt->bindParam(6, $visitDate);
            
            if ($visitStmt->execute()) {
                $visitId = $conn->lastInsertId();
                echo "<p>✅ Test visit created successfully (ID: $visitId)</p>";
                
                // Create test service order
                $serviceOrderQuery = "INSERT INTO service_orders (visit_id, service_id, notes, ordered_by, ordered_at) VALUES (?, ?, ?, ?, NOW())";
                $serviceOrderStmt = $conn->prepare($serviceOrderQuery);
                
                $serviceId = $service['id'];
                $notes = 'Test service order';
                $orderedBy = 1;
                
                $serviceOrderStmt->bindParam(1, $visitId);
                $serviceOrderStmt->bindParam(2, $serviceId);
                $serviceOrderStmt->bindParam(3, $notes);
                $serviceOrderStmt->bindParam(4, $orderedBy);
                
                if ($serviceOrderStmt->execute()) {
                    echo "<p>✅ Test service order created successfully</p>";
                    echo "<p><strong>🎉 Submission mechanism is working correctly!</strong></p>";
                } else {
                    echo "<p>❌ Failed to create test service order</p>";
                }
            } else {
                echo "<p>❌ Failed to create test visit</p>";
            }
        } else {
            echo "<p>❌ No test data available</p>";
        }
        
    } catch (Exception $testError) {
        echo "<p style='color: red;'>❌ Test submission failed: " . $testError->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>