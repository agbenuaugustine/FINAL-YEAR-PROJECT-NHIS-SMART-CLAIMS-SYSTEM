-- Enhanced Smart Claims Database Schema with Hospital Management and Departments
-- Drop database if exists (for clean installation)
DROP DATABASE IF EXISTS smartclaims;

-- Create database
CREATE DATABASE smartclaims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE smartclaims;

-- Hospitals table
CREATE TABLE hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_name VARCHAR(150) NOT NULL,
    hospital_code VARCHAR(20) NOT NULL UNIQUE,
    registration_status ENUM('Pending', 'Approved', 'Rejected', 'Suspended') DEFAULT 'Pending',
    primary_contact_person VARCHAR(100) NOT NULL,
    primary_contact_email VARCHAR(100) NOT NULL,
    primary_contact_phone VARCHAR(20) NOT NULL,
    region VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    town_city VARCHAR(50) NOT NULL,
    postal_address TEXT,
    hospital_type ENUM('Government', 'Private', 'Mission', 'Quasi-Government') NOT NULL,
    hospital_category ENUM('Teaching Hospital', 'Regional Hospital', 'District Hospital', 'Polyclinic', 'Health Centre', 'CHPS', 'Clinic') NOT NULL,
    nhia_accreditation_number VARCHAR(50),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approval_date TIMESTAMP NULL,
    rejection_reason TEXT,
    suspension_reason TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Departments table
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    department_name VARCHAR(100) NOT NULL,
    department_code VARCHAR(20) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dept_per_hospital (hospital_id, department_code)
);

-- Enhanced Users table with hospital and department relationships
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT,
    department_id INT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('superadmin', 'hospital_admin', 'department_head', 'admin', 'doctor', 'nurse', 'pharmacist', 'lab_technician', 'claims_officer', 'receptionist', 'radiologist', 'physiotherapist', 'cashier', 'records_officer', 'finance_officer', 'it_support') NOT NULL,
    employee_id VARCHAR(20),
    phone VARCHAR(20),
    profile_image VARCHAR(255),
    date_of_birth DATE,
    employment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Role permissions table
CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) NOT NULL,
    permission VARCHAR(100) NOT NULL,
    is_granted BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_permission (role, permission)
);

-- User permissions (for custom user-specific permissions)
CREATE TABLE user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    permission VARCHAR(100) NOT NULL,
    is_granted BOOLEAN DEFAULT TRUE,
    granted_by INT NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id),
    UNIQUE KEY unique_user_permission (user_id, permission)
);

-- Enhanced Patients table with hospital tracking
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    nhis_number VARCHAR(20),
    patient_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    other_names VARCHAR(50),
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    date_of_birth DATE NOT NULL,
    phone VARCHAR(15),
    alternate_phone VARCHAR(15),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(15),
    blood_group VARCHAR(5),
    allergies TEXT,
    occupation VARCHAR(100),
    marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed', 'Other'),
    religion VARCHAR(50),
    next_of_kin VARCHAR(100),
    next_of_kin_phone VARCHAR(15),
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_nhis_number (nhis_number),
    INDEX idx_patient_number (patient_number),
    INDEX idx_hospital_id (hospital_id)
);

-- Enhanced Visits table
CREATE TABLE visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    patient_id INT NOT NULL,
    visit_number VARCHAR(20) NOT NULL UNIQUE,
    visit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    visit_type ENUM('OPD', 'Emergency', 'Follow-up', 'Referral', 'Admission', 'Discharge') NOT NULL,
    department_id INT,
    chief_complaint TEXT,
    presenting_complaint TEXT,
    history_of_present_illness TEXT,
    past_medical_history TEXT,
    physical_examination TEXT,
    status ENUM('Waiting', 'In Progress', 'Completed', 'Cancelled', 'Transferred') DEFAULT 'Waiting',
    attending_doctor INT,
    referred_from VARCHAR(100),
    referred_to VARCHAR(100),
    priority ENUM('Low', 'Normal', 'Urgent', 'Critical') DEFAULT 'Normal',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id),
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (attending_doctor) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_visit_number (visit_number),
    INDEX idx_hospital_visit (hospital_id, visit_date)
);

-- Enhanced Vital signs table
CREATE TABLE vital_signs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    temperature DECIMAL(4,1),
    blood_pressure_systolic INT,
    blood_pressure_diastolic INT,
    pulse_rate INT,
    respiratory_rate INT,
    weight DECIMAL(5,2),
    height DECIMAL(5,2),
    bmi DECIMAL(4,2),
    oxygen_saturation INT,
    pain_scale INT,
    notes TEXT,
    recorded_by INT NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- ICD-10 Diagnoses table
CREATE TABLE icd10_codes (
    id VARCHAR(10) PRIMARY KEY,
    description TEXT NOT NULL,
    category VARCHAR(100),
    subcategory VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE
);

-- Enhanced Diagnoses table
CREATE TABLE diagnoses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    icd10_code VARCHAR(10) NOT NULL,
    diagnosis_notes TEXT,
    diagnosis_type ENUM('Primary', 'Secondary', 'Provisional', 'Final', 'Rule Out') NOT NULL,
    severity ENUM('Mild', 'Moderate', 'Severe', 'Critical'),
    diagnosed_by INT NOT NULL,
    diagnosed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (icd10_code) REFERENCES icd10_codes(id),
    FOREIGN KEY (diagnosed_by) REFERENCES users(id)
);

-- Enhanced Medications table
CREATE TABLE medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    generic_name VARCHAR(100),
    brand_name VARCHAR(100),
    description TEXT,
    dosage_form VARCHAR(50),
    strength VARCHAR(50),
    nhis_covered BOOLEAN DEFAULT FALSE,
    unit_price DECIMAL(10,2) NOT NULL,
    manufacturer VARCHAR(100),
    drug_class VARCHAR(100),
    contraindications TEXT,
    side_effects TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Enhanced Prescriptions table
CREATE TABLE prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    medication_id INT NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    route_of_administration VARCHAR(50),
    instructions TEXT,
    quantity INT NOT NULL,
    dispensed_quantity INT DEFAULT 0,
    dispensed BOOLEAN DEFAULT FALSE,
    prescribed_by INT NOT NULL,
    dispensed_by INT,
    prescribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dispensed_at TIMESTAMP NULL,
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (medication_id) REFERENCES medications(id),
    FOREIGN KEY (prescribed_by) REFERENCES users(id),
    FOREIGN KEY (dispensed_by) REFERENCES users(id)
);

-- Enhanced Laboratory tests table
CREATE TABLE lab_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    specimen_type VARCHAR(50),
    nhis_covered BOOLEAN DEFAULT FALSE,
    price DECIMAL(10,2) NOT NULL,
    normal_ranges TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Enhanced Lab orders table
CREATE TABLE lab_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    lab_test_id INT NOT NULL,
    status ENUM('Ordered', 'Specimen Collected', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Ordered',
    results TEXT,
    result_values JSON,
    interpretation TEXT,
    critical_values BOOLEAN DEFAULT FALSE,
    ordered_by INT NOT NULL,
    performed_by INT,
    approved_by INT,
    ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (lab_test_id) REFERENCES lab_tests(id),
    FOREIGN KEY (ordered_by) REFERENCES users(id),
    FOREIGN KEY (performed_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Enhanced Services table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    department VARCHAR(50),
    nhis_covered BOOLEAN DEFAULT FALSE,
    price DECIMAL(10,2) NOT NULL,
    duration_minutes INT,
    requires_appointment BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Enhanced Service orders table
CREATE TABLE service_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    service_id INT NOT NULL,
    status ENUM('Ordered', 'Scheduled', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Ordered',
    scheduled_date TIMESTAMP NULL,
    notes TEXT,
    ordered_by INT NOT NULL,
    performed_by INT,
    ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (ordered_by) REFERENCES users(id),
    FOREIGN KEY (performed_by) REFERENCES users(id)
);

-- Enhanced Claims table
CREATE TABLE claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    visit_id INT NOT NULL,
    claim_number VARCHAR(50) UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL,
    copay_amount DECIMAL(10,2) DEFAULT 0,
    nhis_amount DECIMAL(10,2) NOT NULL,
    status ENUM('Draft', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Paid', 'Partially Paid') DEFAULT 'Draft',
    submission_date TIMESTAMP NULL,
    approval_date TIMESTAMP NULL,
    payment_date TIMESTAMP NULL,
    rejection_reason TEXT,
    reviewer_notes TEXT,
    submitted_by INT NOT NULL,
    reviewed_by INT,
    approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id),
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Enhanced Claim items table
CREATE TABLE claim_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_id INT NOT NULL,
    item_type ENUM('Medication', 'Lab Test', 'Service', 'Consultation', 'Procedure') NOT NULL,
    item_id INT NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    nhis_covered BOOLEAN DEFAULT TRUE,
    copay_percentage DECIMAL(5,2) DEFAULT 0,
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE
);

-- System settings table
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_system_wide BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
    UNIQUE KEY unique_hospital_setting (hospital_id, setting_key)
);

-- Enhanced Audit logs table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    old_values JSON,
    new_values JSON,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_hospital_audit (hospital_id, created_at),
    INDEX idx_user_audit (user_id, created_at),
    INDEX idx_entity_audit (entity_type, entity_id)
);

-- Insert default hospitals (for testing)
INSERT INTO hospitals (hospital_name, hospital_code, registration_status, primary_contact_person, primary_contact_email, primary_contact_phone, region, district, town_city, hospital_type, hospital_category, approval_date) VALUES
('Korle Bu Teaching Hospital', 'KBTH001', 'Approved', 'Dr. John Doe', 'admin@korlebu.edu.gh', '+233 302 665401', 'Greater Accra', 'Accra Metropolis', 'Accra', 'Government', 'Teaching Hospital', NOW()),
('Komfo Anokye Teaching Hospital', 'KATH002', 'Approved', 'Dr. Jane Smith', 'admin@kath.edu.gh', '+233 322 022308', 'Ashanti', 'Kumasi Metropolis', 'Kumasi', 'Government', 'Teaching Hospital', NOW()),
('Ridge Hospital', 'RH003', 'Approved', 'Dr. Michael Johnson', 'admin@ridgehospital.gov.gh', '+233 302 234567', 'Greater Accra', 'Accra Metropolis', 'Accra', 'Government', 'Regional Hospital', NOW());

-- Insert default departments for each hospital
INSERT INTO departments (hospital_id, department_name, department_code, description) VALUES
-- Korle Bu Departments
(1, 'Out Patient Department (OPD)', 'OPD', 'General outpatient services'),
(1, 'Emergency Department', 'EMRG', 'Emergency and trauma care'),
(1, 'Laboratory', 'LAB', 'Clinical laboratory services'),
(1, 'Pharmacy', 'PHARM', 'Pharmaceutical services'),
(1, 'Radiology', 'RAD', 'Imaging and radiology services'),
(1, 'Internal Medicine', 'INT_MED', 'Internal medicine department'),
(1, 'Surgery', 'SURG', 'Surgical department'),
(1, 'Pediatrics', 'PEDS', 'Children healthcare'),
(1, 'Obstetrics & Gynecology', 'OBGYN', 'Women healthcare'),
(1, 'Records', 'REC', 'Medical records management'),
(1, 'Finance', 'FIN', 'Financial services'),
(1, 'Claims Processing', 'CLAIMS', 'NHIS claims management'),
-- KATH Departments
(2, 'Out Patient Department (OPD)', 'OPD', 'General outpatient services'),
(2, 'Emergency Department', 'EMRG', 'Emergency and trauma care'),
(2, 'Laboratory', 'LAB', 'Clinical laboratory services'),
(2, 'Pharmacy', 'PHARM', 'Pharmaceutical services'),
(2, 'Radiology', 'RAD', 'Imaging and radiology services'),
(2, 'Internal Medicine', 'INT_MED', 'Internal medicine department'),
(2, 'Surgery', 'SURG', 'Surgical department'),
(2, 'Pediatrics', 'PEDS', 'Children healthcare'),
(2, 'Obstetrics & Gynecology', 'OBGYN', 'Women healthcare'),
(2, 'Records', 'REC', 'Medical records management'),
(2, 'Finance', 'FIN', 'Financial services'),
(2, 'Claims Processing', 'CLAIMS', 'NHIS claims management'),
-- Ridge Hospital Departments
(3, 'Out Patient Department (OPD)', 'OPD', 'General outpatient services'),
(3, 'Emergency Department', 'EMRG', 'Emergency and trauma care'),
(3, 'Laboratory', 'LAB', 'Clinical laboratory services'),
(3, 'Pharmacy', 'PHARM', 'Pharmaceutical services'),
(3, 'Radiology', 'RAD', 'Imaging and radiology services'),
(3, 'Internal Medicine', 'INT_MED', 'Internal medicine department'),
(3, 'Surgery', 'SURG', 'Surgical department'),
(3, 'Records', 'REC', 'Medical records management'),
(3, 'Finance', 'FIN', 'Financial services'),
(3, 'Claims Processing', 'CLAIMS', 'NHIS claims management');

-- Insert default superadmin user (password: admin123)
INSERT INTO users (username, password, email, full_name, role) 
VALUES ('superadmin', '$2y$10$8zf0SXIrA/UYBk82ovOFWOQA.V6JvW9oVeHn3IVhvC0kIZ5mQYl4e', 'superadmin@smartclaims.com', 'System Super Administrator', 'superadmin');

-- Insert hospital admin users for each hospital
INSERT INTO users (hospital_id, username, password, email, full_name, role, employee_id) VALUES
(1, 'kbth_admin', '$2y$10$8zf0SXIrA/UYBk82ovOFWOQA.V6JvW9oVeHn3IVhvC0kIZ5mQYl4e', 'admin@korlebu.edu.gh', 'KBTH Administrator', 'hospital_admin', 'KBTH001'),
(2, 'kath_admin', '$2y$10$8zf0SXIrA/UYBk82ovOFWOQA.V6JvW9oVeHn3IVhvC0kIZ5mQYl4e', 'admin@kath.edu.gh', 'KATH Administrator', 'hospital_admin', 'KATH001'),
(3, 'ridge_admin', '$2y$10$8zf0SXIrA/UYBk82ovOFWOQA.V6JvW9oVeHn3IVhvC0kIZ5mQYl4e', 'admin@ridgehospital.gov.gh', 'Ridge Hospital Administrator', 'hospital_admin', 'RH001');

-- Insert role permissions
INSERT INTO role_permissions (role, permission) VALUES
-- Superadmin permissions
('superadmin', 'manage_hospitals'),
('superadmin', 'approve_hospitals'),
('superadmin', 'suspend_hospitals'),
('superadmin', 'view_all_data'),
('superadmin', 'manage_system_settings'),
('superadmin', 'view_system_reports'),
('superadmin', 'backup_system'),

-- Hospital Admin permissions
('hospital_admin', 'manage_hospital_settings'),
('hospital_admin', 'manage_departments'),
('hospital_admin', 'manage_users'),
('hospital_admin', 'view_hospital_reports'),
('hospital_admin', 'manage_hospital_data'),
('hospital_admin', 'approve_claims'),

-- Department Head permissions
('department_head', 'manage_department_users'),
('department_head', 'view_department_reports'),
('department_head', 'approve_department_requests'),

-- Common permissions for all roles
('admin', 'view_patients'),
('admin', 'register_patients'),
('admin', 'manage_visits'),
('admin', 'view_reports'),
('doctor', 'view_patients'),
('doctor', 'register_patients'),
('doctor', 'manage_visits'),
('doctor', 'record_vital_signs'),
('doctor', 'make_diagnosis'),
('doctor', 'prescribe_medication'),
('doctor', 'order_lab_tests'),
('nurse', 'view_patients'),
('nurse', 'register_patients'),
('nurse', 'manage_visits'),
('nurse', 'record_vital_signs'),
('pharmacist', 'view_patients'),
('pharmacist', 'dispense_medication'),
('pharmacist', 'manage_inventory'),
('lab_technician', 'view_patients'),
('lab_technician', 'perform_lab_tests'),
('lab_technician', 'manage_lab_orders'),
('claims_officer', 'view_patients'),
('claims_officer', 'process_claims'),
('claims_officer', 'submit_claims'),
('receptionist', 'view_patients'),
('receptionist', 'register_patients'),
('receptionist', 'manage_visits');

-- Insert sample ICD-10 codes
INSERT INTO icd10_codes (id, description, category, subcategory) VALUES
('A00', 'Cholera', 'Infectious diseases', 'Intestinal infectious diseases'),
('E11', 'Type 2 diabetes mellitus', 'Endocrine disorders', 'Diabetes mellitus'),
('I10', 'Essential (primary) hypertension', 'Circulatory system', 'Hypertensive diseases'),
('J18', 'Pneumonia, unspecified organism', 'Respiratory system', 'Pneumonia'),
('M54', 'Dorsalgia', 'Musculoskeletal system', 'Back pain'),
('O00', 'Ectopic pregnancy', 'Pregnancy and childbirth', 'Pregnancy complications'),
('P00', 'Newborn affected by maternal conditions', 'Perinatal period', 'Newborn conditions'),
('Z00', 'General examination without complaint', 'Factors influencing health status', 'Health examination');

-- Insert sample medications
INSERT INTO medications (name, generic_name, dosage_form, strength, nhis_covered, unit_price, drug_class) VALUES
('Paracetamol', 'Acetaminophen', 'Tablet', '500mg', TRUE, 0.50, 'Analgesic'),
('Amoxicillin', 'Amoxicillin', 'Capsule', '500mg', TRUE, 1.20, 'Antibiotic'),
('Metformin', 'Metformin HCl', 'Tablet', '500mg', TRUE, 0.75, 'Anti-diabetic'),
('Lisinopril', 'Lisinopril', 'Tablet', '10mg', TRUE, 1.00, 'ACE Inhibitor'),
('Ibuprofen', 'Ibuprofen', 'Tablet', '400mg', TRUE, 0.60, 'NSAID'),
('Omeprazole', 'Omeprazole', 'Capsule', '20mg', TRUE, 1.50, 'Proton pump inhibitor');

-- Insert sample lab tests
INSERT INTO lab_tests (name, description, category, specimen_type, nhis_covered, price) VALUES
('Complete Blood Count', 'Measures various components of blood', 'Hematology', 'Blood', TRUE, 15.00),
('Blood Glucose', 'Measures blood sugar levels', 'Chemistry', 'Blood', TRUE, 10.00),
('Urinalysis', 'Analysis of urine composition', 'Urinalysis', 'Urine', TRUE, 8.00),
('Liver Function Test', 'Assesses liver function', 'Chemistry', 'Blood', TRUE, 25.00),
('Malaria Test', 'Tests for malaria parasites', 'Parasitology', 'Blood', TRUE, 12.00),
('Typhoid Test', 'Tests for typhoid antibodies', 'Serology', 'Blood', TRUE, 18.00);

-- Insert sample services
INSERT INTO services (name, description, category, department, nhis_covered, price, duration_minutes) VALUES
('General Consultation', 'Consultation with general practitioner', 'Consultation', 'OPD', TRUE, 20.00, 30),
('Specialist Consultation', 'Consultation with specialist', 'Consultation', 'Specialist', TRUE, 40.00, 45),
('Wound Dressing', 'Cleaning and dressing of wounds', 'Procedure', 'OPD', TRUE, 15.00, 20),
('Nebulization', 'Respiratory treatment', 'Procedure', 'OPD', TRUE, 25.00, 15),
('Suturing', 'Stitching of wounds', 'Procedure', 'Emergency', TRUE, 30.00, 30),
('Incision and Drainage', 'Drainage of abscesses', 'Procedure', 'Surgery', TRUE, 35.00, 45);

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_system_wide) VALUES
('system_name', 'Smart Claims NHIS', 'string', 'System name', TRUE),
('default_currency', 'GHS', 'string', 'Default currency', TRUE),
('session_timeout', '1800', 'number', 'Session timeout in seconds', TRUE),
('enable_sms', 'false', 'boolean', 'Enable SMS notifications', TRUE),
('enable_email', 'true', 'boolean', 'Enable email notifications', TRUE),
('backup_frequency', 'daily', 'string', 'Backup frequency', TRUE);