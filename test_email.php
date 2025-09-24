<?php
/**
 * Email Test Page
 * 
 * Test email configuration and sending capabilities
 */

require_once __DIR__ . '/api/config/email_config.php';
require_once __DIR__ . '/api/utils/Mailer.php';

echo "<h2>Email Configuration Test</h2>";

// Test 1: Configuration check
echo "<h3>1. Configuration Check:</h3>";
$config = EmailConfig::getConfig();
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
foreach ($config as $key => $value) {
    if ($key === 'smtp_password') {
        $value = str_repeat('*', strlen($value)); // Hide password
    }
    echo "<tr><td>{$key}</td><td>{$value}</td></tr>";
}
echo "</table>";

// Test 2: SMTP Connection Test
echo "<h3>2. SMTP Connection Test:</h3>";
$connectionTest = EmailConfig::testConnection();
if ($connectionTest['success']) {
    echo "<div style='color: green; background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
    echo "✅ " . $connectionTest['message'];
    echo "</div>";
} else {
    echo "<div style='color: red; background: #ffeeee; padding: 10px; border-radius: 5px;'>";
    echo "❌ " . $connectionTest['message'];
    echo "</div>";
    
    echo "<h4>Common Solutions:</h4>";
    echo "<ul>";
    echo "<li><strong>Gmail:</strong> Use app-specific password instead of regular password</li>";
    echo "<li><strong>Firewall:</strong> Ensure port 587 or 465 is open</li>";
    echo "<li><strong>SSL/TLS:</strong> Try different security settings</li>";
    echo "<li><strong>Host:</strong> Verify SMTP server address is correct</li>";
    echo "</ul>";
}

// Test 3: Send Test Email
echo "<h3>3. Send Test Email:</h3>";

if (isset($_POST['send_test'])) {
    $testEmail = $_POST['test_email'];
    
    if (!empty($testEmail) && filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        try {
            $mailer = new Mailer();
            
            // Create a simple test email
            require_once __DIR__ . '/api/utils/PHPMailer/src/Exception.php';
            require_once __DIR__ . '/api/utils/PHPMailer/src/PHPMailer.php';
            require_once __DIR__ . '/api/utils/PHPMailer/src/SMTP.php';
            
            use PHPMailer\PHPMailer\PHPMailer;
            use PHPMailer\PHPMailer\Exception;
            
            $testMailer = new PHPMailer(true);
            
            // Get config
            $config = EmailConfig::getConfig();
            
            // Server settings
            $testMailer->isSMTP();
            $testMailer->Host = $config['smtp_host'];
            $testMailer->SMTPAuth = true;
            $testMailer->Username = $config['smtp_username'];
            $testMailer->Password = $config['smtp_password'];
            $testMailer->SMTPSecure = $config['smtp_secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $testMailer->Port = $config['smtp_port'];
            
            // SSL options
            $testMailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Enable debug
            $testMailer->SMTPDebug = 2;
            $testMailer->Debugoutput = 'html';
            
            // Recipients
            $testMailer->setFrom($config['from_email'], $config['from_name']);
            $testMailer->addAddress($testEmail);
            
            // Content
            $testMailer->isHTML(true);
            $testMailer->Subject = 'Smart Claims NHIS - Email Test';
            $testMailer->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #1e88e5;'>Email Test Successful!</h2>
                    <p>This is a test email from Smart Claims NHIS system.</p>
                    <p><strong>Test Details:</strong></p>
                    <ul>
                        <li>Sent at: " . date('Y-m-d H:i:s') . "</li>
                        <li>SMTP Host: {$config['smtp_host']}</li>
                        <li>SMTP Port: {$config['smtp_port']}</li>
                        <li>Security: {$config['smtp_secure']}</li>
                    </ul>
                    <p>If you received this email, your SMTP configuration is working correctly!</p>
                </div>
            ";
            
            echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>SMTP Debug Output:</strong><br>";
            ob_start();
            $result = $testMailer->send();
            $debug_output = ob_get_clean();
            echo $debug_output;
            echo "</div>";
            
            if ($result) {
                echo "<div style='color: green; background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
                echo "✅ Test email sent successfully to: {$testEmail}";
                echo "</div>";
            } else {
                echo "<div style='color: red; background: #ffeeee; padding: 10px; border-radius: 5px;'>";
                echo "❌ Failed to send test email";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='color: red; background: #ffeeee; padding: 10px; border-radius: 5px;'>";
            echo "❌ Email test failed: " . $e->getMessage();
            echo "</div>";
        }
    } else {
        echo "<div style='color: red; background: #ffeeee; padding: 10px; border-radius: 5px;'>";
        echo "❌ Please enter a valid email address";
        echo "</div>";
    }
}

?>

<form method="post">
    <label for="test_email">Enter email address to receive test email:</label><br>
    <input type="email" id="test_email" name="test_email" placeholder="your-email@example.com" required style="width: 300px; padding: 5px; margin: 5px 0;"><br>
    <input type="submit" name="send_test" value="Send Test Email" style="padding: 8px 15px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer;">
</form>

<hr>

<h3>4. Email Configuration Help:</h3>

<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">
    <h4>To configure email settings:</h4>
    <ol>
        <li>Edit <code>/api/config/email_config.php</code></li>
        <li>Update the SMTP settings with your email provider details</li>
        <li>Test the configuration using this page</li>
    </ol>
    
    <h4>Common Email Providers:</h4>
    <ul>
        <li><strong>Gmail:</strong> smtp.gmail.com, port 587, TLS</li>
        <li><strong>Outlook:</strong> smtp-mail.outlook.com, port 587, TLS</li>
        <li><strong>Yahoo:</strong> smtp.mail.yahoo.com, port 587, TLS</li>
        <li><strong>cPanel/Shared Hosting:</strong> Usually mail.yourdomain.com</li>
    </ul>
    
    <h4>To temporarily disable email:</h4>
    <p>Set <code>const ENABLE_EMAIL = false;</code> in the email config file.</p>
</div>

<div style="margin-top: 20px;">
    <a href="/smartclaimsCL">← Back to Login</a> | 
    <a href="/smartclaimsCL/register.php">Hospital Registration</a> | 
    <a href="/smartclaimsCL/test_enhanced_login.php">Test Login System</a>
</div>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>