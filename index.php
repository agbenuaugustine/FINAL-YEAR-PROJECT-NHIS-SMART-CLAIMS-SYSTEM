<?php
/**
 * API Index
 * 
 * Entry point for the API
 */

// Set response headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Return API information
echo json_encode([
    'name' => 'Smart Claims API',
    'version' => '1.0.0',
    'description' => 'API for Smart Claims NHIS Claims Administration System',
    'endpoints' => [
        '/api/login.php' => 'User authentication',
        '/api/register.php' => 'User registration',
        '/api/validate-token.php' => 'JWT token validation',
        '/api/users.php' => 'User management',
        // Add more endpoints as they are created
    ]
]);
?>