<?php
/**
 * User Status Fix Utility
 * 
 * This script checks and fixes the is_active status for all users
 */

// Include required files
require_once __DIR__ . '/../config/database.php';

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Check current user statuses
echo "Checking current user statuses...\n";
$stmt = $conn->prepare("SELECT id, username, is_active FROM users");
$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($users) . " users\n";

foreach ($users as $user) {
    echo "User ID: " . $user['id'] . ", Username: " . $user['username'] . ", is_active: " . $user['is_active'] . " (type: " . gettype($user['is_active']) . ")\n";
}

// Fix user statuses
echo "\nFixing user statuses...\n";
$stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE is_active IS NULL OR is_active = 0");
$stmt->execute();
echo "Updated " . $stmt->rowCount() . " users\n";

// Check updated user statuses
echo "\nChecking updated user statuses...\n";
$stmt = $conn->prepare("SELECT id, username, is_active FROM users");
$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    echo "User ID: " . $user['id'] . ", Username: " . $user['username'] . ", is_active: " . $user['is_active'] . " (type: " . gettype($user['is_active']) . ")\n";
}

echo "\nDone!\n";
?>