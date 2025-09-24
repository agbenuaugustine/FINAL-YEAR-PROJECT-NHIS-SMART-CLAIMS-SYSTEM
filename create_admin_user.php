<?php
/**
 * Create Admin User Script
 * 
 * This script creates an admin user in the database
 */

// Include required files
require_once 'api/config/database.php';
require_once 'api/models/User.php';

// Create database connection
$database = new Database();
$conn = $database->getConnection();

// Create user model
$user = new User($conn);

// Set user properties
$user->username = 'admin';
$user->password = 'admin123'; // This will be hashed in the create method
$user->email = 'admin@smartclaims.com';
$user->full_name = 'Admin User';
$user->role = 'admin';
$user->is_active = true;

// Check if user already exists
$temp_user = new User($conn);
if ($temp_user->findByUsername($user->username)) {
    echo "User already exists!";
    exit;
}

// Create the user
if ($user->create()) {
    echo "Admin user created successfully!";
} else {
    echo "Unable to create user.";
}
?>