-- Claims Processing Tables for Smart Claims NHIS System
-- Note: Claims and claim_items tables already exist in enhanced_schema_safe.sql
-- This file adds additional columns and tables if needed

-- Add additional columns to existing claims table if they don't exist
ALTER TABLE claims 
ADD COLUMN IF NOT EXISTS claim_type ENUM('OPD', 'Emergency', 'Maternity', 'Specialist') DEFAULT 'OPD' AFTER claim_number,
ADD COLUMN IF NOT EXISTS priority ENUM('Normal', 'Urgent', 'Emergency') DEFAULT 'Normal' AFTER claim_type,
ADD COLUMN IF NOT EXISTS patient_amount DECIMAL(10,2) DEFAULT 0.00 AFTER nhis_amount,
ADD COLUMN IF NOT EXISTS additional_notes TEXT AFTER patient_amount,
ADD COLUMN IF NOT EXISTS created_by INT AFTER updated_at,
ADD COLUMN IF NOT EXISTS submitted_at TIMESTAMP NULL AFTER created_by,
ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP NULL AFTER submitted_at,
ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(100) NULL AFTER payment_date;

-- Add foreign key for created_by if not exists
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints 
                  WHERE table_schema = DATABASE() 
                  AND table_name = 'claims' 
                  AND constraint_name = 'fk_claims_created_by');

SET @sql = IF(@fk_exists = 0, 
              'ALTER TABLE claims ADD CONSTRAINT fk_claims_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL', 
              'SELECT "FK already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add additional columns to existing claim_items table if they don't exist
ALTER TABLE claim_items 
ADD COLUMN IF NOT EXISTS description VARCHAR(255) AFTER item_name,
ADD COLUMN IF NOT EXISTS unit_cost DECIMAL(10,2) DEFAULT 0.00 AFTER quantity,
ADD COLUMN IF NOT EXISTS amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER unit_cost,
ADD COLUMN IF NOT EXISTS nhia_code VARCHAR(20) NULL AFTER amount,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER nhia_code;

-- Create nhia_tariffs table for standard NHIA rates
CREATE TABLE IF NOT EXISTS nhia_tariffs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_code VARCHAR(20) UNIQUE NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    category ENUM('OPD', 'Laboratory', 'Pharmacy', 'Procedure', 'Administrative') NOT NULL,
    unit_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    effective_date DATE NOT NULL,
    expiry_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_service_code (service_code),
    INDEX idx_category (category),
    INDEX idx_active (is_active)
);

-- Create claim_audit_log table for tracking changes
CREATE TABLE IF NOT EXISTS claim_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NULL,
    notes TEXT NULL,
    performed_by INT NOT NULL,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_claim_id (claim_id),
    INDEX idx_performed_at (performed_at)
);

-- Insert default NHIA tariffs
INSERT INTO nhia_tariffs (service_code, service_name, category, unit_cost, effective_date) VALUES
('OPD-001', 'General OPD Consultation', 'OPD', 45.00, '2024-01-01'),
('OPD-002', 'Specialist Consultation', 'OPD', 85.00, '2024-01-01'),
('LAB-001', 'Full Blood Count', 'Laboratory', 22.00, '2024-01-01'),
('LAB-002', 'Malaria Test', 'Laboratory', 15.00, '2024-01-01'),
('LAB-003', 'Urine Analysis', 'Laboratory', 18.00, '2024-01-01'),
('LAB-004', 'Blood Sugar Test', 'Laboratory', 12.00, '2024-01-01'),
('LAB-005', 'Hepatitis B Test', 'Laboratory', 35.00, '2024-01-01'),
('LAB-006', 'HIV Test', 'Laboratory', 25.00, '2024-01-01'),
('PROC-001', 'Wound Dressing', 'Procedure', 25.00, '2024-01-01'),
('PROC-002', 'Injection Administration', 'Procedure', 15.00, '2024-01-01'),
('PROC-003', 'Blood Pressure Check', 'Procedure', 8.00, '2024-01-01'),
('ADMIN-001', 'Administrative Fee', 'Administrative', 8.00, '2024-01-01'),
('ADMIN-002', 'Registration Fee', 'Administrative', 5.00, '2024-01-01');

-- Add some sample claims for demonstration
INSERT INTO claims (hospital_id, claim_number, visit_id, claim_type, total_amount, nhia_amount, patient_amount, status, submitted_by, created_by) 
SELECT 
    v.hospital_id,
    CONCAT('CLM-', DATE_FORMAT(NOW(), '%Y%m'), '-', LPAD(ROW_NUMBER() OVER (ORDER BY v.id), 6, '0')),
    v.id,
    'OPD',
    ROUND(RAND() * 200 + 100, 2) as total_amt,
    ROUND((RAND() * 200 + 100) * 0.9, 2) as nhia_amt,
    ROUND((RAND() * 200 + 100) * 0.1, 2) as patient_amt,
    CASE 
        WHEN RAND() < 0.3 THEN 'Draft'
        WHEN RAND() < 0.6 THEN 'Submitted'
        WHEN RAND() < 0.8 THEN 'Approved'
        ELSE 'Paid'
    END,
    COALESCE(v.created_by, 1),
    COALESCE(v.created_by, 1)
FROM visits v 
JOIN patients p ON v.patient_id = p.id 
WHERE p.nhis_number IS NOT NULL 
AND p.nhis_number != '' 
AND v.status = 'Completed'
LIMIT 10;