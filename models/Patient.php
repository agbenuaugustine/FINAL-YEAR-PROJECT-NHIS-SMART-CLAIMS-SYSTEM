<?php
/**
 * Patient Model
 * 
 * Handles all patient-related database operations
 */
class Patient {
    // Database connection and table name
    private $conn;
    private $table_name = "patients";

    // Object properties
    public $id;
    
    // Hospital and System Information
    public $hospital_id;
    public $patient_number;
    public $created_by;
    
    // NHIS Information
    public $nhis_number;
    public $nhis_expiry;
    public $membership_type;
    public $policy_status;
    
    // Personal Information
    public $title;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $other_names;
    public $date_of_birth;
    public $gender;
    public $marital_status;
    public $occupation;
    public $religion;
    
    // Contact Information
    public $phone_primary;
    public $phone_secondary;
    public $phone;
    public $alternate_phone;
    public $email;
    public $address;
    public $emergency_contact;
    public $emergency_phone;
    
    // Address Information
    public $region;
    public $district;
    public $town_city;
    public $postal_address;
    public $residential_address;
    public $landmark;
    
    // Medical Information
    public $blood_group;
    public $allergies;
    public $chronic_conditions;
    public $emergency_contact_name;
    public $emergency_contact_relationship;
    public $next_of_kin;
    public $next_of_kin_phone;
    
    // Status
    public $is_active;
    public $created_at;
    public $updated_at;

    /**
     * Constructor with DB connection
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new patient
     * 
     * @return bool True if created successfully, false otherwise
     */
    public function create() {
        // Generate patient number
        $this->patient_number = $this->generatePatientNumber();
        
        // Get user session data for hospital_id and created_by if not set
        if (!$this->hospital_id || !$this->created_by) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $user = $_SESSION['user'] ?? null;
            $this->hospital_id = $this->hospital_id ?: ($user['hospital_id'] ?? 1);
            $this->created_by = $this->created_by ?: ($user['id'] ?? 1);
        }
        
        $query = "INSERT INTO " . $this->table_name . "
                (hospital_id, nhis_number, patient_number, first_name, last_name, other_names, 
                gender, date_of_birth, phone, alternate_phone, address, emergency_contact, emergency_phone,
                blood_group, allergies, occupation, marital_status, religion, next_of_kin, next_of_kin_phone,
                is_active, created_by)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input and map old field names to new database schema
        $hospital_id_clean = $this->sanitize($this->hospital_id);
        $nhis_number_clean = $this->sanitize($this->nhis_number);
        $patient_number_clean = $this->sanitize($this->patient_number);
        $first_name_clean = $this->sanitize($this->first_name);
        $last_name_clean = $this->sanitize($this->last_name);
        $other_names_clean = $this->sanitize($this->middle_name); // Map middle_name to other_names
        $gender_clean = $this->sanitize($this->gender);
        $date_of_birth_clean = $this->sanitize($this->date_of_birth);
        $phone_clean = $this->sanitize($this->phone_primary); // Map phone_primary to phone
        $alternate_phone_clean = $this->sanitize($this->phone_secondary); // Map phone_secondary to alternate_phone
        $address_clean = $this->sanitize($this->residential_address ?: $this->postal_address); // Use residential_address or postal_address
        $emergency_contact_clean = $this->sanitize($this->emergency_contact);
        $emergency_phone_clean = $this->sanitize($this->phone_secondary); // Use secondary phone as emergency phone
        $blood_group_clean = $this->sanitize($this->blood_group);
        $allergies_clean = $this->sanitize($this->allergies);
        $occupation_clean = $this->sanitize($this->occupation);
        $marital_status_clean = $this->sanitize($this->marital_status);
        $religion_clean = $this->sanitize($this->religion);
        $next_of_kin_clean = $this->sanitize($this->emergency_contact_name); // Map emergency_contact_name to next_of_kin
        $next_of_kin_phone_clean = $this->sanitize($this->emergency_contact); // Use emergency_contact as next_of_kin_phone
        $is_active_clean = 1;
        $created_by_clean = $this->sanitize($this->created_by);
        
        // Bind parameters
        $stmt->bindParam(1, $hospital_id_clean);
        $stmt->bindParam(2, $nhis_number_clean);
        $stmt->bindParam(3, $patient_number_clean);
        $stmt->bindParam(4, $first_name_clean);
        $stmt->bindParam(5, $last_name_clean);
        $stmt->bindParam(6, $other_names_clean);
        $stmt->bindParam(7, $gender_clean);
        $stmt->bindParam(8, $date_of_birth_clean);
        $stmt->bindParam(9, $phone_clean);
        $stmt->bindParam(10, $alternate_phone_clean);
        $stmt->bindParam(11, $address_clean);
        $stmt->bindParam(12, $emergency_contact_clean);
        $stmt->bindParam(13, $emergency_phone_clean);
        $stmt->bindParam(14, $blood_group_clean);
        $stmt->bindParam(15, $allergies_clean);
        $stmt->bindParam(16, $occupation_clean);
        $stmt->bindParam(17, $marital_status_clean);
        $stmt->bindParam(18, $religion_clean);
        $stmt->bindParam(19, $next_of_kin_clean);
        $stmt->bindParam(20, $next_of_kin_phone_clean);
        $stmt->bindParam(21, $is_active_clean);
        $stmt->bindParam(22, $created_by_clean);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Sanitize input data
     * 
     * @param mixed $data Data to sanitize
     * @return mixed Sanitized data
     */
    private function sanitize($data) {
        if ($data === null || $data === '') {
            return null;
        }
        return htmlspecialchars(strip_tags($data));
    }

    /**
     * Get a single patient by ID
     * 
     * @param int $id Patient ID
     * @return bool True if patient found, false otherwise
     */
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            
            // NHIS Information
            $this->nhis_number = $row['nhis_number'];
            $this->nhis_expiry = $row['nhis_expiry'];
            $this->membership_type = $row['membership_type'];
            $this->policy_status = $row['policy_status'];
            
            // Personal Information
            $this->title = $row['title'];
            $this->first_name = $row['first_name'];
            $this->middle_name = $row['middle_name'];
            $this->last_name = $row['last_name'];
            $this->date_of_birth = $row['date_of_birth'];
            $this->gender = $row['gender'];
            $this->marital_status = $row['marital_status'];
            $this->occupation = $row['occupation'];
            
            // Contact Information
            $this->phone_primary = $row['phone_primary'];
            $this->phone_secondary = $row['phone_secondary'];
            $this->email = $row['email'];
            $this->emergency_contact = $row['emergency_contact'];
            
            // Address Information
            $this->region = $row['region'];
            $this->district = $row['district'];
            $this->town_city = $row['town_city'];
            $this->postal_address = $row['postal_address'];
            $this->residential_address = $row['residential_address'];
            $this->landmark = $row['landmark'];
            
            // Medical Information
            $this->blood_group = $row['blood_group'];
            $this->allergies = $row['allergies'];
            $this->chronic_conditions = $row['chronic_conditions'];
            $this->emergency_contact_name = $row['emergency_contact_name'];
            $this->emergency_contact_relationship = $row['emergency_contact_relationship'];
            
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }

    /**
     * Find patient by NHIS number
     * 
     * @param string $nhis_number NHIS number to search for
     * @return bool True if patient found, false otherwise
     */
    public function findByNhisNumber($nhis_number) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE nhis_number = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $nhis_number);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->nhis_number = $row['nhis_number'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->gender = $row['gender'];
            $this->date_of_birth = $row['date_of_birth'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->emergency_contact = $row['emergency_contact'];
            $this->emergency_phone = $row['emergency_phone'];
            $this->blood_group = $row['blood_group'];
            $this->allergies = $row['allergies'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }

    /**
     * Update a patient
     * 
     * @return bool True if updated successfully, false otherwise
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    nhis_number = :nhis_number,
                    first_name = :first_name,
                    last_name = :last_name,
                    gender = :gender,
                    date_of_birth = :date_of_birth,
                    phone = :phone,
                    address = :address,
                    emergency_contact = :emergency_contact,
                    emergency_phone = :emergency_phone,
                    blood_group = :blood_group,
                    allergies = :allergies
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->nhis_number = htmlspecialchars(strip_tags($this->nhis_number));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->gender = htmlspecialchars(strip_tags($this->gender));
        $this->date_of_birth = htmlspecialchars(strip_tags($this->date_of_birth));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->emergency_contact = htmlspecialchars(strip_tags($this->emergency_contact));
        $this->emergency_phone = htmlspecialchars(strip_tags($this->emergency_phone));
        $this->blood_group = htmlspecialchars(strip_tags($this->blood_group));
        $this->allergies = htmlspecialchars(strip_tags($this->allergies));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(':nhis_number', $this->nhis_number);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':gender', $this->gender);
        $stmt->bindParam(':date_of_birth', $this->date_of_birth);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':emergency_contact', $this->emergency_contact);
        $stmt->bindParam(':emergency_phone', $this->emergency_phone);
        $stmt->bindParam(':blood_group', $this->blood_group);
        $stmt->bindParam(':allergies', $this->allergies);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Get all patients
     * 
     * @param string $search Optional search term for name or NHIS number
     * @param int $limit Optional limit for pagination
     * @param int $offset Optional offset for pagination
     * @return PDOStatement Result set
     */
    public function read($search = null, $limit = null, $offset = null) {
        $query = "SELECT * FROM " . $this->table_name;
        
        // Add search condition if provided
        if ($search) {
            $query .= " WHERE nhis_number LIKE :search 
                      OR first_name LIKE :search 
                      OR last_name LIKE :search
                      OR CONCAT(first_name, ' ', last_name) LIKE :search";
        }
        
        $query .= " ORDER BY last_name, first_name ASC";
        
        // Add pagination if provided
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :offset, :limit";
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind search parameter if provided
        if ($search) {
            $searchTerm = "%{$search}%";
            $stmt->bindParam(':search', $searchTerm);
        }
        
        // Bind pagination parameters if provided
        if ($limit !== null && $offset !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Count total patients
     * 
     * @param string $search Optional search term
     * @return int Total number of patients
     */
    public function count($search = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        // Add search condition if provided
        if ($search) {
            $query .= " WHERE nhis_number LIKE :search 
                      OR first_name LIKE :search 
                      OR last_name LIKE :search
                      OR CONCAT(first_name, ' ', last_name) LIKE :search";
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind search parameter if provided
        if ($search) {
            $searchTerm = "%{$search}%";
            $stmt->bindParam(':search', $searchTerm);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$row['total'];
    }

    /**
     * Delete a patient
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

    /**
     * Get patient's full name
     * 
     * @return string Full name (first name + last name)
     */
    public function getFullName() {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Calculate patient's age based on date of birth
     * 
     * @return int Age in years
     */
    public function getAge() {
        $dob = new DateTime($this->date_of_birth);
        $now = new DateTime();
        $interval = $now->diff($dob);
        return $interval->y;
    }

    /**
     * Generate patient number
     * 
     * @return string Generated patient number
     */
    private function generatePatientNumber() {
        $year = date('Y');
        $month = date('m');
        
        // Get hospital prefix (if hospital_id is set)
        $prefix = 'PT';
        if ($this->hospital_id) {
            $query = "SELECT hospital_code FROM hospitals WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->hospital_id);
            $stmt->execute();
            $hospital = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($hospital && !empty($hospital['hospital_code'])) {
                $prefix = $hospital['hospital_code'];
            }
        }
        
        // Get next sequence number
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE DATE_FORMAT(created_at, '%Y-%m') = ? AND hospital_id = ?";
        $stmt = $this->conn->prepare($query);
        $currentMonth = $year . '-' . $month;
        $stmt->bindParam(1, $currentMonth);
        $stmt->bindParam(2, $this->hospital_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $sequence = ($result['count'] ?? 0) + 1;
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
?>