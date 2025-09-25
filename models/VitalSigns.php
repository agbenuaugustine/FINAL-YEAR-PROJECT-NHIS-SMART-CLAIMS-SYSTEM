<?php
/**
 * Vital Signs Model
 * 
 * Handles all vital signs-related database operations
 */
class VitalSigns {
    // Database connection and table name
    private $conn;
    private $table_name = "vital_signs";

    // Object properties
    public $id;
    public $visit_id;
    public $temperature;
    public $temp_method;
    public $systolic;
    public $diastolic;
    public $blood_pressure;
    public $bp_position;
    public $bp_arm;
    public $pulse_rate;
    public $pulse_rhythm;
    public $pulse_strength;
    public $respiratory_rate;
    public $breathing_pattern;
    public $weight;
    public $height;
    public $bmi;
    public $oxygen_saturation;
    public $oxygen_support;
    public $pain_score;
    public $consciousness_level;
    public $general_appearance;
    public $notes;
    public $recorded_by;
    public $recorded_at;

    // Additional properties for joins
    public $recorder_name;

    /**
     * Constructor with DB connection
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create new vital signs record
     * 
     * @return bool True if created successfully, false otherwise
     */
    public function create() {
        // Calculate BMI if weight and height are provided
        if (!empty($this->weight) && !empty($this->height) && $this->height > 0) {
            // Convert height from cm to m
            $heightInMeters = $this->height / 100;
            $this->bmi = round($this->weight / ($heightInMeters * $heightInMeters), 2);
        }

        // Create blood pressure string from systolic/diastolic
        if (!empty($this->systolic) && !empty($this->diastolic)) {
            $this->blood_pressure = $this->systolic . '/' . $this->diastolic;
        }

        $query = "INSERT INTO " . $this->table_name . "
                (visit_id, temperature, temp_method, systolic, diastolic, blood_pressure, bp_position, bp_arm,
                pulse_rate, pulse_rhythm, pulse_strength, respiratory_rate, breathing_pattern,
                weight, height, bmi, oxygen_saturation, oxygen_support, pain_score, 
                consciousness_level, general_appearance, notes, recorded_by)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->visit_id = $this->sanitize($this->visit_id);
        $this->temperature = $this->sanitize($this->temperature);
        $this->temp_method = $this->sanitize($this->temp_method);
        $this->systolic = $this->sanitize($this->systolic);
        $this->diastolic = $this->sanitize($this->diastolic);
        $this->blood_pressure = $this->sanitize($this->blood_pressure);
        $this->bp_position = $this->sanitize($this->bp_position);
        $this->bp_arm = $this->sanitize($this->bp_arm);
        $this->pulse_rate = $this->sanitize($this->pulse_rate);
        $this->pulse_rhythm = $this->sanitize($this->pulse_rhythm);
        $this->pulse_strength = $this->sanitize($this->pulse_strength);
        $this->respiratory_rate = $this->sanitize($this->respiratory_rate);
        $this->breathing_pattern = $this->sanitize($this->breathing_pattern);
        $this->weight = $this->sanitize($this->weight);
        $this->height = $this->sanitize($this->height);
        $this->oxygen_saturation = $this->sanitize($this->oxygen_saturation);
        $this->oxygen_support = $this->sanitize($this->oxygen_support);
        $this->pain_score = $this->sanitize($this->pain_score);
        $this->consciousness_level = $this->sanitize($this->consciousness_level);
        $this->general_appearance = $this->sanitize($this->general_appearance);
        $this->notes = $this->sanitize($this->notes);
        $this->recorded_by = $this->sanitize($this->recorded_by);
        
        // Bind parameters
        $stmt->bindParam(1, $this->visit_id);
        $stmt->bindParam(2, $this->temperature);
        $stmt->bindParam(3, $this->temp_method);
        $stmt->bindParam(4, $this->systolic);
        $stmt->bindParam(5, $this->diastolic);
        $stmt->bindParam(6, $this->blood_pressure);
        $stmt->bindParam(7, $this->bp_position);
        $stmt->bindParam(8, $this->bp_arm);
        $stmt->bindParam(9, $this->pulse_rate);
        $stmt->bindParam(10, $this->pulse_rhythm);
        $stmt->bindParam(11, $this->pulse_strength);
        $stmt->bindParam(12, $this->respiratory_rate);
        $stmt->bindParam(13, $this->breathing_pattern);
        $stmt->bindParam(14, $this->weight);
        $stmt->bindParam(15, $this->height);
        $stmt->bindParam(16, $this->bmi);
        $stmt->bindParam(17, $this->oxygen_saturation);
        $stmt->bindParam(18, $this->oxygen_support);
        $stmt->bindParam(19, $this->pain_score);
        $stmt->bindParam(20, $this->consciousness_level);
        $stmt->bindParam(21, $this->general_appearance);
        $stmt->bindParam(22, $this->notes);
        $stmt->bindParam(23, $this->recorded_by);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Get vital signs for a specific visit
     * 
     * @param int $visit_id Visit ID
     * @return bool True if vital signs found, false otherwise
     */
    public function readByVisit($visit_id) {
        $query = "SELECT vs.*, u.full_name as recorder_name
                  FROM " . $this->table_name . " vs
                  LEFT JOIN users u ON vs.recorded_by = u.id
                  WHERE vs.visit_id = ?
                  ORDER BY vs.recorded_at DESC
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $visit_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->visit_id = $row['visit_id'];
            $this->temperature = $row['temperature'];
            $this->blood_pressure = $row['blood_pressure'];
            $this->pulse_rate = $row['pulse_rate'];
            $this->respiratory_rate = $row['respiratory_rate'];
            $this->weight = $row['weight'];
            $this->height = $row['height'];
            $this->bmi = $row['bmi'];
            $this->oxygen_saturation = $row['oxygen_saturation'];
            $this->recorded_by = $row['recorded_by'];
            $this->recorded_at = $row['recorded_at'];
            $this->recorder_name = $row['recorder_name'];
            
            return true;
        }
        
        return false;
    }

    /**
     * Get a single vital signs record by ID
     * 
     * @param int $id Vital signs ID
     * @return bool True if vital signs found, false otherwise
     */
    public function readOne($id) {
        $query = "SELECT vs.*, u.full_name as recorder_name
                  FROM " . $this->table_name . " vs
                  LEFT JOIN users u ON vs.recorded_by = u.id
                  WHERE vs.id = ?
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->visit_id = $row['visit_id'];
            $this->temperature = $row['temperature'];
            $this->blood_pressure = $row['blood_pressure'];
            $this->pulse_rate = $row['pulse_rate'];
            $this->respiratory_rate = $row['respiratory_rate'];
            $this->weight = $row['weight'];
            $this->height = $row['height'];
            $this->bmi = $row['bmi'];
            $this->oxygen_saturation = $row['oxygen_saturation'];
            $this->recorded_by = $row['recorded_by'];
            $this->recorded_at = $row['recorded_at'];
            $this->recorder_name = $row['recorder_name'];
            
            return true;
        }
        
        return false;
    }

    /**
     * Update vital signs record
     * 
     * @return bool True if updated successfully, false otherwise
     */
    public function update() {
        // Calculate BMI if weight and height are provided
        if (!empty($this->weight) && !empty($this->height) && $this->height > 0) {
            // Convert height from cm to m
            $heightInMeters = $this->height / 100;
            $this->bmi = round($this->weight / ($heightInMeters * $heightInMeters), 2);
        }

        $query = "UPDATE " . $this->table_name . "
                SET
                    temperature = :temperature,
                    blood_pressure = :blood_pressure,
                    pulse_rate = :pulse_rate,
                    respiratory_rate = :respiratory_rate,
                    weight = :weight,
                    height = :height,
                    bmi = :bmi,
                    oxygen_saturation = :oxygen_saturation,
                    recorded_by = :recorded_by
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->temperature = htmlspecialchars(strip_tags($this->temperature));
        $this->blood_pressure = htmlspecialchars(strip_tags($this->blood_pressure));
        $this->pulse_rate = htmlspecialchars(strip_tags($this->pulse_rate));
        $this->respiratory_rate = htmlspecialchars(strip_tags($this->respiratory_rate));
        $this->weight = htmlspecialchars(strip_tags($this->weight));
        $this->height = htmlspecialchars(strip_tags($this->height));
        $this->oxygen_saturation = htmlspecialchars(strip_tags($this->oxygen_saturation));
        $this->recorded_by = htmlspecialchars(strip_tags($this->recorded_by));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(':temperature', $this->temperature);
        $stmt->bindParam(':blood_pressure', $this->blood_pressure);
        $stmt->bindParam(':pulse_rate', $this->pulse_rate);
        $stmt->bindParam(':respiratory_rate', $this->respiratory_rate);
        $stmt->bindParam(':weight', $this->weight);
        $stmt->bindParam(':height', $this->height);
        $stmt->bindParam(':bmi', $this->bmi);
        $stmt->bindParam(':oxygen_saturation', $this->oxygen_saturation);
        $stmt->bindParam(':recorded_by', $this->recorded_by);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Get vital signs history for a patient
     * 
     * @param int $patient_id Patient ID
     * @param int $limit Optional limit for pagination
     * @return PDOStatement Result set
     */
    public function getPatientHistory($patient_id, $limit = 10) {
        $query = "SELECT vs.*, v.visit_date, u.full_name as recorder_name
                  FROM " . $this->table_name . " vs
                  JOIN visits v ON vs.visit_id = v.id
                  LEFT JOIN users u ON vs.recorded_by = u.id
                  WHERE v.patient_id = ?
                  ORDER BY vs.recorded_at DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $patient_id);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Delete a vital signs record
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
}
?>