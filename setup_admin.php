<?php
/**
 * Setup Admin User
 * 
 * Create a default hospital admin user for testing
 * Run this once to create the admin user
 */

require_once __DIR__ . '/api/config/database.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die('Database connection failed');
}

// Check if admin user already exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE username = 'admin' OR role = 'hospital_admin' LIMIT 1");
$check_stmt->execute();

if ($check_stmt->fetch()) {
    echo "Admin user already exists!<br>";
    echo "You can log in with existing credentials.<br>";
} else {
    // Create admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    $insert_stmt = $conn->prepare("
        INSERT INTO users (username, email, full_name, password, role, hospital_id, is_active, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
    ");
    
    $success = $insert_stmt->execute([
        'admin',
        'admin@hospital.com',
        'Hospital Administrator',
        $admin_password,
        'hospital_admin',
        1
    ]);
    
    if ($success) {
        echo "✅ Admin user created successfully!<br><br>";
        echo "<strong>Login Credentials:</strong><br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br><br>";
        echo "You can now login and create other users.<br>";
    } else {
        echo "❌ Error creating admin user<br>";
    }
}

echo "<br><a href='index.php'>Go to Login Page</a>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Setup Admin - Smart Claims NHIS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h2>Smart Claims NHIS - Admin Setup</h2>
    <p>This page creates the initial hospital admin user.</p>
</body>
</html>