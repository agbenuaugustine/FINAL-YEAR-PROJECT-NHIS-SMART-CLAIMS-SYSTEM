<?php
/**
 * Patient Controller
 * 
 * Handles patient-related operations
 */

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../utils/Validator.php';

class PatientController {
    // Database connection and patient model
    private $conn;
    private $patient;
    private $validator;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Get database connection
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // Initialize patient model
        $this->patient = new Patient($this->conn);
        
        // Initialize validator
        $this->validator = new Validator();
    }
    
    /**
     * Get all patients
     * 
     * @param array $params Query parameters
     * @return array Response with status and data
     */
    public function getPatients($params = []) {
        // Extract parameters
        $search = isset($params['search']) ? $params['search'] : null;
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
        
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // Get patients
        $stmt = $this->patient->read($search, $limit, $offset);
        $patients = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $patients[] = $row;
        }
        
        // Get total count for pagination
        $total = $this->patient->count($search);
        $totalPages = ceil($total / $limit);
        
        // Return response
        return [
            'status' => 'success',
            'data' => [
                'patients' => $patients,
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
     * Get a single patient by ID
     * 
     * @param int $id Patient ID
     * @return array Response with status and data
     */
    public function getPatient($id) {
        // Validate ID
        if (!$this->validator->validateId($id)) {
            return [
                'status' => 'error',
                'message' => 'Invalid patient ID'
            ];
        }
        
        // Get patient
        if ($this->patient->readOne($id)) {
            // Return response
            return [
                'status' => 'success',
                'data' => [
                    'id' => $this->patient->id,
                    'nhis_number' => $this->patient->nhis_number,
                    'first_name' => $this->patient->first_name,
                    'last_name' => $this->patient->last_name,
                    'gender' => $this->patient->gender,
                    'date_of_birth' => $this->patient->date_of_birth,
                    'age' => $this->patient->getAge(),
                    'phone' => $this->patient->phone,
                    'address' => $this->patient->address,
                    'emergency_contact' => $this->patient->emergency_contact,
                    'emergency_phone' => $this->patient->emergency_phone,
                    'blood_group' => $this->patient->blood_group,
                    'allergies' => $this->patient->allergies,
                    'created_at' => $this->patient->created_at,
                    'updated_at' => $this->patient->updated_at
                ]
            ];
        } else {
            // Patient not found
            return [
                'status' => 'error',
                'message' => 'Patient not found'
            ];
        }
    }
    
    /**
     * Create a new patient
     * 
     * @param array $data Patient data
     * @return array Response with status and data
     */
    public function createPatient($data) {
        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'gender', 'date_of_birth'];
        $missingFields = $this->validator->validateRequired($data, $requiredFields);
        
        if (!empty($missingFields)) {
            return [
                'status' => 'error',
                'message' => 'Missing required fields: ' . implode(', ', $missingFields)
            ];
        }
        
        // Validate NHIS number if provided
        if (!empty($data['nhis_number'])) {
            if ($this->patient->findByNhisNumber($data['nhis_number'])) {
                return [
                    'status' => 'error',
                    'message' => 'NHIS number already exists'
                ];
            }
        }
        
        // Get user session data for hospital_id and created_by
        $user = $_SESSION['user'] ?? null;
        $hospital_id = $user['hospital_id'] ?? 1; // Default to 1 if no hospital_id
        $created_by = $user['id'] ?? 1; // Default to 1 if no user id
        
        // Set hospital and system properties
        $this->patient->hospital_id = $hospital_id;
        $this->patient->created_by = $created_by;
        
        // Set patient properties (keep old field names for compatibility)
        // NHIS Information
        $this->patient->nhis_number = $data['nhis_number'] ?? null;
        $this->patient->nhis_expiry = $data['nhis_expiry'] ?? null;
        $this->patient->membership_type = $data['membership_type'] ?? null;
        $this->patient->policy_status = $data['policy_status'] ?? 'Pending';
        
        // Personal Information
        $this->patient->title = $data['title'] ?? null;
        $this->patient->first_name = $data['first_name'];
        $this->patient->middle_name = $data['middle_name'] ?? null;
        $this->patient->last_name = $data['last_name'];
        $this->patient->date_of_birth = $data['date_of_birth'];
        $this->patient->gender = $data['gender'];
        $this->patient->marital_status = $data['marital_status'] ?? null;
        $this->patient->occupation = $data['occupation'] ?? null;
        $this->patient->religion = $data['religion'] ?? null;
        
        // Contact Information
        $this->patient->phone_primary = $data['phone_primary'] ?? null;
        $this->patient->phone_secondary = $data['phone_secondary'] ?? null;
        $this->patient->email = $data['email'] ?? null;
        $this->patient->emergency_contact = $data['emergency_contact'] ?? null;
        
        // Address Information
        $this->patient->region = $data['region'] ?? null;
        $this->patient->district = $data['district'] ?? null;
        $this->patient->town_city = $data['town_city'] ?? null;
        $this->patient->postal_address = $data['postal_address'] ?? null;
        $this->patient->residential_address = $data['residential_address'] ?? null;
        $this->patient->landmark = $data['landmark'] ?? null;
        
        // Medical Information
        $this->patient->blood_group = $data['blood_group'] ?? null;
        $this->patient->allergies = $data['allergies'] ?? null;
        $this->patient->chronic_conditions = $data['chronic_conditions'] ?? null;
        $this->patient->emergency_contact_name = $data['emergency_contact_name'] ?? null;
        $this->patient->emergency_contact_relationship = $data['emergency_contact_relationship'] ?? null;
        
        // Create patient
        if ($this->patient->create()) {
            // Return response
            return [
                'status' => 'success',
                'message' => 'Patient created successfully',
                'data' => [
                    'id' => $this->patient->id,
                    'nhis_number' => $this->patient->nhis_number,
                    'first_name' => $this->patient->first_name,
                    'last_name' => $this->patient->last_name,
                    'full_name' => $this->patient->getFullName()
                ]
            ];
        } else {
            // Failed to create patient
            return [
                'status' => 'error',
                'message' => 'Failed to create patient'
            ];
        }
    }
    
    /**
     * Update a patient
     * 
     * @param int $id Patient ID
     * @param array $data Patient data
     * @return array Response with status and data
     */
    public function updatePatient($id, $data) {
        // Validate ID
        if (!$this->validator->validateId($id)) {
            return [
                'status' => 'error',
                'message' => 'Invalid patient ID'
            ];
        }
        
        // Check if patient exists
        if (!$this->patient->readOne($id)) {
            return [
                'status' => 'error',
                'message' => 'Patient not found'
            ];
        }
        
        // Validate NHIS number if changed
        if (!empty($data['nhis_number']) && $data['nhis_number'] !== $this->patient->nhis_number) {
            $tempPatient = new Patient($this->conn);
            if ($tempPatient->findByNhisNumber($data['nhis_number'])) {
                return [
                    'status' => 'error',
                    'message' => 'NHIS number already exists'
                ];
            }
        }
        
        // Update patient properties
        $this->patient->nhis_number = $data['nhis_number'] ?? $this->patient->nhis_number;
        $this->patient->first_name = $data['first_name'] ?? $this->patient->first_name;
        $this->patient->last_name = $data['last_name'] ?? $this->patient->last_name;
        $this->patient->gender = $data['gender'] ?? $this->patient->gender;
        $this->patient->date_of_birth = $data['date_of_birth'] ?? $this->patient->date_of_birth;
        $this->patient->phone = $data['phone'] ?? $this->patient->phone;
        $this->patient->address = $data['address'] ?? $this->patient->address;
        $this->patient->emergency_contact = $data['emergency_contact'] ?? $this->patient->emergency_contact;
        $this->patient->emergency_phone = $data['emergency_phone'] ?? $this->patient->emergency_phone;
        $this->patient->blood_group = $data['blood_group'] ?? $this->patient->blood_group;
        $this->patient->allergies = $data['allergies'] ?? $this->patient->allergies;
        
        // Update patient
        if ($this->patient->update()) {
            // Return response
            return [
                'status' => 'success',
                'message' => 'Patient updated successfully',
                'data' => [
                    'id' => $this->patient->id,
                    'nhis_number' => $this->patient->nhis_number,
                    'first_name' => $this->patient->first_name,
                    'last_name' => $this->patient->last_name,
                    'full_name' => $this->patient->getFullName()
                ]
            ];
        } else {
            // Failed to update patient
            return [
                'status' => 'error',
                'message' => 'Failed to update patient'
            ];
        }
    }
    
    /**
     * Delete a patient
     * 
     * @param int $id Patient ID
     * @return array Response with status and message
     */
    public function deletePatient($id) {
        // Validate ID
        if (!$this->validator->validateId($id)) {
            return [
                'status' => 'error',
                'message' => 'Invalid patient ID'
            ];
        }
        
        // Check if patient exists
        if (!$this->patient->readOne($id)) {
            return [
                'status' => 'error',
                'message' => 'Patient not found'
            ];
        }
        
        // Set patient ID for deletion
        $this->patient->id = $id;
        
        // Delete patient
        if ($this->patient->delete()) {
            // Return response
            return [
                'status' => 'success',
                'message' => 'Patient deleted successfully'
            ];
        } else {
            // Failed to delete patient
            return [
                'status' => 'error',
                'message' => 'Failed to delete patient'
            ];
        }
    }
    
    /**
     * Search for patients by NHIS number or name
     * 
     * @param string $term Search term
     * @return array Response with status and data
     */
    public function searchPatients($term) {
        if (empty($term) || strlen($term) < 2) {
            return [
                'status' => 'error',
                'message' => 'Search term must be at least 2 characters'
            ];
        }
        
        // Get patients
        $stmt = $this->patient->read($term, 10, 0);
        $patients = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $patients[] = [
                'id' => $row['id'],
                'nhis_number' => $row['nhis_number'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'full_name' => $row['first_name'] . ' ' . $row['last_name'],
                'gender' => $row['gender'],
                'date_of_birth' => $row['date_of_birth']
            ];
        }
        
        // Return response
        return [
            'status' => 'success',
            'data' => $patients
        ];
    }
}
?>