<?php
/**
 * Quick Fix for Login/Registration Issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Quick Fix Applied</h1>";

try {
    // Connect to database
    require_once __DIR__ . '/api/config/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    echo "<p>✅ Database connected successfully</p>";

    // 1. Fix common issues with users table
    
    // Check if admin user exists and is active
    $stmt = $conn->prepare("SELECT id, username, is_active FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        // Create admin user
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        if ($stmt->execute(['admin', $adminPassword, 'admin@smartclaims.com', 'System Administrator', 'superadmin', 1])) {
            echo "<p>✅ Created admin user (username: admin, password: admin123)</p>";
        } else {
            echo "<p>❌ Failed to create admin user</p>";
        }
    } else {
        // Ensure admin is active
        if (!$admin['is_active']) {
            $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
            if ($stmt->execute([$admin['id']])) {
                echo "<p>✅ Activated admin user</p>";
            }
        } else {
            echo "<p>✅ Admin user exists and is active</p>";
        }
    }

    // 2. Fix any NULL is_active values
    $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE is_active IS NULL");
    $affectedRows = $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Fixed " . $stmt->rowCount() . " users with NULL is_active status</p>";
    }

    // 3. Check hospitals table
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM hospitals");
    $stmt->execute();
    $hospitalCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>Hospitals in database: " . $hospitalCount . "</p>";

    // 4. Fix potential JSON issues in API files
    $apiLoginFile = __DIR__ . '/api/login.php';
    if (file_exists($apiLoginFile)) {
        $content = file_get_contents($apiLoginFile);
        
        // Remove BOM if present
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
            file_put_contents($apiLoginFile, $content);
            echo "<p>✅ Removed BOM from login.php</p>";
        }
        
        echo "<p>✅ Login API file checked</p>";
    }

    $apiRegisterFile = __DIR__ . '/api/hospital-register.php';
    if (file_exists($apiRegisterFile)) {
        $content = file_get_contents($apiRegisterFile);
        
        // Remove BOM if present
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
            file_put_contents($apiRegisterFile, $content);
            echo "<p>✅ Removed BOM from hospital-register.php</p>";
        }
        
        echo "<p>✅ Registration API file checked</p>";
    }

    echo "<hr>";
    echo "<h2>✅ Quick Fix Complete!</h2>";
    echo "<p><strong>Test credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <code>admin</code></li>";
    echo "<li>Password: <code>admin123</code></li>";
    echo "</ul>";
    
    echo "<p><a href='/smartclaimsCL/' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
code { background: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
</style>