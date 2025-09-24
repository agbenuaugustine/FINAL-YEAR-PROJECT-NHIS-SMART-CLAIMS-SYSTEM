<?php
/**
 * Diagnosis & Medication Page
 * 
 * ICD-10 diagnosis codes linked to prescriptions
 */

// Include secure authentication middleware
require_once __DIR__ . '/secure_auth.php';

// User data is now available from secure_auth
// $user and $role variables are already set
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Diagnosis & Medication - Smart Claims NHIS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Apple-inspired styles */
        @import url('https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary-color: #0071e3;
            --secondary-color: #06c;
            --success-color: #34c759;
            --warning-color: #ff9500;
            --danger-color: #ff3b30;
            --light-bg: #f5f5f7;
            --card-bg: #ffffff;
            --text-primary: #1d1d1f;
            --text-secondary: #86868b;
            --border-color: #d2d2d7;
            
            /* NHIS Theme Colors */
            --nhis-primary: #1a5b8a;
            --nhis-secondary: #2c8fb8;
            --nhis-accent: #5cb85c;
            --nhis-gold: #f0ad4e;
            --ghana-red: #ce1126;
            --ghana-gold: #fcd116;
            --ghana-green: #006b3f;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text-primary);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
            width: 100%;
            position: relative;
            max-width: 100vw;
        }
        
        /* App container */
        .app-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem;
            overflow-x: hidden;
            width: 100%;
        }
        
        /* Header */
        .app-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            margin-bottom: 1.5rem;
        }
        
        .app-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
        }
        
        .app-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0f2b5b, #1e88e5);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            transform: rotate(10deg);
            box-shadow: 0 4px 8px rgba(30, 136, 229, 0.3);
            margin-right: 0.75rem;
        }
        
        /* Navigation */
        .app-nav {
            display: flex;
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            overflow: hidden;
            position: sticky;
            top: 1rem;
            z-index: 100;
        }
        
        .nav-item {
            flex: 1;
            padding: 0.75rem 1rem;
            text-align: center;
            color: var(--text-secondary);
            font-weight: 500;
            transition: all 0.2s ease;
            position: relative;
            white-space: nowrap;
        }
        
        .nav-item:hover {
            color: var(--primary-color);
        }
        
        .nav-item.active {
            color: var(--primary-color);
        }
        
        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 3px;
        }
        
        .nav-item i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }
        
        /* Cards */
        .card {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 1.75rem;
            margin-bottom: 1.75rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        
        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            background-color: #fff;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
        }
        
        /* Diagnosis search */
        .diagnosis-search {
            position: relative;
        }
        
        .diagnosis-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        
        .diagnosis-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: background-color 0.2s ease;
        }
        
        .diagnosis-item:hover {
            background-color: rgba(0, 113, 227, 0.05);
        }
        
        .diagnosis-code {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .diagnosis-name {
            color: var(--text-primary);
            font-size: 0.9rem;
        }
        
        /* Selected diagnosis */
        .selected-diagnosis {
            background: linear-gradient(135deg, rgba(52, 199, 89, 0.1), rgba(48, 209, 88, 0.1));
            border: 1px solid rgba(52, 199, 89, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .diagnosis-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .diagnosis-title {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .diagnosis-subtitle {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Medication styles */
        .medication-item {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: box-shadow 0.2s ease;
        }
        
        .medication-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .medication-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        
        .medication-name {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .medication-strength {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0071e3, #42a1ec);
            color: white;
            box-shadow: 0 2px 8px rgba(0, 113, 227, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.4);
        }
        
        .btn-secondary {
            background: #f5f5f7;
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #34c759, #30d158);
            color: white;
            box-shadow: 0 2px 8px rgba(52, 199, 89, 0.3);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ff9500, #ffcc00);
            color: white;
            box-shadow: 0 2px 8px rgba(255, 149, 0, 0.3);
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        /* Tables */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }
        
        .table th {
            background: rgba(248, 250, 252, 0.9);
            padding: 1rem 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            color: var(--text-primary);
        }
        
        /* Alert Styles */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left-color: #17a2b8;
        }
        
        /* Drug interaction warning */
        .interaction-warning {
            background: linear-gradient(135deg, #fff3cd, #ffeeba);
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 1rem;
            margin: 0.5rem 0;
        }
        
        /* Mobile Navigation */
        .mobile-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-color);
            padding: 0.5rem 0;
            z-index: 1000;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            height: 4.5rem;
            width: 100%;
            max-width: 100%;
        }

        .mobile-nav-item {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            padding: 0.4rem 0;
            color: var(--text-secondary);
            font-size: 0.65rem;
            transition: all 0.2s ease;
            width: 14.28%; /* 100% / 7 items */
            text-align: center;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }

        .mobile-nav-item i {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }

        .mobile-nav-item.active {
            color: var(--primary-color);
        }
        
        .mobile-nav-item.active::after {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 25px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 3px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .app-nav {
                display: none;
            }
            
            .mobile-nav {
                display: flex;
                justify-content: space-around;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .app-container {
                padding: 0.5rem;
                padding-bottom: 5.5rem;
            }
        }
        
        /* Animated background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(70px);
            opacity: 0.3;
        }
        
        .bg-shape-1 {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #0071e3, #5ac8fa);
            top: -200px;
            left: -200px;
            animation: float 25s infinite ease-in-out;
        }
        
        .bg-shape-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #5ac8fa, #007aff);
            bottom: -150px;
            right: -150px;
            animation: float 20s infinite ease-in-out reverse;
        }
        
        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(50px, 50px) rotate(10deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <h1 class="app-title">
                <div class="app-logo">
                    <i class="fas fa-file-medical"></i>
                </div>
                Smart Claims
            </h1>
            
            <div class="user-menu relative">
                <div class="user-button cursor-pointer" id="userMenuButton">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="hidden md:inline"><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></span>
                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                </div>
                <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 hidden">
                    <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <i class="fas fa-user-circle mr-2"></i> Profile
                    </a>
                    <a href="settings.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                    <div class="border-t border-gray-200 my-1"></div>
                    <a href="logout" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>
        </header>
        
        <!-- Navigation -->
        <nav class="app-nav">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="client-registration.php" class="nav-item">
                <i class="fas fa-user-plus"></i>
                <span>Client Registration</span>
            </a>
            <a href="service-requisition.php" class="nav-item">
                <i class="fas fa-clipboard-list"></i>
                <span>Service Requisition</span>
            </a>
            <a href="vital-signs.php" class="nav-item">
                <i class="fas fa-heartbeat"></i>
                <span>Vital Signs</span>
            </a>
            <a href="diagnosis-medication.php" class="nav-item active">
                <i class="fas fa-stethoscope"></i>
                <span>Diagnosis & Medication</span>
            </a>
            <a href="claims-processing.php" class="nav-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Claims Processing</span>
            </a>
            <a href="reports.php" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </nav>
        
        <!-- Main Content -->
        <main>
            <!-- Page Header -->
            <div class="card">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="card-title text-2xl font-bold mb-2">
                            <i class="fas fa-stethoscope mr-2"></i>
                            Diagnosis & Medication
                        </h2>
                        <p class="text-secondary text-lg">ICD-10 diagnosis codes linked to NHIS-approved prescriptions</p>
                        <p class="text-sm text-gray-600 mt-2">Automated drug interaction checks and dosage recommendations</p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="btn btn-secondary" onclick="loadPreviousPrescription()">
                            <i class="fas fa-history mr-2"></i>
                            Previous Rx
                        </button>
                        <button class="btn btn-warning" onclick="generatePrescription()">
                            <i class="fas fa-file-prescription mr-2"></i>
                            Generate Rx
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer"></div>

            <!-- Patient Information -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-user-check mr-2"></i>
                    Patient Information
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="patient_search" class="form-label">Search Patient</label>
                        <div class="relative">
                            <input type="text" 
                                   id="patient_search" 
                                   class="form-control pr-10" 
                                   placeholder="Enter NHIS number or name">
                            <button class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-500" onclick="searchPatient()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="consultation_date" class="form-label">Consultation Date</label>
                        <input type="date" id="consultation_date" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="physician" class="form-label">Attending Physician</label>
                        <input type="text" 
                               id="physician" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($user['full_name'] ?? 'Current User'); ?>" 
                               readonly>
                    </div>
                </div>
                
                <!-- Selected Patient Info -->
                <div id="selectedPatientInfo" class="hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-800 mb-2">Patient Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                            <div><strong>Name:</strong> <span id="patient_name">-</span></div>
                            <div><strong>NHIS:</strong> <span id="patient_nhis">-</span></div>
                            <div><strong>Age:</strong> <span id="patient_age">-</span></div>
                            <div><strong>Allergies:</strong> <span id="patient_allergies" class="text-red-600">-</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clinical Assessment -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Clinical Assessment
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="chief_complaint" class="form-label">Chief Complaint</label>
                        <textarea id="chief_complaint" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Patient's main concern or reason for visit"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="history_present_illness" class="form-label">History of Present Illness</label>
                        <textarea id="history_present_illness" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Detailed history of current illness"></textarea>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="physical_examination" class="form-label">Physical Examination Findings</label>
                        <textarea id="physical_examination" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Key physical examination findings"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="clinical_impression" class="form-label">Clinical Impression</label>
                        <textarea id="clinical_impression" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Initial clinical assessment"></textarea>
                    </div>
                </div>
            </div>

            <!-- Diagnosis Section -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-diagnoses mr-2"></i>
                    Diagnosis (ICD-10)
                </h3>
                
                <div class="form-group">
                    <label for="diagnosis_search" class="form-label">Search ICD-10 Codes</label>
                    <div class="diagnosis-search">
                        <input type="text" 
                               id="diagnosis_search" 
                               class="form-control" 
                               placeholder="Type diagnosis or ICD-10 code..."
                               autocomplete="off"
                               onkeyup="searchDiagnosis(this.value)">
                        <div id="diagnosis_dropdown" class="diagnosis-dropdown"></div>
                    </div>
                </div>
                
                <!-- Selected Diagnoses -->
                <div id="selected_diagnoses">
                    <!-- Selected diagnoses will appear here -->
                </div>
                
                <div class="form-group">
                    <label for="differential_diagnosis" class="form-label">Differential Diagnosis</label>
                    <textarea id="differential_diagnosis" 
                              class="form-control" 
                              rows="2" 
                              placeholder="Alternative diagnoses considered"></textarea>
                </div>
            </div>

            <!-- Medication Section -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-pills mr-2"></i>
                    Medication Prescription
                </h3>
                
                <div id="drug_interactions_alert" class="hidden">
                    <div class="interaction-warning">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <strong>Drug Interaction Warning:</strong>
                            <span id="interaction_details"></span>
                        </div>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="medication_search" class="form-label">Search Medication</label>
                        <div class="relative">
                            <input type="text" 
                                   id="medication_search" 
                                   class="form-control pr-10" 
                                   placeholder="Search NHIS approved medications">
                            <button class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-500" onclick="searchMedication()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="medication_strength" class="form-label">Strength/Dosage</label>
                        <select id="medication_strength" class="form-control">
                            <option value="">Select strength</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="dosage_form" class="form-label">Dosage Form</label>
                        <select id="dosage_form" class="form-control">
                            <option value="">Select form</option>
                            <option value="Tablet">Tablet</option>
                            <option value="Capsule">Capsule</option>
                            <option value="Syrup">Syrup</option>
                            <option value="Injection">Injection</option>
                            <option value="Cream">Cream</option>
                            <option value="Ointment">Ointment</option>
                            <option value="Drops">Drops</option>
                            <option value="Inhaler">Inhaler</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="frequency" class="form-label">Frequency</label>
                        <select id="frequency" class="form-control">
                            <option value="">Select frequency</option>
                            <option value="Once daily">Once daily (OD)</option>
                            <option value="Twice daily">Twice daily (BD)</option>
                            <option value="Three times daily">Three times daily (TDS)</option>
                            <option value="Four times daily">Four times daily (QDS)</option>
                            <option value="Every 4 hours">Every 4 hours</option>
                            <option value="Every 6 hours">Every 6 hours</option>
                            <option value="Every 8 hours">Every 8 hours</option>
                            <option value="As needed">As needed (PRN)</option>
                            <option value="At bedtime">At bedtime</option>
                            <option value="Before meals">Before meals</option>
                            <option value="After meals">After meals</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration" class="form-label">Duration</label>
                        <select id="duration" class="form-control">
                            <option value="">Select duration</option>
                            <option value="3 days">3 days</option>
                            <option value="5 days">5 days</option>
                            <option value="7 days">7 days</option>
                            <option value="10 days">10 days</option>
                            <option value="14 days">14 days</option>
                            <option value="21 days">21 days</option>
                            <option value="1 month">1 month</option>
                            <option value="3 months">3 months</option>
                            <option value="Ongoing">Ongoing</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" 
                               id="quantity" 
                               class="form-control" 
                               min="1" 
                               placeholder="Number of units">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="special_instructions" class="form-label">Special Instructions</label>
                    <textarea id="special_instructions" 
                              class="form-control" 
                              rows="2" 
                              placeholder="Special dosing instructions or patient counseling notes"></textarea>
                </div>
                
                <div class="flex justify-end">
                    <button class="btn btn-primary" onclick="addMedication()">
                        <i class="fas fa-plus mr-2"></i>
                        Add Medication
                    </button>
                </div>
                
                <!-- Prescribed Medications -->
                <div id="prescribed_medications" class="mt-6">
                    <h4 class="text-lg font-semibold mb-3">Prescribed Medications</h4>
                    <div id="medication_list" class="space-y-3">
                        <div class="text-center text-gray-500 py-4">
                            <i class="fas fa-pills text-3xl mb-2"></i>
                            <p>No medications prescribed yet</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Treatment Plan -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Treatment Plan & Follow-up
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="treatment_plan" class="form-label">Treatment Plan</label>
                        <textarea id="treatment_plan" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Detailed treatment plan and recommendations"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="patient_education" class="form-label">Patient Education</label>
                        <textarea id="patient_education" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Information provided to patient about condition and treatment"></textarea>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="follow_up" class="form-label">Follow-up Instructions</label>
                        <select id="follow_up" class="form-control">
                            <option value="">Select follow-up</option>
                            <option value="No follow-up needed">No follow-up needed</option>
                            <option value="Return in 3 days">Return in 3 days</option>
                            <option value="Return in 1 week">Return in 1 week</option>
                            <option value="Return in 2 weeks">Return in 2 weeks</option>
                            <option value="Return in 1 month">Return in 1 month</option>
                            <option value="Return if symptoms worsen">Return if symptoms worsen</option>
                            <option value="Refer to specialist">Refer to specialist</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="next_appointment" class="form-label">Next Appointment Date</label>
                        <input type="date" id="next_appointment" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="warning_signs" class="form-label">Warning Signs</label>
                        <textarea id="warning_signs" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Signs/symptoms that require immediate medical attention"></textarea>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="card">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-shield-alt mr-1"></i>
                        All prescriptions are checked against NHIS formulary and drug interactions
                    </div>
                    <div class="flex space-x-3">
                        <button class="btn btn-secondary" onclick="saveDraft()">
                            <i class="fas fa-save mr-2"></i>
                            Save Draft
                        </button>
                        <button class="btn btn-warning" onclick="printPrescription()">
                            <i class="fas fa-print mr-2"></i>
                            Print Prescription
                        </button>

                        <button class="btn btn-success" onclick="finalizeDiagnosis()">
                            <i class="fas fa-check-circle mr-2"></i>
                            Finalize & Proceed
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Consultations -->
            <div class="card">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="card-title text-xl font-bold">
                        <i class="fas fa-history mr-2"></i>
                        Recent Consultations
                    </h3>
                    <div class="flex space-x-2">
                        <button class="btn btn-info btn-sm" onclick="showWorkflowGuide()" title="How the system works">
                            <i class="fas fa-question-circle"></i>
                            Workflow Guide
                        </button>
                        <button class="btn btn-warning btn-sm" onclick="testMedications()" title="Check medication database">
                            <i class="fas fa-pills"></i>
                            Test Meds
                        </button>
                        <button class="btn btn-info btn-sm" onclick="debugPrescriptions()" title="Debug prescription data">
                            <i class="fas fa-bug"></i>
                            Debug
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="loadRecentConsultations()" title="Refresh Consultations">
                            <i class="fas fa-sync-alt"></i>
                            Refresh
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Primary Diagnosis</th>
                                <th>Medications</th>
                                <th>Physician</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recent_consultations">
                            <tr>
                                <td colspan="7" class="text-center py-4">Loading recent consultations...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        
        <!-- Mobile Navigation -->
        <div class="mobile-nav">
            <a href="dashboard" class="mobile-nav-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="client-registration" class="mobile-nav-item">
                <i class="fas fa-user-plus"></i>
                <span>Clients</span>
            </a>
            <a href="service-requisition" class="mobile-nav-item">
                <i class="fas fa-clipboard-list"></i>
                <span>Services</span>
            </a>
            <a href="vital-signs" class="mobile-nav-item">
                <i class="fas fa-heartbeat"></i>
                <span>Vitals</span>
            </a>
            <a href="diagnosis-medication" class="mobile-nav-item active">
                <i class="fas fa-stethoscope"></i>
                <span>Diagnosis</span>
            </a>
            <a href="claims-processing" class="mobile-nav-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Claims</span>
            </a>
            <a href="reports" class="mobile-nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </div>
    </div>

    <script>
        // ICD-10 diagnosis data (sample)
        const icd10Data = [
            { code: 'J00', name: 'Acute nasopharyngitis [common cold]', category: 'Respiratory' },
            { code: 'J06.9', name: 'Acute upper respiratory infection, unspecified', category: 'Respiratory' },
            { code: 'A09', name: 'Diarrhoea and gastroenteritis of presumed infectious origin', category: 'Digestive' },
            { code: 'K30', name: 'Functional dyspepsia', category: 'Digestive' },
            { code: 'M79.1', name: 'Myalgia', category: 'Musculoskeletal' },
            { code: 'R50.9', name: 'Fever, unspecified', category: 'Symptoms' },
            { code: 'R51', name: 'Headache', category: 'Symptoms' },
            { code: 'I10', name: 'Essential (primary) hypertension', category: 'Circulatory' },
            { code: 'E11.9', name: 'Type 2 diabetes mellitus without complications', category: 'Endocrine' },
            { code: 'B50.9', name: 'Plasmodium falciparum malaria, unspecified', category: 'Infectious' }
        ];

        // Medication data (NHIS approved)
        const medicationData = [
            { name: 'Paracetamol', strengths: ['500mg', '1000mg'], forms: ['Tablet', 'Syrup'], category: 'Analgesic' },
            { name: 'Ibuprofen', strengths: ['200mg', '400mg', '600mg'], forms: ['Tablet', 'Syrup'], category: 'NSAID' },
            { name: 'Amoxicillin', strengths: ['250mg', '500mg'], forms: ['Capsule', 'Syrup'], category: 'Antibiotic' },
            { name: 'Ciprofloxacin', strengths: ['250mg', '500mg'], forms: ['Tablet'], category: 'Antibiotic' },
            { name: 'Metformin', strengths: ['500mg', '850mg', '1000mg'], forms: ['Tablet'], category: 'Antidiabetic' },
            { name: 'Amlodipine', strengths: ['5mg', '10mg'], forms: ['Tablet'], category: 'Antihypertensive' },
            { name: 'Omeprazole', strengths: ['20mg', '40mg'], forms: ['Capsule'], category: 'PPI' },
            { name: 'Artesunate-Amodiaquine', strengths: ['100mg/270mg'], forms: ['Tablet'], category: 'Antimalarial' }
        ];

        let currentPatient = null;
        let selectedDiagnoses = [];
        let prescribedMedications = [];
        let selectedMedication = null;
        
        // Helper function to scroll to top
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        


        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the page
            initializePage();
            loadRecentConsultations();
            
            // Set today's date
            document.getElementById('consultation_date').value = new Date().toISOString().split('T')[0];
        });

        // Initialize page
        function initializePage() {
            // Setup user dropdown
            const userMenuButton = document.getElementById('userMenuButton');
            const userDropdown = document.getElementById('userDropdown');
            
            userMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
            });
            
            document.addEventListener('click', function(e) {
                if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.add('hidden');
                }
                
                // Hide diagnosis dropdown when clicking outside
                if (!e.target.closest('.diagnosis-search')) {
                    document.getElementById('diagnosis_dropdown').style.display = 'none';
                }
            });

            // Setup patient search
            document.getElementById('patient_search').addEventListener('input', function() {
                if (this.value.length >= 3) {
                    searchPatient();
                }
            });
            
            // Setup medication search
            document.getElementById('medication_search').addEventListener('input', function() {
                if (this.value.length >= 2) {
                    searchMedication();
                }
            });
        }

        // Search patient
        async function searchPatient() {
            const searchTerm = document.getElementById('patient_search').value.trim();
            
            if (searchTerm.length < 3) {
                return;
            }
            
            try {
                console.log('Searching for patients with term:', searchTerm);
                const url = `../diagnosis-medication-api.php?action=search_patients&q=${encodeURIComponent(searchTerm)}`;
                console.log('Request URL:', url);
                
                const response = await fetch(url);
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('API Response:', result);
                
                if (result.status === 'success') {
                    if (result.data && result.data.length > 0) {
                        // Show dropdown for patient selection
                        showPatientSelection(result.data);
                    } else {
                        showPatientSelection([], 'No patients found matching your search');
                    }
                } else {
                    showPatientSelection([], `Search failed: ${result.message || 'Unknown error'}`);
                }
            } catch (error) {
                console.error('Error searching patients:', error);
                showPatientSelection([], `Search failed: ${error.message}`);
            }
        }
        
        // Show patient selection dropdown
        function showPatientSelection(patients, errorMessage = null) {
            const searchContainer = document.getElementById('patient_search').parentElement;
            
            // Remove existing dropdown
            const existingDropdown = document.getElementById('patientDropdown');
            if (existingDropdown) {
                existingDropdown.remove();
            }
            
            // Create dropdown element
            const dropdown = document.createElement('div');
            dropdown.id = 'patientDropdown';
            dropdown.className = 'absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto';
            
            if (errorMessage) {
                dropdown.innerHTML = `
                    <div class="p-3 text-gray-500 text-center">
                        ${errorMessage}
                    </div>
                `;
            } else {
                dropdown.innerHTML = patients.map(patient => {
                    const fullName = `${patient.first_name || ''} ${patient.last_name || ''}`.trim();
                    const displayName = fullName || patient.name || 'Unnamed Patient';
                    
                    return `
                        <div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                             onclick="selectPatientFromDropdown('${patient.id}', '${displayName}', '${patient.nhis_number || ''}', '${patient.date_of_birth || ''}', '${patient.allergies || ''}')">
                            <div class="font-medium text-gray-900">${displayName}</div>
                            <div class="text-sm text-gray-500">
                                NHIS: ${patient.nhis_number || 'Not provided'} | 
                                DOB: ${patient.date_of_birth || 'Not provided'}
                            </div>
                        </div>
                    `;
                }).join('');
            }
            
            // Position dropdown relative to search container
            searchContainer.style.position = 'relative';
            searchContainer.appendChild(dropdown);
            
            // Hide dropdown when clicking outside
            setTimeout(() => {
                document.addEventListener('click', function hideDropdown(e) {
                    if (!searchContainer.contains(e.target)) {
                        dropdown.remove();
                        document.removeEventListener('click', hideDropdown);
                    }
                });
            }, 100);
        }
        
        // Select patient from dropdown
        function selectPatientFromDropdown(id, name, nhis, dateOfBirth, allergies) {
            const patient = {
                id: id,
                name: name,
                nhis_number: nhis,
                nhis: nhis, // Keep both for compatibility
                date_of_birth: dateOfBirth,
                allergies: allergies
            };
            
            console.log('Selecting patient:', patient);
            selectPatient(patient);
            
            // Remove dropdown
            const dropdown = document.getElementById('patientDropdown');
            if (dropdown) {
                dropdown.remove();
            }
        }

        // Select patient
        function selectPatient(patient) {
            scrollToTop();
            currentPatient = patient;
            
            // Calculate age if date_of_birth is available
            let age = 'N/A';
            if (patient.date_of_birth) {
                const today = new Date();
                const birthDate = new Date(patient.date_of_birth);
                age = Math.floor((today - birthDate) / (365.25 * 24 * 60 * 60 * 1000)) + ' years';
            }
            
            // Show patient info
            document.getElementById('selectedPatientInfo').classList.remove('hidden');
            document.getElementById('patient_name').textContent = patient.name || 'N/A';
            document.getElementById('patient_nhis').textContent = patient.nhis || patient.nhis_number || 'N/A';
            document.getElementById('patient_age').textContent = age;
            document.getElementById('patient_allergies').textContent = patient.allergies || 'None recorded';
            
            // Update search field
            document.getElementById('patient_search').value = patient.name || '';
            
            // Remove patient options if visible
            const existingOptions = document.querySelector('.patient-options');
            if (existingOptions) {
                existingOptions.remove();
            }
            
            showAlert('Patient selected successfully', 'success');
            
            // Show allergy warning if present
            if (patient.allergies && patient.allergies !== '-' && patient.allergies !== 'None recorded') {
                showAlert(`⚠️ Allergy Alert: ${patient.allergies}`, 'danger');
            }
        }

        // Search diagnosis
        async function searchDiagnosis(term) {
            const dropdown = document.getElementById('diagnosis_dropdown');
            
            if (term.length < 2) {
                dropdown.style.display = 'none';
                return;
            }
            
            try {
                const response = await fetch(`../diagnosis-medication-api.php?action=search_icd10&query=${encodeURIComponent(term)}&limit=10`);
                const result = await response.json();
                
                if (result.status === 'success' && result.data.length > 0) {
                    dropdown.innerHTML = '';
                    result.data.forEach(diagnosis => {
                        const item = document.createElement('div');
                        item.className = 'diagnosis-item';
                        item.innerHTML = `
                            <div class="diagnosis-code">${diagnosis.id}</div>
                            <div class="diagnosis-name">${diagnosis.description}</div>
                        `;
                        item.onclick = () => selectDiagnosis({
                            code: diagnosis.id,
                            name: diagnosis.description,
                            category: diagnosis.category || 'General'
                        });
                        dropdown.appendChild(item);
                    });
                    dropdown.style.display = 'block';
                } else {
                    dropdown.style.display = 'none';
                }
            } catch (error) {
                console.error('Error searching diagnoses:', error);
                dropdown.style.display = 'none';
            }
        }

        // Select diagnosis
        function selectDiagnosis(diagnosis) {
            scrollToTop();
            
            // Check if already selected
            if (selectedDiagnoses.find(d => d.code === diagnosis.code)) {
                showAlert('Diagnosis already selected', 'warning');
                return;
            }
            
            selectedDiagnoses.push({...diagnosis, temp_id: Date.now()});
            updateSelectedDiagnoses();
            
            // Clear search
            document.getElementById('diagnosis_search').value = '';
            document.getElementById('diagnosis_dropdown').style.display = 'none';
            showAlert(`Added diagnosis: ${diagnosis.code} - ${diagnosis.name}`, 'success');
        }

        // Update selected diagnoses display
        function updateSelectedDiagnoses() {
            const container = document.getElementById('selected_diagnoses');
            
            if (selectedDiagnoses.length === 0) {
                container.innerHTML = '';
                return;
            }
            
            container.innerHTML = '';
            
            selectedDiagnoses.forEach(diagnosis => {
                const diagnosisDiv = document.createElement('div');
                diagnosisDiv.className = 'selected-diagnosis';
                diagnosisDiv.innerHTML = `
                    <div class="diagnosis-header">
                        <div>
                            <div class="diagnosis-title">${diagnosis.code} - ${diagnosis.name}</div>
                            <div class="diagnosis-subtitle">Category: ${diagnosis.category || 'Not specified'}</div>
                        </div>
                        <button class="text-red-500 hover:text-red-700" onclick="removeDiagnosis(${diagnosis.temp_id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                container.appendChild(diagnosisDiv);
            });
        }

        // Remove diagnosis
        function removeDiagnosis(temp_id) {
            selectedDiagnoses = selectedDiagnoses.filter(d => d.temp_id !== temp_id);
            updateSelectedDiagnoses();
            showAlert('Diagnosis removed', 'success');
        }

        // Search medication
        async function searchMedication() {
            const searchTerm = document.getElementById('medication_search').value.trim();
            
            if (!searchTerm) {
                showAlert('Please enter medication name', 'warning');
                return;
            }
            
            try {
                const response = await fetch(`../diagnosis-medication-api.php?action=search_medications&query=${encodeURIComponent(searchTerm)}&limit=10`);
                const result = await response.json();
                
                if (result.status === 'success' && result.data.length > 0) {
                    displayMedicationOptions(result.data);
                } else {
                    showAlert('Medication not found in NHIS formulary', 'danger');
                    clearMedicationStrengths();
                }
            } catch (error) {
                console.error('Error searching medications:', error);
                showAlert('Error searching medications. Please try again.', 'danger');
            }
        }
        
        // Display medication options
        function displayMedicationOptions(medications) {
            if (medications.length === 1) {
                selectMedication(medications[0]);
            } else {
                let optionsHtml = '<div class="medication-options" style="background: white; border: 1px solid #ddd; border-radius: 8px; max-height: 200px; overflow-y: auto; margin-top: 5px; z-index: 1000; position: absolute; width: 100%;">';
                
                medications.forEach(medication => {
                    optionsHtml += `
                        <div class="medication-option" style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;" 
                             onclick="selectMedication(${JSON.stringify(medication).replace(/"/g, '&quot;')})">
                            <strong>${medication.name}</strong> (${medication.strength})<br>
                            <small>${medication.generic_name || ''} | ${medication.dosage_form || ''} | ${medication.drug_class || ''}</small>
                        </div>
                    `;
                });
                
                optionsHtml += '</div>';
                
                const searchContainer = document.getElementById('medication_search').parentElement;
                let existingOptions = searchContainer.querySelector('.medication-options');
                if (existingOptions) {
                    existingOptions.remove();
                }
                searchContainer.insertAdjacentHTML('afterend', optionsHtml);
            }
        }
        
        // Select medication
        function selectMedication(medication) {
            selectedMedication = medication;
            
            // Update search field
            document.getElementById('medication_search').value = medication.name;
            
            // Populate strength
            const strengthSelect = document.getElementById('medication_strength');
            strengthSelect.innerHTML = '<option value="">Select strength</option>';
            if (medication.strength) {
                const option = document.createElement('option');
                option.value = medication.strength;
                option.textContent = medication.strength;
                option.selected = true;
                strengthSelect.appendChild(option);
            }
            
            // Set dosage form if available
            if (medication.dosage_form) {
                document.getElementById('dosage_form').value = medication.dosage_form;
            }
            
            // Remove medication options if visible
            const existingOptions = document.querySelector('.medication-options');
            if (existingOptions) {
                existingOptions.remove();
            }
            
            showAlert(`Selected: ${medication.name} (${medication.drug_class || 'NHIS Approved'})`, 'success');
        }
        
        // Clear medication strengths
        function clearMedicationStrengths() {
            document.getElementById('medication_strength').innerHTML = '<option value="">Select strength</option>';
        }

        // Add medication to prescription
        function addMedication() {
            scrollToTop();
            
            const medicationName = document.getElementById('medication_search').value.trim();
            const strength = document.getElementById('medication_strength').value;
            const form = document.getElementById('dosage_form').value;
            const frequency = document.getElementById('frequency').value;
            const duration = document.getElementById('duration').value;
            const quantity = document.getElementById('quantity').value;
            const instructions = document.getElementById('special_instructions').value;
            
            // Validate required fields
            if (!medicationName || !strength || !form || !frequency || !duration || !quantity) {
                showAlert('Please fill in all required medication fields', 'warning');
                return;
            }
            
            if (!selectedMedication) {
                showAlert('Please select a medication from the search results', 'warning');
                return;
            }
            
            // Check for duplicates
            if (prescribedMedications.find(med => med.medication_id === selectedMedication.id)) {
                showAlert('This medication is already prescribed', 'warning');
                return;
            }
            
            const medication = {
                id: Date.now(),
                medication_id: selectedMedication.id,
                name: medicationName,
                strength: strength,
                form: form,
                frequency: frequency,
                duration: duration,
                quantity: quantity,
                instructions: instructions,
                unit_price: selectedMedication.unit_price || 0
            };
            
            prescribedMedications.push(medication);
            updateMedicationList();
            clearMedicationForm();
            checkDrugInteractions();
            
            showAlert(`Added medication: ${medicationName} ${strength}`, 'success');
        }

        // Update medication list display
        function updateMedicationList() {
            const container = document.getElementById('medication_list');
            
            if (prescribedMedications.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-gray-500 py-4">
                        <i class="fas fa-pills text-3xl mb-2"></i>
                        <p>No medications prescribed yet</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = '';
            prescribedMedications.forEach(medication => {
                const medicationDiv = document.createElement('div');
                medicationDiv.className = 'medication-item';
                medicationDiv.innerHTML = `
                    <div class="medication-header">
                        <div>
                            <div class="medication-name">${medication.name} ${medication.strength}</div>
                            <div class="medication-strength">${medication.form} - ${medication.frequency} for ${medication.duration}</div>
                        </div>
                        <button class="text-red-500 hover:text-red-700" onclick="removeMedication(${medication.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="text-sm text-gray-600 mt-2">
                        <strong>Quantity:</strong> ${medication.quantity} units
                        ${medication.instructions ? `<br><strong>Instructions:</strong> ${medication.instructions}` : ''}
                    </div>
                `;
                container.appendChild(medicationDiv);
            });
        }

        // Remove medication
        function removeMedication(id) {
            prescribedMedications = prescribedMedications.filter(med => med.id !== id);
            updateMedicationList();
            checkDrugInteractions();
            showAlert('Medication removed', 'success');
        }

        // Clear medication form
        function clearMedicationForm() {
            document.getElementById('medication_search').value = '';
            document.getElementById('medication_strength').value = '';
            document.getElementById('dosage_form').value = '';
            document.getElementById('frequency').value = '';
            document.getElementById('duration').value = '';
            document.getElementById('quantity').value = '';
            document.getElementById('special_instructions').value = '';
        }

        // Check drug interactions
        function checkDrugInteractions() {
            const alertDiv = document.getElementById('drug_interactions_alert');
            const detailsSpan = document.getElementById('interaction_details');
            
            // Simple interaction check (in real implementation, use drug interaction database)
            const interactions = [];
            
            // Check for common interactions
            const medicationNames = prescribedMedications.map(med => med.name.toLowerCase());
            
            if (medicationNames.includes('warfarin') && medicationNames.includes('ibuprofen')) {
                interactions.push('Warfarin + Ibuprofen: Increased bleeding risk');
            }
            
            if (medicationNames.includes('metformin') && medicationNames.includes('ciprofloxacin')) {
                interactions.push('Metformin + Ciprofloxacin: Monitor glucose levels');
            }
            
            if (interactions.length > 0) {
                detailsSpan.textContent = interactions.join('; ');
                alertDiv.classList.remove('hidden');
            } else {
                alertDiv.classList.add('hidden');
            }
        }

        // Finalize diagnosis and proceed to claims
        async function finalizeDiagnosis() {
            scrollToTop();
            
            if (!currentPatient) {
                showAlert('Please select a patient first', 'warning');
                return;
            }
            
            if (selectedDiagnoses.length === 0) {
                showAlert('Please add at least one diagnosis', 'warning');
                return;
            }
            
            if (prescribedMedications.length === 0) {
                showAlert('Please add at least one medication', 'warning');
                return;
            }
            
            // Check if all medications have valid medication_id (might be missing if added before DB was populated)
            const invalidMedications = prescribedMedications.filter(med => !med.medication_id);
            if (invalidMedications.length > 0) {
                const invalidNames = invalidMedications.map(med => med.name).join(', ');
                showAlert(`⚠️ Some medications are invalid: ${invalidNames}. Please remove and re-add them from the search.`, 'warning');
                return;
            }
            
            try {
                showAlert('Finalizing consultation...', 'warning');
                
                // First create or get active visit
                console.log('Creating visit for patient:', currentPatient.id);
                
                const visitResponse = await fetch('../visit-api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        patient_id: currentPatient.id,
                        visit_type: 'OPD',
                        chief_complaint: document.getElementById('chief_complaint')?.value || '',
                        presenting_complaint: document.getElementById('presenting_complaint')?.value || '',
                        history_present_illness: document.getElementById('history_present_illness')?.value || '',
                        past_medical_history: document.getElementById('past_medical_history')?.value || '',
                        physical_examination: document.getElementById('physical_examination')?.value || ''
                    })
                });
                
                console.log('Visit response status:', visitResponse.status);
                console.log('Visit response headers:', visitResponse.headers.get('content-type'));
                
                // Check response status first
                if (!visitResponse.ok) {
                    const errorText = await visitResponse.text();
                    console.error('Visit API HTTP error:', errorText);
                    throw new Error(`Visit API error (${visitResponse.status}): ${errorText}`);
                }
                
                // Get response text first to check if it's empty
                const responseText = await visitResponse.text();
                console.log('Visit raw response:', responseText);
                
                if (!responseText || responseText.trim() === '') {
                    throw new Error('Visit API returned empty response');
                }
                
                // Try to parse JSON
                let visitResult;
                try {
                    visitResult = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error('Visit API JSON parse error:', jsonError);
                    console.error('Response text was:', responseText);
                    throw new Error('Visit API returned invalid JSON: ' + responseText.substring(0, 200));
                }
                
                console.log('Visit result:', visitResult);
                
                if (visitResult.status !== 'success') {
                    throw new Error(visitResult.message || 'Failed to create visit');
                }
                
                const visitId = visitResult.data.id;
                
                // Save diagnoses
                for (const diagnosis of selectedDiagnoses) {
                    const diagnosisData = {
                        visit_id: visitId,
                        icd10_code: diagnosis.code, // Use the actual ICD-10 code from database
                        diagnosis_notes: diagnosis.notes || '',
                        diagnosis_type: diagnosis.type || 'Primary'
                    };
                    
                    // Check if icd10_code is valid
                    if (!diagnosisData.icd10_code) {
                        throw new Error(`Missing ICD-10 code for diagnosis: ${diagnosis.name || 'Unknown'}`);
                    }
                    
                    const diagnosisResponse = await fetch('../diagnosis-medication-api.php?action=save_diagnosis', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(diagnosisData)
                    });
                    
                    // Check response status and parse safely
                    if (!diagnosisResponse.ok) {
                        const errorText = await diagnosisResponse.text();
                        throw new Error(`Diagnosis API error (${diagnosisResponse.status}): ${errorText}`);
                    }
                    
                    const diagnosisResponseText = await diagnosisResponse.text();
                    if (!diagnosisResponseText || diagnosisResponseText.trim() === '') {
                        throw new Error('Diagnosis API returned empty response');
                    }
                    
                    let diagnosisResult;
                    try {
                        diagnosisResult = JSON.parse(diagnosisResponseText);
                    } catch (jsonError) {
                        throw new Error('Diagnosis API returned invalid JSON: ' + diagnosisResponseText.substring(0, 200));
                    }
                    
                    console.log('Diagnosis response:', diagnosisResult);
                    
                    if (diagnosisResult.status !== 'success') {
                        throw new Error(`Failed to save diagnosis: ${diagnosisResult.message}`);
                    }
                }
                
                // Save prescriptions
                for (const medication of prescribedMedications) {
                    // Ensure medication has required medication_id
                    if (!medication.medication_id) {
                        throw new Error(`Medication "${medication.name}" is missing medication_id. Please re-add from search.`);
                    }
                    
                    const prescriptionData = {
                        visit_id: visitId,
                        medication_id: medication.medication_id,
                        dosage: medication.strength,
                        frequency: medication.frequency,
                        duration: medication.duration,
                        route_of_administration: 'Oral',
                        instructions: medication.instructions || '',
                        quantity: parseInt(medication.quantity) || 1
                    };
                    
                    console.log('Saving prescription:', prescriptionData);
                    
                    const prescriptionResponse = await fetch('../diagnosis-medication-api.php?action=save_prescription', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(prescriptionData)
                    });
                    
                    // Check response status and parse safely
                    if (!prescriptionResponse.ok) {
                        const errorText = await prescriptionResponse.text();
                        throw new Error(`Prescription API error (${prescriptionResponse.status}): ${errorText}`);
                    }
                    
                    const prescriptionResponseText = await prescriptionResponse.text();
                    if (!prescriptionResponseText || prescriptionResponseText.trim() === '') {
                        throw new Error('Prescription API returned empty response');
                    }
                    
                    let prescriptionResult;
                    try {
                        prescriptionResult = JSON.parse(prescriptionResponseText);
                    } catch (jsonError) {
                        throw new Error('Prescription API returned invalid JSON: ' + prescriptionResponseText.substring(0, 200));
                    }
                    
                    console.log('Prescription response:', prescriptionResult);
                    
                    if (prescriptionResult.status !== 'success') {
                        throw new Error(`Failed to save prescription for ${medication.name}: ${prescriptionResult.message}`);
                    }
                }
                
                // Finalize consultation
                console.log('Finalizing consultation for visit:', visitId);
                
                const finalizeResponse = await fetch('../diagnosis-medication-api.php?action=finalize_consultation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        visit_id: visitId
                    })
                });
                
                // Check response status and parse safely
                if (!finalizeResponse.ok) {
                    const errorText = await finalizeResponse.text();
                    throw new Error(`Finalize API error (${finalizeResponse.status}): ${errorText}`);
                }
                
                const finalizeResponseText = await finalizeResponse.text();
                if (!finalizeResponseText || finalizeResponseText.trim() === '') {
                    throw new Error('Finalize API returned empty response');
                }
                
                let finalizeResult;
                try {
                    finalizeResult = JSON.parse(finalizeResponseText);
                } catch (jsonError) {
                    throw new Error('Finalize API returned invalid JSON: ' + finalizeResponseText.substring(0, 200));
                }
                
                console.log('Finalize response:', finalizeResult);
                
                if (finalizeResult.status !== 'success') {
                    throw new Error(`Failed to finalize consultation: ${finalizeResult.message}`);
                }
                
                showAlert(`Consultation finalized successfully! Visit ID: ${visitId}`, 'success');
                
                // Ask if user wants to proceed to claims processing
                setTimeout(() => {
                    if (confirm('Would you like to proceed to claims processing for this consultation?')) {
                        window.location.href = `claims-processing.php?visit=${visitId}&patient=${currentPatient.id}`;
                    } else {
                        // Reset form
                        resetForm();
                    }
                }, 2000);
                
            } catch (error) {
                console.error('Error finalizing consultation:', error);
                showAlert('Error finalizing consultation: ' + error.message, 'danger');
            }
        }
        
        // Reset form after successful submission
        function resetForm() {
            // Clear form fields
            document.getElementById('chief_complaint').value = '';
            document.getElementById('history_present_illness').value = '';
            document.getElementById('physical_examination').value = '';
            document.getElementById('clinical_impression').value = '';
            document.getElementById('treatment_plan').value = '';
            document.getElementById('patient_education').value = '';
            document.getElementById('follow_up').value = '';
            document.getElementById('next_appointment').value = '';
            document.getElementById('warning_signs').value = '';
            document.getElementById('differential_diagnosis').value = '';
            
            // Clear selected data
            selectedDiagnoses = [];
            prescribedMedications = [];
            selectedMedication = null;
            
            // Update displays
            updateSelectedDiagnoses();
            updateMedicationList();
            clearMedicationForm();
            
            // Hide patient info
            document.getElementById('selectedPatientInfo').classList.add('hidden');
            document.getElementById('patient_search').value = '';
            currentPatient = null;
            
            showAlert('Form reset. Ready for next patient.', 'info');
        }

        // Save draft
        function saveDraft() {
            scrollToTop();
            
            const draftData = {
                patient: currentPatient,
                diagnoses: selectedDiagnoses,
                medications: prescribedMedications,
                formData: {
                    chiefComplaint: document.getElementById('chief_complaint').value,
                    historyPresentIllness: document.getElementById('history_present_illness').value,
                    physicalExamination: document.getElementById('physical_examination').value,
                    clinicalImpression: document.getElementById('clinical_impression').value,
                    treatmentPlan: document.getElementById('treatment_plan').value,
                    patientEducation: document.getElementById('patient_education').value,
                    followUp: document.getElementById('follow_up').value,
                    nextAppointment: document.getElementById('next_appointment').value,
                    warningSigns: document.getElementById('warning_signs').value
                }
            };
            
            localStorage.setItem('consultationDraft', JSON.stringify(draftData));
            showAlert('Draft saved successfully', 'success');
        }

        // Print prescription
        function printPrescription() {
            if (prescribedMedications.length === 0) {
                showAlert('No medications to print', 'warning');
                return;
            }
            
            if (!currentPatient) {
                showAlert('Please select a patient first', 'warning');
                return;
            }
            
            // Create print content
            const printContent = generatePrintableRx();
            
            // Open new window and print
            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            
            // Close print window after printing (with delay)
            setTimeout(() => {
                printWindow.close();
            }, 1000);
        }

        // Generate prescription
        function generatePrescription() {
            if (prescribedMedications.length === 0) {
                showAlert('No medications prescribed', 'warning');
                return;
            }
            
            if (!currentPatient) {
                showAlert('Please select a patient first', 'warning');
                return;
            }
            
            showAlert('Generating NHIS prescription form...', 'info');
            
            // Generate downloadable prescription
            const prescriptionData = generatePrescriptionData();
            downloadPrescription(prescriptionData);
            
            showAlert('Prescription form generated and downloaded!', 'success');
        }
        
        // Generate printable prescription content
        function generatePrintableRx() {
            const today = new Date().toLocaleDateString();
            const primaryDiagnosis = selectedDiagnoses.find(d => d.type === 'Primary') || selectedDiagnoses[0];
            
            return `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Prescription - ${currentPatient.name}</title>
                    <style>
                        body { 
                            font-family: Arial, sans-serif; 
                            margin: 20px; 
                            color: #000;
                            background: white;
                        }
                        .header { 
                            text-align: center; 
                            border-bottom: 2px solid #000; 
                            padding-bottom: 15px; 
                            margin-bottom: 20px; 
                        }
                        .hospital-name { 
                            font-size: 24px; 
                            font-weight: bold; 
                            margin-bottom: 5px; 
                        }
                        .prescription-title { 
                            font-size: 18px; 
                            margin-top: 10px; 
                        }
                        .patient-info { 
                            margin-bottom: 20px; 
                            border: 1px solid #ccc; 
                            padding: 15px; 
                        }
                        .patient-info h3 { 
                            margin-top: 0; 
                            color: #333; 
                        }
                        .info-row { 
                            display: flex; 
                            margin-bottom: 8px; 
                        }
                        .info-label { 
                            font-weight: bold; 
                            width: 120px; 
                        }
                        .medications { 
                            margin-bottom: 30px; 
                        }
                        .medications h3 { 
                            color: #333; 
                            border-bottom: 1px solid #ccc; 
                            padding-bottom: 5px; 
                        }
                        .medication-item { 
                            border: 1px solid #ddd; 
                            margin-bottom: 15px; 
                            padding: 15px; 
                            background: #f9f9f9; 
                        }
                        .medication-name { 
                            font-size: 16px; 
                            font-weight: bold; 
                            color: #2563eb; 
                            margin-bottom: 8px; 
                        }
                        .medication-details { 
                            display: grid; 
                            grid-template-columns: 1fr 1fr; 
                            gap: 10px; 
                        }
                        .detail-item { 
                            display: flex; 
                        }
                        .detail-label { 
                            font-weight: bold; 
                            width: 80px; 
                        }
                        .instructions { 
                            grid-column: 1 / -1; 
                            margin-top: 8px; 
                            padding: 8px; 
                            background: white; 
                            border-radius: 4px; 
                        }
                        .footer { 
                            margin-top: 40px; 
                            padding-top: 20px; 
                            border-top: 1px solid #ccc; 
                        }
                        .signature-section { 
                            display: flex; 
                            justify-content: space-between; 
                            margin-top: 40px; 
                        }
                        .signature-box { 
                            text-align: center; 
                            width: 200px; 
                        }
                        .signature-line { 
                            border-top: 1px solid #000; 
                            margin-top: 40px; 
                            padding-top: 5px; 
                        }
                        @media print {
                            body { margin: 0; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="hospital-name">${currentPatient.hospital_name || 'SMART CLAIMS HOSPITAL'}</div>
                        <div>NHIS Accredited Healthcare Provider</div>
                        <div class="prescription-title">PRESCRIPTION</div>
                    </div>
                    
                    <div class="patient-info">
                        <h3>PATIENT INFORMATION</h3>
                        <div class="info-row">
                            <span class="info-label">Patient Name:</span>
                            <span>${currentPatient.name}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">NHIS Number:</span>
                            <span>${currentPatient.nhis_number || 'N/A'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date of Birth:</span>
                            <span>${currentPatient.date_of_birth || 'N/A'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Gender:</span>
                            <span>${currentPatient.gender || 'N/A'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span>${currentPatient.phone || 'N/A'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date:</span>
                            <span>${today}</span>
                        </div>
                        ${primaryDiagnosis ? `
                        <div class="info-row">
                            <span class="info-label">Diagnosis:</span>
                            <span>${primaryDiagnosis.code} - ${primaryDiagnosis.description}</span>
                        </div>
                        ` : ''}
                    </div>
                    
                    <div class="medications">
                        <h3>PRESCRIBED MEDICATIONS</h3>
                        ${prescribedMedications.map((med, index) => `
                            <div class="medication-item">
                                <div class="medication-name">${index + 1}. ${med.name}</div>
                                <div class="medication-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Strength:</span>
                                        <span>${med.strength || 'N/A'}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Form:</span>
                                        <span>${med.form || 'N/A'}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Frequency:</span>
                                        <span>${med.frequency || 'N/A'}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Duration:</span>
                                        <span>${med.duration || 'N/A'}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Quantity:</span>
                                        <span>${med.quantity || 'N/A'}</span>
                                    </div>
                                    ${med.instructions ? `
                                    <div class="instructions">
                                        <strong>Instructions:</strong> ${med.instructions}
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="footer">
                        <div><strong>Note:</strong> All prescribed medications are NHIS-covered. Please present this prescription and your NHIS card at the pharmacy.</div>
                        
                        <div class="signature-section">
                            <div class="signature-box">
                                <div class="signature-line">
                                    Doctor's Signature
                                </div>
                            </div>
                            <div class="signature-box">
                                <div class="signature-line">
                                    Date: ${today}
                                </div>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            `;
        }
        
        // Generate prescription data for download
        function generatePrescriptionData() {
            const today = new Date().toLocaleDateString();
            const primaryDiagnosis = selectedDiagnoses.find(d => d.type === 'Primary') || selectedDiagnoses[0];
            
            return {
                patient: currentPatient,
                date: today,
                diagnosis: primaryDiagnosis,
                medications: prescribedMedications,
                prescriber: '${user.full_name || "Doctor"}' // This should come from session
            };
        }
        
        // Download prescription as HTML file
        function downloadPrescription(prescriptionData) {
            const content = generatePrintableRx();
            const blob = new Blob([content], { type: 'text/html' });
            const url = window.URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `prescription_${currentPatient.name.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.html`;
            
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }

        // Load previous prescription
        function loadPreviousPrescription() {
            if (!currentPatient) {
                showAlert('Please select a patient first', 'warning');
                return;
            }
            
            // Mock previous prescription data
            const previousRx = [
                {
                    name: 'Amlodipine',
                    strength: '5mg',
                    form: 'Tablet',
                    frequency: 'Once daily',
                    duration: '1 month',
                    quantity: '30',
                    instructions: 'Take in the morning'
                }
            ];
            
            prescribedMedications = previousRx.map(med => ({...med, id: Date.now() + Math.random()}));
            updateMedicationList();
            
            showAlert('Previous prescription loaded', 'success');
        }

        // Load recent consultations
        async function loadRecentConsultations() {
            const tableBody = document.getElementById('recent_consultations');
            
            try {
                // Show loading message
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Loading recent consultations...
                        </td>
                    </tr>
                `;
                
                const response = await fetch('../diagnosis-medication-api.php?action=get_recent_consultations&limit=10');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    tableBody.innerHTML = '';
                    
                    if (result.data && result.data.length > 0) {
                        result.data.forEach(consultation => {
                            const row = document.createElement('tr');
                            const visitDate = new Date(consultation.visit_date).toLocaleDateString();
                            const diagnosisText = consultation.primary_diagnosis 
                                ? `${consultation.icd10_code} - ${consultation.primary_diagnosis}` 
                                : 'No diagnosis recorded';
                            const medicationText = consultation.medication_count > 0 
                                ? `${consultation.medication_count} medication${consultation.medication_count > 1 ? 's' : ''}` 
                                : 'No medications';
                            
                            // Determine status based on consultation completeness
                            let status = consultation.status || 'Pending';
                            let statusClass = 'bg-gray-100 text-gray-800';
                            
                            // Logic to determine actual status
                            if (consultation.status === 'Completed') {
                                status = 'Completed';
                                statusClass = 'bg-green-100 text-green-800';
                            } else if (consultation.primary_diagnosis && consultation.medication_count > 0) {
                                status = 'Awaiting Approval';
                                statusClass = 'bg-orange-100 text-orange-800';
                            } else if (consultation.primary_diagnosis || consultation.medication_count > 0) {
                                status = 'In Progress';
                                statusClass = 'bg-blue-100 text-blue-800';
                            } else {
                                status = 'Pending';
                                statusClass = 'bg-yellow-100 text-yellow-800';
                            }
                            
                            row.innerHTML = `
                                <td>${visitDate}</td>
                                <td>
                                    <div class="font-medium">${consultation.patient_name}</div>
                                    <div class="text-sm text-gray-500">${consultation.nhis_number || 'No NHIS'}</div>
                                </td>
                                <td>${diagnosisText}</td>
                                <td>${medicationText}</td>
                                <td>${consultation.physician_name || 'Unknown'}</td>
                                <td>
                                    <span class="px-2 py-1 text-xs rounded-full ${statusClass}">
                                        ${status}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="viewConsultation(${consultation.visit_id})" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center py-4 text-gray-500">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No recent consultations found
                                </td>
                            </tr>
                        `;
                    }
                } else {
                    throw new Error(result.message || 'Failed to load consultations');
                }
            } catch (error) {
                console.error('Error loading recent consultations:', error);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4 text-red-500">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Error loading consultations: ${error.message}
                        </td>
                    </tr>
                `;
            }
        }

        // View consultation details
        async function viewConsultation(visitId) {
            try {
                // Show loading
                showAlert('Loading consultation details...', 'info');
                
                // Fetch consultation details
                const [diagnosesResponse, prescriptionsResponse] = await Promise.all([
                    fetch(`../diagnosis-medication-api.php?action=get_consultation_diagnoses&visit_id=${visitId}`),
                    fetch(`../diagnosis-medication-api.php?action=get_consultation_prescriptions&visit_id=${visitId}`)
                ]);
                
                if (!diagnosesResponse.ok || !prescriptionsResponse.ok) {
                    throw new Error('Failed to fetch consultation details');
                }
                
                const diagnosesResult = await diagnosesResponse.json();
                const prescriptionsResult = await prescriptionsResponse.json();
                
                if (diagnosesResult.status === 'success' && prescriptionsResult.status === 'success') {
                    showConsultationModal(visitId, diagnosesResult.data, prescriptionsResult.data);
                } else {
                    throw new Error('Failed to load consultation data');
                }
                
            } catch (error) {
                console.error('Error loading consultation:', error);
                showAlert('Error loading consultation details: ' + error.message, 'danger');
            }
        }
        
        // Show consultation details in modal
        function showConsultationModal(visitId, diagnoses, prescriptions) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
            modal.id = 'consultationModal';
            
            modal.innerHTML = `
                <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <!-- Header -->
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-900">
                                <i class="fas fa-eye mr-2"></i>
                                Consultation Details - Visit #${visitId}
                            </h3>
                            <button onclick="closeConsultationModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <!-- Diagnoses Section -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold mb-3 text-blue-600">
                                <i class="fas fa-stethoscope mr-2"></i>
                                Diagnoses (${diagnoses.length})
                            </h4>
                            ${diagnoses.length > 0 ? `
                                <div class="space-y-3">
                                    ${diagnoses.map(diagnosis => `
                                        <div class="bg-blue-50 p-3 rounded-lg border-l-4 border-blue-500">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full ${diagnosis.diagnosis_type === 'Primary' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'}">
                                                        ${diagnosis.diagnosis_type}
                                                    </span>
                                                    <h5 class="font-medium mt-1">${diagnosis.icd10_code}</h5>
                                                    <p class="text-sm text-gray-600">${diagnosis.diagnosis_description || 'No description'}</p>
                                                    ${diagnosis.diagnosis_notes ? `<p class="text-sm text-gray-500 mt-1"><strong>Notes:</strong> ${diagnosis.diagnosis_notes}</p>` : ''}
                                                </div>
                                                <span class="text-xs text-gray-400">${new Date(diagnosis.diagnosed_at).toLocaleDateString()}</span>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : '<p class="text-gray-500 italic">No diagnoses recorded for this visit.</p>'}
                        </div>
                        
                        <!-- Prescriptions Section -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold mb-3 text-green-600">
                                <i class="fas fa-pills mr-2"></i>
                                Prescriptions (${prescriptions.length})
                            </h4>
                            ${prescriptions.length > 0 ? `
                                <div class="space-y-3">
                                    ${prescriptions.map(prescription => `
                                        <div class="bg-green-50 p-3 rounded-lg border-l-4 border-green-500">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h5 class="font-medium">${prescription.medication_name}</h5>
                                                    ${prescription.generic_name ? `<p class="text-sm text-gray-600">Generic: ${prescription.generic_name}</p>` : ''}
                                                    <div class="grid grid-cols-2 gap-4 mt-2 text-sm">
                                                        <div><strong>Dosage:</strong> ${prescription.dosage}</div>
                                                        <div><strong>Frequency:</strong> ${prescription.frequency}</div>
                                                        <div><strong>Duration:</strong> ${prescription.duration}</div>
                                                        <div><strong>Quantity:</strong> ${prescription.quantity}</div>
                                                    </div>
                                                    ${prescription.instructions ? `<p class="text-sm text-gray-500 mt-1"><strong>Instructions:</strong> ${prescription.instructions}</p>` : ''}
                                                </div>
                                                <span class="text-xs text-gray-400">${new Date(prescription.prescribed_at).toLocaleDateString()}</span>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : '<p class="text-gray-500 italic">No prescriptions recorded for this visit.</p>'}
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                            <button onclick="closeConsultationModal()" class="btn btn-secondary">
                                <i class="fas fa-times mr-2"></i>
                                Close
                            </button>
                            <button onclick="approveConsultation(${visitId})" class="btn btn-success">
                                <i class="fas fa-check mr-2"></i>
                                Approve & Finalize
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        }
        
        // Close consultation modal
        function closeConsultationModal() {
            const modal = document.getElementById('consultationModal');
            if (modal) {
                modal.remove();
            }
        }
        
        // Approve consultation
        async function approveConsultation(visitId) {
            if (!confirm('Are you sure you want to approve and finalize this consultation?')) {
                return;
            }
            
            try {
                const response = await fetch('../diagnosis-medication-api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'approve_consultation',
                        visit_id: visitId
                    })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showAlert('Consultation approved successfully!', 'success');
                    closeConsultationModal();
                    loadRecentConsultations(); // Refresh the list
                } else {
                    throw new Error(result.message || 'Failed to approve consultation');
                }
                
            } catch (error) {
                console.error('Error approving consultation:', error);
                showAlert('Error approving consultation: ' + error.message, 'danger');
            }
        }
        
        // Show workflow guide
        function showWorkflowGuide() {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
            modal.id = 'workflowModal';
            
            modal.innerHTML = `
                <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
                    <div class="mt-3">
                        <!-- Header -->
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-gray-900">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                Diagnosis & Medication System Workflow
                            </h3>
                            <button onclick="closeWorkflowModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <!-- Workflow Steps -->
                        <div class="space-y-6">
                            <!-- Step 1 -->
                            <div class="border-l-4 border-blue-500 pl-4">
                                <h4 class="text-lg font-semibold text-blue-600 mb-2">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 inline-flex items-center justify-center text-sm mr-2">1</span>
                                    Patient Search & Selection
                                </h4>
                                <p class="text-gray-700 mb-2">Search for patient by name or NHIS number (minimum 3 characters). Select the patient to view their information and medical history.</p>
                                <p class="text-sm text-gray-500"><strong>Who:</strong> Doctor/Nurse</p>
                            </div>
                            
                            <!-- Step 2 -->
                            <div class="border-l-4 border-green-500 pl-4">
                                <h4 class="text-lg font-semibold text-green-600 mb-2">
                                    <span class="bg-green-500 text-white rounded-full w-6 h-6 inline-flex items-center justify-center text-sm mr-2">2</span>
                                    Diagnosis Recording
                                </h4>
                                <p class="text-gray-700 mb-2">Add diagnoses using ICD-10 codes. Mark primary diagnosis and add any secondary conditions. Include clinical notes as needed.</p>
                                <p class="text-sm text-gray-500"><strong>Who:</strong> Doctor</p>
                            </div>
                            
                            <!-- Step 3 -->
                            <div class="border-l-4 border-purple-500 pl-4">
                                <h4 class="text-lg font-semibold text-purple-600 mb-2">
                                    <span class="bg-purple-500 text-white rounded-full w-6 h-6 inline-flex items-center justify-center text-sm mr-2">3</span>
                                    Medication Prescription
                                </h4>
                                <p class="text-gray-700 mb-2">Prescribe medications with proper dosage, frequency, duration, and instructions. System shows NHIS-covered medications only.</p>
                                <p class="text-sm text-gray-500"><strong>Who:</strong> Doctor</p>
                            </div>
                            
                            <!-- Step 4 -->
                            <div class="border-l-4 border-orange-500 pl-4">
                                <h4 class="text-lg font-semibold text-orange-600 mb-2">
                                    <span class="bg-orange-500 text-white rounded-full w-6 h-6 inline-flex items-center justify-center text-sm mr-2">4</span>
                                    Consultation Review & Approval
                                </h4>
                                <p class="text-gray-700 mb-2">Review completed consultations in "Recent Consultations". Click "View" to see details, then "Approve & Finalize" to complete.</p>
                                <p class="text-sm text-gray-500"><strong>Who:</strong> Senior Doctor/Supervisor</p>
                            </div>
                            
                            <!-- Step 5 -->
                            <div class="border-l-4 border-red-500 pl-4">
                                <h4 class="text-lg font-semibold text-red-600 mb-2">
                                    <span class="bg-red-500 text-white rounded-full w-6 h-6 inline-flex items-center justify-center text-sm mr-2">5</span>
                                    Claims Processing
                                </h4>
                                <p class="text-gray-700 mb-2">Approved consultations are automatically prepared for NHIS claims submission with all required documentation.</p>
                                <p class="text-sm text-gray-500"><strong>Who:</strong> Claims Officer</p>
                            </div>
                        </div>
                        
                        <!-- Status Meanings -->
                        <div class="mt-8 bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-3">
                                <i class="fas fa-info-circle mr-2"></i>
                                Consultation Status Meanings
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center">
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 mr-2">Pending</span>
                                    <span class="text-sm">Waiting for diagnosis/prescription completion</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 mr-2">In Progress</span>
                                    <span class="text-sm">Currently being worked on by clinician</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800 mr-2">Awaiting Approval</span>
                                    <span class="text-sm">Ready for supervisor review</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 mr-2">Completed</span>
                                    <span class="text-sm">Approved and ready for claims</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Important Notes -->
                        <div class="mt-6 bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-2 text-blue-700">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Important Notes
                            </h4>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• Only NHIS-covered medications are available for prescription</li>
                                <li>• All consultations require supervisor approval before claims submission</li>
                                <li>• Primary diagnosis is required for all consultations</li>
                                <li>• Patient allergies are automatically checked against prescribed medications</li>
                                <li>• All actions are logged for audit purposes</li>
                            </ul>
                        </div>
                        
                        <!-- Close Button -->
                        <div class="flex justify-end mt-6 pt-4 border-t">
                            <button onclick="closeWorkflowModal()" class="btn btn-primary">
                                <i class="fas fa-check mr-2"></i>
                                Got it!
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        }
        
        // Close workflow modal
        function closeWorkflowModal() {
            const modal = document.getElementById('workflowModal');
            if (modal) {
                modal.remove();
            }
        }
        
        // Test medications database
        async function testMedications() {
            try {
                showAlert('Testing medication database...', 'info');
                
                const response = await fetch('../diagnosis-medication-api.php?action=test_medications');
                const result = await response.json();
                
                if (result.status === 'success') {
                    const sampleList = result.samples.map(med => 
                        `${med.name} (${med.strength || 'N/A'}) - ${med.generic_name || 'N/A'}`
                    ).join('\n');
                    
                    showAlert(`✅ Database OK: ${result.count} medications found\n\nSamples:\n${sampleList}`, 'success');
                } else {
                    showAlert(`❌ ${result.message}\n\nTo fix: Run populate_medications.php script`, 'danger');
                }
                
            } catch (error) {
                console.error('Error testing medications:', error);
                showAlert('Error testing medication database: ' + error.message, 'danger');
            }
        }
        
        // Debug function to check current prescription data
        function debugPrescriptions() {
            console.log('Current prescribed medications:', prescribedMedications);
            console.log('Current selected diagnoses:', selectedDiagnoses);
            
            const medSummary = prescribedMedications.map(med => 
                `${med.name} (ID: ${med.medication_id || 'MISSING'})`
            ).join('\n');
            
            showAlert(`Debug Info:\n\nPrescribed Medications:\n${medSummary}\n\nDiagnoses: ${selectedDiagnoses.length}`, 'info');
        }

        // Show alert message
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'} mr-2"></i>
                ${message}
            `;
            
            alertContainer.appendChild(alertDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>