<?php
/**
 * Email Configuration
 * 
 * Centralized email settings for Smart Claims NHIS
 */

// Import PHPMailer classes
require_once __DIR__ . '/../utils/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../utils/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../utils/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailConfig {
    // SMTP Settings - Update these with your actual email server details
    const SMTP_HOST = 'mail.electicast.com';
    const SMTP_USERNAME = 'samuel@electicast.com';
    const SMTP_PASSWORD = 'waxtron@123?';
    const SMTP_PORT = 587;
    const SMTP_SECURE = 'tls'; // 'tls' or 'ssl'
    
    // Alternative settings for Gmail (uncomment to use)
    // const SMTP_HOST = 'smtp.gmail.com';
    // const SMTP_USERNAME = 'your-gmail@gmail.com';
    // const SMTP_PASSWORD = 'your-app-password';
    // const SMTP_PORT = 587;
    // const SMTP_SECURE = 'tls';
    
    // Alternative settings for other common providers
    // Outlook/Hotmail:
    // const SMTP_HOST = 'smtp-mail.outlook.com';
    // const SMTP_PORT = 587;
    
    // Yahoo:
    // const SMTP_HOST = 'smtp.mail.yahoo.com';
    // const SMTP_PORT = 587;
    
    // From Address
    const FROM_EMAIL = 'samuel@electicast.com';
    const FROM_NAME = 'Smart Claims NHIS';
    
    // Admin Email
    const ADMIN_EMAIL = 'snsowaa2019@gmail.com';
    
    // Email Settings
    const ENABLE_EMAIL = true; // Set to false to disable all email sending
    const EMAIL_DEBUG = false; // Set to true for debugging email issues
    const EMAIL_TIMEOUT = 60; // Email timeout in seconds
    
    /**
     * Get all email configuration as array
     */
    public static function getConfig() {
        return [
            'smtp_host' => self::SMTP_HOST,
            'smtp_username' => self::SMTP_USERNAME,
            'smtp_password' => self::SMTP_PASSWORD,
            'smtp_port' => self::SMTP_PORT,
            'smtp_secure' => self::SMTP_SECURE,
            'from_email' => self::FROM_EMAIL,
            'from_name' => self::FROM_NAME,
            'admin_email' => self::ADMIN_EMAIL,
            'enable_email' => self::ENABLE_EMAIL,
            'email_debug' => self::EMAIL_DEBUG,
            'email_timeout' => self::EMAIL_TIMEOUT
        ];
    }
    
    /**
     * Test email configuration
     */
    public static function testConnection() {
        try {
            
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = self::SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = self::SMTP_USERNAME;
            $mail->Password = self::SMTP_PASSWORD;
            $mail->SMTPSecure = self::SMTP_SECURE;
            $mail->Port = self::SMTP_PORT;
            
            // Additional settings
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            $mail->Timeout = self::EMAIL_TIMEOUT;
            
            // Test connection
            if ($mail->smtpConnect()) {
                $mail->smtpClose();
                return ['success' => true, 'message' => 'SMTP connection successful'];
            } else {
                return ['success' => false, 'message' => 'SMTP connection failed'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'SMTP test failed: ' . $e->getMessage()];
        }
    }
}
?>