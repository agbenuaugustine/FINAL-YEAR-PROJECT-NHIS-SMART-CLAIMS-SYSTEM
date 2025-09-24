<?php
// Simple test file to check if PHP is working and the path is correct
echo "Test file is working!<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Register.php exists: " . (file_exists(__DIR__ . '/register.php') ? 'YES' : 'NO') . "<br>";
echo "Register.php is readable: " . (is_readable(__DIR__ . '/register.php') ? 'YES' : 'NO') . "<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br>";

// Check request URI to see what's happening
echo "<br>REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "<br>";

// Check if register.php can be included
if (file_exists(__DIR__ . '/register.php')) {
    echo "<br><strong>Test these links:</strong><br>";
    echo "<a href='register.php' target='_blank'>Link 1: register.php</a><br>";
    echo "<a href='./register.php' target='_blank'>Link 2: ./register.php</a><br>";
    echo "<a href='/register.php' target='_blank'>Link 3: /register.php</a><br>";
    
    // Test direct access
    echo "<br><strong>Direct URL tests:</strong><br>";
    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
    echo "Base URL: " . $baseUrl . "<br>";
    echo "<a href='" . $baseUrl . "/register.php' target='_blank'>Full URL: " . $baseUrl . "/register.php</a><br>";
}
?>