-- Clean up duplicate indexes on hospital_id
-- Run these one by one:

-- 1. Drop the regular index (keep the unique one)
DROP INDEX idx_hospital_id ON hospitals;

-- 2. Verify the table structure
DESCRIBE hospitals;

-- 3. Check if users table exists and structure
DESCRIBE users;

-- 4. If users table doesn't have hospital_id column, add it:
-- ALTER TABLE users ADD COLUMN hospital_id INT(11) AFTER id;

-- 5. Test a simple insert to see if it works
-- INSERT INTO hospitals (hospital_name, hospital_code, hospital_id, hospital_type, hospital_category, primary_contact_person, primary_contact_email, primary_contact_phone, region, district, town_city, postal_address, registration_status, is_active) 
-- VALUES ('Test Hospital', 'TEST123', 'HSP123456789', 'Private', 'Clinic', 'Test Person', 'test@example.com', '1234567890', 'Greater Accra', 'Accra Metro', 'Accra', 'P.O. Box 123', 'Pending', 1);