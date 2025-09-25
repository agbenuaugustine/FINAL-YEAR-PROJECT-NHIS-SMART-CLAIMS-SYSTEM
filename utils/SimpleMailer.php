<?php
require_once __DIR__ . '/../config/email_config.php';

class SimpleMailer {
    private $config;
    private $enabled;
    
    public function __construct() {
        $this->config = EmailConfig::getConfig();
        $this->enabled = $this->config['enable_email'];
    }
    
    public function sendHospitalRegistrationEmails($hospitalEmail, $adminName, $hospitalName, $adminUsername, $hospitalCode) {
        if (!$this->enabled) {
            return true; // Return true so registration doesn't fail
        }
        
        // Simple PHP mail function approach
        $subject = "Hospital Registration - Smart Claims NHIS System";
        $message = $this->getHospitalEmailMessage($adminName, $hospitalName, $adminUsername, $hospitalCode);
        $headers = "From: " . $this->config['from_email'] . "\r\n";
        $headers .= "Reply-To: " . $this->config['from_email'] . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        try {
            // Send to hospital
            $result1 = mail($hospitalEmail, $subject, $message, $headers);
            
            // Send to admin
            $adminSubject = "New Hospital Registration - Smart Claims NHIS";
            $adminMessage = $this->getAdminEmailMessage($hospitalName, $hospitalEmail, $adminUsername, $hospitalCode);
            $result2 = mail($this->config['admin_email'], $adminSubject, $adminMessage, $headers);
            
            return $result1 && $result2;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function getHospitalEmailMessage($adminName, $hospitalName, $adminUsername, $hospitalCode) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2 style='color: #1e88e5;'>Hospital Registration Confirmation</h2>
            <p>Dear {$adminName},</p>
            <p>Thank you for registering <strong>{$hospitalName}</strong> with Smart Claims NHIS.</p>
            <p><strong>Registration Details:</strong></p>
            <ul>
                <li>Hospital Name: {$hospitalName}</li>
                <li>Hospital Code: {$hospitalCode}</li>
                <li>Admin Username: {$adminUsername}</li>
                <li>Registration Date: " . date('Y-m-d H:i:s') . "</li>
            </ul>
            <p>Your registration is pending approval. We will notify you once approved.</p>
            <p>Best regards,<br>Smart Claims NHIS Team</p>
        </body>
        </html>
        ";
    }
    
    private function getAdminEmailMessage($hospitalName, $hospitalEmail, $adminUsername, $hospitalCode) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2 style='color: #1e88e5;'>New Hospital Registration</h2>
            <p>A new hospital has registered:</p>
            <ul>
                <li>Hospital Name: {$hospitalName}</li>
                <li>Hospital Code: {$hospitalCode}</li>
                <li>Admin Email: {$hospitalEmail}</li>
                <li>Admin Username: {$adminUsername}</li>
                <li>Registration Date: " . date('Y-m-d H:i:s') . "</li>
            </ul>
            <p>Please review and approve this registration.</p>
        </body>
        </html>
        ";
    }
}
?>