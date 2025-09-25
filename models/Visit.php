<?php
/**
 * Visit Model
 * 
 * Handles all visit-related database operations
 */
class Visit {
    // Database connection and table name
    private $conn;
    private $table_name = "visits";

    // Object properties
    public $id;
    public $patient_id;
    public $visit_date;
    public $visit_type;
    public $chief_complaint;
    public $status;
    public $attending_doctor;
    public $created_by;
    public $created_at;
    public $updated_at;

    // Additional properties for joins
    public $patient_name;
    public $doctor_name;

    /**
     * Constructor with DB connection
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new visit
     * 
     * @return bool True if created successfully, false otherwise
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (patient_id, visit_type, chief_complaint, status, attending_doctor, created_by)
                VALUES
                (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));
        $this->visit_type = htmlspecialchars(strip_tags($this->visit_type));
        $this->chief_complaint = htmlspecialchars(strip_tags($this->chief_complaint));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->attending_doctor = htmlspecialchars(strip_tags($this->attending_doctor));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by));
        
        // Bind parameters
        $stmt->bindParam(1, $this->patient_id);
        $stmt->bindParam(2, $this->visit_type);
        $stmt->bindParam(3, $this->chief_complaint);
        $stmt->bindParam(4, $this->status);
        $stmt->bindParam(5, $this->attending_doctor);
        $stmt->bindParam(6, $this->created_by);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Get a single visit by ID
     * 
     * @param int $id Visit ID
     * @return bool True if visit found, false otherwise
     */
    public function readOne($id) {
        $query = "SELECT v.*, 
                  CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                  CONCAT(u.full_name) as doctor_name
                  FROM " . $this->table_name . " v
                  LEFT JOIN patients p ON v.patient_id = p.id
                  LEFT JOIN users u ON v.attending_doctor = u.id
                  WHERE v.id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->patient_id = $row['patient_id'];
            $this->visit_date = $row['visit_date'];
            $this->visit_type = $row['visit_type'];
            $this->chief_complaint = $row['chief_complaint'];
            $this->status = $row['status'];
            $this->attending_doctor = $row['attending_doctor'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->patient_name = $row['patient_name'];
            $this->doctor_name = $row['doctor_name'];
            
            return true;
        }
        
        return false;
    }

    /**
     * Update a visit
     * 
     * @return bool True if updated successfully, false otherwise
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    patient_id = :patient_id,
                    visit_type = :visit_type,
                    chief_complaint = :chief_complaint,
                    status = :status,
                    attending_doctor = :attending_doctor
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->patient_id = htmlspecialchars(strip_tags($this->patient_id));
        $this->visit_type = htmlspecialchars(strip_tags($this->visit_type));
        $this->chief_complaint = htmlspecialchars(strip_tags($this->chief_complaint));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->attending_doctor = htmlspecialchars(strip_tags($this->attending_doctor));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(':patient_id', $this->patient_id);
        $stmt->bindParam(':visit_type', $this->visit_type);
        $stmt->bindParam(':chief_complaint', $this->chief_complaint);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':attending_doctor', $this->attending_doctor);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Get all visits with patient and doctor information
     * 
     * @param string $search Optional search term
     * @param int $limit Optional limit for pagination
     * @param int $offset Optional offset for pagination
     * @return PDOStatement Result set
     */
    public function read($search = null, $limit = null, $offset = null) {
        $query = "SELECT v.*, 
                  CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                  p.nhis_number,
                  CONCAT(u.full_name) as doctor_name
                  FROM " . $this->table_name . " v
                  LEFT JOIN patients p ON v.patient_id = p.id
                  LEFT JOIN users u ON v.attending_doctor = u.id";
        
        // Add search condition if provided
        if ($search) {
            $query .= " WHERE p.nhis_number LIKE :search 
                      OR p.first_name LIKE :search 
                      OR p.last_name LIKE :search
                      OR CONCAT(p.first_name, ' ', p.last_name) LIKE :search
                      OR v.chief_complaint LIKE :search";
        }
        
        $query .= " ORDER BY v.visit_date DESC";
        
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
     * Get visits for a specific patient
     * 
     * @param int $patient_id Patient ID
     * @return PDOStatement Result set
     */
    public function getPatientVisits($patient_id) {
        $query = "SELECT v.*, 
                  CONCAT(u.full_name) as doctor_name
                  FROM " . $this->table_name . " v
                  LEFT JOIN users u ON v.attending_doctor = u.id
                  WHERE v.patient_id = ?
                  ORDER BY v.visit_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $patient_id);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Count total visits
     * 
     * @param string $search Optional search term
     * @return int Total number of visits
     */
    public function count($search = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " v
                  LEFT JOIN patients p ON v.patient_id = p.id";
        
        // Add search condition if provided
        if ($search) {
            $query .= " WHERE p.nhis_number LIKE :search 
                      OR p.first_name LIKE :search 
                      OR p.last_name LIKE :search
                      OR CONCAT(p.first_name, ' ', p.last_name) LIKE :search
                      OR v.chief_complaint LIKE :search";
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
     * Delete a visit
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
     * Get recent visits for dashboard
     * 
     * @param int $limit Number of visits to return
     * @return PDOStatement Result set
     */
    public function getRecentVisits($limit = 5) {
        $query = "SELECT v.*, 
                  CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                  p.nhis_number,
                  CONCAT(u.full_name) as doctor_name
                  FROM " . $this->table_name . " v
                  LEFT JOIN patients p ON v.patient_id = p.id
                  LEFT JOIN users u ON v.attending_doctor = u.id
                  ORDER BY v.visit_date DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Get visit statistics
     * 
     * @return array Statistics about visits
     */
    public function getStatistics() {
        $stats = [];
        
        // Total visits today
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE DATE(visit_date) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['today'] = (int)$row['count'];
        
        // Total visits this week
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE YEARWEEK(visit_date, 1) = YEARWEEK(CURDATE(), 1)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['this_week'] = (int)$row['count'];
        
        // Total visits this month
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE MONTH(visit_date) = MONTH(CURDATE()) 
                  AND YEAR(visit_date) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['this_month'] = (int)$row['count'];
        
        // Visits by status
        $query = "SELECT status, COUNT(*) as count FROM " . $this->table_name . " 
                  GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_status'] = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats['by_status'][$row['status']] = (int)$row['count'];
        }
        
        return $stats;
    }
}
?>