-- SQL to add hospital_id column to hospitals table (HANDLES EXISTING DATA)
-- Run these queries ONE BY ONE in your database management tool (phpMyAdmin, MySQL Workbench, etc.)

-- Step 1: Add the column as nullable first
ALTER TABLE hospitals 
ADD COLUMN hospital_id VARCHAR(50) 
AFTER hospital_code;

-- Step 2: Update existing records with unique hospital_id values
-- This generates unique IDs for existing hospitals
UPDATE hospitals 
SET hospital_id = CONCAT('HSP', LPAD(id, 6, '0'), LPAD(FLOOR(RAND() * 999) + 100, 3, '0'))
WHERE hospital_id IS NULL OR hospital_id = '';

-- Step 3: Now make it NOT NULL and UNIQUE
ALTER TABLE hospitals 
MODIFY COLUMN hospital_id VARCHAR(50) NOT NULL;

-- Step 4: Add unique constraint
ALTER TABLE hospitals 
ADD CONSTRAINT uk_hospital_id UNIQUE (hospital_id);

-- Step 5: Add an index for better performance (optional)
CREATE INDEX idx_hospital_id ON hospitals(hospital_id);

-- Step 6: Add a comment to the column (optional)
ALTER TABLE hospitals 
MODIFY COLUMN hospital_id VARCHAR(50) NOT NULL 
COMMENT 'Auto-generated unique hospital identifier';

-- Verification query - Run this to check the results
SELECT id, hospital_name, hospital_code, hospital_id FROM hospitals;