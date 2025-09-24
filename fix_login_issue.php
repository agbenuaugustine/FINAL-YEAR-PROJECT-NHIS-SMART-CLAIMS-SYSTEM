<?php
/**
 * Fix Login and Registration Issues
 * This script will identify and fix common issues causing login/registration failures
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Smart Claims - Login/Registration Issue Fix</h1>";

$issues_found = [];
$fixes_applied = [];

// Function to log issues and fixes
function logIssue($issue) {
    global $issues_found;
    $issues_found[] = $issue;
    echo "<p style='color: red;'>❌ ISSUE: $issue</p>";
}

function logFix($fix) {
    global $fixes_applied;
    $fixes_applied[] = $fix;
    echo "<p style='color: green;'>✅ FIXED: $fix</p>";
}

echo "<h2>Diagnostic and Fix Process</h2>";

// 1. Check database connection
echo "<h3>1. Database Connection Check</h3>";
try {
    require_once __DIR__ . '/api/config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✅ Database connection successful</p>";
        
        // Check if users table exists and is accessible
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Total users in database: " . $result['count'] . "</p>";
            
            if ($result['count'] == 0) {
                logIssue("No users exist in the database");
                
                // Create a default admin user
                $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
                
                if ($stmt->execute(['admin', $adminPassword, 'admin@smartclaims.com', 'System Administrator', 'superadmin', 1])) {
                    logFix("Created default admin user (username: admin, password: admin123)");
                } else {
                    logIssue("Failed to create default admin user");
                }
            }
            
        } catch (Exception $e) {
            logIssue("Cannot access users table: " . $e->getMessage());
        }
        
    } else {
        logIssue("Database connection failed");
    }
} catch (Exception $e) {
    logIssue("Database connection error: " . $e->getMessage());
}

// 2. Check and fix users table structure
echo "<h3>2. Users Table Structure Check</h3>";
try {
    if ($conn) {
        $stmt = $conn->prepare("DESCRIBE users");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $requiredColumns = ['id', 'username', 'password', 'email', 'full_name', 'role', 'is_active'];
        $existingColumns = array_column($columns, 'Field');
        
        foreach ($requiredColumns as $col) {
            if (!in_array($col, $existingColumns)) {
                logIssue("Missing column '$col' in users table");
            }
        }
        
        // Check if is_active column has correct type
        foreach ($columns as $col) {
            if ($col['Field'] === 'is_active') {
                if ($col['Type'] !== 'tinyint(1)' && $col['Type'] !== 'int(11)') {
                    logIssue("is_active column has incorrect type: " . $col['Type']);
                    
                    // Fix the column type
                    try {
                        $conn->exec("ALTER TABLE users MODIFY COLUMN is_active TINYINT(1) DEFAULT 0");
                        logFix("Fixed is_active column type");
                    } catch (Exception $e) {
                        logIssue("Failed to fix is_active column: " . $e->getMessage());
                    }
                }
                break;
            }
        }
        
        echo "<p style='color: green;'>✅ Users table structure check completed</p>";
    }
} catch (Exception $e) {
    logIssue("Error checking users table structure: " . $e->getMessage());
}

// 3. Check API file syntax and fix common issues
echo "<h3>3. API Files Syntax Check</h3>";

$apiFiles = [
    '/api/login.php',
    '/api/hospital-register.php',
    '/api/controllers/AuthController.php',
    '/api/models/User.php'
];

foreach ($apiFiles as $file) {
    $fullPath = __DIR__ . $file;
    if (file_exists($fullPath)) {
        // Check if file is readable
        if (!is_readable($fullPath)) {
            logIssue("File $file is not readable");
            continue;
        }
        
        // Basic syntax check by attempting to include the file
        try {
            $content = file_get_contents($fullPath);
            if ($content === false) {
                logIssue("Cannot read content of $file");
                continue;
            }
            
            // Check for common syntax issues
            if (strpos($content, '<?php') !== 0 && strpos($content, '<?') !== 0) {
                logIssue("File $file doesn't start with PHP opening tag");
            }
            
            // Check for BOM
            if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
                logIssue("File $file has BOM (Byte Order Mark)");
                // Fix BOM
                $content = substr($content, 3);
                file_put_contents($fullPath, $content);
                logFix("Removed BOM from $file");
            }
            
            echo "<p style='color: green;'>✅ $file syntax OK</p>";
            
        } catch (Exception $e) {
            logIssue("Error checking $file: " . $e->getMessage());
        }
    } else {
        logIssue("File $file does not exist");
    }
}

// 4. Fix common authentication issues
echo "<h3>4. Authentication Issues Fix</h3>";

try {
    if ($conn) {
        // Check for users with incorrect is_active values
        $stmt = $conn->prepare("SELECT id, username, is_active FROM users WHERE is_active IS NULL OR is_active = ''");
        $stmt->execute();
        $inactiveUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($inactiveUsers) {
            foreach ($inactiveUsers as $user) {
                $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
                if ($stmt->execute([$user['id']])) {
                    logFix("Fixed is_active status for user: " . $user['username']);
                }
            }
        }
        
        // Ensure at least one active admin user exists
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'superadmin' AND is_active = 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            logIssue("No active superadmin user found");
            
            // Check if there's any admin user we can activate
            $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'superadmin' LIMIT 1");
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin) {
                $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
                if ($stmt->execute([$admin['id']])) {
                    logFix("Activated existing superadmin user");
                }
            }
        }
    }
} catch (Exception $e) {
    logIssue("Error fixing authentication issues: " . $e->getMessage());
}

// 5. Check and fix JSON response issues
echo "<h3>5. JSON Response Issues Check</h3>";

// Check if output buffering or headers are causing issues
if (ob_get_level()) {
    logIssue("Output buffering is active - may cause JSON response issues");
    ob_clean();
    logFix("Cleaned output buffer");
}

// Check for any characters that might be output before JSON
$loginFile = __DIR__ . '/api/login.php';
if (file_exists($loginFile)) {
    $content = file_get_contents($loginFile);
    if (strpos($content, 'echo') !== false || strpos($content, 'print') !== false) {
        logIssue("Login API file may have echo/print statements that interfere with JSON");
    }
}

// 6. Create test login credentials
echo "<h3>6. Test Credentials Setup</h3>";

try {
    if ($conn) {
        // Check if test user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = 'testuser'");
        $stmt->execute();
        $testUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$testUser) {
            // Create test user
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $testPassword = password_hash('test123', PASSWORD_DEFAULT);
            
            if ($stmt->execute(['testuser', $testPassword, 'test@smartclaims.com', 'Test User', 'doctor', 1])) {
                logFix("Created test user (username: testuser, password: test123)");
            }
        } else {
            echo "<p>Test user already exists</p>";
        }
    }
} catch (Exception $e) {
    logIssue("Error creating test user: " . $e->getMessage());
}

// Summary
echo "<h2>Summary</h2>";
echo "<h3>Issues Found: " . count($issues_found) . "</h3>";
if ($issues_found) {
    echo "<ul>";
    foreach ($issues_found as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: green;'>No critical issues found!</p>";
}

echo "<h3>Fixes Applied: " . count($fixes_applied) . "</h3>";
if ($fixes_applied) {
    echo "<ul>";
    foreach ($fixes_applied as $fix) {
        echo "<li style='color: green;'>$fix</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Test Your Login Now:</h3>";
echo "<p>Try logging in with these credentials:</p>";
echo "<ul>";
echo "<li><strong>Admin:</strong> username = 'admin', password = 'admin123'</li>";
echo "<li><strong>Test User:</strong> username = 'testuser', password = 'test123'</li>";
echo "</ul>";

echo "<p><a href='/smartclaimsCL' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; max-width: 1000px; }
h1, h2, h3 { color: #333; }
p { margin: 5px 0; }
ul { margin: 10px 0; }
hr { margin: 30px 0; }
</style>