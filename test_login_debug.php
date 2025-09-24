<?php
/**
 * Debug Login Issue
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Login Debug Test</h2>";

// Test 1: Check if API endpoints are accessible
echo "<h3>1. Testing API endpoints accessibility:</h3>";

echo "<h4>Testing login.php:</h4>";
$loginUrl = "http://localhost/smartclaimsCL/api/login.php";
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode(['username' => 'test', 'password' => 'test'])
    ]
]);

try {
    $result = file_get_contents($loginUrl, false, $context);
    if ($result !== false) {
        echo "✅ Login API accessible<br>";
        echo "Response: " . htmlspecialchars($result) . "<br>";
    } else {
        echo "❌ Login API not accessible<br>";
    }
} catch (Exception $e) {
    echo "❌ Error accessing login API: " . $e->getMessage() . "<br>";
}

echo "<h4>Testing hospital-register.php:</h4>";
$registerUrl = "http://localhost/smartclaimsCL/api/hospital-register.php";
$context2 = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode(['test' => 'data'])
    ]
]);

try {
    $result2 = file_get_contents($registerUrl, false, $context2);
    if ($result2 !== false) {
        echo "✅ Register API accessible<br>";
        echo "Response: " . htmlspecialchars($result2) . "<br>";
    } else {
        echo "❌ Register API not accessible<br>";
    }
} catch (Exception $e) {
    echo "❌ Error accessing register API: " . $e->getMessage() . "<br>";
}

// Test 2: Check database connection directly
echo "<h3>2. Database Connection Test:</h3>";
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
        
        // Check users table structure
        $stmt = $conn->prepare("DESCRIBE users");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Users table columns:<br>";
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")<br>";
        }
        
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Check if AuthController works directly
echo "<h3>3. AuthController Direct Test:</h3>";
try {
    // Simulate POST data
    $_POST['test'] = 'true';
    
    require_once __DIR__ . '/api/controllers/AuthController.php';
    $auth = new AuthController();
    
    echo "✅ AuthController instantiated successfully<br>";
    
    // Test with fake login data by simulating php://input
    $testData = json_encode(['username' => 'nonexistent', 'password' => 'test']);
    
    // Can't easily test this without mocking php://input, but we can check if the class loads
    echo "✅ AuthController class loaded without errors<br>";
    
} catch (Exception $e) {
    echo "❌ AuthController error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

// Test 4: Check for any PHP errors in error log
echo "<h3>4. Recent PHP Error Log:</h3>";
$errorLogPath = 'C:\xampp\apache\logs\error.log';
if (file_exists($errorLogPath)) {
    $log = file_get_contents($errorLogPath);
    $lines = explode("\n", $log);
    $recentLines = array_slice($lines, -20); // Last 20 lines
    
    echo "<pre>";
    foreach ($recentLines as $line) {
        if (!empty(trim($line))) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "Error log not found at: $errorLogPath<br>";
}

// Test 5: Check .htaccess files
echo "<h3>5. Check .htaccess Configuration:</h3>";

// Check main .htaccess
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "✅ Main .htaccess exists<br>";
    $htaccess = file_get_contents(__DIR__ . '/.htaccess');
    echo "<pre>" . htmlspecialchars($htaccess) . "</pre>";
} else {
    echo "❌ Main .htaccess not found<br>";
}

// Check API .htaccess
if (file_exists(__DIR__ . '/api/.htaccess')) {
    echo "✅ API .htaccess exists<br>";
    $apiHtaccess = file_get_contents(__DIR__ . '/api/.htaccess');
    echo "<pre>" . htmlspecialchars($apiHtaccess) . "</pre>";
} else {
    echo "❌ API .htaccess not found<br>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3, h4 { color: #333; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>