-- Run these queries to check your table structures

-- 1. Check hospitals table structure
DESCRIBE hospitals;

-- 2. Check users table structure  
DESCRIBE users;

-- 3. Check if users table has hospital_id column
SHOW COLUMNS FROM users LIKE 'hospital_id';

-- 4. If users table doesn't have hospital_id, run this:
-- ALTER TABLE users ADD COLUMN hospital_id INT(11) AFTER id;

-- 5. Check constraints
SHOW CREATE TABLE hospitals;
SHOW CREATE TABLE users;