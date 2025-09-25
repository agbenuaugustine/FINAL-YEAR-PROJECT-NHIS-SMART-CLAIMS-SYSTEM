<?php
/**
 * Diagnosis Model
 * 
 * Handles all diagnosis-related database operations
 */
class Diagnosis {
    // Database connection and table name
    private $conn;
    private $table_name = "diagnoses";

    // Object properties
    public $id;
    public $visit_id;
    public $icd10_code;
    public $diagnosis_notes;
    public $diagnosis_type;
    public $diagnosed_by;
    public $diagnosed_at;

    // Additional properties for joins
    public $icd10_description;
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
     * Create a new diagnosis
     * 
     * @return bool True if created successfully, false otherwise
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (visit_id, icd10_code, diagnosis_notes, diagnosis_type, diagnosed_by)
                VALUES
                (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->visit_id = htmlspecialchars(strip_tags($this->visit_id));
        $this->icd10_code = htmlspecialchars(strip_tags($this->icd10_code));
        $this->diagnosis_notes = htmlspecialchars(strip_tags($this->diagnosis_notes));
        $this->diagnosis_type = htmlspecialchars(strip_tags($this->diagnosis_type));
        $this->diagnosed_by = htmlspecialchars(strip_tags($this->diagnosed_by));
        
        // Bind parameters
        $stmt->bindParam(1, $this->visit_id);
        $stmt->bindParam(2, $this->icd10_code);
        $stmt->bindParam(3, $this->diagnosis_notes);
        $stmt->bindParam(4, $this->diagnosis_type);
        $stmt->bindParam(5, $this->diagnosed_by);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Get a single diagnosis by ID
     * 
     * @param int $id Diagnosis ID
     * @return bool True if diagnosis found, false otherwise
     */
    public function readOne($id) {
        $query = "SELECT d.*, i.description as icd10_description, u.full_name as doctor_name
                  FROM " . $this->table_name . " d
                  LEFT JOIN icd10_codes i ON d.icd10_code = i.id
                  LEFT JOIN users u ON d.diagnosed_by = u.id
                  WHERE d.id = ?
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->visit_id = $row['visit_id'];
            $this->icd10_code = $row['icd10_code'];
            $this->diagnosis_notes = $row['diagnosis_notes'];
            $this->diagnosis_type = $row['diagnosis_type'];
            $this->diagnosed_by = $row['diagnosed_by'];
            $this->diagnosed_at = $row['diagnosed_at'];
            $this->icd10_description = $row['icd10_description'];
            $this->doctor_name = $row['doctor_name'];
            
            return true;
        }
        
        return false;
    }

    /**
     * Get diagnoses for a specific visit
     * 
     * @param int $visit_id Visit ID
     * @return PDOStatement Result set
     */
    public function getVisitDiagnoses($visit_id) {
        $query = "SELECT d.*, i.description as icd10_description, u.full_name as doctor_name
                  FROM " . $this->table_name . " d
                  LEFT JOIN icd10_codes i ON d.icd10_code = i.id
                  LEFT JOIN users u ON d.diagnosed_by = u.id
                  WHERE d.visit_id = ?
                  ORDER BY d.diagnosis_type, d.diagnosed_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $visit_id);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Update a diagnosis
     * 
     * @return bool True if updated successfully, false otherwise
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    icd10_code = :icd10_code,
                    diagnosis_notes = :diagnosis_notes,
                    diagnosis_type = :diagnosis_type,
                    diagnosed_by = :diagnosed_by
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->icd10_code = htmlspecialchars(strip_tags($this->icd10_code));
        $this->diagnosis_notes = htmlspecialchars(strip_tags($this->diagnosis_notes));
        $this->diagnosis_type = htmlspecialchars(strip_tags($this->diagnosis_type));
        $this->diagnosed_by = htmlspecialchars(strip_tags($this->diagnosed_by));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(':icd10_code', $this->icd10_code);
        $stmt->bindParam(':diagnosis_notes', $this->diagnosis_notes);
        $stmt->bindParam(':diagnosis_type', $this->diagnosis_type);
        $stmt->bindParam(':diagnosed_by', $this->diagnosed_by);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Delete a diagnosis
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
     * Get patient diagnosis history
     * 
     * @param int $patient_id Patient ID
     * @return PDOStatement Result set
     */
    public function getPatientDiagnosisHistory($patient_id) {
        $query = "SELECT d.*, i.description as icd10_description, 
                  u.full_name as doctor_name, v.visit_date
                  FROM " . $this->table_name . " d
                  JOIN visits v ON d.visit_id = v.id
                  LEFT JOIN icd10_codes i ON d.icd10_code = i.id
                  LEFT JOIN users u ON d.diagnosed_by = u.id
                  WHERE v.patient_id = ?
                  ORDER BY d.diagnosed_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $patient_id);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Search ICD-10 codes
     * 
     * @param string $search Search term
     * @param int $limit Optional limit for pagination
     * @return PDOStatement Result set
     */
    public function searchICD10Codes($search, $limit = 20) {
        $query = "SELECT * FROM icd10_codes
                  WHERE id LIKE :search OR description LIKE :search
                  ORDER BY id ASC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        
        $searchTerm = "%{$search}%";
        $stmt->bindParam(':search', $searchTerm);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Get common diagnoses for quick selection
     * 
     * @param int $limit Number of diagnoses to return
     * @return PDOStatement Result set
     */
    public function getCommonDiagnoses($limit = 10) {
        $query = "SELECT d.icd10_code, i.description, COUNT(*) as count
                  FROM " . $this->table_name . " d
                  JOIN icd10_codes i ON d.icd10_code = i.id
                  GROUP BY d.icd10_code
                  ORDER BY count DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
}
?>