-- Enhanced Smart Claims Database Schema with Hospital Management and Departments
-- Safe version without DROP DATABASE (for environments where DROP DATABASE is disabled)

-- Create database (only if it doesn't exist)
CREATE DATABASE IF NOT EXISTS smartclaims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE smartclaims;

-- Drop tables if they exist (in correct order to handle foreign key constraints)
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS system_settings;
DROP TABLE IF EXISTS claim_items;
DROP TABLE IF EXISTS claims;
DROP TABLE IF EXISTS service_orders;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS lab_orders;
DROP TABLE IF EXISTS lab_tests;
DROP TABLE IF EXISTS prescriptions;
DROP TABLE IF EXISTS medications;
DROP TABLE IF EXISTS diagnoses;
DROP TABLE IF EXISTS icd10_codes;
DROP TABLE IF EXISTS vital_signs;
DROP TABLE IF EXISTS visits;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS user_permissions;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS hospitals;

SET FOREIGN_KEY_CHECKS = 1;

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
    id VARCHAR(20) PRIMARY KEY,
    description TEXT NOT NULL,
    category VARCHAR(100),
    subcategory VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE
);

-- Enhanced Diagnoses table
CREATE TABLE diagnoses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    icd10_code VARCHAR(20) NOT NULL,
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
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    category VARCHAR(50) DEFAULT 'General',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Enhanced Audit logs table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    hospital_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id),
    INDEX idx_user_id (user_id),
    INDEX idx_hospital_id (hospital_id),
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp)
);

-- Insert sample hospitals
INSERT INTO hospitals (hospital_name, hospital_code, registration_status, primary_contact_person, primary_contact_email, primary_contact_phone, region, district, town_city, postal_address, hospital_type, hospital_category, nhia_accreditation_number, approval_date) VALUES
('Korle Bu Teaching Hospital', 'KBTH', 'Approved', 'Dr. Ophelia Dadzie', 'info@kbth.gov.gh', '+233302674516', 'Greater Accra', 'Accra Metropolitan', 'Accra', 'P.O. Box 77, Korle Bu, Accra', 'Government', 'Teaching Hospital', 'NHIA-001-KBTH', NOW()),
('Komfo Anokye Teaching Hospital', 'KATH', 'Approved', 'Prof. Otchere Addai-Mensah', 'info@kath.gov.gh', '+233322022701', 'Ashanti', 'Kumasi Metropolitan', 'Kumasi', 'P.O. Box 1934, Kumasi', 'Government', 'Teaching Hospital', 'NHIA-002-KATH', NOW()),
('Ridge Hospital', 'RH', 'Approved', 'Dr. Aba Folson', 'info@ridgehospital.gov.gh', '+233302231670', 'Greater Accra', 'Accra Metropolitan', 'Accra', 'P.O. Box 9, Ridge, Accra', 'Government', 'Regional Hospital', 'NHIA-003-RH', NOW()),
('Tamale Teaching Hospital', 'TTH', 'Approved', 'Dr. John Mahama', 'info@tth.gov.gh', '+233372022441', 'Northern', 'Tamale Metropolitan', 'Tamale', 'P.O. Box 16, Tamale', 'Government', 'Teaching Hospital', 'NHIA-004-TTH', NOW()),
('Cape Coast Teaching Hospital', 'CCTH', 'Approved', 'Dr. Emmanuel Tinkorang', 'info@ccth.gov.gh', '+233332132631', 'Central', 'Cape Coast Metropolitan', 'Cape Coast', 'P.O. Box 5, Cape Coast', 'Government', 'Teaching Hospital', 'NHIA-005-CCTH', NOW());

-- Insert departments for each hospital
INSERT INTO departments (hospital_id, department_name, department_code, description) VALUES
-- KBTH Departments
(1, 'Out Patient Department', 'OPD', 'General outpatient consultations'),
(1, 'Emergency Department', 'EMERG', 'Emergency and trauma care'),
(1, 'Laboratory', 'LAB', 'Clinical laboratory services'),
(1, 'Pharmacy', 'PHARM', 'Pharmaceutical services'),
(1, 'Radiology', 'RADIO', 'Medical imaging services'),
(1, 'Internal Medicine', 'INTMED', 'Internal medicine consultations'),
(1, 'Surgery', 'SURG', 'Surgical services'),
(1, 'Pediatrics', 'PEDIA', 'Child healthcare services'),
(1, 'Obstetrics & Gynecology', 'OBGYN', 'Maternal and reproductive health'),
(1, 'Records', 'REC', 'Medical records management'),
(1, 'Finance', 'FIN', 'Financial management'),
(1, 'Claims Processing', 'CLAIMS', 'NHIS claims processing'),

-- KATH Departments
(2, 'Out Patient Department', 'OPD', 'General outpatient consultations'),
(2, 'Emergency Department', 'EMERG', 'Emergency and trauma care'),
(2, 'Laboratory', 'LAB', 'Clinical laboratory services'),
(2, 'Pharmacy', 'PHARM', 'Pharmaceutical services'),
(2, 'Radiology', 'RADIO', 'Medical imaging services'),
(2, 'Internal Medicine', 'INTMED', 'Internal medicine consultations'),
(2, 'Surgery', 'SURG', 'Surgical services'),
(2, 'Pediatrics', 'PEDIA', 'Child healthcare services'),
(2, 'Obstetrics & Gynecology', 'OBGYN', 'Maternal and reproductive health'),
(2, 'Records', 'REC', 'Medical records management'),
(2, 'Finance', 'FIN', 'Financial management'),
(2, 'Claims Processing', 'CLAIMS', 'NHIS claims processing'),

-- Ridge Hospital Departments
(3, 'Out Patient Department', 'OPD', 'General outpatient consultations'),
(3, 'Emergency Department', 'EMERG', 'Emergency and trauma care'),
(3, 'Laboratory', 'LAB', 'Clinical laboratory services'),
(3, 'Pharmacy', 'PHARM', 'Pharmaceutical services'),
(3, 'Radiology', 'RADIO', 'Medical imaging services'),
(3, 'Internal Medicine', 'INTMED', 'Internal medicine consultations'),
(3, 'Surgery', 'SURG', 'Surgical services'),
(3, 'Pediatrics', 'PEDIA', 'Child healthcare services'),
(3, 'Obstetrics & Gynecology', 'OBGYN', 'Maternal and reproductive health'),
(3, 'Records', 'REC', 'Medical records management'),
(3, 'Finance', 'FIN', 'Financial management'),
(3, 'Claims Processing', 'CLAIMS', 'NHIS claims processing'),

-- TTH Departments
(4, 'Out Patient Department', 'OPD', 'General outpatient consultations'),
(4, 'Emergency Department', 'EMERG', 'Emergency and trauma care'),
(4, 'Laboratory', 'LAB', 'Clinical laboratory services'),
(4, 'Pharmacy', 'PHARM', 'Pharmaceutical services'),
(4, 'Radiology', 'RADIO', 'Medical imaging services'),
(4, 'Internal Medicine', 'INTMED', 'Internal medicine consultations'),
(4, 'Surgery', 'SURG', 'Surgical services'),
(4, 'Pediatrics', 'PEDIA', 'Child healthcare services'),
(4, 'Obstetrics & Gynecology', 'OBGYN', 'Maternal and reproductive health'),
(4, 'Records', 'REC', 'Medical records management'),
(4, 'Finance', 'FIN', 'Financial management'),
(4, 'Claims Processing', 'CLAIMS', 'NHIS claims processing'),

-- CCTH Departments
(5, 'Out Patient Department', 'OPD', 'General outpatient consultations'),
(5, 'Emergency Department', 'EMERG', 'Emergency and trauma care'),
(5, 'Laboratory', 'LAB', 'Clinical laboratory services'),
(5, 'Pharmacy', 'PHARM', 'Pharmaceutical services'),
(5, 'Radiology', 'RADIO', 'Medical imaging services'),
(5, 'Internal Medicine', 'INTMED', 'Internal medicine consultations'),
(5, 'Surgery', 'SURG', 'Surgical services'),
(5, 'Pediatrics', 'PEDIA', 'Child healthcare services'),
(5, 'Obstetrics & Gynecology', 'OBGYN', 'Maternal and reproductive health'),
(5, 'Records', 'REC', 'Medical records management'),
(5, 'Finance', 'FIN', 'Financial management'),
(5, 'Claims Processing', 'CLAIMS', 'NHIS claims processing');

-- Insert sample users
INSERT INTO users (hospital_id, department_id, username, password, email, full_name, role, employee_id, phone) VALUES
-- Superadmin (no hospital assignment)
(NULL, NULL, 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin@smartclaims.gov.gh', 'System Administrator', 'superadmin', 'SUPER001', '+233244000000'),

-- KBTH Users
(1, 1, 'kbth_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@kbth.gov.gh', 'KBTH Hospital Administrator', 'hospital_admin', 'KBTH001', '+233302674516'),
(1, 1, 'kbth_opd_head', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'opd.head@kbth.gov.gh', 'Dr. Sarah Mensah', 'department_head', 'KBTH002', '+233244123456'),
(1, 1, 'kbth_doctor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor1@kbth.gov.gh', 'Dr. Kwame Asante', 'doctor', 'KBTH003', '+233244234567'),
(1, 1, 'kbth_nurse1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nurse1@kbth.gov.gh', 'Nurse Joyce Addo', 'nurse', 'KBTH004', '+233244345678'),
(1, 4, 'kbth_pharmacist1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pharmacist1@kbth.gov.gh', 'Pharm. Michael Osei', 'pharmacist', 'KBTH005', '+233244456789'),
(1, 3, 'kbth_lab_tech1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'labtech1@kbth.gov.gh', 'James Appiah', 'lab_technician', 'KBTH006', '+233244567890'),
(1, 12, 'kbth_claims1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'claims1@kbth.gov.gh', 'Mary Boateng', 'claims_officer', 'KBTH007', '+233244678901'),

-- KATH Users
(2, 13, 'kath_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@kath.gov.gh', 'KATH Hospital Administrator', 'hospital_admin', 'KATH001', '+233322022701'),
(2, 13, 'kath_doctor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor1@kath.gov.gh', 'Dr. Akosua Frimpong', 'doctor', 'KATH002', '+233244111222'),
(2, 16, 'kath_pharmacist1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pharmacist1@kath.gov.gh', 'Pharm. Emmanuel Gyasi', 'pharmacist', 'KATH003', '+233244222333'),

-- Ridge Hospital Users
(3, 25, 'ridge_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ridgehospital.gov.gh', 'Ridge Hospital Administrator', 'hospital_admin', 'RH001', '+233302231670'),
(3, 25, 'ridge_doctor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor1@ridgehospital.gov.gh', 'Dr. Charles Amankwah', 'doctor', 'RH002', '+233244333444');

-- Insert comprehensive role permissions
INSERT INTO role_permissions (role, permission) VALUES
-- Superadmin permissions (ALL)
('superadmin', '*'),

-- Hospital Admin permissions
('hospital_admin', 'manage_hospital'),
('hospital_admin', 'manage_departments'),
('hospital_admin', 'manage_users'),
('hospital_admin', 'view_all_patients'),
('hospital_admin', 'view_all_visits'),
('hospital_admin', 'view_all_claims'),
('hospital_admin', 'generate_reports'),
('hospital_admin', 'manage_system_settings'),
('hospital_admin', 'view_audit_logs'),
('hospital_admin', 'approve_claims'),
('hospital_admin', 'manage_staff'),

-- Department Head permissions
('department_head', 'manage_department_staff'),
('department_head', 'view_department_patients'),
('department_head', 'view_department_visits'),
('department_head', 'generate_department_reports'),
('department_head', 'approve_department_requests'),
('department_head', 'manage_department_resources'),

-- Doctor permissions
('doctor', 'register_patients'),
('doctor', 'view_patient_info'),
('doctor', 'edit_patient_info'),
('doctor', 'create_visits'),
('doctor', 'view_visits'),
('doctor', 'edit_visits'),
('doctor', 'record_vital_signs'),
('doctor', 'add_diagnoses'),
('doctor', 'prescribe_medications'),
('doctor', 'order_lab_tests'),
('doctor', 'view_lab_results'),
('doctor', 'order_services'),
('doctor', 'generate_medical_reports'),
('doctor', 'refer_patients'),

-- Nurse permissions
('nurse', 'register_patients'),
('nurse', 'view_patient_info'),
('nurse', 'edit_patient_info'),
('nurse', 'create_visits'),
('nurse', 'view_visits'),
('nurse', 'record_vital_signs'),
('nurse', 'administer_medications'),
('nurse', 'patient_education'),
('nurse', 'wound_care'),
('nurse', 'patient_triage'),

-- Pharmacist permissions
('pharmacist', 'view_prescriptions'),
('pharmacist', 'verify_prescriptions'),
('pharmacist', 'dispense_medications'),
('pharmacist', 'check_drug_interactions'),
('pharmacist', 'patient_counseling'),
('pharmacist', 'manage_inventory'),
('pharmacist', 'generate_pharmacy_reports'),

-- Lab Technician permissions
('lab_technician', 'view_lab_orders'),
('lab_technician', 'perform_lab_tests'),
('lab_technician', 'enter_lab_results'),
('lab_technician', 'quality_control'),
('lab_technician', 'manage_lab_equipment'),
('lab_technician', 'generate_lab_reports'),

-- Claims Officer permissions
('claims_officer', 'process_claims'),
('claims_officer', 'submit_claims'),
('claims_officer', 'track_claim_status'),
('claims_officer', 'verify_claim_documents'),
('claims_officer', 'communicate_with_nhia'),
('claims_officer', 'generate_claims_reports'),

-- Records Officer permissions
('records_officer', 'register_patients'),
('records_officer', 'view_patient_info'),
('records_officer', 'edit_patient_info'),
('records_officer', 'manage_medical_records'),
('records_officer', 'archive_records'),
('records_officer', 'retrieve_records'),
('records_officer', 'generate_records_reports'),

-- Finance Officer permissions
('finance_officer', 'view_financial_data'),
('finance_officer', 'process_payments'),
('finance_officer', 'manage_accounts'),
('finance_officer', 'generate_financial_reports'),
('finance_officer', 'manage_billing'),
('finance_officer', 'approve_expenses'),

-- Receptionist permissions
('receptionist', 'register_patients'),
('receptionist', 'view_patient_info'),
('receptionist', 'schedule_appointments'),
('receptionist', 'patient_check_in'),
('receptionist', 'manage_waiting_lists'),
('receptionist', 'patient_inquiries'),

-- Radiologist permissions
('radiologist', 'view_radiology_orders'),
('radiologist', 'perform_radiology_procedures'),
('radiologist', 'interpret_images'),
('radiologist', 'generate_radiology_reports'),
('radiologist', 'manage_radiology_equipment'),

-- Cashier permissions
('cashier', 'process_payments'),
('cashier', 'generate_receipts'),
('cashier', 'manage_cash'),
('cashier', 'billing_assistance'),

-- IT Support permissions
('it_support', 'manage_user_accounts'),
('it_support', 'system_maintenance'),
('it_support', 'technical_support'),
('it_support', 'backup_data'),
('it_support', 'restore_data'),

-- Admin permissions (general)
('admin', 'manage_users'),
('admin', 'view_all_data'),
('admin', 'generate_reports'),
('admin', 'manage_settings'),
('admin', 'view_audit_logs');

-- Insert sample ICD-10 codes
INSERT INTO icd10_codes (id, description, category, subcategory) VALUES
('A00', 'Cholera', 'Certain infectious and parasitic diseases', 'Intestinal infectious diseases'),
('A09', 'Infectious gastroenteritis and colitis, unspecified', 'Certain infectious and parasitic diseases', 'Intestinal infectious diseases'),
('B50', 'Plasmodium falciparum malaria', 'Certain infectious and parasitic diseases', 'Protozoal diseases'),
('I10', 'Essential (primary) hypertension', 'Diseases of the circulatory system', 'Hypertensive diseases'),
('E11', 'Type 2 diabetes mellitus', 'Endocrine, nutritional and metabolic diseases', 'Diabetes mellitus'),
('J00', 'Acute nasopharyngitis [common cold]', 'Diseases of the respiratory system', 'Acute upper respiratory infections'),
('K59', 'Other functional intestinal disorders', 'Diseases of the digestive system', 'Other diseases of intestines'),
('N39', 'Other disorders of urinary system', 'Diseases of the genitourinary system', 'Other diseases of the urinary system'),
('O80', 'Encounter for full-term uncomplicated delivery', 'Pregnancy, childbirth and the puerperium', 'Encounter for delivery'),
('Z00', 'Encounter for general examination without complaint, suspected or reported diagnosis', 'Factors influencing health status and contact with health services', 'Persons encountering health services for examinations');

-- Insert sample medications
INSERT INTO medications (name, generic_name, brand_name, dosage_form, strength, nhis_covered, unit_price, manufacturer, drug_class) VALUES
('Paracetamol', 'Paracetamol', 'Panadol', 'Tablet', '500mg', TRUE, 0.50, 'Various', 'Analgesic'),
('Amoxicillin', 'Amoxicillin', 'Amoxil', 'Capsule', '250mg', TRUE, 2.50, 'Various', 'Antibiotic'),
('Artemether + Lumefantrine', 'Artemether + Lumefantrine', 'Coartem', 'Tablet', '20mg + 120mg', TRUE, 8.50, 'Novartis', 'Antimalarial'),
('Metformin', 'Metformin', 'Glucophage', 'Tablet', '500mg', TRUE, 1.50, 'Various', 'Antidiabetic'),
('Lisinopril', 'Lisinopril', 'Prinivil', 'Tablet', '10mg', TRUE, 3.00, 'Various', 'ACE Inhibitor'),
('Omeprazole', 'Omeprazole', 'Losec', 'Capsule', '20mg', TRUE, 4.50, 'Various', 'Proton Pump Inhibitor'),
('Salbutamol', 'Salbutamol', 'Ventolin', 'Inhaler', '100mcg', TRUE, 12.50, 'GSK', 'Bronchodilator'),
('Folic Acid', 'Folic Acid', 'Folvite', 'Tablet', '5mg', TRUE, 0.75, 'Various', 'Vitamin'),
('ORS', 'Oral Rehydration Salt', 'WHO-ORS', 'Powder', '20.5g', TRUE, 1.25, 'Various', 'Electrolyte'),
('Ibuprofen', 'Ibuprofen', 'Brufen', 'Tablet', '400mg', TRUE, 1.00, 'Various', 'NSAID');

-- Insert sample lab tests
INSERT INTO lab_tests (name, description, category, specimen_type, nhis_covered, price, normal_ranges) VALUES
('Full Blood Count', 'Complete blood count with differential', 'Hematology', 'Blood', TRUE, 25.00, 'WBC: 4.0-11.0 x10^9/L, RBC: 4.5-5.5 x10^12/L, Hgb: 12-16 g/dL'),
('Malaria Parasite', 'Microscopic examination for malaria parasites', 'Microbiology', 'Blood', TRUE, 15.00, 'Negative'),
('Urine Analysis', 'Complete urine examination', 'Chemistry', 'Urine', TRUE, 20.00, 'Protein: Negative, Glucose: Negative, Blood: Negative'),
('Blood Sugar (Random)', 'Random blood glucose level', 'Chemistry', 'Blood', TRUE, 12.00, '3.9-7.8 mmol/L'),
('Blood Sugar (Fasting)', 'Fasting blood glucose level', 'Chemistry', 'Blood', TRUE, 12.00, '3.9-6.1 mmol/L'),
('Hepatitis B Surface Antigen', 'HBsAg screening test', 'Serology', 'Blood', TRUE, 35.00, 'Non-reactive'),
('HIV Screening', 'HIV antibody screening test', 'Serology', 'Blood', TRUE, 30.00, 'Non-reactive'),
('Widal Test', 'Typhoid fever screening', 'Serology', 'Blood', TRUE, 25.00, 'Non-significant titers'),
('Stool Analysis', 'Microscopic examination of stool', 'Microbiology', 'Stool', TRUE, 18.00, 'No parasites seen'),
('Pregnancy Test', 'Beta-hCG qualitative test', 'Serology', 'Urine', TRUE, 15.00, 'Negative');

-- Insert sample services
INSERT INTO services (name, description, category, department, nhis_covered, price, duration_minutes, requires_appointment) VALUES
('OPD Consultation', 'General outpatient consultation', 'Consultation', 'OPD', TRUE, 15.00, 30, FALSE),
('Specialist Consultation', 'Specialist doctor consultation', 'Consultation', 'Various', TRUE, 25.00, 45, TRUE),
('Emergency Consultation', 'Emergency department consultation', 'Emergency', 'Emergency', TRUE, 20.00, 20, FALSE),
('Wound Dressing', 'Wound care and dressing', 'Nursing', 'OPD', TRUE, 10.00, 15, FALSE),
('Injection (IM/IV)', 'Intramuscular or intravenous injection', 'Nursing', 'OPD', TRUE, 5.00, 10, FALSE),
('Blood Pressure Check', 'Blood pressure measurement', 'Nursing', 'OPD', TRUE, 3.00, 5, FALSE),
('X-Ray (Chest)', 'Chest X-ray examination', 'Radiology', 'Radiology', TRUE, 40.00, 20, TRUE),
('Ultrasound Scan', 'Abdominal ultrasound examination', 'Radiology', 'Radiology', TRUE, 60.00, 30, TRUE),
('Minor Surgery', 'Minor surgical procedure', 'Surgery', 'Surgery', TRUE, 150.00, 60, TRUE),
('Physiotherapy Session', 'Physical therapy session', 'Therapy', 'Physiotherapy', TRUE, 25.00, 45, TRUE);

-- Insert sample system settings
INSERT INTO system_settings (setting_key, setting_value, description, category) VALUES
('hospital_name', 'Smart Claims NHIS System', 'Default system name', 'General'),
('nhia_provider_code', 'NHIA-SYS-001', 'NHIA provider identification code', 'NHIA'),
('claim_submission_endpoint', 'https://nhia.gov.gh/api/claims/submit', 'NHIA claims submission endpoint', 'NHIA'),
('max_session_timeout', '3600', 'Maximum session timeout in seconds', 'Security'),
('enable_audit_logging', '1', 'Enable comprehensive audit logging', 'Security'),
('backup_retention_days', '90', 'Number of days to retain backups', 'System'),
('password_min_length', '8', 'Minimum password length requirement', 'Security'),
('enable_2fa', '0', 'Enable two-factor authentication', 'Security'),
('claim_auto_submit', '0', 'Automatically submit approved claims', 'NHIA'),
('notification_email', 'admin@smartclaims.gov.gh', 'System notification email address', 'General');

-- Create indexes for better performance
CREATE INDEX idx_patients_hospital_id ON patients(hospital_id);
CREATE INDEX idx_visits_hospital_id ON visits(hospital_id);
CREATE INDEX idx_visits_patient_id ON visits(patient_id);
CREATE INDEX idx_visits_date ON visits(visit_date);
CREATE INDEX idx_claims_hospital_id ON claims(hospital_id);
CREATE INDEX idx_claims_status ON claims(status);
CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_timestamp ON audit_logs(timestamp);