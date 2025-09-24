-- Migration to fix ICD-10 code length issue
-- This fixes the "Data too long for column 'id'" error in icd10_codes table

USE uenrclai_claims;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Update icd10_codes table to allow longer ICD-10 codes
ALTER TABLE icd10_codes MODIFY COLUMN id VARCHAR(20);

-- Update diagnoses table to match the new length
ALTER TABLE diagnoses MODIFY COLUMN icd10_code VARCHAR(20) NOT NULL;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verify the changes
DESCRIBE icd10_codes;
DESCRIBE diagnoses;

SELECT 'Migration completed successfully - ICD-10 code length increased to VARCHAR(20)' as status;