<?php
/**
 * Logout Script
 * 
 * Handles user logout
 */

require_once __DIR__ . '/auth.php';

$auth = Auth::getInstance();
$auth->logout();

// Redirect to login page
header('Location: ../login.php');
exit();
?>