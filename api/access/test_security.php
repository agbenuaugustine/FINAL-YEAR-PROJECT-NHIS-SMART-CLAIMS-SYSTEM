<?php
/**
 * Security Test Script
 * This script tests the security implementation
 * Run this to verify everything is working correctly
 */

echo "=== Security System Test ===\n\n";

// Test 1: Check if secure_auth.php exists
echo "1. Testing secure_auth.php file...\n";
if (file_exists(__DIR__ . '/secure_auth.php')) {
    echo "   ✓ secure_auth.php exists\n";
} else {
    echo "   ✗ secure_auth.php not found\n";
    exit(1);
}

// Test 2: Check if session is started
echo "\n2. Testing session management...\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "   ✓ Session started\n";

// Test 3: Simulate unauthenticated access
echo "\n3. Testing authentication check...\n";
// Clear any existing session
session_unset();
session_destroy();

// Try to include secure_auth.php (should redirect)
ob_start();
include __DIR__ . '/secure_auth.php';
$output = ob_get_clean();

if (strpos($output, 'Location:') !== false) {
    echo "   ✓ Authentication check working (redirect detected)\n";
} else {
    echo "   ✗ Authentication check not working\n";
}

// Test 4: Check protected files
echo "\n4. Testing protected files...\n";
$protected_files = [
    'dashboard.php',
    'patient-registration.php',
    'visits.php',
    'claims.php'
];

foreach ($protected_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $content = file_get_contents(__DIR__ . '/' . $file);
        if (strpos($content, 'secure_auth.php') !== false) {
            echo "   ✓ $file is protected\n";
        } else {
            echo "   ✗ $file is NOT protected\n";
        }
    } else {
        echo "   - $file not found\n";
    }
}

// Test 5: Check .htaccess file
echo "\n5. Testing .htaccess security...\n";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "   ✓ .htaccess file exists\n";
} else {
    echo "   ✗ .htaccess file not found\n";
}

echo "\n=== Security Test Complete ===\n";
echo "If all tests passed, your security system is working correctly!\n";
?> 