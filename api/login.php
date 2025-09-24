<?php
ini_set('display_errors', 0);
error_reporting(0);
ob_start();
ob_clean();

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_clean();
    echo json_encode(['status' => 'ok']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    require_once __DIR__ . '/config/database.php';
    
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['username']) || !isset($data['password'])) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Username and password required']);
        exit;
    }
    
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }
    
    // Find user
    $stmt = $conn->prepare("SELECT id, username, password, email, full_name, role, hospital_id, department_id, is_active FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($data['password'], $user['password'])) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        exit;
    }
    
    if (!$user['is_active']) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Account is inactive']);
        exit;
    }
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Set session data in the format expected by secure_auth.php
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'full_name' => $user['full_name'],
        'role' => $user['role'],
        'hospital_id' => $user['hospital_id'],
        'department_id' => $user['department_id'],
        'is_active' => $user['is_active']
    ];
    
    // Also set individual session variables for compatibility
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['hospital_id'] = $user['hospital_id'];
    $_SESSION['department_id'] = $user['department_id'];
    $_SESSION['last_activity'] = time();
    
    // Generate a simple session token for frontend
    $token = 'session_' . bin2hex(random_bytes(16));
    $_SESSION['token'] = $token;
    
    ob_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
            'hospital_id' => $user['hospital_id'],
            'department_id' => $user['department_id'],
            'is_active' => $user['is_active']
        ]
    ]);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
?>