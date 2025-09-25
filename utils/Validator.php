<?php
/**
 * Validator Utility
 * 
 * Provides validation methods for input data
 */
class Validator {
    /**
     * Validate required fields
     * 
     * @param array $data Data to validate
     * @param array $fields Required fields
     * @return array Missing fields
     */
    public function validateRequired($data, $fields) {
        $missing = [];
        
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }
    
    /**
     * Validate ID
     * 
     * @param mixed $id ID to validate
     * @return bool True if valid, false otherwise
     */
    public function validateId($id) {
        return is_numeric($id) && $id > 0;
    }
    
    /**
     * Validate email
     * 
     * @param string $email Email to validate
     * @return bool True if valid, false otherwise
     */
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate date
     * 
     * @param string $date Date to validate (YYYY-MM-DD)
     * @return bool True if valid, false otherwise
     */
    public function validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Validate phone number
     * 
     * @param string $phone Phone number to validate
     * @return bool True if valid, false otherwise
     */
    public function validatePhone($phone) {
        // Basic phone validation - adjust as needed for your country's format
        return preg_match('/^[0-9+\-\s()]{7,15}$/', $phone) === 1;
    }
    
    /**
     * Validate NHIS number
     * 
     * @param string $nhis NHIS number to validate
     * @return bool True if valid, false otherwise
     */
    public function validateNHIS($nhis) {
        // Basic NHIS validation - adjust as needed for Ghana's NHIS format
        return preg_match('/^[A-Z0-9]{6,12}$/', $nhis) === 1;
    }
    
    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @return bool True if valid, false otherwise
     */
    public function validatePassword($password) {
        // Password must be at least 8 characters and contain at least one uppercase, one lowercase, and one number
        return strlen($password) >= 8 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
    
    /**
     * Validate decimal number
     * 
     * @param mixed $number Number to validate
     * @return bool True if valid, false otherwise
     */
    public function validateDecimal($number) {
        return is_numeric($number);
    }
    
    /**
     * Sanitize input
     * 
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    public function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }
}
?>