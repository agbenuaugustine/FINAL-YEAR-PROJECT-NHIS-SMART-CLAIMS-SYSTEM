<?php
/**
 * Database Configuration
 * 
 * This file contains the database connection settings for the Smart Claims application.
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'uenrclai_claims');
define('DB_USER', 'uenrclai_claims');
define('DB_PASS', 'O6%gs,i429.i8k)-');

/**
 * Database Connection Class
 */
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    /**
     * Get the database connection
     * 
     * @return PDO|null Database connection object or null on failure
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            // Log the error instead of echoing it
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }

        return $this->conn;
    }
}
?>