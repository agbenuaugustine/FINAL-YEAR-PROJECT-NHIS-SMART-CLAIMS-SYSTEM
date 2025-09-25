<?php
/**
 * Service Controller
 * 
 * Handles service-related operations for requisitions
 */

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../models/Visit.php';
require_once __DIR__ . '/../utils/Validator.php';

class ServiceController {
    // Database connection and models
    private $conn;
    private $patient;
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
        $this->patient = new Patient($this->conn);
        $this->visit = new Visit($this->conn);
        
        // Initialize validator
        $this->validator = new Validator();
    }
    
    /**
     * Search patients/clients
     * 
     * @param string $searchTerm Search term (NHIS, name, phone)
     * @return array Response with status and data
     */
    public function searchPatients($searchTerm) {
        // Validate search term
        if (strlen($searchTerm) < 3) {
            return [
                'status' => 'error',
                'message' => 'Search term must be at least 3 characters'
            ];
        }
        
        try {
            $query = "SELECT id, nhis_number, first_name, middle_name, last_name, 
                            date_of_birth, gender, phone_primary, membership_type, policy_status
                     FROM patients 
                     WHERE nhis_number LIKE ? 
                        OR CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) LIKE ?
                        OR phone_primary LIKE ?
                        OR first_name LIKE ?
                        OR last_name LIKE ?
                     ORDER BY last_name, first_name
                     LIMIT 10";
            
            $stmt = $this->conn->prepare($query);
            $searchPattern = '%' . $searchTerm . '%';
            $stmt->bindParam(1, $searchPattern);
            $stmt->bindParam(2, $searchPattern);
            $stmt->bindParam(3, $searchPattern);
            $stmt->bindParam(4, $searchPattern);
            $stmt->bindParam(5, $searchPattern);
            $stmt->execute();
            
            $patients = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Calculate age
                $birthDate = new DateTime($row['date_of_birth']);
                $now = new DateTime();
                $age = $now->diff($birthDate)->y;
                
                // Format full name
                $fullName = trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']);
                
                $patients[] = [
                    'id' => $row['id'],
                    'nhis' => $row['nhis_number'],
                    'name' => $fullName,
                    'age' => $age . ' years',
                    'gender' => $row['gender'],
                    'phone' => $row['phone_primary'],
                    'membership_type' => $row['membership_type'],
                    'policy_status' => $row['policy_status']
                ];
            }
            
            return [
                'status' => 'success',
                'data' => $patients,
                'count' => count($patients)
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Search failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all services by category
     * 
     * @param string $category Service category (OPD, Laboratory, Pharmacy, etc.)
     * @return array Response with status and data
     */
    public function getServicesByCategory($category = null) {
        try {
            $query = "SELECT id, name, category, subcategory, description, 
                            nhis_covered, nhis_tariff, private_price, 
                            requires_approval, estimated_duration, department
                     FROM services 
                     WHERE is_active = 1";
            
            $params = [];
            if ($category) {
                $query .= " AND category = ?";
                $params[] = $category;
            }
            
            $query .= " ORDER BY category, name";
            
            $stmt = $this->conn->prepare($query);
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key + 1, $value);
                }
            }
            $stmt->execute();
            
            $services = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $services[] = [
                    'id' => $row['id'],
                    'code' => 'SVC' . str_pad($row['id'], 3, '0', STR_PAD_LEFT),
                    'name' => $row['name'],
                    'category' => $row['category'],
                    'subcategory' => $row['subcategory'],
                    'description' => $row['description'],
                    'nhis_covered' => (bool)$row['nhis_covered'],
                    'tariff' => (float)($row['nhis_covered'] ? $row['nhis_tariff'] : $row['private_price']),
                    'nhis_tariff' => (float)$row['nhis_tariff'],
                    'private_price' => (float)$row['private_price'],
                    'requires_approval' => (bool)$row['requires_approval'],
                    'estimated_duration' => $row['estimated_duration'],
                    'department' => $row['department']
                ];
            }
            
            return [
                'status' => 'success',
                'data' => $services,
                'count' => count($services)
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to load services: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get medications for pharmacy services
     * 
     * @return array Response with status and data
     */
    public function getMedications() {
        try {
            $query = "SELECT id, name, category, generic_name, description, 
                            dosage_form, strength, manufacturer,
                            nhis_covered, unit_price, stock_level
                     FROM medications 
                     WHERE stock_level > 0
                     ORDER BY name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $medications = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $medications[] = [
                    'id' => $row['id'],
                    'code' => 'MED' . str_pad($row['id'], 3, '0', STR_PAD_LEFT),
                    'name' => $row['name'] . ' ' . $row['strength'],
                    'category' => $row['category'],
                    'generic_name' => $row['generic_name'],
                    'description' => $row['description'],
                    'dosage_form' => $row['dosage_form'],
                    'strength' => $row['strength'],
                    'manufacturer' => $row['manufacturer'],
                    'nhis_covered' => (bool)$row['nhis_covered'],
                    'tariff' => (float)$row['unit_price'],
                    'stock_level' => $row['stock_level']
                ];
            }
            
            return [
                'status' => 'success',
                'data' => $medications,
                'count' => count($medications)
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to load medications: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get lab tests
     * 
     * @return array Response with status and data
     */
    public function getLabTests() {
        try {
            $query = "SELECT id, name, category, sample_type, description, 
                            preparation_instructions, normal_range, turnaround_time,
                            requires_fasting, nhis_covered, price
                     FROM lab_tests 
                     ORDER BY category, name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $labTests = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $labTests[] = [
                    'id' => $row['id'],
                    'code' => 'LAB' . str_pad($row['id'], 3, '0', STR_PAD_LEFT),
                    'name' => $row['name'],
                    'category' => $row['category'],
                    'sample_type' => $row['sample_type'],
                    'description' => $row['description'],
                    'preparation_instructions' => $row['preparation_instructions'],
                    'normal_range' => $row['normal_range'],
                    'turnaround_time' => $row['turnaround_time'],
                    'requires_fasting' => (bool)$row['requires_fasting'],
                    'nhis_covered' => (bool)$row['nhis_covered'],
                    'tariff' => (float)$row['price']
                ];
            }
            
            return [
                'status' => 'success',
                'data' => $labTests,
                'count' => count($labTests)
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to load lab tests: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a service requisition (visit with service orders)
     * 
     * @param array $data Requisition data
     * @return array Response with status and data
     */
    public function createServiceRequisition($data) {
        // Validate required fields
        $requiredFields = ['patient_id', 'visit_type', 'services', 'created_by'];
        $missingFields = $this->validator->validateRequired($data, $requiredFields);
        
        if (!empty($missingFields)) {
            return [
                'status' => 'error',
                'message' => 'Missing required fields: ' . implode(', ', $missingFields)
            ];
        }
        
        if (empty($data['services']) || !is_array($data['services'])) {
            return [
                'status' => 'error',
                'message' => 'At least one service must be selected'
            ];
        }
        
        try {
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Create visit record
            $visitQuery = "INSERT INTO visits 
                          (patient_id, visit_type, priority, chief_complaint, status, created_by, visit_date)
                          VALUES (?, ?, ?, ?, 'Waiting', ?, ?)";
            
            $visitStmt = $this->conn->prepare($visitQuery);
            $visitStmt->bindParam(1, $data['patient_id']);
            $visitStmt->bindParam(2, $data['visit_type']);
            $visitStmt->bindParam(3, $data['priority'] ?? 'Routine');
            $visitStmt->bindParam(4, $data['chief_complaint'] ?? 'Service requisition');
            $visitStmt->bindParam(5, $data['created_by']);
            $visitStmt->bindParam(6, $data['visit_date'] ?? date('Y-m-d H:i:s'));
            
            if (!$visitStmt->execute()) {
                throw new Exception('Failed to create visit record');
            }
            
            $visitId = $this->conn->lastInsertId();
            
            // Generate visit number
            $visitNumber = 'V' . date('Y') . str_pad($visitId, 6, '0', STR_PAD_LEFT);
            $updateVisitQuery = "UPDATE visits SET visit_number = ? WHERE id = ?";
            $updateVisitStmt = $this->conn->prepare($updateVisitQuery);
            $updateVisitStmt->bindParam(1, $visitNumber);
            $updateVisitStmt->bindParam(2, $visitId);
            $updateVisitStmt->execute();
            
            // Create service orders
            $totalAmount = 0;
            $serviceOrderQuery = "INSERT INTO service_orders 
                                 (visit_id, service_id, notes, ordered_by, ordered_at)
                                 VALUES (?, ?, ?, ?, NOW())";
            $serviceOrderStmt = $this->conn->prepare($serviceOrderQuery);
            
            foreach ($data['services'] as $service) {
                $serviceId = $service['id'] ?? null;
                if (!$serviceId) continue;
                
                $serviceOrderStmt->bindParam(1, $visitId);
                $serviceOrderStmt->bindParam(2, $serviceId);
                $serviceOrderStmt->bindParam(3, $service['notes'] ?? null);
                $serviceOrderStmt->bindParam(4, $data['created_by']);
                
                if (!$serviceOrderStmt->execute()) {
                    throw new Exception('Failed to create service order');
                }
                
                $totalAmount += (float)($service['tariff'] ?? 0);
            }
            
            // Commit transaction
            $this->conn->commit();
            
            return [
                'status' => 'success',
                'message' => 'Service requisition created successfully',
                'data' => [
                    'visit_id' => $visitId,
                    'visit_number' => $visitNumber,
                    'total_amount' => $totalAmount,
                    'services_count' => count($data['services'])
                ]
            ];
            
        } catch (Exception $e) {
            // Rollback transaction
            $this->conn->rollback();
            
            return [
                'status' => 'error',
                'message' => 'Failed to create service requisition: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get recent service requisitions
     * 
     * @param int $limit Number of records to return
     * @return array Response with status and data
     */
    public function getRecentRequisitions($limit = 10) {
        try {
            $query = "SELECT v.id, v.visit_number, v.visit_date, v.visit_type, v.status,
                            p.first_name, p.middle_name, p.last_name, p.nhis_number,
                            u.full_name as created_by_name,
                            COUNT(so.id) as services_count
                     FROM visits v
                     LEFT JOIN patients p ON v.patient_id = p.id
                     LEFT JOIN users u ON v.created_by = u.id
                     LEFT JOIN service_orders so ON v.id = so.visit_id
                     WHERE v.status IN ('Waiting', 'In Progress')
                     GROUP BY v.id, v.visit_number, v.visit_date, v.visit_type, v.status,
                              p.first_name, p.middle_name, p.last_name, p.nhis_number,
                              u.full_name
                     ORDER BY v.created_at DESC
                     LIMIT ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $requisitions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $fullName = trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']);
                
                $requisitions[] = [
                    'id' => $row['id'],
                    'visit_number' => $row['visit_number'],
                    'visit_date' => $row['visit_date'],
                    'visit_type' => $row['visit_type'],
                    'status' => $row['status'],
                    'patient_name' => $fullName,
                    'nhis_number' => $row['nhis_number'],
                    'created_by' => $row['created_by_name'],
                    'services_count' => $row['services_count']
                ];
            }
            
            return [
                'status' => 'success',
                'data' => $requisitions,
                'count' => count($requisitions)
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to load recent requisitions: ' . $e->getMessage()
            ];
        }
    }
}
?>