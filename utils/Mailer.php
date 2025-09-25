<?php
require_once __DIR__ . '/../config/email_config.php';

// Try to include PHPMailer - check different possible locations
$phpmailer_loaded = false;

// Check if PHPMailer is in the standard location
if (file_exists(__DIR__ . '/PHPMailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
    $phpmailer_loaded = true;
} elseif (class_exists('PHPMailer')) {
    // PHPMailer is already available globally
    $phpmailer_loaded = true;
}

class Mailer {
    private $mailer;
    private $config;
    private $enabled;
    
    public function __construct() {
        // Get email configuration
        $this->config = EmailConfig::getConfig();
        $this->enabled = $this->config['enable_email'];
        
        if (!$this->enabled) {
            error_log("Email is disabled in configuration");
            return;
        }
        
        $this->mailer = new PHPMailer(true);
        
        try {
            // Server settings from configuration
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp_host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['smtp_username'];
            $this->mailer->Password = $this->config['smtp_password'];
            $this->mailer->SMTPSecure = $this->config['smtp_secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->Port = $this->config['smtp_port'];
            
            // Additional SMTP settings for better reliability
            $this->mailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Enable SMTP debugging if configured
            $this->mailer->SMTPDebug = $this->config['email_debug'] ? 2 : 0;
            $this->mailer->Debugoutput = function($str, $level) {
                error_log("SMTP Debug: $str");
            };
            
            // Timeout settings
            $this->mailer->Timeout = $this->config['email_timeout'];
            $this->mailer->SMTPKeepAlive = true;
            
            // Default sender
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            
        } catch (Exception $e) {
            error_log("Mailer initialization failed: " . $e->getMessage());
            $this->enabled = false; // Disable email if initialization fails
        }
    }
    
    public function sendRegistrationEmails($userEmail, $facilityName, $username) {
        if (!$this->enabled) {
            error_log("Email sending is disabled - skipping registration emails");
            return true; // Return true so registration doesn't fail
        }
        
        try {
            error_log("Attempting to send registration emails to: " . $userEmail);
            
            // Send email to user
            $this->mailer->clearAddresses();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();
            $this->mailer->addAddress($userEmail);
            $this->mailer->Subject = 'Welcome to Smart Claims NHIS';
            $this->mailer->isHTML(true);
            $this->mailer->Body = $this->getUserEmailTemplate($facilityName, $username);
            
            $result1 = $this->mailer->send();
            error_log("User email sent result: " . ($result1 ? "success" : "failed"));
            
            // Send notification to admin
            $this->mailer->clearAddresses();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();
            $this->mailer->addAddress($this->config['admin_email']);
            $this->mailer->Subject = 'New Healthcare Provider Registration';
            $this->mailer->Body = $this->getAdminEmailTemplate($facilityName, $userEmail, $username);
            
            $result2 = $this->mailer->send();
            error_log("Admin email sent result: " . ($result2 ? "success" : "failed"));
            
            return $result1 && $result2;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            if ($this->mailer) {
                error_log("Email error info: " . $this->mailer->ErrorInfo);
            }
            return false; // Return false but don't fail the registration
        }
    }
    
    public function sendHospitalRegistrationEmails($hospitalEmail, $adminName, $hospitalName, $adminUsername, $hospitalCode) {
        if (!$this->enabled) {
            error_log("Email sending is disabled - skipping hospital registration emails");
            return true; // Return true so registration doesn't fail
        }
        
        try {
            error_log("Attempting to send hospital registration emails to: " . $hospitalEmail);
            
            // Send email to hospital admin
            $this->mailer->clearAddresses();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();
            $this->mailer->addAddress($hospitalEmail);
            $this->mailer->Subject = 'Hospital Registration - Smart Claims NHIS System';
            $this->mailer->isHTML(true);
            $this->mailer->Body = $this->getHospitalUserEmailTemplate($adminName, $hospitalName, $adminUsername, $hospitalCode);
            
            $result1 = $this->mailer->send();
            error_log("Hospital admin email sent result: " . ($result1 ? "success" : "failed"));
            
            // Send notification to system admin
            $this->mailer->clearAddresses();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();
            $this->mailer->addAddress($this->config['admin_email']);
            $this->mailer->Subject = 'New Hospital Registration - Smart Claims NHIS';
            $this->mailer->Body = $this->getHospitalAdminEmailTemplate($hospitalName, $hospitalEmail, $adminUsername, $hospitalCode);
            
            $result2 = $this->mailer->send();
            error_log("System admin email sent result: " . ($result2 ? "success" : "failed"));
            
            return $result1 && $result2;
        } catch (Exception $e) {
            error_log("Hospital registration email sending failed: " . $e->getMessage());
            if ($this->mailer) {
                error_log("Hospital email error info: " . $this->mailer->ErrorInfo);
            }
            return false; // Return false but don't fail the registration
        }
    }
    
    private function getUserEmailTemplate($facilityName, $username) {
        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #1e88e5;'>Welcome to Smart Claims NHIS!</h2>
                <p>Dear {$facilityName},</p>
                <p>Thank you for registering with Smart Claims NHIS. Your account has been created successfully and is pending approval by our administrators.</p>
                <p>Your registration details:</p>
                <ul>
                    <li>Facility Name: {$facilityName}</li>
                    <li>Username: {$username}</li>
                </ul>
                <p>We will notify you once your account has been approved. This process typically takes 1-2 business days.</p>
                <p>If you have any questions, please don't hesitate to contact our support team.</p>
                <br>
                <p>Best regards,<br>Smart Claims NHIS Team</p>
            </div>
        ";
    }
    
    private function getAdminEmailTemplate($facilityName, $userEmail, $username) {
        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #1e88e5;'>New Healthcare Provider Registration</h2>
                <p>A new healthcare provider has registered on Smart Claims NHIS:</p>
                <ul>
                    <li>Facility Name: {$facilityName}</li>
                    <li>Email: {$userEmail}</li>
                    <li>Username: {$username}</li>
                    <li>Registration Date: " . date('Y-m-d H:i:s') . "</li>
                </ul>
                <p>Please review and approve this registration through the admin dashboard.</p>
            </div>
        ";
    }
    
    private function getHospitalUserEmailTemplate($adminName, $hospitalName, $adminUsername, $hospitalCode) {
        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #1e88e5; margin-bottom: 5px;'>Smart Claims NHIS</h1>
                    <p style='color: #666; margin: 0;'>Hospital Management System</p>
                </div>
                
                <h2 style='color: #1e88e5; border-bottom: 2px solid #e3f2fd; padding-bottom: 10px;'>Hospital Registration Confirmation</h2>
                
                <p>Dear {$adminName},</p>
                
                <p>Thank you for registering <strong>{$hospitalName}</strong> with the Smart Claims NHIS system. Your hospital registration has been successfully submitted and is currently pending approval by our system administrators.</p>
                
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0;'>
                    <h3 style='color: #1e88e5; margin-top: 0;'>Registration Details:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: bold; width: 40%;'>Hospital Name:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6;'>{$hospitalName}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: bold;'>Hospital Code:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6;'>{$hospitalCode}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: bold;'>Admin Username:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6;'>{$adminUsername}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Registration Date:</td>
                            <td style='padding: 8px 0;'>" . date('F j, Y \a\t g:i A') . "</td>
                        </tr>
                    </table>
                </div>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                    <h4 style='color: #856404; margin-top: 0;'>⏳ What Happens Next?</h4>
                    <ol style='color: #856404; margin: 0; padding-left: 20px;'>
                        <li>Our administrators will review your hospital registration</li>
                        <li>We may contact you for additional verification if needed</li>
                        <li>Once approved, you'll receive login credentials</li>
                        <li>You can then access the full Smart Claims NHIS system</li>
                    </ol>
                </div>
                
                <div style='background-color: #e8f5e8; border: 1px solid #c3e6c3; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                    <h4 style='color: #2e7d2e; margin-top: 0;'>🚀 What You'll Get Access To:</h4>
                    <ul style='color: #2e7d2e; margin: 0; padding-left: 20px;'>
                        <li>Patient registration and management</li>
                        <li>Service requisition and billing</li>
                        <li>NHIS claims processing and submission</li>
                        <li>Laboratory and pharmacy integration</li>
                        <li>Comprehensive reporting and analytics</li>
                        <li>Multi-department user management</li>
                    </ul>
                </div>
                
                <p><strong>Expected Approval Time:</strong> 1-3 business days</p>
                
                <p>If you have any questions or need immediate assistance, please contact our support team:</p>
                <ul>
                    <li>Email: support@smartclaims.com</li>
                    <li>Phone: +233 XX XXX XXXX</li>
                </ul>
                
                <div style='border-top: 1px solid #e0e0e0; margin-top: 30px; padding-top: 20px; text-align: center; color: #666;'>
                    <p style='margin: 0;'>Best regards,<br><strong>Smart Claims NHIS Team</strong></p>
                    <p style='margin: 10px 0 0 0; font-size: 12px;'>© 2024 Smart Claims NHIS. All rights reserved.</p>
                </div>
            </div>
        ";
    }
    
    private function getHospitalAdminEmailTemplate($hospitalName, $hospitalEmail, $adminUsername, $hospitalCode) {
        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #1e88e5; margin-bottom: 5px;'>Smart Claims NHIS</h1>
                    <p style='color: #666; margin: 0;'>Admin Notification</p>
                </div>
                
                <h2 style='color: #dc3545; border-bottom: 2px solid #f8d7da; padding-bottom: 10px;'>🏥 New Hospital Registration</h2>
                
                <p>A new hospital has registered and is awaiting approval:</p>
                
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0;'>
                    <h3 style='color: #1e88e5; margin-top: 0;'>Hospital Details:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: bold; width: 40%;'>Hospital Name:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6;'>{$hospitalName}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: bold;'>Hospital Code:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6;'>{$hospitalCode}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: bold;'>Contact Email:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6;'>{$hospitalEmail}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6; font-weight: bold;'>Admin Username:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #dee2e6;'>{$adminUsername}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Registration Date:</td>
                            <td style='padding: 8px 0;'>" . date('F j, Y \a\t g:i A') . "</td>
                        </tr>
                    </table>
                </div>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                    <h4 style='color: #856404; margin-top: 0;'>⚡ Action Required:</h4>
                    <ol style='color: #856404; margin: 0; padding-left: 20px;'>
                        <li>Login to the admin dashboard</li>
                        <li>Review the hospital registration details</li>
                        <li>Verify the hospital credentials and documentation</li>
                        <li>Approve or reject the registration</li>
                        <li>The hospital will be notified automatically</li>
                    </ol>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='/smartclaimsCL/api/access/dashboard.php' style='background-color: #1e88e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>
                        Review Registration
                    </a>
                </div>
                
                <div style='border-top: 1px solid #e0e0e0; margin-top: 30px; padding-top: 20px; text-align: center; color: #666;'>
                    <p style='margin: 0;'>Smart Claims NHIS Admin System</p>
                    <p style='margin: 10px 0 0 0; font-size: 12px;'>This is an automated notification.</p>
                </div>
            </div>
        ";
    }
} 