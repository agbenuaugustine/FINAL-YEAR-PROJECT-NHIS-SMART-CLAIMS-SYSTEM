<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    echo '{"status":"ok"}';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '{"status":"error","message":"Method not allowed"}';
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo '{"status":"error","message":"Invalid data"}';
    exit;
}

$required_fields = [
    'hospital_name', 'hospital_code', 'hospital_type', 'hospital_category',
    'region', 'district', 'town_city', 'postal_address',
    'primary_contact_person', 'primary_contact_email', 'primary_contact_phone',
    'admin_username', 'admin_password', 'admin_full_name'
];

foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        echo '{"status":"error","message":"Field ' . $field . ' is required"}';
        exit;
    }
}

try {
    require_once __DIR__ . '/config/database.php';
    

    
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        echo '{"status":"error","message":"Database connection failed"}';
        exit;
    }
    
    $conn->beginTransaction();
    
    // Check duplicates
    $stmt = $conn->prepare("SELECT id FROM hospitals WHERE hospital_code = ?");
    $stmt->execute([$data['hospital_code']]);
    if ($stmt->fetch()) {
        $conn->rollback();
        echo '{"status":"error","message":"Hospital code already exists"}';
        exit;
    }
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$data['admin_username']]);
    if ($stmt->fetch()) {
        $conn->rollback();
        echo '{"status":"error","message":"Username already exists"}';
        exit;
    }
    
    // Insert hospital
    $hospital_sql = "INSERT INTO hospitals (hospital_name, hospital_code, hospital_type, hospital_category, primary_contact_person, primary_contact_email, primary_contact_phone, region, district, town_city, postal_address, nhia_accreditation_number, registration_status, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 1)";
    
    $stmt = $conn->prepare($hospital_sql);
    $stmt->execute([
        $data['hospital_name'], $data['hospital_code'], $data['hospital_type'], $data['hospital_category'],
        $data['primary_contact_person'], $data['primary_contact_email'], $data['primary_contact_phone'],
        $data['region'], $data['district'], $data['town_city'], $data['postal_address'],
        $data['nhia_accreditation_number'] ?? null
    ]);
    
    $hospital_id = $conn->lastInsertId();
    
    // Create admin user
    $admin_sql = "INSERT INTO users (hospital_id, username, password, email, full_name, role, phone, is_active, created_at) VALUES (?, ?, ?, ?, ?, 'hospital_admin', ?, 0, NOW())";
    $hashed_password = password_hash($data['admin_password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare($admin_sql);
    $stmt->execute([
        $hospital_id, $data['admin_username'], $hashed_password,
        $data['primary_contact_email'], $data['admin_full_name'], $data['primary_contact_phone']
    ]);
    
    $conn->commit();
    
    // Try to send simple email notification
    $emailSent = false;
    if (function_exists('mail')) {
        $to = $data['primary_contact_email'];
        $subject = "Hospital Registration - Smart Claims NHIS";
        $message = "Dear " . $data['admin_full_name'] . ",\n\n";
        $message .= "Your hospital registration for " . $data['hospital_name'] . " has been submitted successfully.\n";
        $message .= "Hospital Code: " . $data['hospital_code'] . "\n";
        $message .= "Your application is pending approval.\n\n";
        $message .= "Best regards,\nSmart Claims NHIS Team";
        $headers = "From: noreply@smartclaims.com";
        
        $emailSent = @mail($to, $subject, $message, $headers);
    }
    
    echo '{"status":"success","message":"Hospital registration submitted successfully! Your application is pending approval.","hospital":{"id":' . $hospital_id . ',"name":"' . addslashes($data['hospital_name']) . '","code":"' . addslashes($data['hospital_code']) . '","status":"Pending"},"email_sent":' . ($emailSent ? 'true' : 'false') . '}';
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo '{"status":"error","message":"Registration failed"}';
}
?>