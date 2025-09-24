-- Fresh Smart Claims Database Schema
-- Creates all tables from scratch with proper order to avoid foreign key issues

USE smartclaims;

-- 1. Users table (Parent table - no dependencies)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'doctor', 'nurse', 'pharmacist', 'lab_technician', 'claims_officer', 'receptionist', 'radiologist', 'physiotherapist', 'cashier') NOT NULL,
    department VARCHAR(50),
    license_number VARCHAR(50),
    specialization VARCHAR(100),
    phone VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- 2. Patients table (Parent table - no dependencies)
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- NHIS Information
    nhis_number VARCHAR(20) UNIQUE,
    nhis_expiry DATE,
    membership_type ENUM('SSNIT_Contributor', 'SSNIT_Pensioner', 'Informal_Sector', 'Indigent', 'Under_18', '70_Above', 'Pregnant_Women'),
    policy_status ENUM('Active', 'Expired', 'Suspended', 'Pending') DEFAULT 'Pending',
    
    -- Personal Information
    title ENUM('Mr', 'Mrs', 'Miss', 'Dr', 'Prof', 'Rev'),
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed'),
    occupation VARCHAR(100),
    
    -- Contact Information
    phone_primary VARCHAR(15),
    phone_secondary VARCHAR(15),
    email VARCHAR(100),
    emergency_contact VARCHAR(15),
    
    -- Address Information
    region VARCHAR(50),
    district VARCHAR(50),
    town_city VARCHAR(50),
    postal_address VARCHAR(100),
    residential_address TEXT,
    landmark VARCHAR(100),
    
    -- Medical Information
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'),
    allergies TEXT,
    chronic_conditions TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_relationship ENUM('Spouse', 'Parent', 'Child', 'Sibling', 'Friend', 'Other'),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. ICD-10 Codes table (Parent table for diagnoses)
CREATE TABLE icd10_codes (
    id VARCHAR(10) PRIMARY KEY,
    description TEXT NOT NULL,
    category VARCHAR(100)
);

-- 4. Medications table (Parent table for prescriptions)
CREATE TABLE medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    generic_name VARCHAR(100),
    description TEXT,
    dosage_form VARCHAR(50),
    strength VARCHAR(50),
    manufacturer VARCHAR(100),
    nhis_covered BOOLEAN DEFAULT FALSE,
    requires_prescription BOOLEAN DEFAULT TRUE,
    unit_price DECIMAL(10,2) NOT NULL,
    stock_level INT DEFAULT 0,
    reorder_level INT DEFAULT 10,
    expiry_date DATE,
    batch_number VARCHAR(50),
    interactions TEXT,
    contraindications TEXT,
    side_effects TEXT
);

-- 5. Lab Tests table (Parent table for lab orders)
CREATE TABLE lab_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    sample_type VARCHAR(50),
    description TEXT,
    preparation_instructions TEXT,
    normal_range VARCHAR(100),
    turnaround_time INT, -- in hours
    requires_fasting BOOLEAN DEFAULT FALSE,
    nhis_covered BOOLEAN DEFAULT FALSE,
    price DECIMAL(10,2) NOT NULL
);

-- 6. Services table (Parent table for service orders)
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('OPD', 'Laboratory', 'Pharmacy', 'Imaging', 'Procedure', 'Consultation') NOT NULL,
    subcategory VARCHAR(50),
    nhis_covered BOOLEAN DEFAULT FALSE,
    nhis_tariff DECIMAL(10,2),
    private_price DECIMAL(10,2) NOT NULL,
    requires_approval BOOLEAN DEFAULT FALSE,
    estimated_duration INT, -- in minutes
    department VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 7. Visits table (Child table - depends on patients and users)
CREATE TABLE visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    visit_number VARCHAR(20) UNIQUE,
    visit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    visit_type ENUM('OPD', 'Emergency', 'Follow-up', 'Referral', 'Admission') NOT NULL,
    priority ENUM('Routine', 'Urgent', 'Emergency') DEFAULT 'Routine',
    chief_complaint TEXT,
    history_present_illness TEXT,
    past_medical_history TEXT,
    family_history TEXT,
    social_history TEXT,
    review_of_systems TEXT,
    physical_examination TEXT,
    assessment_plan TEXT,
    status ENUM('Waiting', 'In Progress', 'Completed', 'Cancelled', 'No Show') DEFAULT 'Waiting',
    attending_doctor INT,
    department VARCHAR(50),
    room_number VARCHAR(20),
    estimated_duration INT, -- in minutes
    actual_duration INT,
    follow_up_date DATE,
    follow_up_instructions TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (attending_doctor) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- 8. Vital Signs table (Child table - depends on visits and users)
CREATE TABLE vital_signs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    temperature DECIMAL(4,1),
    temp_method ENUM('Oral', 'Axillary', 'Rectal', 'Tympanic', 'Temporal'),
    systolic INT,
    diastolic INT,
    blood_pressure VARCHAR(10),
    bp_position ENUM('Sitting', 'Standing', 'Lying'),
    bp_arm ENUM('Left', 'Right'),
    pulse_rate INT,
    pulse_rhythm ENUM('Regular', 'Irregular', 'Rapid', 'Bradycardia'),
    pulse_strength ENUM('Strong', 'Weak', 'Bounding', 'Thread'),
    respiratory_rate INT,
    breathing_pattern ENUM('Normal', 'Labored', 'Shallow', 'Deep', 'Irregular'),
    weight DECIMAL(5,2),
    height DECIMAL(5,2),
    bmi DECIMAL(4,2),
    oxygen_saturation INT,
    oxygen_support ENUM('Room Air', 'Nasal Cannula', 'Face Mask', 'Non-rebreather', 'Ventilator'),
    pain_score INT CHECK (pain_score >= 0 AND pain_score <= 10),
    consciousness_level ENUM('Alert', 'Drowsy', 'Confused', 'Unresponsive'),
    general_appearance ENUM('Well', 'Ill', 'Distressed', 'Critical'),
    notes TEXT,
    recorded_by INT NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- 9. Diagnoses table (Child table - depends on visits, icd10_codes, and users)
CREATE TABLE diagnoses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    icd10_code VARCHAR(10) NOT NULL,
    diagnosis_notes TEXT,
    diagnosis_type ENUM('Primary', 'Secondary', 'Provisional', 'Final') NOT NULL,
    diagnosed_by INT NOT NULL,
    diagnosed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (icd10_code) REFERENCES icd10_codes(id) ON DELETE RESTRICT,
    FOREIGN KEY (diagnosed_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- 10. Prescriptions table (Child table - depends on visits, medications, and users)
CREATE TABLE prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    medication_id INT NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    instructions TEXT,
    indication VARCHAR(200),
    route ENUM('Oral', 'IV', 'IM', 'SC', 'Topical', 'Inhalation', 'Rectal', 'Sublingual') DEFAULT 'Oral',
    start_date DATE,
    end_date DATE,
    quantity INT NOT NULL,
    refills_remaining INT DEFAULT 0,
    dispensed BOOLEAN DEFAULT FALSE,
    dispensed_quantity INT DEFAULT 0,
    dispensed_date TIMESTAMP NULL,
    dispensed_by INT,
    pharmacy_notes TEXT,
    prescribed_by INT NOT NULL,
    prescribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (medication_id) REFERENCES medications(id) ON DELETE RESTRICT,
    FOREIGN KEY (prescribed_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (dispensed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 11. Lab Orders table (Child table - depends on visits, lab_tests, and users)
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
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (lab_test_id) REFERENCES lab_tests(id) ON DELETE RESTRICT,
    FOREIGN KEY (ordered_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 12. Service Orders table (Child table - depends on visits, services, and users)
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
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    FOREIGN KEY (ordered_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 13. Claims table (Child table - depends on patients, visits, and users)
CREATE TABLE claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    visit_id INT NOT NULL,
    claim_number VARCHAR(50) UNIQUE,
    provider_code VARCHAR(20),
    diagnosis_codes TEXT, -- JSON array of ICD-10 codes
    treatment_date DATE,
    discharge_date DATE,
    claim_type ENUM('OPD', 'IPD', 'Emergency', 'Maternity', 'Surgery'),
    total_amount DECIMAL(10,2) NOT NULL,
    nhis_copayment DECIMAL(10,2) DEFAULT 0,
    patient_copayment DECIMAL(10,2) DEFAULT 0,
    exemption_category VARCHAR(50),
    supporting_documents TEXT, -- JSON array of document paths
    status ENUM('Draft', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Paid') DEFAULT 'Draft',
    submission_date TIMESTAMP NULL,
    approval_date TIMESTAMP NULL,
    payment_date TIMESTAMP NULL,
    rejection_reason TEXT,
    reviewer_id INT,
    reviewer_notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 14. Claim Items table (Child table - depends on claims)
CREATE TABLE claim_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_id INT NOT NULL,
    item_type ENUM('Medication', 'Lab Test', 'Service', 'Consultation') NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    nhis_covered BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE
);

-- 15. Appointments table (Child table - depends on patients and users)
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    appointment_type ENUM('Consultation', 'Follow-up', 'Procedure', 'Check-up') NOT NULL,
    duration INT DEFAULT 30, -- in minutes
    status ENUM('Scheduled', 'Confirmed', 'In Progress', 'Completed', 'Cancelled', 'No Show') DEFAULT 'Scheduled',
    reason VARCHAR(200),
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- 16. System Settings table (Child table - depends on users)
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_encrypted BOOLEAN DEFAULT FALSE,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 17. Notifications table (Child table - depends on users)
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    related_type VARCHAR(50), -- 'appointment', 'claim', 'patient', etc.
    related_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 18. Activity Logs table (Child table - depends on users)
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 19. Audit Logs table (Child table - depends on users)
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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, full_name, role, department) 
VALUES ('admin', '$2y$10$8zf0SXIrA/UYBk82ovOFWOQA.V6JvW9oVeHn3IVhvC0kIZ5mQYl4e', 'admin@smartclaims.com', 'System Administrator', 'admin', 'Administration');

-- Insert sample doctor users
INSERT INTO users (username, password, email, full_name, role, department, license_number, specialization, phone) VALUES
('dr.mensah', '$2y$10$8zf0SXIrA/UYBk82ovOFWOQA.V6JvW9oVeHn3IVhvC0kIZ5mQYl4e', 'dr.mensah@smartclaims.com', 'Dr. Kwame Mensah', 'doctor', 'General Medicine', 'MD001', 'General Practice', '0244123456'),
('dr.asante', '$2y$10$8zf0SXIrA/UYBk82ovOFWOQA.V6JvW9oVeHn3IVhvC0kIZ5mQYl4e', 'dr.asante@smartclaims.com', 'Dr. Akosua Asante', 'doctor', 'Pediatrics', 'MD002', 'Pediatrics', '0244123457'),
('nurse.afia', '$2y$10$8zf0SXIrA/UYBk82ovOFWOQA.V6JvW9oVeHn3IVhvC0kIZ5mQYl4e', 'nurse.afia@smartclaims.com', 'Nurse Afia Boateng', 'nurse', 'General Medicine', 'RN001', 'General Nursing', '0244123458');

-- Insert comprehensive ICD-10 codes
INSERT INTO icd10_codes (id, description, category) VALUES
('A00', 'Cholera', 'Infectious and parasitic diseases'),
('A09', 'Diarrhoea and gastroenteritis of presumed infectious origin', 'Infectious and parasitic diseases'),
('B35', 'Dermatophytosis', 'Infectious and parasitic diseases'),
('E10', 'Type 1 diabetes mellitus', 'Endocrine, nutritional and metabolic diseases'),
('E11', 'Type 2 diabetes mellitus', 'Endocrine, nutritional and metabolic diseases'),
('E78', 'Disorders of lipoprotein metabolism', 'Endocrine, nutritional and metabolic diseases'),
('I10', 'Essential (primary) hypertension', 'Diseases of the circulatory system'),
('I25', 'Chronic ischaemic heart disease', 'Diseases of the circulatory system'),
('J00', 'Acute nasopharyngitis [common cold]', 'Diseases of the respiratory system'),
('J18', 'Pneumonia, unspecified organism', 'Diseases of the respiratory system'),
('J45', 'Asthma', 'Diseases of the respiratory system'),
('K59', 'Other functional intestinal disorders', 'Diseases of the digestive system'),
('M54', 'Dorsalgia', 'Diseases of the musculoskeletal system'),
('M25', 'Other joint disorders', 'Diseases of the musculoskeletal system'),
('N39', 'Other disorders of urinary system', 'Diseases of the genitourinary system'),
('O00', 'Ectopic pregnancy', 'Pregnancy, childbirth and the puerperium'),
('P00', 'Newborn affected by maternal conditions', 'Conditions originating in the perinatal period'),
('Z00', 'General examination without complaint', 'Factors influencing health status');

-- Insert comprehensive medications
INSERT INTO medications (name, category, generic_name, description, dosage_form, strength, manufacturer, nhis_covered, requires_prescription, unit_price, stock_level) VALUES
('Panadol', 'Analgesic', 'Paracetamol', 'Pain reliever and fever reducer', 'Tablet', '500mg', 'GSK', TRUE, FALSE, 0.50, 500),
('Panadol Extra', 'Analgesic', 'Paracetamol + Caffeine', 'Pain reliever and fever reducer with caffeine', 'Tablet', '500mg + 65mg', 'GSK', TRUE, FALSE, 0.75, 300),
('Amoxil', 'Antibiotic', 'Amoxicillin', 'Broad spectrum penicillin antibiotic', 'Capsule', '500mg', 'GSK', TRUE, TRUE, 1.50, 200),
('Flagyl', 'Antibiotic', 'Metronidazole', 'Antibiotic and antiprotozoal', 'Tablet', '400mg', 'Sanofi', TRUE, TRUE, 1.25, 150),
('Glucophage', 'Antidiabetic', 'Metformin HCl', 'Type 2 diabetes medication', 'Tablet', '500mg', 'Merck', TRUE, TRUE, 1.25, 100),
('Lisinopril', 'ACE Inhibitor', 'Lisinopril', 'Blood pressure medication', 'Tablet', '10mg', 'Teva', TRUE, TRUE, 1.00, 80),
('Amlodipine', 'Calcium Channel Blocker', 'Amlodipine besylate', 'Blood pressure medication', 'Tablet', '5mg', 'Pfizer', TRUE, TRUE, 0.80, 120),
('Ventolin', 'Bronchodilator', 'Salbutamol', 'Asthma reliever inhaler', 'Inhaler', '100mcg', 'GSK', TRUE, TRUE, 15.00, 50),
('Prednisolone', 'Steroid', 'Prednisolone', 'Anti-inflammatory steroid', 'Tablet', '5mg', 'Actavis', TRUE, TRUE, 0.30, 80),
('Omeprazole', 'PPI', 'Omeprazole', 'Proton pump inhibitor for acid reflux', 'Capsule', '20mg', 'AstraZeneca', TRUE, TRUE, 1.50, 100);

-- Insert comprehensive lab tests
INSERT INTO lab_tests (name, category, sample_type, description, preparation_instructions, normal_range, turnaround_time, requires_fasting, nhis_covered, price) VALUES
('Full Blood Count', 'Hematology', 'EDTA Blood', 'Complete blood count analysis', 'No special preparation required', 'Age and gender specific', 2, FALSE, TRUE, 20.00),
('Fasting Blood Sugar', 'Biochemistry', 'Fluoride Blood', 'Blood glucose measurement', 'Fast for 8-12 hours before test', '3.9-6.1 mmol/L', 1, TRUE, TRUE, 15.00),
('Random Blood Sugar', 'Biochemistry', 'Fluoride Blood', 'Random blood glucose measurement', 'No special preparation required', '<11.1 mmol/L', 1, FALSE, TRUE, 12.00),
('Malaria Parasite', 'Parasitology', 'EDTA Blood', 'Malaria parasite detection', 'No special preparation required', 'Negative', 1, FALSE, TRUE, 12.00),
('Hepatitis B Surface Antigen', 'Serology', 'Serum', 'Hepatitis B screening test', 'No special preparation required', 'Non-reactive', 24, FALSE, TRUE, 25.00),
('HIV Screening', 'Serology', 'Serum', 'HIV antibody test', 'No special preparation required', 'Non-reactive', 4, FALSE, TRUE, 30.00),
('Urine Routine', 'Clinical Chemistry', 'Mid-stream Urine', 'Complete urinalysis', 'Mid-stream clean catch urine', 'Normal parameters', 1, FALSE, TRUE, 10.00),
('Stool Routine', 'Parasitology', 'Fresh Stool', 'Stool examination for parasites', 'Fresh stool sample required', 'No parasites seen', 2, FALSE, TRUE, 8.00),
('Lipid Profile', 'Biochemistry', 'Serum', 'Cholesterol and triglycerides', 'Fast for 12 hours before test', 'Age specific ranges', 3, TRUE, TRUE, 35.00),
('Liver Function Test', 'Biochemistry', 'Serum', 'Liver enzymes and function', 'No special preparation required', 'Within normal limits', 4, FALSE, TRUE, 40.00);

-- Insert comprehensive services
INSERT INTO services (name, category, subcategory, description, nhis_covered, nhis_tariff, private_price, requires_approval, estimated_duration, department) VALUES
('General Consultation', 'Consultation', 'OPD', 'General practitioner consultation', TRUE, 25.00, 40.00, FALSE, 30, 'General Medicine'),
('Specialist Consultation', 'Consultation', 'OPD', 'Specialist doctor consultation', TRUE, 45.00, 70.00, FALSE, 45, 'Speciality'),
('Emergency Consultation', 'Consultation', 'Emergency', 'Emergency department consultation', TRUE, 35.00, 60.00, FALSE, 20, 'Emergency'),
('Follow-up Consultation', 'Consultation', 'Follow-up', 'Follow-up visit consultation', TRUE, 20.00, 35.00, FALSE, 20, 'General Medicine'),
('Wound Dressing', 'Procedure', 'Minor Surgery', 'Cleaning and dressing of wounds', TRUE, 20.00, 35.00, FALSE, 20, 'Treatment Room'),
('Minor Surgery', 'Procedure', 'Minor Surgery', 'Simple surgical procedures', TRUE, 100.00, 150.00, TRUE, 60, 'Minor Theatre'),
('Suturing', 'Procedure', 'Minor Surgery', 'Stitching of wounds', TRUE, 30.00, 50.00, FALSE, 30, 'Treatment Room'),
('ECG', 'Imaging', 'Cardiac', 'Electrocardiogram', TRUE, 30.00, 50.00, FALSE, 15, 'Cardiology'),
('X-Ray Chest', 'Imaging', 'Radiology', 'Chest X-ray examination', TRUE, 40.00, 60.00, FALSE, 10, 'Radiology'),
('Ultrasound Abdomen', 'Imaging', 'Radiology', 'Abdominal ultrasound scan', TRUE, 60.00, 90.00, FALSE, 30, 'Radiology'),
('Physiotherapy Session', 'Procedure', 'Rehabilitation', 'Physical therapy treatment', TRUE, 35.00, 55.00, FALSE, 60, 'Physiotherapy'),
('Nebulization', 'Procedure', 'Treatment', 'Respiratory therapy treatment', TRUE, 25.00, 40.00, FALSE, 20, 'Treatment Room');

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('facility_name', 'Smart Claims Medical Center', 'string', 'Name of the medical facility'),
('facility_code', 'SCMC001', 'string', 'NHIA facility code'),
('nhis_api_url', 'https://api.nhis.gov.gh/v1/', 'string', 'NHIS API base URL'),
('copayment_rate', '10', 'number', 'Default copayment percentage'),
('session_timeout', '3600', 'number', 'Session timeout in seconds'),
('enable_sms_notifications', 'true', 'boolean', 'Enable SMS notifications'),
('enable_email_notifications', 'true', 'boolean', 'Enable email notifications'),
('max_file_upload_size', '5242880', 'number', 'Maximum file upload size in bytes (5MB)'),
('appointment_slot_duration', '30', 'number', 'Default appointment duration in minutes'),
('working_hours_start', '08:00', 'string', 'Facility opening time'),
('working_hours_end', '17:00', 'string', 'Facility closing time');

-- Insert sample patient data
INSERT INTO patients (nhis_number, nhis_expiry, membership_type, policy_status, title, first_name, last_name, date_of_birth, gender, marital_status, occupation, phone_primary, email, region, district, town_city, residential_address, blood_group, allergies) VALUES
('0123456789', '2024-12-31', 'SSNIT_Contributor', 'Active', 'Mr', 'Kwame', 'Asante', '1985-06-15', 'Male', 'Married', 'Teacher', '0244123456', 'kwame.asante@email.com', 'Greater Accra', 'Accra Metropolitan', 'Accra', 'House No. 123, Adabraka', 'O+', 'None known'),
('9876543210', '2024-11-30', 'Informal_Sector', 'Active', 'Mrs', 'Akosua', 'Mensah', '1990-03-22', 'Female', 'Single', 'Trader', '0244654321', 'akosua.mensah@email.com', 'Ashanti', 'Kumasi Metropolitan', 'Kumasi', 'Adum Commercial Area', 'A+', 'Penicillin allergy');

-- Create indexes for better performance
CREATE INDEX idx_patients_nhis ON patients(nhis_number);
CREATE INDEX idx_patients_name ON patients(first_name, last_name);
CREATE INDEX idx_visits_patient ON visits(patient_id);
CREATE INDEX idx_visits_date ON visits(visit_date);
CREATE INDEX idx_claims_patient ON claims(patient_id);
CREATE INDEX idx_claims_status ON claims(status);
CREATE INDEX idx_prescriptions_visit ON prescriptions(visit_id);
CREATE INDEX idx_lab_orders_visit ON lab_orders(visit_id);
CREATE INDEX idx_vital_signs_visit ON vital_signs(visit_id);

-- Create triggers for automatic visit number generation
DELIMITER //
CREATE TRIGGER generate_visit_number 
BEFORE INSERT ON visits 
FOR EACH ROW 
BEGIN 
    IF NEW.visit_number IS NULL THEN
        SET NEW.visit_number = CONCAT('V', YEAR(NOW()), LPAD(NEW.id, 6, '0'));
    END IF;
END//

CREATE TRIGGER generate_claim_number 
BEFORE INSERT ON claims 
FOR EACH ROW 
BEGIN 
    IF NEW.claim_number IS NULL THEN
        SET NEW.claim_number = CONCAT('CL', YEAR(NOW()), LPAD(NEW.id, 6, '0'));
    END IF;
END//
DELIMITER ;

-- Success message
SELECT 'Database schema created successfully!' as status;