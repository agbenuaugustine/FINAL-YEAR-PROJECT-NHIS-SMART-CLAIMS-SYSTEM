-- Smart Claims Database Schema (Modified version without DROP DATABASE)

-- Use the database (make sure to select the smartclaims database in phpMyAdmin before running this script)
USE smartclaims;

-- Users table for authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'doctor', 'nurse', 'pharmacist', 'lab_technician', 'claims_officer', 'receptionist') NOT NULL,
    department VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Patients table
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nhis_number VARCHAR(20) UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    date_of_birth DATE NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(15),
    blood_group VARCHAR(5),
    allergies TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Visits table
CREATE TABLE visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    visit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    visit_type ENUM('OPD', 'Emergency', 'Follow-up', 'Referral') NOT NULL,
    chief_complaint TEXT,
    status ENUM('Waiting', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Waiting',
    attending_doctor INT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (attending_doctor) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Vital signs table
CREATE TABLE vital_signs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    temperature DECIMAL(4,1),
    blood_pressure VARCHAR(10),
    pulse_rate INT,
    respiratory_rate INT,
    weight DECIMAL(5,2),
    height DECIMAL(5,2),
    bmi DECIMAL(4,2),
    oxygen_saturation INT,
    recorded_by INT NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- ICD-10 Diagnoses table
CREATE TABLE icd10_codes (
    id VARCHAR(10) PRIMARY KEY,
    description TEXT NOT NULL,
    category VARCHAR(100)
);

-- Diagnoses table
CREATE TABLE diagnoses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    icd10_code VARCHAR(10) NOT NULL,
    diagnosis_notes TEXT,
    diagnosis_type ENUM('Primary', 'Secondary', 'Provisional', 'Final') NOT NULL,
    diagnosed_by INT NOT NULL,
    diagnosed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(id),
    FOREIGN KEY (icd10_code) REFERENCES icd10_codes(id),
    FOREIGN KEY (diagnosed_by) REFERENCES users(id)
);

-- Medications table
CREATE TABLE medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    dosage_form VARCHAR(50),
    strength VARCHAR(50),
    nhis_covered BOOLEAN DEFAULT FALSE,
    unit_price DECIMAL(10,2) NOT NULL
);

-- Prescriptions table
CREATE TABLE prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    medication_id INT NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    instructions TEXT,
    quantity INT NOT NULL,
    dispensed BOOLEAN DEFAULT FALSE,
    prescribed_by INT NOT NULL,
    prescribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(id),
    FOREIGN KEY (medication_id) REFERENCES medications(id),
    FOREIGN KEY (prescribed_by) REFERENCES users(id)
);

-- Laboratory tests table
CREATE TABLE lab_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    nhis_covered BOOLEAN DEFAULT FALSE,
    price DECIMAL(10,2) NOT NULL
);

-- Lab orders table
CREATE TABLE lab_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    lab_test_id INT NOT NULL,
    status ENUM('Ordered', 'Specimen Collected', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Ordered',
    results TEXT,
    ordered_by INT NOT NULL,
    performed_by INT,
    ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (visit_id) REFERENCES visits(id),
    FOREIGN KEY (lab_test_id) REFERENCES lab_tests(id),
    FOREIGN KEY (ordered_by) REFERENCES users(id),
    FOREIGN KEY (performed_by) REFERENCES users(id)
);

-- Services table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    nhis_covered BOOLEAN DEFAULT FALSE,
    price DECIMAL(10,2) NOT NULL
);

-- Service orders table
CREATE TABLE service_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    service_id INT NOT NULL,
    status ENUM('Ordered', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Ordered',
    notes TEXT,
    ordered_by INT NOT NULL,
    performed_by INT,
    ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (visit_id) REFERENCES visits(id),
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (ordered_by) REFERENCES users(id),
    FOREIGN KEY (performed_by) REFERENCES users(id)
);

-- Claims table
CREATE TABLE claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    claim_number VARCHAR(50) UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('Draft', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Paid') DEFAULT 'Draft',
    submission_date TIMESTAMP NULL,
    approval_date TIMESTAMP NULL,
    payment_date TIMESTAMP NULL,
    rejection_reason TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Claim items table
CREATE TABLE claim_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_id INT NOT NULL,
    item_type ENUM('Medication', 'Lab Test', 'Service', 'Consultation') NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    nhis_covered BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (claim_id) REFERENCES claims(id)
);

-- Audit logs table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, full_name, role) 
VALUES ('admin', '$2y$10$8zf0SXIrA/UYBk82ovOFWOQA.V6JvW9oVeHn3IVhvC0kIZ5mQYl4e', 'admin@smartclaims.com', 'System Administrator', 'admin');

-- Insert some sample ICD-10 codes
INSERT INTO icd10_codes (id, description, category) VALUES
('A00', 'Cholera', 'Infectious diseases'),
('E11', 'Type 2 diabetes mellitus', 'Endocrine disorders'),
('I10', 'Essential (primary) hypertension', 'Circulatory system'),
('J18', 'Pneumonia, unspecified organism', 'Respiratory system'),
('M54', 'Dorsalgia', 'Musculoskeletal system'),
('O00', 'Ectopic pregnancy', 'Pregnancy and childbirth'),
('P00', 'Newborn affected by maternal conditions', 'Perinatal period'),
('Z00', 'General examination without complaint', 'Factors influencing health status');

-- Insert sample medications
INSERT INTO medications (name, description, dosage_form, strength, nhis_covered, unit_price) VALUES
('Paracetamol', 'Pain reliever and fever reducer', 'Tablet', '500mg', TRUE, 0.50),
('Amoxicillin', 'Antibiotic', 'Capsule', '500mg', TRUE, 1.20),
('Metformin', 'Anti-diabetic medication', 'Tablet', '500mg', TRUE, 0.75),
('Lisinopril', 'ACE inhibitor for hypertension', 'Tablet', '10mg', TRUE, 1.00),
('Ibuprofen', 'NSAID for pain and inflammation', 'Tablet', '400mg', TRUE, 0.60),
('Omeprazole', 'Proton pump inhibitor', 'Capsule', '20mg', TRUE, 1.50);

-- Insert sample lab tests
INSERT INTO lab_tests (name, description, nhis_covered, price) VALUES
('Complete Blood Count', 'Measures various components of blood', TRUE, 15.00),
('Blood Glucose', 'Measures blood sugar levels', TRUE, 10.00),
('Urinalysis', 'Analysis of urine composition', TRUE, 8.00),
('Liver Function Test', 'Assesses liver function', TRUE, 25.00),
('Malaria Test', 'Tests for malaria parasites', TRUE, 12.00),
('Typhoid Test', 'Tests for typhoid antibodies', TRUE, 18.00);

-- Insert sample services
INSERT INTO services (name, description, category, nhis_covered, price) VALUES
('General Consultation', 'Consultation with general practitioner', 'Consultation', TRUE, 20.00),
('Specialist Consultation', 'Consultation with specialist', 'Consultation', TRUE, 40.00),
('Wound Dressing', 'Cleaning and dressing of wounds', 'Procedure', TRUE, 15.00),
('Nebulization', 'Respiratory treatment', 'Procedure', TRUE, 25.00),
('Suturing', 'Stitching of wounds', 'Procedure', TRUE, 30.00),
('Incision and Drainage', 'Drainage of abscesses', 'Procedure', TRUE, 35.00);