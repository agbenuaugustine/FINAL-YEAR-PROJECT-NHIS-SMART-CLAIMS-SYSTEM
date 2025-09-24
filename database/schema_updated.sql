-- Updated Smart Claims Database Schema
-- This file updates the existing schema to match all current form fields

USE smartclaims;

-- Drop and recreate patients table with all current form fields
DROP TABLE IF EXISTS patients;
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

-- Add missing fields to vital_signs table
ALTER TABLE vital_signs ADD COLUMN temp_method ENUM('Oral', 'Axillary', 'Rectal', 'Tympanic', 'Temporal') AFTER temperature;
ALTER TABLE vital_signs ADD COLUMN systolic INT AFTER temp_method;
ALTER TABLE vital_signs ADD COLUMN diastolic INT AFTER systolic;
ALTER TABLE vital_signs ADD COLUMN bp_position ENUM('Sitting', 'Standing', 'Lying') AFTER blood_pressure;
ALTER TABLE vital_signs ADD COLUMN bp_arm ENUM('Left', 'Right') AFTER bp_position;
ALTER TABLE vital_signs ADD COLUMN pulse_rhythm ENUM('Regular', 'Irregular', 'Rapid', 'Bradycardia') AFTER pulse_rate;
ALTER TABLE vital_signs ADD COLUMN pulse_strength ENUM('Strong', 'Weak', 'Bounding', 'Thread') AFTER pulse_rhythm;
ALTER TABLE vital_signs ADD COLUMN breathing_pattern ENUM('Normal', 'Labored', 'Shallow', 'Deep', 'Irregular') AFTER respiratory_rate;
ALTER TABLE vital_signs ADD COLUMN oxygen_support ENUM('Room Air', 'Nasal Cannula', 'Face Mask', 'Non-rebreather', 'Ventilator') AFTER oxygen_saturation;
ALTER TABLE vital_signs ADD COLUMN pain_score INT CHECK (pain_score >= 0 AND pain_score <= 10) AFTER bmi;
ALTER TABLE vital_signs ADD COLUMN consciousness_level ENUM('Alert', 'Drowsy', 'Confused', 'Unresponsive') AFTER pain_score;
ALTER TABLE vital_signs ADD COLUMN general_appearance ENUM('Well', 'Ill', 'Distressed', 'Critical') AFTER consciousness_level;
ALTER TABLE vital_signs ADD COLUMN notes TEXT AFTER general_appearance;

-- Create missing tables for service requisition

-- Update services table with more fields
DROP TABLE IF EXISTS services;
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

-- Update medications table with more details
ALTER TABLE medications ADD COLUMN category VARCHAR(50) AFTER name;
ALTER TABLE medications ADD COLUMN generic_name VARCHAR(100) AFTER category;
ALTER TABLE medications ADD COLUMN manufacturer VARCHAR(100) AFTER strength;
ALTER TABLE medications ADD COLUMN requires_prescription BOOLEAN DEFAULT TRUE AFTER nhis_covered;
ALTER TABLE medications ADD COLUMN stock_level INT DEFAULT 0 AFTER requires_prescription;
ALTER TABLE medications ADD COLUMN reorder_level INT DEFAULT 10 AFTER stock_level;
ALTER TABLE medications ADD COLUMN expiry_date DATE AFTER reorder_level;
ALTER TABLE medications ADD COLUMN batch_number VARCHAR(50) AFTER expiry_date;
ALTER TABLE medications ADD COLUMN interactions TEXT AFTER batch_number;
ALTER TABLE medications ADD COLUMN contraindications TEXT AFTER interactions;
ALTER TABLE medications ADD COLUMN side_effects TEXT AFTER contraindications;

-- Update lab_tests table
ALTER TABLE lab_tests ADD COLUMN category VARCHAR(50) AFTER name;
ALTER TABLE lab_tests ADD COLUMN sample_type VARCHAR(50) AFTER category;
ALTER TABLE lab_tests ADD COLUMN preparation_instructions TEXT AFTER sample_type;
ALTER TABLE lab_tests ADD COLUMN normal_range VARCHAR(100) AFTER preparation_instructions;
ALTER TABLE lab_tests ADD COLUMN turnaround_time INT AFTER normal_range; -- in hours
ALTER TABLE lab_tests ADD COLUMN requires_fasting BOOLEAN DEFAULT FALSE AFTER turnaround_time;

-- Create comprehensive visits table with more fields
DROP TABLE IF EXISTS visits;
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
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (attending_doctor) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Update prescriptions table with more details
ALTER TABLE prescriptions ADD COLUMN indication VARCHAR(200) AFTER instructions;
ALTER TABLE prescriptions ADD COLUMN route ENUM('Oral', 'IV', 'IM', 'SC', 'Topical', 'Inhalation', 'Rectal', 'Sublingual') DEFAULT 'Oral' AFTER indication;
ALTER TABLE prescriptions ADD COLUMN start_date DATE AFTER route;
ALTER TABLE prescriptions ADD COLUMN end_date DATE AFTER start_date;
ALTER TABLE prescriptions ADD COLUMN refills_remaining INT DEFAULT 0 AFTER end_date;
ALTER TABLE prescriptions ADD COLUMN pharmacy_notes TEXT AFTER refills_remaining;
ALTER TABLE prescriptions ADD COLUMN dispensed_quantity INT DEFAULT 0 AFTER dispensed;
ALTER TABLE prescriptions ADD COLUMN dispensed_date TIMESTAMP NULL AFTER dispensed_quantity;
ALTER TABLE prescriptions ADD COLUMN dispensed_by INT AFTER dispensed_date;

-- Update claims table with more fields
ALTER TABLE claims ADD COLUMN patient_id INT AFTER visit_id;
ALTER TABLE claims ADD COLUMN provider_code VARCHAR(20) AFTER claim_number;
ALTER TABLE claims ADD COLUMN diagnosis_codes TEXT AFTER provider_code; -- JSON array of ICD-10 codes
ALTER TABLE claims ADD COLUMN treatment_date DATE AFTER diagnosis_codes;
ALTER TABLE claims ADD COLUMN discharge_date DATE AFTER treatment_date;
ALTER TABLE claims ADD COLUMN claim_type ENUM('OPD', 'IPD', 'Emergency', 'Maternity', 'Surgery') AFTER discharge_date;
ALTER TABLE claims ADD COLUMN nhis_copayment DECIMAL(10,2) DEFAULT 0 AFTER total_amount;
ALTER TABLE claims ADD COLUMN patient_copayment DECIMAL(10,2) DEFAULT 0 AFTER nhis_copayment;
ALTER TABLE claims ADD COLUMN exemption_category VARCHAR(50) AFTER patient_copayment;
ALTER TABLE claims ADD COLUMN supporting_documents TEXT AFTER exemption_category; -- JSON array of document paths
ALTER TABLE claims ADD COLUMN reviewer_id INT AFTER rejection_reason;
ALTER TABLE claims ADD COLUMN reviewer_notes TEXT AFTER reviewer_id;

-- Add foreign key for patient_id in claims
ALTER TABLE claims ADD FOREIGN KEY (patient_id) REFERENCES patients(id);
ALTER TABLE claims ADD FOREIGN KEY (reviewer_id) REFERENCES users(id);

-- Create appointment system
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
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Create system settings table
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_encrypted BOOLEAN DEFAULT FALSE,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Insert some system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('facility_name', 'Smart Claims Medical Center', 'string', 'Name of the medical facility'),
('facility_code', 'SCMC001', 'string', 'NHIA facility code'),
('nhis_api_url', 'https://api.nhis.gov.gh/v1/', 'string', 'NHIS API base URL'),
('copayment_rate', '10', 'number', 'Default copayment percentage'),
('session_timeout', '3600', 'number', 'Session timeout in seconds'),
('enable_sms_notifications', 'true', 'boolean', 'Enable SMS notifications'),
('enable_email_notifications', 'true', 'boolean', 'Enable email notifications');

-- Insert more comprehensive sample data

-- Sample medications
INSERT INTO medications (name, category, generic_name, description, dosage_form, strength, manufacturer, nhis_covered, requires_prescription, unit_price, stock_level) VALUES
('Panadol Extra', 'Analgesic', 'Paracetamol + Caffeine', 'Pain reliever and fever reducer with caffeine', 'Tablet', '500mg + 65mg', 'GSK', TRUE, FALSE, 0.75, 150),
('Amoxil', 'Antibiotic', 'Amoxicillin', 'Broad spectrum antibiotic', 'Capsule', '500mg', 'GSK', TRUE, TRUE, 1.50, 200),
('Glucophage', 'Antidiabetic', 'Metformin HCl', 'Type 2 diabetes medication', 'Tablet', '500mg', 'Merck', TRUE, TRUE, 1.25, 100),
('Lisinopril', 'ACE Inhibitor', 'Lisinopril', 'Blood pressure medication', 'Tablet', '10mg', 'Teva', TRUE, TRUE, 1.00, 80),
('Ventolin', 'Bronchodilator', 'Salbutamol', 'Asthma reliever inhaler', 'Inhaler', '100mcg', 'GSK', TRUE, TRUE, 15.00, 50);

-- Sample lab tests with categories
INSERT INTO lab_tests (name, category, sample_type, description, preparation_instructions, normal_range, turnaround_time, requires_fasting, nhis_covered, price) VALUES
('Full Blood Count', 'Hematology', 'EDTA Blood', 'Complete blood count analysis', 'No special preparation required', 'Age and gender specific', 2, FALSE, TRUE, 20.00),
('Fasting Blood Sugar', 'Biochemistry', 'Fluoride Blood', 'Blood glucose measurement', 'Fast for 8-12 hours', '3.9-6.1 mmol/L', 1, TRUE, TRUE, 15.00),
('Malaria Parasite', 'Parasitology', 'EDTA Blood', 'Malaria parasite detection', 'No special preparation', 'Negative', 1, FALSE, TRUE, 12.00),
('Hepatitis B Surface Antigen', 'Serology', 'Serum', 'Hepatitis B screening', 'No special preparation', 'Non-reactive', 24, FALSE, TRUE, 25.00),
('Urine Routine', 'Clinical Chemistry', 'Mid-stream Urine', 'Urinalysis', 'Mid-stream clean catch', 'Normal parameters', 1, FALSE, TRUE, 10.00);

-- Sample services with categories
INSERT INTO services (name, category, subcategory, description, nhis_covered, nhis_tariff, private_price, requires_approval, estimated_duration, department) VALUES
('General Consultation', 'OPD', 'Consultation', 'General practitioner consultation', TRUE, 25.00, 40.00, FALSE, 30, 'General Medicine'),
('Specialist Consultation', 'OPD', 'Consultation', 'Specialist doctor consultation', TRUE, 45.00, 70.00, FALSE, 45, 'Speciality'),
('Wound Dressing', 'Procedure', 'Minor Surgery', 'Cleaning and dressing of wounds', TRUE, 20.00, 35.00, FALSE, 20, 'Treatment Room'),
('ECG', 'Imaging', 'Cardiac', 'Electrocardiogram', TRUE, 30.00, 50.00, FALSE, 15, 'Cardiology'),
('X-Ray Chest', 'Imaging', 'Radiology', 'Chest X-ray examination', TRUE, 40.00, 60.00, FALSE, 10, 'Radiology'),
('Physiotherapy Session', 'Procedure', 'Rehabilitation', 'Physical therapy session', TRUE, 35.00, 55.00, FALSE, 60, 'Physiotherapy');

-- Add more ICD-10 codes
INSERT INTO icd10_codes (id, description, category) VALUES
('A09', 'Diarrhoea and gastroenteritis of presumed infectious origin', 'Infectious diseases'),
('B35', 'Dermatophytosis', 'Infectious diseases'),
('E10', 'Type 1 diabetes mellitus', 'Endocrine disorders'),
('E78', 'Disorders of lipoprotein metabolism', 'Endocrine disorders'),
('I25', 'Chronic ischaemic heart disease', 'Circulatory system'),
('J00', 'Acute nasopharyngitis [common cold]', 'Respiratory system'),
('J45', 'Asthma', 'Respiratory system'),
('K59', 'Other functional intestinal disorders', 'Digestive system'),
('M25', 'Other joint disorders', 'Musculoskeletal system'),
('N39', 'Other disorders of urinary system', 'Genitourinary system');

-- Update existing users table with more roles
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'doctor', 'nurse', 'pharmacist', 'lab_technician', 'claims_officer', 'receptionist', 'radiologist', 'physiotherapist', 'cashier');

-- Add more user fields
ALTER TABLE users ADD COLUMN license_number VARCHAR(50) AFTER department;
ALTER TABLE users ADD COLUMN specialization VARCHAR(100) AFTER license_number;
ALTER TABLE users ADD COLUMN phone VARCHAR(15) AFTER specialization;
ALTER TABLE users ADD COLUMN address TEXT AFTER phone;

-- Create notifications table
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
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create activity logs table
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
    FOREIGN KEY (user_id) REFERENCES users(id)
);