<?php
/**
 * Test Enhanced Login System
 * 
 * This file tests the enhanced login functionality with the new database schema
 */

echo "<h2>Testing Enhanced Login System</h2>";

// Include database connection
require_once __DIR__ . '/api/config/database.php';

try {
    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<h3>Database Connection: ✅ Success</h3>";
    
    // Test 1: Check if tables exist
    echo "<h3>Checking Database Tables:</h3>";
    
    $tables = ['hospitals', 'departments', 'users', 'role_permissions'];
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            echo "✅ {$table} table exists<br>";
        } else {
            echo "❌ {$table} table missing<br>";
        }
    }
    
    // Test 2: Check for sample data
    echo "<h3>Sample Data Check:</h3>";
    
    // Check hospitals
    $stmt = $conn->query("SELECT COUNT(*) as count FROM hospitals");
    $count = $stmt->fetch()['count'];
    echo "Hospitals: {$count} records<br>";
    
    // Check departments
    $stmt = $conn->query("SELECT COUNT(*) as count FROM departments");
    $count = $stmt->fetch()['count'];
    echo "Departments: {$count} records<br>";
    
    // Check users
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch()['count'];
    echo "Users: {$count} records<br>";
    
    // Test 3: List sample users with their roles
    echo "<h3>Sample Users:</h3>";
    $stmt = $conn->query("
        SELECT u.username, u.role, u.full_name, u.is_active, 
               h.hospital_name, d.department_name
        FROM users u 
        LEFT JOIN hospitals h ON u.hospital_id = h.id 
        LEFT JOIN departments d ON u.department_id = d.id
        LIMIT 10
    ");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Username</th><th>Full Name</th><th>Role</th><th>Hospital</th><th>Department</th><th>Active</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activeStatus = $row['is_active'] ? '✅' : '❌';
        echo "<tr>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['full_name']}</td>";
        echo "<td>{$row['role']}</td>";
        echo "<td>" . ($row['hospital_name'] ?: 'N/A') . "</td>";
        echo "<td>" . ($row['department_name'] ?: 'N/A') . "</td>";
        echo "<td>{$activeStatus}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test 4: Test login API directly
    echo "<h3>API Test (Login Endpoint):</h3>";
    echo "<p>You can test the login API with these credentials:</p>";
    echo "<ul>";
    echo "<li><strong>Superadmin:</strong> Username: superadmin, Password: admin123</li>";
    echo "<li><strong>Hospital Admin:</strong> Username: kbth_admin, Password: admin123</li>";
    echo "<li><strong>Doctor:</strong> Username: kbth_doctor1, Password: admin123</li>";
    echo "</ul>";
    
    echo "<h3>Test Login Form:</h3>";
    echo "<form method='post' action='test_enhanced_login.php'>";
    echo "Username: <input type='text' name='test_username' value='superadmin'><br><br>";
    echo "Password: <input type='password' name='test_password' value='admin123'><br><br>";
    echo "<input type='submit' value='Test Login'>";
    echo "</form>";
    
    // Process test login if form submitted
    if ($_POST['test_username'] && $_POST['test_password']) {
        echo "<h3>Login Test Result:</h3>";
        
        // Include auth controller
        require_once __DIR__ . '/api/controllers/AuthController.php';
        
        // Create auth controller
        $auth = new AuthController();
        
        // Mock the request data
        $_POST_backup = $_POST;
        $mock_data = json_encode([
            'username' => $_POST['test_username'],
            'password' => $_POST['test_password']
        ]);
        
        // Temporarily replace php://input
        file_put_contents('php://temp', $mock_data);
        
        try {
            // Simulate login
            $result = $auth->login();
            
            echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
            echo "<strong>Login Result:</strong><br>";
            echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div style='background: #ffeeee; padding: 10px; border-radius: 5px; color: red;'>";
            echo "<strong>Error:</strong> " . $e->getMessage();
            echo "</div>";
        }
        
        $_POST = $_POST_backup;
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Database Error:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Quick Actions:</h3>";
echo "<a href='/smartclaimsCL'>← Back to Login Page</a> | ";
echo "<a href='/smartclaimsCL/register.php'>Hospital Registration</a> | ";
echo "<a href='/smartclaimsCL/setup_enhanced_database.php'>Setup Database</a>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
form { margin: 10px 0; }
input[type="text"], input[type="password"] { padding: 5px; margin: 5px 0; width: 200px; }
input[type="submit"] { padding: 8px 15px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; }
</style>