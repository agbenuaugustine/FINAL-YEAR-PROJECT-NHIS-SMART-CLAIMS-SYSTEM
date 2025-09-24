<?php
require_once __DIR__ . '/api/config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Create admin user
$stmt = $conn->prepare("INSERT IGNORE INTO users (username, password, email, full_name, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$stmt->execute(['admin', $adminPassword, 'admin@smartclaims.com', 'System Administrator', 'superadmin', 1]);

// Fix any is_active issues
$conn->exec("UPDATE users SET is_active = 1 WHERE is_active IS NULL OR is_active = 0");

echo "Setup complete. Login with admin/admin123";
?>