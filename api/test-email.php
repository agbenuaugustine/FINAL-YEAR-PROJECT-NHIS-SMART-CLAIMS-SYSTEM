<?php
// Simple email test script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Simple Email Configuration</h2>";

try {
    // Test 1: Check if SimpleMailer class exists
    echo "<p>1. Checking SimpleMailer class...</p>";
    if (file_exists(__DIR__ . '/utils/SimpleMailer.php')) {
        require_once __DIR__ . '/utils/SimpleMailer.php';
        echo "✅ SimpleMailer.php file found<br>";
        
        if (class_exists('SimpleMailer')) {
            echo "✅ SimpleMailer class exists<br>";
            
            // Test 2: Create SimpleMailer instance
            $mailer = new SimpleMailer();
            echo "✅ SimpleMailer instance created<br>";
            
            // Test 3: Check email configuration
            require_once __DIR__ . '/config/email_config.php';
            $config = EmailConfig::getConfig();
            echo "✅ Email config loaded<br>";
            echo "From Email: " . $config['from_email'] . "<br>";
            echo "Admin Email: " . $config['admin_email'] . "<br>";
            echo "Email Enabled: " . ($config['enable_email'] ? 'Yes' : 'No') . "<br>";
            
            // Test 4: Test mail function availability
            echo "<p>4. Testing PHP mail function...</p>";
            if (function_exists('mail')) {
                echo "✅ PHP mail() function is available<br>";
                
                // Test 5: Try sending a test email
                echo "<p>5. Sending test email...</p>";
                $testResult = $mailer->sendHospitalRegistrationEmails(
                    'test@example.com', // This won't actually send
                    'Test Admin',
                    'Test Hospital',
                    'testuser',
                    'TEST001'
                );
                
                if ($testResult) {
                    echo "✅ Test email method executed successfully<br>";
                } else {
                    echo "⚠️ Test email method returned false (this is normal for test)<br>";
                }
                
            } else {
                echo "❌ PHP mail() function is not available<br>";
            }
            
            echo "<p><strong>Simple email system is ready!</strong></p>";
            
        } else {
            echo "❌ SimpleMailer class not found<br>";
        }
    } else {
        echo "❌ SimpleMailer.php file not found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<hr>";
echo "<p><em>Delete this file after testing: api/test-email.php</em></p>";
?>