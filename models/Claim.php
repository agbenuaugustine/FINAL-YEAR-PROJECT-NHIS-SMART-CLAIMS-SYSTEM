<?php
/**
 * Claim Model
 * 
 * Handles all claim-related database operations
 */
class Claim {
    // Database connection and table names
    private $conn;
    private $table_name = "claims";
    private $items_table = "claim_items";

    // Object properties
    public $id;
    public $visit_id;
    public $claim_number;
    public $total_amount;
    public $status;
    public $submission_date;
    public $approval_date;
    public $payment_date;
    public $rejection_reason;
    public $created_by;
    public $created_at;
    public $updated_at;

    // Additional properties for joins
    public $patient_name;
    public $patient_nhis;
    public $visit_date;
    public $creator_name;
    public $items = [];

    /**
     * Constructor with DB connection
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new claim
     * 
     * @return bool True if created successfully, false otherwise
     */
    public function create() {
        // Generate a unique claim number
        if (empty($this->claim_number)) {
            $this->claim_number = $this->generateClaimNumber();
        }

        // Start transaction
        $this->conn->beginTransaction();

        try {
            $query = "INSERT INTO " . $this->table_name . "
                    (visit_id, claim_number, total_amount, status, created_by)
                    VALUES
                    (?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            
            // Sanitize input
            $this->visit_id = htmlspecialchars(strip_tags($this->visit_id));
            $this->claim_number = htmlspecialchars(strip_tags($this->claim_number));
            $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->created_by = htmlspecialchars(strip_tags($this->created_by));
            
            // Bind parameters
            $stmt->bindParam(1, $this->visit_id);
            $stmt->bindParam(2, $this->claim_number);
            $stmt->bindParam(3, $this->total_amount);
            $stmt->bindParam(4, $this->status);
            $stmt->bindParam(5, $this->created_by);
            
            // Execute query
            $stmt->execute();
            $this->id = $this->conn->lastInsertId();
            
            // If we have items, insert them
            if (!empty($this->items)) {
                $this->addClaimItems($this->items);
            }
            
            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            error_log("Error creating claim: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add items to a claim
     * 
     * @param array $items Array of claim items
     * @return bool True if items added successfully, false otherwise
     */
    public function addClaimItems($items) {
        $query = "INSERT INTO " . $this->items_table . "
                (claim_id, item_type, item_id, quantity, unit_price, total_price, nhis_covered)
                VALUES
                (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($items as $item) {
            // Sanitize input
            $claim_id = htmlspecialchars(strip_tags($this->id));
            $item_type = htmlspecialchars(strip_tags($item['item_type']));
            $item_id = htmlspecialchars(strip_tags($item['item_id']));
            $quantity = htmlspecialchars(strip_tags($item['quantity']));
            $unit_price = htmlspecialchars(strip_tags($item['unit_price']));
            $total_price = htmlspecialchars(strip_tags($item['total_price']));
            $nhis_covered = isset($item['nhis_covered']) ? $item['nhis_covered'] : true;
            
            // Bind parameters
            $stmt->bindParam(1, $claim_id);
            $stmt->bindParam(2, $item_type);
            $stmt->bindParam(3, $item_id);
            $stmt->bindParam(4, $quantity);
            $stmt->bindParam(5, $unit_price);
            $stmt->bindParam(6, $total_price);
            $stmt->bindParam(7, $nhis_covered);
            
            // Execute query
            $stmt->execute();
        }
        
        // Update total amount
        $this->updateTotalAmount();
        
        return true;
    }

    /**
     * Update the total amount of a claim based on its items
     * 
     * @return bool True if updated successfully, false otherwise
     */
    public function updateTotalAmount() {
        $query = "SELECT SUM(total_price) as total FROM " . $this->items_table . "
                  WHERE claim_id = ? AND nhis_covered = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $row['total'] ?? 0;
        
        $query = "UPDATE " . $this->table_name . "
                  SET total_amount = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $total);
        $stmt->bindParam(2, $this->id);
        
        if ($stmt->execute()) {
            $this->total_amount = $total;
            return true;
        }
        
        return false;
    }

    /**
     * Get a single claim by ID with all related information
     * 
     * @param int $id Claim ID
     * @return bool True if claim found, false otherwise
     */
    public function readOne($id) {
        $query = "SELECT c.*, 
                  CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                  p.nhis_number as patient_nhis,
                  v.visit_date,
                  u.full_name as creator_name
                  FROM " . $this->table_name . " c
                  JOIN visits v ON c.visit_id = v.id
                  JOIN patients p ON v.patient_id = p.id
                  JOIN users u ON c.created_by = u.id
                  WHERE c.id = ?
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->visit_id = $row['visit_id'];
            $this->claim_number = $row['claim_number'];
            $this->total_amount = $row['total_amount'];
            $this->status = $row['status'];
            $this->submission_date = $row['submission_date'];
            $this->approval_date = $row['approval_date'];
            $this->payment_date = $row['payment_date'];
            $this->rejection_reason = $row['rejection_reason'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->patient_name = $row['patient_name'];
            $this->patient_nhis = $row['patient_nhis'];
            $this->visit_date = $row['visit_date'];
            $this->creator_name = $row['creator_name'];
            
            // Get claim items
            $this->items = $this->getClaimItems();
            
            return true;
        }
        
        return false;
    }

    /**
     * Get claim items
     * 
     * @return array Array of claim items
     */
    public function getClaimItems() {
        $items = [];
        
        $query = "SELECT ci.*, 
                  CASE 
                    WHEN ci.item_type = 'Medication' THEN (SELECT name FROM medications WHERE id = ci.item_id)
                    WHEN ci.item_type = 'Lab Test' THEN (SELECT name FROM lab_tests WHERE id = ci.item_id)
                    WHEN ci.item_type = 'Service' THEN (SELECT name FROM services WHERE id = ci.item_id)
                    ELSE 'Consultation'
                  END as item_name
                  FROM " . $this->items_table . " ci
                  WHERE ci.claim_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $row;
        }
        
        return $items;
    }

    /**
     * Update a claim
     * 
     * @return bool True if updated successfully, false otherwise
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    status = :status,
                    submission_date = :submission_date,
                    approval_date = :approval_date,
                    payment_date = :payment_date,
                    rejection_reason = :rejection_reason
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->rejection_reason = htmlspecialchars(strip_tags($this->rejection_reason));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':submission_date', $this->submission_date);
        $stmt->bindParam(':approval_date', $this->approval_date);
        $stmt->bindParam(':payment_date', $this->payment_date);
        $stmt->bindParam(':rejection_reason', $this->rejection_reason);
        $stmt->bindParam(':id', $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Get all claims with related information
     * 
     * @param string $search Optional search term
     * @param string $status Optional status filter
     * @param int $limit Optional limit for pagination
     * @param int $offset Optional offset for pagination
     * @return PDOStatement Result set
     */
    public function read($search = null, $status = null, $limit = null, $offset = null) {
        $query = "SELECT c.*, 
                  CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                  p.nhis_number as patient_nhis,
                  v.visit_date,
                  u.full_name as creator_name
                  FROM " . $this->table_name . " c
                  JOIN visits v ON c.visit_id = v.id
                  JOIN patients p ON v.patient_id = p.id
                  JOIN users u ON c.created_by = u.id
                  WHERE 1=1";
        
        // Add search condition if provided
        if ($search) {
            $query .= " AND (c.claim_number LIKE :search 
                      OR p.nhis_number LIKE :search 
                      OR p.first_name LIKE :search 
                      OR p.last_name LIKE :search
                      OR CONCAT(p.first_name, ' ', p.last_name) LIKE :search)";
        }
        
        // Add status filter if provided
        if ($status) {
            $query .= " AND c.status = :status";
        }
        
        $query .= " ORDER BY c.created_at DESC";
        
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
        
        // Bind status parameter if provided
        if ($status) {
            $stmt->bindParam(':status', $status);
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
     * Count total claims
     * 
     * @param string $search Optional search term
     * @param string $status Optional status filter
     * @return int Total number of claims
     */
    public function count($search = null, $status = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " c
                  JOIN visits v ON c.visit_id = v.id
                  JOIN patients p ON v.patient_id = p.id
                  WHERE 1=1";
        
        // Add search condition if provided
        if ($search) {
            $query .= " AND (c.claim_number LIKE :search 
                      OR p.nhis_number LIKE :search 
                      OR p.first_name LIKE :search 
                      OR p.last_name LIKE :search
                      OR CONCAT(p.first_name, ' ', p.last_name) LIKE :search)";
        }
        
        // Add status filter if provided
        if ($status) {
            $query .= " AND c.status = :status";
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind search parameter if provided
        if ($search) {
            $searchTerm = "%{$search}%";
            $stmt->bindParam(':search', $searchTerm);
        }
        
        // Bind status parameter if provided
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$row['total'];
    }

    /**
     * Delete a claim and its items
     * 
     * @return bool True if deleted successfully, false otherwise
     */
    public function delete() {
        // Start transaction
        $this->conn->beginTransaction();

        try {
            // Delete claim items first
            $query = "DELETE FROM " . $this->items_table . " WHERE claim_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();
            
            // Delete the claim
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();
            
            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            error_log("Error deleting claim: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Submit a claim (change status to Submitted and set submission date)
     * 
     * @return bool True if submitted successfully, false otherwise
     */
    public function submit() {
        $query = "UPDATE " . $this->table_name . "
                SET status = 'Submitted', submission_date = NOW()
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            $this->status = 'Submitted';
            $this->submission_date = date('Y-m-d H:i:s');
            return true;
        }
        
        return false;
    }

    /**
     * Approve a claim (change status to Approved and set approval date)
     * 
     * @return bool True if approved successfully, false otherwise
     */
    public function approve() {
        $query = "UPDATE " . $this->table_name . "
                SET status = 'Approved', approval_date = NOW()
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            $this->status = 'Approved';
            $this->approval_date = date('Y-m-d H:i:s');
            return true;
        }
        
        return false;
    }

    /**
     * Reject a claim (change status to Rejected and set rejection reason)
     * 
     * @param string $reason Rejection reason
     * @return bool True if rejected successfully, false otherwise
     */
    public function reject($reason) {
        $query = "UPDATE " . $this->table_name . "
                SET status = 'Rejected', rejection_reason = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $reason);
        $stmt->bindParam(2, $this->id);
        
        if ($stmt->execute()) {
            $this->status = 'Rejected';
            $this->rejection_reason = $reason;
            return true;
        }
        
        return false;
    }

    /**
     * Mark a claim as paid (change status to Paid and set payment date)
     * 
     * @return bool True if marked as paid successfully, false otherwise
     */
    public function markAsPaid() {
        $query = "UPDATE " . $this->table_name . "
                SET status = 'Paid', payment_date = NOW()
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            $this->status = 'Paid';
            $this->payment_date = date('Y-m-d H:i:s');
            return true;
        }
        
        return false;
    }

    /**
     * Generate a unique claim number
     * 
     * @return string Unique claim number
     */
    private function generateClaimNumber() {
        $prefix = 'NHIS-';
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        
        // Get the count of claims for today
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                  WHERE DATE(created_at) = CURDATE()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $row['count'] + 1;
        
        // Format the count with leading zeros
        $countFormatted = str_pad($count, 4, '0', STR_PAD_LEFT);
        
        // Generate the claim number
        $claimNumber = $prefix . $year . $month . $day . '-' . $countFormatted;
        
        return $claimNumber;
    }

    /**
     * Get claim statistics
     * 
     * @return array Statistics about claims
     */
    public function getStatistics() {
        $stats = [];
        
        // Total claims by status
        $query = "SELECT status, COUNT(*) as count, SUM(total_amount) as total
                  FROM " . $this->table_name . "
                  GROUP BY status";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $stats['by_status'] = [];
        $stats['total_amount'] = 0;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats['by_status'][$row['status']] = [
                'count' => (int)$row['count'],
                'amount' => (float)$row['total']
            ];
            
            if ($row['status'] == 'Approved' || $row['status'] == 'Paid') {
                $stats['total_amount'] += (float)$row['total'];
            }
        }
        
        // Claims this month
        $query = "SELECT COUNT(*) as count, SUM(total_amount) as total
                  FROM " . $this->table_name . "
                  WHERE MONTH(created_at) = MONTH(CURDATE())
                  AND YEAR(created_at) = YEAR(CURDATE())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['this_month'] = [
            'count' => (int)$row['count'],
            'amount' => (float)$row['total']
        ];
        
        // Claims pending submission
        $query = "SELECT COUNT(*) as count
                  FROM " . $this->table_name . "
                  WHERE status = 'Draft'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['pending_submission'] = (int)$row['count'];
        
        return $stats;
    }
}
?>