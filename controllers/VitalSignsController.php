<?php
/**
 * Vital Signs Controller
 * 
 * Handles vital signs-related operations
 */

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/VitalSigns.php';
require_once __DIR__ . '/../models/Visit.php';
require_once __DIR__ . '/../utils/Validator.php';

class VitalSignsController {
    // Database connection and models
    private $conn;
    private $vitalSigns;
    private $visit;
    private $validator;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Get database connection
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // Initialize models
        $this->vitalSigns = new VitalSigns($this->conn);
        $this->visit = new Visit($this->conn);
        
        // Initialize validator
        $this->validator = new Validator();
    }
    
    /**
     * Create vital signs record
     * 
     * @param array $data Vital signs data
     * @return array Response with status and data
     */
    public function createVitalSigns($data) {
        // Validate required fields
        $requiredFields = ['visit_id', 'recorded_by'];
        $missingFields = $this->validator->validateRequired($data, $requiredFields);
        
        if (!empty($missingFields)) {
            return [
                'status' => 'error',
                'message' => 'Missing required fields: ' . implode(', ', $missingFields)
            ];
        }
        
        // Set vital signs properties
        $this->vitalSigns->visit_id = $data['visit_id'];
        $this->vitalSigns->temperature = $data['temperature'] ?? null;
        $this->vitalSigns->temp_method = $data['temp_method'] ?? null;
        $this->vitalSigns->systolic = $data['systolic'] ?? null;
        $this->vitalSigns->diastolic = $data['diastolic'] ?? null;
        $this->vitalSigns->bp_position = $data['bp_position'] ?? null;
        $this->vitalSigns->bp_arm = $data['bp_arm'] ?? null;
        $this->vitalSigns->pulse_rate = $data['pulse_rate'] ?? null;
        $this->vitalSigns->pulse_rhythm = $data['pulse_rhythm'] ?? null;
        $this->vitalSigns->pulse_strength = $data['pulse_strength'] ?? null;
        $this->vitalSigns->respiratory_rate = $data['respiratory_rate'] ?? null;
        $this->vitalSigns->breathing_pattern = $data['breathing_pattern'] ?? null;
        $this->vitalSigns->weight = $data['weight'] ?? null;
        $this->vitalSigns->height = $data['height'] ?? null;
        $this->vitalSigns->oxygen_saturation = $data['oxygen_saturation'] ?? null;
        $this->vitalSigns->oxygen_support = $data['oxygen_support'] ?? null;
        $this->vitalSigns->pain_score = $data['pain_score'] ?? null;
        $this->vitalSigns->consciousness_level = $data['consciousness_level'] ?? null;
        $this->vitalSigns->general_appearance = $data['general_appearance'] ?? null;
        $this->vitalSigns->notes = $data['notes'] ?? null;
        $this->vitalSigns->recorded_by = $data['recorded_by'];
        
        // Create vital signs
        if ($this->vitalSigns->create()) {
            return [
                'status' => 'success',
                'message' => 'Vital signs recorded successfully',
                'data' => [
                    'id' => $this->vitalSigns->id,
                    'visit_id' => $this->vitalSigns->visit_id,
                    'bmi' => $this->vitalSigns->bmi
                ]
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to record vital signs'
            ];
        }
    }

    /**
     * Get vital signs for a visit
     * 
     * @param int $visitId Visit ID
     * @return array Response with status and data
     */
    public function getVitalSignsByVisit($visitId) {
        // Validate visit ID
        if (!$this->validator->validateId($visitId)) {
            return [
                'status' => 'error',
                'message' => 'Invalid visit ID'
            ];
        }
        
        // Check if visit exists
        if (!$this->visit->readOne($visitId)) {
            return [
                'status' => 'error',
                'message' => 'Visit not found'
            ];
        }
        
        // Get vital signs
        if ($this->vitalSigns->readByVisit($visitId)) {
            // Return response
            return [
                'status' => 'success',
                'data' => [
                    'id' => $this->vitalSigns->id,
                    'visit_id' => $this->vitalSigns->visit_id,
                    'temperature' => $this->vitalSigns->temperature,
                    'blood_pressure' => $this->vitalSigns->blood_pressure,
                    'pulse_rate' => $this->vitalSigns->pulse_rate,
                    'respiratory_rate' => $this->vitalSigns->respiratory_rate,
                    'weight' => $this->vitalSigns->weight,
                    'height' => $this->vitalSigns->height,
                    'bmi' => $this->vitalSigns->bmi,
                    'oxygen_saturation' => $this->vitalSigns->oxygen_saturation,
                    'recorded_by' => $this->vitalSigns->recorded_by,
                    'recorder_name' => $this->vitalSigns->recorder_name,
                    'recorded_at' => $this->vitalSigns->recorded_at
                ]
            ];
        } else {
            // No vital signs found
            return [
                'status' => 'success',
                'message' => 'No vital signs recorded for this visit',
                'data' => null
            ];
        }
    }
    
    /**
     * Record vital signs for a visit
     * 
     * @param array $data Vital signs data
     * @return array Response with status and data
     */
    public function recordVitalSigns($data) {
        // Validate required fields
        $requiredFields = ['visit_id', 'recorded_by'];
        $missingFields = $this->validator->validateRequired($data, $requiredFields);
        
        if (!empty($missingFields)) {
            return [
                'status' => 'error',
                'message' => 'Missing required fields: ' . implode(', ', $missingFields)
            ];
        }
        
        // Validate visit ID
        if (!$this->visit->readOne($data['visit_id'])) {
            return [
                'status' => 'error',
                'message' => 'Invalid visit ID'
            ];
        }
        
        // Set vital signs properties
        $this->vitalSigns->visit_id = $data['visit_id'];
        $this->vitalSigns->temperature = $data['temperature'] ?? null;
        $this->vitalSigns->blood_pressure = $data['blood_pressure'] ?? null;
        $this->vitalSigns->pulse_rate = $data['pulse_rate'] ?? null;
        $this->vitalSigns->respiratory_rate = $data['respiratory_rate'] ?? null;
        $this->vitalSigns->weight = $data['weight'] ?? null;
        $this->vitalSigns->height = $data['height'] ?? null;
        $this->vitalSigns->oxygen_saturation = $data['oxygen_saturation'] ?? null;
        $this->vitalSigns->recorded_by = $data['recorded_by'];
        
        // Record vital signs
        if ($this->vitalSigns->create()) {
            // Return response
            return [
                'status' => 'success',
                'message' => 'Vital signs recorded successfully',
                'data' => [
                    'id' => $this->vitalSigns->id,
                    'visit_id' => $this->vitalSigns->visit_id,
                    'temperature' => $this->vitalSigns->temperature,
                    'blood_pressure' => $this->vitalSigns->blood_pressure,
                    'pulse_rate' => $this->vitalSigns->pulse_rate,
                    'respiratory_rate' => $this->vitalSigns->respiratory_rate,
                    'weight' => $this->vitalSigns->weight,
                    'height' => $this->vitalSigns->height,
                    'bmi' => $this->vitalSigns->bmi,
                    'oxygen_saturation' => $this->vitalSigns->oxygen_saturation
                ]
            ];
        } else {
            // Failed to record vital signs
            return [
                'status' => 'error',
                'message' => 'Failed to record vital signs'
            ];
        }
    }
    
    /**
     * Update vital signs
     * 
     * @param int $id Vital signs ID
     * @param array $data Vital signs data
     * @return array Response with status and data
     */
    public function updateVitalSigns($id, $data) {
        // Validate ID
        if (!$this->validator->validateId($id)) {
            return [
                'status' => 'error',
                'message' => 'Invalid vital signs ID'
            ];
        }
        
        // Check if vital signs exist
        if (!$this->vitalSigns->readOne($id)) {
            return [
                'status' => 'error',
                'message' => 'Vital signs not found'
            ];
        }
        
        // Update vital signs properties
        $this->vitalSigns->temperature = $data['temperature'] ?? $this->vitalSigns->temperature;
        $this->vitalSigns->blood_pressure = $data['blood_pressure'] ?? $this->vitalSigns->blood_pressure;
        $this->vitalSigns->pulse_rate = $data['pulse_rate'] ?? $this->vitalSigns->pulse_rate;
        $this->vitalSigns->respiratory_rate = $data['respiratory_rate'] ?? $this->vitalSigns->respiratory_rate;
        $this->vitalSigns->weight = $data['weight'] ?? $this->vitalSigns->weight;
        $this->vitalSigns->height = $data['height'] ?? $this->vitalSigns->height;
        $this->vitalSigns->oxygen_saturation = $data['oxygen_saturation'] ?? $this->vitalSigns->oxygen_saturation;
        $this->vitalSigns->recorded_by = $data['recorded_by'] ?? $this->vitalSigns->recorded_by;
        
        // Update vital signs
        if ($this->vitalSigns->update()) {
            // Return response
            return [
                'status' => 'success',
                'message' => 'Vital signs updated successfully',
                'data' => [
                    'id' => $this->vitalSigns->id,
                    'temperature' => $this->vitalSigns->temperature,
                    'blood_pressure' => $this->vitalSigns->blood_pressure,
                    'pulse_rate' => $this->vitalSigns->pulse_rate,
                    'respiratory_rate' => $this->vitalSigns->respiratory_rate,
                    'weight' => $this->vitalSigns->weight,
                    'height' => $this->vitalSigns->height,
                    'bmi' => $this->vitalSigns->bmi,
                    'oxygen_saturation' => $this->vitalSigns->oxygen_saturation
                ]
            ];
        } else {
            // Failed to update vital signs
            return [
                'status' => 'error',
                'message' => 'Failed to update vital signs'
            ];
        }
    }
    
    /**
     * Get vital signs history for a patient
     * 
     * @param int $patientId Patient ID
     * @param int $limit Optional limit for pagination
     * @return array Response with status and data
     */
    public function getPatientVitalSignsHistory($patientId, $limit = 10) {
        // Validate patient ID
        if (!$this->validator->validateId($patientId)) {
            return [
                'status' => 'error',
                'message' => 'Invalid patient ID'
            ];
        }
        
        // Get vital signs history
        $stmt = $this->vitalSigns->getPatientHistory($patientId, $limit);
        $history = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $history[] = $row;
        }
        
        // Return response
        return [
            'status' => 'success',
            'data' => $history
        ];
    }
    
    /**
     * Delete vital signs
     * 
     * @param int $id Vital signs ID
     * @return array Response with status and message
     */
    public function deleteVitalSigns($id) {
        // Validate ID
        if (!$this->validator->validateId($id)) {
            return [
                'status' => 'error',
                'message' => 'Invalid vital signs ID'
            ];
        }
        
        // Check if vital signs exist
        if (!$this->vitalSigns->readOne($id)) {
            return [
                'status' => 'error',
                'message' => 'Vital signs not found'
            ];
        }
        
        // Delete vital signs
        if ($this->vitalSigns->delete()) {
            // Return response
            return [
                'status' => 'success',
                'message' => 'Vital signs deleted successfully'
            ];
        } else {
            // Failed to delete vital signs
            return [
                'status' => 'error',
                'message' => 'Failed to delete vital signs'
            ];
        }
    }
}
?>