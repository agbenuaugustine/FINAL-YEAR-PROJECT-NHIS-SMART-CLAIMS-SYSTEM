<?php
require_once 'api/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "Database connection: SUCCESS\n";
    
    // Create visits table if it doesn't exist
    $createVisitsTable = "
    CREATE TABLE IF NOT EXISTS visits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        visit_number VARCHAR(20) UNIQUE,
        visit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        visit_type ENUM('OPD', 'Emergency', 'Follow-up', 'Referral', 'Admission') NOT NULL,
        priority ENUM('Routine', 'Urgent', 'Emergency') DEFAULT 'Routine',
        chief_complaint TEXT,
        history_present_illness TEXT,
        past_medical_history TEXT,
        family_history TEXT,
        social_history TEXT,
        review_of_systems TEXT,
        physical_examination TEXT,
        assessment_plan TEXT,
        status ENUM('Waiting', 'In Progress', 'Completed', 'Cancelled', 'No Show') DEFAULT 'Waiting',
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($createVisitsTable);
    echo "visits table: CREATED/EXISTS\n";
    
    // Create service_orders table if it doesn't exist
    $createServiceOrdersTable = "
    CREATE TABLE IF NOT EXISTS service_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visit_id INT NOT NULL,
        service_id INT NOT NULL,
        status ENUM('Ordered', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Ordered',
        notes TEXT,
        ordered_by INT NOT NULL,
        performed_by INT,
        ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL
    )";
    
    $conn->exec($createServiceOrdersTable);
    echo "service_orders table: CREATED/EXISTS\n";
    
    // Create services table if it doesn't exist
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
        requires_approval BOOLEAN DEFAULT FALSE,
        estimated_duration INT,
        department VARCHAR(50),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($createServicesTable);
    echo "services table: CREATED/EXISTS\n";
    
    // Create patients table if it doesn't exist
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
        marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed'),
        occupation VARCHAR(100),
        phone_primary VARCHAR(15),
        phone_secondary VARCHAR(15),
        email VARCHAR(100),
        emergency_contact VARCHAR(15),
        region VARCHAR(50),
        district VARCHAR(50),
        town_city VARCHAR(50),
        postal_address VARCHAR(100),
        residential_address TEXT,
        landmark VARCHAR(100),
        blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'),
        allergies TEXT,
        chronic_conditions TEXT,
        emergency_contact_name VARCHAR(100),
        emergency_contact_relationship ENUM('Spouse', 'Parent', 'Child', 'Sibling', 'Friend', 'Other'),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($createPatientsTable);
    echo "patients table: CREATED/EXISTS\n";
    
    // Insert sample services if table is empty
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
        
        echo "Sample services: INSERTED\n";
    }
    
    // Insert sample patient if table is empty
    $patientCount = $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    if ($patientCount == 0) {
        $insertPatient = $conn->prepare("
            INSERT INTO patients (nhis_number, first_name, last_name, date_of_birth, gender, phone_primary, policy_status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $insertPatient->execute(['NHIS123456789', 'John', 'Doe', '1990-01-01', 'Male', '0244123456', 'Active']);
        echo "Sample patient: INSERTED\n";
    }
    
    echo "\nDatabase setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>