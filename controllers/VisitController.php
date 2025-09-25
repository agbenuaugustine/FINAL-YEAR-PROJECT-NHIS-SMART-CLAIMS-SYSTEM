<?php
/**
 * Visit Controller
 * 
 * Handles visit-related operations
 */

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Visit.php';
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../utils/Validator.php';

class VisitController {
    // Database connection and models
    private $conn;
    private $visit;
    private $patient;
    private $validator;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Get database connection
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // Initialize models
        $this->visit = new Visit($this->conn);
        $this->patient = new Patient($this->conn);
        
        // Initialize validator
        $this->validator = new Validator();
    }
    
    /**
     * Get all visits
     * 
     * @param array $params Query parameters
     * @return array Response with status and data
     */
    public function getVisits($params = []) {
        // Extract parameters
        $search = isset($params['search']) ? $params['search'] : null;
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
        
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // Get visits
        $stmt = $this->visit->read($search, $limit, $offset);
        $visits = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $visits[] = $row;
        }
        
        // Get total count for pagination
        $total = $this->visit->count($search);
        $totalPages = ceil($total / $limit);
        
        // Return response
        return [
            'status' => 'success',
            'data' => [
                'visits' => $visits,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ]
        ];
    }
    
    /**
     * Get a single visit by ID
     * 
     * @param int $id Visit ID
     * @return array Response with status and data
     */
    public function getVisit($id) {
        // Validate ID
        if (!$this->validator->validateId($id)) {
            return [
                'status' => 'error',
                'message' => 'Invalid visit ID'
            ];
        }
        
        // Get visit
        if ($this->visit->readOne($id)) {
            // Return response
            return [
                'status' => 'success',
                'data' => [
                    'id' => $this->visit->id,
                    'patient_id' => $this->visit->patient_id,
                    'patient_name' => $this->visit->patient_name,
                    'visit_date' => $this->visit->visit_date,
                    'visit_type' => $this->visit->visit_type,
                    'chief_complaint' => $this->visit->chief_complaint,
                    'status' => $this->visit->status,
                    'attending_doctor' => $this->visit->attending_doctor,
                    'doctor_name' => $this->visit->doctor_name,
                    'created_by' => $this->visit->created_by,
                    'created_at' => $this->visit->created_at,
                    'updated_at' => $this->visit->updated_at
                ]
            ];
        } else {
            // Visit not found
            return [
                'status' => 'error',
                'message' => 'Visit not found'
            ];
        }
    }
    
    /**
     * Create a new visit
     * 
     * @param array $data Visit data
     * @return array Response with status and data
     */
    public function createVisit($data) {
        // Validate required fields
        $requiredFields = ['patient_id', 'visit_type', 'chief_complaint', 'created_by'];
        $missingFields = $this->validator->validateRequired($data, $requiredFields);
        
        if (!empty($missingFields)) {
            return [
                'status' => 'error',
                'message' => 'Missing required fields: ' . implode(', ', $missingFields)
            ];
        }
        
        // Validate patient ID
        if (!$this->patient->readOne($data['patient_id'])) {
            return [
                'status' => 'error',
                'message' => 'Invalid patient ID'
            ];
        }
        
        // Set visit properties
        $this->visit->patient_id = $data['patient_id'];
        $this->visit->visit_type = $data['visit_type'];
        $this->visit->chief_complaint = $data['chief_complaint'];
        $this->visit->status = $data['status'] ?? 'Waiting';
        $this->visit->attending_doctor = $data['attending_doctor'] ?? null;
        $this->visit->created_by = $data['created_by'];
        
        // Create visit
        if ($this->visit->create()) {
            // Return response
            return [
                'status' => 'success',
                'message' => 'Visit created successfully',
                'data' => [
                    'id' => $this->visit->id,
                    'patient_id' => $this->visit->patient_id,
                    'patient_name' => $this->patient->getFullName(),
                    'visit_type' => $this->visit->visit_type,
                    'status' => $this->visit->status
                ]
            ];
        } else {
            // Failed to create visit
            return [
                'status' => 'error',
                'message' => 'Failed to create visit'
            ];
        }
    }
    
    /**
     * Update a visit
     * 
     * @param int $id Visit ID
     * @param array $data Visit data
     * @return array Response with status and data
     */
    public function updateVisit($id, $data) {
        // Validate ID
        if (!$this->validator->validateId($id)) {
            return [
                'status' => 'error',
                'message' => 'Invalid visit ID'
            ];
        }
        
        // Check if visit exists
        if (!$this->visit->readOne($id)) {
            return [
                'status' => 'error',
                'message' => 'Visit not found'
            ];
        }
        
        // Validate patient ID if provided
        if (isset($data['patient_id']) && !$this->patient->readOne($data['patient_id'])) {
            return [
                'status' => 'error',
                'message' => 'Invalid patient ID'
            ];
        }
        
        // Update visit properties
        $this->visit->patient_id = $data['patient_id'] ?? $this->visit->patient_id;
        $this->visit->visit_type = $data['visit_type'] ?? $this->visit->visit_type;
        $this->visit->chief_complaint = $data['chief_complaint'] ?? $this->visit->chief_complaint;
        $this->visit->status = $data['status'] ?? $this->visit->status;
        $this->visit->attending_doctor = $data['attending_doctor'] ?? $this->visit->attending_doctor;
        
        // Update visit
        if ($this->visit->update()) {
            // Return response
            return [
                'status' => 'success',
                'message' => 'Visit updated successfully',
                'data' => [
                    'id' => $this->visit->id,
                    'patient_id' => $this->visit->patient_id,
                    'visit_type' => $this->visit->visit_type,
                    'status' => $this->visit->status
                ]
            ];
        } else {
            // Failed to update visit
            return [
                'status' => 'error',
                'message' => 'Failed to update visit'
            ];
        }
    }
    
    /**
     * Delete a visit
     * 
     * @param int $id Visit ID
     * @return array Response with status and message
     */
    public function deleteVisit($id) {
        // Validate ID
        if (!$this->validator->validateId($id)) {
            return [
                'status' => 'error',
                'message' => 'Invalid visit ID'
            ];
        }
        
        // Check if visit exists
        if (!$this->visit->readOne($id)) {
            return [
                'status' => 'error',
                'message' => 'Visit not found'
            ];
        }
        
        // Delete visit
        if ($this->visit->delete()) {
            // Return response
            return [
                'status' => 'success',
                'message' => 'Visit deleted successfully'
            ];
        } else {
            // Failed to delete visit
            return [
                'status' => 'error',
                'message' => 'Failed to delete visit'
            ];
        }
    }
    
    /**
     * Get visits for a specific patient
     * 
     * @param int $patientId Patient ID
     * @return array Response with status and data
     */
    public function getPatientVisits($patientId) {
        // Validate patient ID
        if (!$this->validator->validateId($patientId)) {
            return [
                'status' => 'error',
                'message' => 'Invalid patient ID'
            ];
        }
        
        // Check if patient exists
        if (!$this->patient->readOne($patientId)) {
            return [
                'status' => 'error',
                'message' => 'Patient not found'
            ];
        }
        
        // Get visits
        $stmt = $this->visit->getPatientVisits($patientId);
        $visits = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $visits[] = $row;
        }
        
        // Return response
        return [
            'status' => 'success',
            'data' => [
                'patient' => [
                    'id' => $this->patient->id,
                    'nhis_number' => $this->patient->nhis_number,
                    'name' => $this->patient->getFullName()
                ],
                'visits' => $visits
            ]
        ];
    }
    
    /**
     * Get recent visits for dashboard
     * 
     * @param int $limit Number of visits to return
     * @return array Response with status and data
     */
    public function getRecentVisits($limit = 5) {
        // Get recent visits
        $stmt = $this->visit->getRecentVisits($limit);
        $visits = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $visits[] = $row;
        }
        
        // Return response
        return [
            'status' => 'success',
            'data' => $visits
        ];
    }
    
    /**
     * Get visit statistics
     * 
     * @return array Response with status and data
     */
    public function getStatistics() {
        // Get statistics
        $stats = $this->visit->getStatistics();
        
        // Return response
        return [
            'status' => 'success',
            'data' => $stats
        ];
    }
}
?>