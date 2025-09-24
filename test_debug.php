<?php
/**
 * Debug Login and Registration Issues
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Login & Registration Debug Test</h2>";

// Test 1: Check database connection
echo "<h3>1. Database Connection Test:</h3>";
try {
    require_once __DIR__ . '/api/config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Database connection successful<br>";
        
        // Check if there are any users
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total users in database: " . $row['count'] . "<br>";
        
        // Check if any active users exist
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Active users in database: " . $row['count'] . "<br>";
        
        // Show some user data
        $stmt = $conn->prepare("SELECT id, username, email, role, is_active FROM users LIMIT 5");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($users) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Is Active</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                echo "<td>" . htmlspecialchars($user['is_active']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 2: Check AuthController instantiation
echo "<h3>2. AuthController Test:</h3>";
try {
    require_once __DIR__ . '/api/controllers/AuthController.php';
    $auth = new AuthController();
    echo "✅ AuthController instantiated successfully<br>";
} catch (Exception $e) {
    echo "❌ AuthController error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

// Test 3: Test User Model
echo "<h3>3. User Model Test:</h3>";
try {
    require_once __DIR__ . '/api/models/User.php';
    $user = new User($conn);
    echo "✅ User model instantiated successfully<br>";
    
    // Try to find a test user
    $user->username = 'admin'; // Common admin username
    if ($user->findByUsername('admin')) {
        echo "✅ Found admin user<br>";
        echo "User ID: " . $user->id . "<br>";
        echo "Is Active: " . ($user->is_active ? 'Yes' : 'No') . "<br>";
    } else {
        echo "❌ Admin user not found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ User model error: " . $e->getMessage() . "<br>";
}

// Test 4: Check if JWT Handler works
echo "<h3>4. JWT Handler Test:</h3>";
try {
    require_once __DIR__ . '/api/utils/JwtHandler.php';
    $jwt = new JwtHandler();
    echo "✅ JWT Handler instantiated successfully<br>";
    
    // Test token generation
    $testToken = $jwt->generateToken(['id' => 1, 'username' => 'test']);
    if ($testToken) {
        echo "✅ Token generation successful<br>";
        echo "Sample token: " . substr($testToken, 0, 50) . "...<br>";
    } else {
        echo "❌ Token generation failed<br>";
    }
    
} catch (Exception $e) {
    echo "❌ JWT Handler error: " . $e->getMessage() . "<br>";
}

// Test 5: Test direct API endpoint access
echo "<h3>5. API Endpoint Response Test:</h3>";

// Create a test request to login API
echo "<h4>Testing Login API with invalid credentials:</h4>";
$testData = json_encode(['username' => 'test_user', 'password' => 'test_pass']);

// Simulate the API call process
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Mock php://input
file_put_contents('php://temp', $testData);

try {
    ob_start();
    
    // Include the login API file to test it
    $oldStdout = fopen('php://stdout', 'w');
    
    // Capture the output
    include __DIR__ . '/api/login.php';
    
    $output = ob_get_clean();
    
    echo "Login API Output:<br>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Login API test error: " . $e->getMessage() . "<br>";
}

// Test 6: Check if required files exist
echo "<h3>6. Required Files Check:</h3>";
$requiredFiles = [
    '/api/config/database.php',
    '/api/controllers/AuthController.php',
    '/api/models/User.php',
    '/api/utils/JwtHandler.php',
    '/api/utils/Mailer.php',
    '/api/login.php',
    '/api/hospital-register.php',
    '/register.php',
    '/index.php'
];

foreach ($requiredFiles as $file) {
    $fullPath = __DIR__ . $file;
    if (file_exists($fullPath)) {
        echo "✅ " . $file . " exists<br>";
    } else {
        echo "❌ " . $file . " missing<br>";
    }
}

// Test 7: Check permissions
echo "<h3>7. File Permissions Check:</h3>";
$apiDir = __DIR__ . '/api';
if (is_readable($apiDir)) {
    echo "✅ API directory is readable<br>";
} else {
    echo "❌ API directory is not readable<br>";
}

if (is_writable(__DIR__)) {
    echo "✅ Root directory is writable<br>";
} else {
    echo "❌ Root directory is not writable<br>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3, h4 { color: #333; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; max-height: 300px; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>