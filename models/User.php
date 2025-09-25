<?php
/**
 * User Model
 * 
 * Handles all user-related database operations
 */
class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";

    // Object properties
    public $id;
    public $hospital_id;
    public $department_id;
    public $username;
    public $password;
    public $email;
    public $full_name;
    public $role;
    public $employee_id;
    public $phone;
    public $profile_image;
    public $date_of_birth;
    public $employment_date;
    public $created_at;
    public $updated_at;
    public $last_login;
    public $is_active;

    /**
     * Constructor with DB connection
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get a single user by ID
     * 
     * @param int $id User ID
     * @return bool True if user found, false otherwise
     */
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->hospital_id = $row['hospital_id'];
            $this->department_id = $row['department_id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->full_name = $row['full_name'];
            $this->role = $row['role'];
            $this->employee_id = $row['employee_id'];
            $this->phone = $row['phone'];
            $this->profile_image = $row['profile_image'];
            $this->date_of_birth = $row['date_of_birth'];
            $this->employment_date = $row['employment_date'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->last_login = $row['last_login'];
            $this->is_active = $row['is_active'];
            
            return true;
        }
        
        return false;
    }

    /**
     * Get a user by username
     * 
     * @param string $username Username to search for
     * @return bool True if user found, false otherwise
     */
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->hospital_id = $row['hospital_id'];
            $this->department_id = $row['department_id'];
            $this->username = $row['username'];
            $this->password = $row['password'];
            $this->email = $row['email'];
            $this->full_name = $row['full_name'];
            $this->role = $row['role'];
            $this->employee_id = $row['employee_id'];
            $this->phone = $row['phone'];
            $this->profile_image = $row['profile_image'];
            $this->date_of_birth = $row['date_of_birth'];
            $this->employment_date = $row['employment_date'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->last_login = $row['last_login'];
            $this->is_active = $row['is_active'];
            
            return true;
        }
        
        return false;
    }

    /**
     * Create a new user
     * 
     * @return bool True if created successfully, false otherwise
     */
    public function create() {
        // Hash the password
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        
        $query = "INSERT INTO " . $this->table_name . "
                (hospital_id, department_id, username, password, email, full_name, role, employee_id, phone, is_active)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->role = htmlspecialchars(strip_tags($this->role));
        if ($this->employee_id) $this->employee_id = htmlspecialchars(strip_tags($this->employee_id));
        if ($this->phone) $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->is_active = $this->is_active ? 1 : 0;
        
        // Bind parameters
        $stmt->bindParam(1, $this->hospital_id);
        $stmt->bindParam(2, $this->department_id);
        $stmt->bindParam(3, $this->username);
        $stmt->bindParam(4, $this->password);
        $stmt->bindParam(5, $this->email);
        $stmt->bindParam(6, $this->full_name);
        $stmt->bindParam(7, $this->role);
        $stmt->bindParam(8, $this->employee_id);
        $stmt->bindParam(9, $this->phone);
        $stmt->bindParam(10, $this->is_active);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Update a user
     * 
     * @return bool True if updated successfully, false otherwise
     */
    public function update() {
        // If password was passed, hash it
        $password_set = !empty($this->password) ? ", password = :password" : "";
        
        $query = "UPDATE " . $this->table_name . "
                SET
                    username = :username,
                    email = :email,
                    full_name = :full_name,
                    role = :role,
                    department = :department,
                    is_active = :is_active
                    {$password_set}
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->is_active = $this->is_active ? 1 : 0;
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':id', $this->id);
        
        // If password was passed, bind it
        if (!empty($password_set)) {
            $this->password = password_hash($this->password, PASSWORD_BCRYPT);
            $stmt->bindParam(':password', $this->password);
        }
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Update last login timestamp
     * 
     * @return bool True if updated successfully, false otherwise
     */
    public function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . "
                SET last_login = NOW()
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Get all users
     * 
     * @return PDOStatement Result set
     */
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY full_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Delete a user
     * 
     * @return bool True if deleted successfully, false otherwise
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>