<?php
/**
 * Enhanced Database Setup Script
 * 
 * This script sets up the enhanced Smart Claims database with hospital management,
 * departments, and comprehensive role-based permissions.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '', // Change this if you have a password
    'database' => 'smartclaims'
];

echo "<h1>Smart Claims Enhanced Database Setup</h1>\n";
echo "<p>Setting up the enhanced database with hospital management and departments...</p>\n";

try {
    // First, connect without selecting a database to create it
    $pdo = new PDO(
        "mysql:host={$config['host']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );

    echo "<p>✓ Connected to MySQL server</p>\n";

    // Read and execute the enhanced schema (safe version)
    $schema_file = __DIR__ . '/database/enhanced_schema_safe.sql';
    
    if (!file_exists($schema_file)) {
        throw new Exception("Enhanced schema file not found: {$schema_file}");
    }

    echo "<p>✓ Found enhanced schema file</p>\n";

    // Read the SQL file
    $sql = file_get_contents($schema_file);
    
    if ($sql === false) {
        throw new Exception("Could not read schema file");
    }

    echo "<p>✓ Read enhanced schema file</p>\n";

    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );

    echo "<p>✓ Parsed " . count($statements) . " SQL statements</p>\n";

    // Execute each statement
    $executed = 0;
    foreach ($statements as $statement) {
        if (trim($statement)) {
            try {
                $pdo->exec($statement);
                $executed++;
            } catch (PDOException $e) {
                // Log the error but continue with other statements
                echo "<p style='color: orange;'>⚠ Warning executing statement: " . $e->getMessage() . "</p>\n";
            }
        }
    }

    echo "<p>✓ Successfully executed {$executed} SQL statements</p>\n";

    // Verify the setup by checking key tables
    $tables_to_check = [
        'hospitals', 'departments', 'users', 'patients', 'visits', 
        'vital_signs', 'diagnoses', 'prescriptions', 'lab_orders', 
        'claims', 'role_permissions', 'audit_logs'
    ];

    echo "<h2>Verifying Database Setup</h2>\n";
    
    // Connect to the specific database now
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    foreach ($tables_to_check as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `{$table}`");
            $result = $stmt->fetch();
            echo "<p>✓ Table '{$table}': {$result['count']} records</p>\n";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error checking table '{$table}': " . $e->getMessage() . "</p>\n";
        }
    }

    // Display sample data
    echo "<h2>Sample Data Verification</h2>\n";

    // Check hospitals
    try {
        $stmt = $pdo->query("SELECT hospital_name, hospital_code, registration_status FROM hospitals LIMIT 3");
        $hospitals = $stmt->fetchAll();
        
        echo "<h3>Sample Hospitals:</h3>\n";
        foreach ($hospitals as $hospital) {
            echo "<p>• {$hospital['hospital_name']} ({$hospital['hospital_code']}) - {$hospital['registration_status']}</p>\n";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error fetching hospitals: " . $e->getMessage() . "</p>\n";
    }

    // Check departments
    try {
        $stmt = $pdo->query("
            SELECT d.department_name, d.department_code, h.hospital_name 
            FROM departments d 
            JOIN hospitals h ON d.hospital_id = h.id 
            LIMIT 5
        ");
        $departments = $stmt->fetchAll();
        
        echo "<h3>Sample Departments:</h3>\n";
        foreach ($departments as $dept) {
            echo "<p>• {$dept['department_name']} ({$dept['department_code']}) at {$dept['hospital_name']}</p>\n";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error fetching departments: " . $e->getMessage() . "</p>\n";
    }

    // Check users
    try {
        $stmt = $pdo->query("SELECT username, full_name, role FROM users");
        $users = $stmt->fetchAll();
        
        echo "<h3>System Users:</h3>\n";
        foreach ($users as $user) {
            echo "<p>• {$user['full_name']} ({$user['username']}) - {$user['role']}</p>\n";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error fetching users: " . $e->getMessage() . "</p>\n";
    }

    // Check role permissions
    try {
        $stmt = $pdo->query("
            SELECT role, COUNT(*) as permission_count 
            FROM role_permissions 
            GROUP BY role
        ");
        $role_perms = $stmt->fetchAll();
        
        echo "<h3>Role Permissions:</h3>\n";
        foreach ($role_perms as $perm) {
            echo "<p>• {$perm['role']}: {$perm['permission_count']} permissions</p>\n";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error fetching role permissions: " . $e->getMessage() . "</p>\n";
    }

    echo "<h2>Setup Complete!</h2>\n";
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h3>✓ Enhanced Smart Claims Database Setup Successful!</h3>\n";
    echo "<p><strong>Default Login Credentials:</strong></p>\n";
    echo "<ul>\n";
    echo "<li><strong>Superadmin:</strong> Username: <code>superadmin</code>, Password: <code>admin123</code></li>\n";
    echo "<li><strong>Hospital Admins:</strong> Username: <code>kbth_admin</code>, <code>kath_admin</code>, <code>ridge_admin</code>, Password: <code>admin123</code></li>\n";
    echo "</ul>\n";
    echo "<p><strong>Features Added:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>Hospital registration and management system</li>\n";
    echo "<li>Department-based organization</li>\n";
    echo "<li>Comprehensive role-based permissions</li>\n";
    echo "<li>Enhanced patient and visit tracking</li>\n";
    echo "<li>Improved claims processing workflow</li>\n";
    echo "<li>Audit logging and compliance features</li>\n";
    echo "<li>Department-specific dashboards</li>\n";
    echo "</ul>\n";
    echo "<p><strong>Available Departments:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>OPD (Outpatient Department)</li>\n";
    echo "<li>Laboratory</li>\n";
    echo "<li>Pharmacy</li>\n";
    echo "<li>Claims Processing</li>\n";
    echo "<li>Finance</li>\n";
    echo "<li>Records Management</li>\n";
    echo "<li>Radiology</li>\n";
    echo "<li>Emergency</li>\n";
    echo "</ul>\n";
    echo "</div>\n";

    echo "<p><a href='/smartclaimsCL/index.php' style='display: inline-block; background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>\n";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h3>✗ Setup Failed!</h3>\n";
    echo "<p>Error: " . $e->getMessage() . "</p>\n";
    echo "</div>\n";
    
    // Additional debugging information
    echo "<h3>Debugging Information:</h3>\n";
    echo "<p>PHP Version: " . PHP_VERSION . "</p>\n";
    echo "<p>MySQL Extension: " . (extension_loaded('pdo_mysql') ? 'Available' : 'Not Available') . "</p>\n";
    echo "<p>Current Directory: " . __DIR__ . "</p>\n";
    echo "<p>Schema File Path: " . $schema_file . "</p>\n";
    echo "<p>Schema File Exists: " . (file_exists($schema_file) ? 'Yes' : 'No') . "</p>\n";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Smart Claims Enhanced Database Setup</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px; 
            line-height: 1.6;
        }
        h1, h2, h3 { color: #333; }
        code { 
            background: #f4f4f4; 
            padding: 2px 5px; 
            border-radius: 3px; 
            font-family: 'Courier New', monospace;
        }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
</body>
</html>