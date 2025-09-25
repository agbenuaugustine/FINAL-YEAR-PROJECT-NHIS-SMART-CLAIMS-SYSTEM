<?php
/**
 * Vital Signs Page
 * 
 * Record patient vital signs including temperature, blood pressure, pulse, etc.
 */

// Include secure authentication middleware
require_once __DIR__ . '/secure_auth.php';

// User data is now available from secure_auth.php
// $user and $role variables are already set
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Vital Signs - Smart Claims NHIS</title>
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
        
        /* Vital sign input groups */
        .vital-group {
            background: linear-gradient(135deg, rgba(0, 113, 227, 0.05), rgba(66, 161, 236, 0.05));
            border: 1px solid rgba(0, 113, 227, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .vital-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .vital-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-right: 1rem;
        }
        
        .vital-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .vital-normal {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }
        
        .vital-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: auto;
        }
        
        .status-normal {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
        }
        
        .status-warning {
            background: linear-gradient(135deg, #fff3cd, #ffeeba);
            color: #856404;
        }
        
        .status-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
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
        
        /* Charts */
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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
                    <a href="/smartclaimsCL/api/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
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
            <a href="vital-signs.php" class="nav-item active">
                <i class="fas fa-heartbeat"></i>
                <span>Vital Signs</span>
            </a>
            <a href="diagnosis-medication.php" class="nav-item">
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
                            <i class="fas fa-heartbeat mr-2"></i>
                            Vital Signs Recording
                        </h2>
                        <p class="text-secondary text-lg">Record and monitor patient vital signs with automated assessment</p>
                        <p class="text-sm text-gray-600 mt-2">Real-time alerts for abnormal readings</p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="btn btn-secondary" onclick="loadTemplate()">
                            <i class="fas fa-clipboard mr-2"></i>
                            Load Template
                        </button>
                        <button class="btn btn-warning" onclick="printVitals()">
                            <i class="fas fa-print mr-2"></i>
                            Print Chart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer"></div>

            <!-- Client Selection -->
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
                        <label for="visit_date" class="form-label">Visit Date & Time</label>
                        <input type="datetime-local" id="visit_date" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="recorded_by" class="form-label">Recorded By</label>
                        <input type="text" 
                               id="recorded_by" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($user['full_name'] ?? 'Current User'); ?>" 
                               readonly>
                    </div>
                </div>
                
                <!-- Selected Patient Info -->
                <div id="selectedPatientInfo" class="hidden">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h4 class="font-semibold text-green-800 mb-2">Patient Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                            <div><strong>Name:</strong> <span id="patient_name">-</span></div>
                            <div><strong>NHIS:</strong> <span id="patient_nhis">-</span></div>
                            <div><strong>Age:</strong> <span id="patient_age">-</span></div>
                            <div><strong>Gender:</strong> <span id="patient_gender">-</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vital Signs Form -->
            <form id="vitalSignsForm" class="space-y-6">
                <!-- Temperature -->
                <div class="vital-group">
                    <div class="vital-header">
                        <div class="vital-icon" style="background: linear-gradient(135deg, #ff6b35, #f7931e);">
                            <i class="fas fa-thermometer-half"></i>
                        </div>
                        <div class="flex-1">
                            <div class="vital-name">Body Temperature</div>
                            <div class="vital-normal">Normal: 36.1°C - 37.2°C (97°F - 99°F)</div>
                        </div>
                        <div id="temp-status" class="vital-status status-normal hidden">Normal</div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="temperature" class="form-label">Temperature (°C)</label>
                            <input type="number" 
                                   id="temperature" 
                                   name="temperature" 
                                   class="form-control" 
                                   step="0.1" 
                                   min="30" 
                                   max="45" 
                                   placeholder="37.0"
                                   onchange="assessVitals()">
                        </div>
                        <div class="form-group">
                            <label for="temp_method" class="form-label">Method</label>
                            <select id="temp_method" name="temp_method" class="form-control">
                                <option value="">Select method</option>
                                <option value="Oral">Oral</option>
                                <option value="Axillary">Axillary</option>
                                <option value="Rectal">Rectal</option>
                                <option value="Tympanic">Tympanic</option>
                                <option value="Temporal">Temporal</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Blood Pressure -->
                <div class="vital-group">
                    <div class="vital-header">
                        <div class="vital-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="flex-1">
                            <div class="vital-name">Blood Pressure</div>
                            <div class="vital-normal">Normal: 90/60 - 120/80 mmHg</div>
                        </div>
                        <div id="bp-status" class="vital-status status-normal hidden">Normal</div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="systolic" class="form-label">Systolic (mmHg)</label>
                            <input type="number" 
                                   id="systolic" 
                                   name="systolic" 
                                   class="form-control" 
                                   min="60" 
                                   max="250" 
                                   placeholder="120"
                                   onchange="assessVitals()">
                        </div>
                        <div class="form-group">
                            <label for="diastolic" class="form-label">Diastolic (mmHg)</label>
                            <input type="number" 
                                   id="diastolic" 
                                   name="diastolic" 
                                   class="form-control" 
                                   min="40" 
                                   max="150" 
                                   placeholder="80"
                                   onchange="assessVitals()">
                        </div>
                        <div class="form-group">
                            <label for="bp_position" class="form-label">Position</label>
                            <select id="bp_position" name="bp_position" class="form-control">
                                <option value="">Select position</option>
                                <option value="Sitting">Sitting</option>
                                <option value="Standing">Standing</option>
                                <option value="Lying">Lying</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bp_arm" class="form-label">Arm</label>
                            <select id="bp_arm" name="bp_arm" class="form-control">
                                <option value="">Select arm</option>
                                <option value="Left">Left</option>
                                <option value="Right">Right</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Pulse & Heart Rate -->
                <div class="vital-group">
                    <div class="vital-header">
                        <div class="vital-icon" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div class="flex-1">
                            <div class="vital-name">Pulse & Heart Rate</div>
                            <div class="vital-normal">Normal: 60-100 beats per minute</div>
                        </div>
                        <div id="pulse-status" class="vital-status status-normal hidden">Normal</div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="pulse_rate" class="form-label">Pulse Rate (bpm)</label>
                            <input type="number" 
                                   id="pulse_rate" 
                                   name="pulse_rate" 
                                   class="form-control" 
                                   min="30" 
                                   max="200" 
                                   placeholder="72"
                                   onchange="assessVitals()">
                        </div>
                        <div class="form-group">
                            <label for="pulse_rhythm" class="form-label">Rhythm</label>
                            <select id="pulse_rhythm" name="pulse_rhythm" class="form-control">
                                <option value="">Select rhythm</option>
                                <option value="Regular">Regular</option>
                                <option value="Irregular">Irregular</option>
                                <option value="Irregularly Irregular">Irregularly Irregular</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="pulse_strength" class="form-label">Strength</label>
                            <select id="pulse_strength" name="pulse_strength" class="form-control">
                                <option value="">Select strength</option>
                                <option value="Weak">Weak</option>
                                <option value="Normal">Normal</option>
                                <option value="Strong">Strong</option>
                                <option value="Bounding">Bounding</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Respiratory Rate -->
                <div class="vital-group">
                    <div class="vital-header">
                        <div class="vital-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                            <i class="fas fa-lungs"></i>
                        </div>
                        <div class="flex-1">
                            <div class="vital-name">Respiratory Rate</div>
                            <div class="vital-normal">Normal: 12-20 breaths per minute</div>
                        </div>
                        <div id="resp-status" class="vital-status status-normal hidden">Normal</div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="respiratory_rate" class="form-label">Rate (breaths/min)</label>
                            <input type="number" 
                                   id="respiratory_rate" 
                                   name="respiratory_rate" 
                                   class="form-control" 
                                   min="5" 
                                   max="60" 
                                   placeholder="16"
                                   onchange="assessVitals()">
                        </div>
                        <div class="form-group">
                            <label for="breathing_pattern" class="form-label">Pattern</label>
                            <select id="breathing_pattern" name="breathing_pattern" class="form-control">
                                <option value="">Select pattern</option>
                                <option value="Regular">Regular</option>
                                <option value="Irregular">Irregular</option>
                                <option value="Shallow">Shallow</option>
                                <option value="Deep">Deep</option>
                                <option value="Labored">Labored</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Oxygen Saturation -->
                <div class="vital-group">
                    <div class="vital-header">
                        <div class="vital-icon" style="background: linear-gradient(135deg, #27ae60, #229954);">
                            <i class="fas fa-percent"></i>
                        </div>
                        <div class="flex-1">
                            <div class="vital-name">Oxygen Saturation (SpO2)</div>
                            <div class="vital-normal">Normal: 95-100%</div>
                        </div>
                        <div id="spo2-status" class="vital-status status-normal hidden">Normal</div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="oxygen_saturation" class="form-label">SpO2 (%)</label>
                            <input type="number" 
                                   id="oxygen_saturation" 
                                   name="oxygen_saturation" 
                                   class="form-control" 
                                   min="70" 
                                   max="100" 
                                   placeholder="98"
                                   onchange="assessVitals()">
                        </div>
                        <div class="form-group">
                            <label for="oxygen_support" class="form-label">Oxygen Support</label>
                            <select id="oxygen_support" name="oxygen_support" class="form-control">
                                <option value="">Select support</option>
                                <option value="Room Air">Room Air</option>
                                <option value="Nasal Cannula">Nasal Cannula</option>
                                <option value="Face Mask">Face Mask</option>
                                <option value="Non-rebreather">Non-rebreather</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Physical Measurements -->
                <div class="vital-group">
                    <div class="vital-header">
                        <div class="vital-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                            <i class="fas fa-weight"></i>
                        </div>
                        <div class="flex-1">
                            <div class="vital-name">Physical Measurements</div>
                            <div class="vital-normal">Weight, Height, BMI</div>
                        </div>
                        <div id="bmi-status" class="vital-status status-normal hidden">Normal</div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="weight" class="form-label">Weight (kg)</label>
                            <input type="number" 
                                   id="weight" 
                                   name="weight" 
                                   class="form-control" 
                                   step="0.1" 
                                   min="1" 
                                   max="300" 
                                   placeholder="70.0"
                                   onchange="calculateBMI()">
                        </div>
                        <div class="form-group">
                            <label for="height" class="form-label">Height (cm)</label>
                            <input type="number" 
                                   id="height" 
                                   name="height" 
                                   class="form-control" 
                                   min="50" 
                                   max="250" 
                                   placeholder="170"
                                   onchange="calculateBMI()">
                        </div>
                        <div class="form-group">
                            <label for="bmi" class="form-label">BMI</label>
                            <input type="text" 
                                   id="bmi" 
                                   name="bmi" 
                                   class="form-control" 
                                   readonly 
                                   placeholder="Auto-calculated">
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="card">
                    <h3 class="card-title text-xl font-bold mb-4">
                        <i class="fas fa-sticky-note mr-2"></i>
                        Additional Information
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="pain_score" class="form-label">Pain Score (0-10)</label>
                            <select id="pain_score" name="pain_score" class="form-control">
                                <option value="">Select pain level</option>
                                <option value="0">0 - No Pain</option>
                                <option value="1">1 - Minimal Pain</option>
                                <option value="2">2 - Mild Pain</option>
                                <option value="3">3 - Uncomfortable</option>
                                <option value="4">4 - Moderate Pain</option>
                                <option value="5">5 - Moderately Strong</option>
                                <option value="6">6 - Strong Pain</option>
                                <option value="7">7 - Very Strong</option>
                                <option value="8">8 - Intense Pain</option>
                                <option value="9">9 - Excruciating</option>
                                <option value="10">10 - Unimaginable</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="consciousness_level" class="form-label">Consciousness Level</label>
                            <select id="consciousness_level" name="consciousness_level" class="form-control">
                                <option value="">Select level</option>
                                <option value="Alert">Alert</option>
                                <option value="Lethargic">Lethargic</option>
                                <option value="Obtunded">Obtunded</option>
                                <option value="Stuporous">Stuporous</option>
                                <option value="Comatose">Comatose</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="general_appearance" class="form-label">General Appearance</label>
                            <select id="general_appearance" name="general_appearance" class="form-control">
                                <option value="">Select appearance</option>
                                <option value="Well">Well appearing</option>
                                <option value="Mild distress">Mild distress</option>
                                <option value="Moderate distress">Moderate distress</option>
                                <option value="Severe distress">Severe distress</option>
                                <option value="Critically ill">Critically ill</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes" class="form-label">Clinical Notes</label>
                        <textarea id="notes" 
                                  name="notes" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Enter any additional observations or notes..."></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            All vital signs are automatically assessed against normal ranges
                        </div>
                        <div class="flex space-x-3">
                            <button type="button" class="btn btn-secondary" onclick="clearForm()">
                                <i class="fas fa-broom mr-2"></i>
                                Clear Form
                            </button>
                            <button type="button" class="btn btn-warning" onclick="saveDraft()">
                                <i class="fas fa-save mr-2"></i>
                                Save Draft
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-heartbeat mr-2"></i>
                                Record Vitals
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Recent Vital Signs -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-history mr-2"></i>
                    Recent Vital Signs
                </h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Patient</th>
                                <th>Temp (°C)</th>
                                <th>BP (mmHg)</th>
                                <th>Pulse (bpm)</th>
                                <th>Resp (/min)</th>
                                <th>SpO2 (%)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recent-vitals">
                            <tr>
                                <td colspan="9" class="text-center py-4">Loading recent vital signs...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        
        <!-- Mobile Navigation -->
        <div class="mobile-nav">
            <a href="dashboard.php" class="mobile-nav-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="client-registration.php" class="mobile-nav-item">
                <i class="fas fa-user-plus"></i>
                <span>Clients</span>
            </a>
            <a href="service-requisition.php" class="mobile-nav-item">
                <i class="fas fa-clipboard-list"></i>
                <span>Services</span>
            </a>
            <a href="vital-signs.php" class="mobile-nav-item active">
                <i class="fas fa-heartbeat"></i>
                <span>Vitals</span>
            </a>
            <a href="diagnosis-medication.php" class="mobile-nav-item">
                <i class="fas fa-stethoscope"></i>
                <span>Diagnosis</span>
            </a>
            <a href="claims-processing.php" class="mobile-nav-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Claims</span>
            </a>
            <a href="reports.php" class="mobile-nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </div>
    </div>

    <script>
        let currentPatient = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the page
            initializePage();
            loadRecentVitals();
            
            // Set current date and time
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('visit_date').value = now.toISOString().slice(0, 16);
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
            });

            // Setup patient search
            document.getElementById('patient_search').addEventListener('input', function() {
                if (this.value.length >= 3) {
                    searchPatient();
                }
            });

            // Setup form submission
            document.getElementById('vitalSignsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                recordVitals();
            });
        }

        // Search patient
        function searchPatient() {
            const searchTerm = document.getElementById('patient_search').value.trim();
            
            if (searchTerm.length < 3) {
                showAlert('Please enter at least 3 characters to search', 'warning');
                return;
            }
            
            // Simulate API call
            setTimeout(() => {
                // Mock patient data
                const mockPatient = {
                    id: 'PT001',
                    name: 'Sarah Johnson',
                    nhis: '9876543210',
                    age: '28 years',
                    gender: 'Female',
                    lastVitals: {
                        date: '2024-01-10',
                        temperature: 36.8,
                        systolic: 118,
                        diastolic: 75,
                        pulse: 78
                    }
                };
                
                selectPatient(mockPatient);
            }, 1000);
        }

        // Select patient
        function selectPatient(patient) {
            currentPatient = patient;
            
            // Show patient info
            document.getElementById('selectedPatientInfo').classList.remove('hidden');
            document.getElementById('patient_name').textContent = patient.name;
            document.getElementById('patient_nhis').textContent = patient.nhis;
            document.getElementById('patient_age').textContent = patient.age;
            document.getElementById('patient_gender').textContent = patient.gender;
            
            showAlert('Patient selected successfully', 'success');
            
            // Load previous vitals if available
            if (patient.lastVitals) {
                showAlert(`Last vitals recorded on ${patient.lastVitals.date}`, 'warning');
            }
        }

        // Assess vital signs
        function assessVitals() {
            // Temperature assessment
            const temp = parseFloat(document.getElementById('temperature').value);
            if (temp) {
                const tempStatus = document.getElementById('temp-status');
                tempStatus.classList.remove('hidden');
                
                if (temp < 36.1) {
                    tempStatus.className = 'vital-status status-danger';
                    tempStatus.textContent = 'Hypothermia';
                } else if (temp > 37.2) {
                    tempStatus.className = 'vital-status status-warning';
                    tempStatus.textContent = 'Fever';
                } else {
                    tempStatus.className = 'vital-status status-normal';
                    tempStatus.textContent = 'Normal';
                }
            }

            // Blood pressure assessment
            const systolic = parseInt(document.getElementById('systolic').value);
            const diastolic = parseInt(document.getElementById('diastolic').value);
            if (systolic && diastolic) {
                const bpStatus = document.getElementById('bp-status');
                bpStatus.classList.remove('hidden');
                
                if (systolic < 90 || diastolic < 60) {
                    bpStatus.className = 'vital-status status-danger';
                    bpStatus.textContent = 'Hypotension';
                } else if (systolic > 140 || diastolic > 90) {
                    bpStatus.className = 'vital-status status-warning';
                    bpStatus.textContent = 'Hypertension';
                } else if (systolic > 120 || diastolic > 80) {
                    bpStatus.className = 'vital-status status-warning';
                    bpStatus.textContent = 'Elevated';
                } else {
                    bpStatus.className = 'vital-status status-normal';
                    bpStatus.textContent = 'Normal';
                }
            }

            // Pulse assessment
            const pulse = parseInt(document.getElementById('pulse_rate').value);
            if (pulse) {
                const pulseStatus = document.getElementById('pulse-status');
                pulseStatus.classList.remove('hidden');
                
                if (pulse < 60) {
                    pulseStatus.className = 'vital-status status-warning';
                    pulseStatus.textContent = 'Bradycardia';
                } else if (pulse > 100) {
                    pulseStatus.className = 'vital-status status-warning';
                    pulseStatus.textContent = 'Tachycardia';
                } else {
                    pulseStatus.className = 'vital-status status-normal';
                    pulseStatus.textContent = 'Normal';
                }
            }

            // Respiratory rate assessment
            const respRate = parseInt(document.getElementById('respiratory_rate').value);
            if (respRate) {
                const respStatus = document.getElementById('resp-status');
                respStatus.classList.remove('hidden');
                
                if (respRate < 12) {
                    respStatus.className = 'vital-status status-warning';
                    respStatus.textContent = 'Bradypnea';
                } else if (respRate > 20) {
                    respStatus.className = 'vital-status status-warning';
                    respStatus.textContent = 'Tachypnea';
                } else {
                    respStatus.className = 'vital-status status-normal';
                    respStatus.textContent = 'Normal';
                }
            }

            // SpO2 assessment
            const spo2 = parseInt(document.getElementById('oxygen_saturation').value);
            if (spo2) {
                const spo2Status = document.getElementById('spo2-status');
                spo2Status.classList.remove('hidden');
                
                if (spo2 < 95) {
                    spo2Status.className = 'vital-status status-danger';
                    spo2Status.textContent = 'Hypoxemia';
                } else {
                    spo2Status.className = 'vital-status status-normal';
                    spo2Status.textContent = 'Normal';
                }
            }
        }

        // Calculate BMI
        function calculateBMI() {
            const weight = parseFloat(document.getElementById('weight').value);
            const height = parseFloat(document.getElementById('height').value);
            
            if (weight && height) {
                const heightInMeters = height / 100;
                const bmi = weight / (heightInMeters * heightInMeters);
                
                document.getElementById('bmi').value = bmi.toFixed(1);
                
                // BMI assessment
                const bmiStatus = document.getElementById('bmi-status');
                bmiStatus.classList.remove('hidden');
                
                if (bmi < 18.5) {
                    bmiStatus.className = 'vital-status status-warning';
                    bmiStatus.textContent = 'Underweight';
                } else if (bmi > 30) {
                    bmiStatus.className = 'vital-status status-danger';
                    bmiStatus.textContent = 'Obese';
                } else if (bmi > 25) {
                    bmiStatus.className = 'vital-status status-warning';
                    bmiStatus.textContent = 'Overweight';
                } else {
                    bmiStatus.className = 'vital-status status-normal';
                    bmiStatus.textContent = 'Normal';
                }
            }
        }

        // Record vital signs
        function recordVitals() {
            if (!currentPatient) {
                showAlert('Please select a patient first', 'warning');
                return;
            }

            // Validate required fields
            const requiredFields = ['temperature', 'systolic', 'diastolic', 'pulse_rate', 'respiratory_rate'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value) {
                    input.classList.add('border-red-500');
                    isValid = false;
                } else {
                    input.classList.remove('border-red-500');
                }
            });
            
            if (!isValid) {
                showAlert('Please fill in all required vital signs', 'danger');
                return;
            }

            const formData = new FormData(document.getElementById('vitalSignsForm'));
            const vitalData = {
                patient: currentPatient,
                datetime: document.getElementById('visit_date').value,
                recordedBy: document.getElementById('recorded_by').value,
                vitals: {}
            };
            
            // Collect form data
            for (let [key, value] of formData.entries()) {
                vitalData.vitals[key] = value;
            }
            
            // Show recording in progress
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Recording...';
            submitBtn.disabled = true;
            
            // Make API call to record vital signs
            fetch('/smartclaimsCL/api/vital-signs-api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    visit_id: currentPatient.visit_id || 1, // Temporary - should be from actual visit
                    recorded_by: 1, // Should be from session user
                    ...vitalData.vitals
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    showAlert(`Vital signs recorded successfully! Record ID: ${result.data.id}`, 'success');
                    
                    // Reload recent vitals
                    loadRecentVitals();
                    
                    // Ask if user wants to proceed to diagnosis
                    setTimeout(() => {
                        if (confirm('Would you like to proceed to diagnosis and medication for this patient?')) {
                            window.location.href = `diagnosis-medication.php?patient=${currentPatient.id}&vitals=${result.data.id}`;
                        }
                    }, 2000);
                } else {
                    showAlert(`Failed to record vital signs: ${result.message}`, 'danger');
                }
            })
            .catch(error => {
                console.error('Error recording vital signs:', error);
                showAlert('Failed to record vital signs. Please check your connection.', 'danger');
            })
            .finally(() => {
                // Reset button
                submitBtn.innerHTML = '<i class="fas fa-heartbeat mr-2"></i>Record Vitals';
                submitBtn.disabled = false;
            });
        }

        // Save draft
        function saveDraft() {
            const formData = new FormData(document.getElementById('vitalSignsForm'));
            const draftData = {};
            
            for (let [key, value] of formData.entries()) {
                draftData[key] = value;
            }
            
            localStorage.setItem('vitalsDraft', JSON.stringify(draftData));
            showAlert('Draft saved successfully', 'success');
        }

        // Clear form
        function clearForm() {
            document.getElementById('vitalSignsForm').reset();
            
            // Hide all status indicators
            document.querySelectorAll('.vital-status').forEach(status => {
                status.classList.add('hidden');
            });
            
            // Clear validation classes
            document.querySelectorAll('.border-red-500').forEach(el => {
                el.classList.remove('border-red-500');
            });
            
            showAlert('Form cleared successfully', 'success');
        }

        // Load template
        function loadTemplate() {
            // Load common vital signs template
            document.getElementById('temperature').value = '37.0';
            document.getElementById('temp_method').value = 'Oral';
            document.getElementById('systolic').value = '120';
            document.getElementById('diastolic').value = '80';
            document.getElementById('bp_position').value = 'Sitting';
            document.getElementById('bp_arm').value = 'Left';
            document.getElementById('pulse_rate').value = '72';
            document.getElementById('pulse_rhythm').value = 'Regular';
            document.getElementById('pulse_strength').value = 'Normal';
            document.getElementById('respiratory_rate').value = '16';
            document.getElementById('breathing_pattern').value = 'Regular';
            document.getElementById('oxygen_saturation').value = '98';
            document.getElementById('oxygen_support').value = 'Room Air';
            document.getElementById('pain_score').value = '0';
            document.getElementById('consciousness_level').value = 'Alert';
            document.getElementById('general_appearance').value = 'Well';
            
            assessVitals();
            showAlert('Template loaded successfully', 'success');
        }

        // Print vitals
        function printVitals() {
            window.print();
        }

        // Load recent vitals
        function loadRecentVitals() {
            const tableBody = document.getElementById('recent-vitals');
            
            // Mock data
            const mockVitals = [
                {
                    datetime: '2024-01-15 10:30',
                    patient: 'Sarah Johnson',
                    temperature: 36.8,
                    bp: '118/75',
                    pulse: 78,
                    respiratory: 16,
                    spo2: 98,
                    status: 'Normal',
                    id: 'VS001'
                },
                {
                    datetime: '2024-01-15 09:15',
                    patient: 'John Doe',
                    temperature: 38.2,
                    bp: '140/90',
                    pulse: 88,
                    respiratory: 18,
                    spo2: 96,
                    status: 'Abnormal',
                    id: 'VS002'
                }
            ];
            
            tableBody.innerHTML = '';
            
            mockVitals.forEach(vital => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${vital.datetime}</td>
                    <td>${vital.patient}</td>
                    <td>${vital.temperature}°C</td>
                    <td>${vital.bp}</td>
                    <td>${vital.pulse}</td>
                    <td>${vital.respiratory}</td>
                    <td>${vital.spo2}%</td>
                    <td>
                        <span class="px-2 py-1 text-xs rounded-full ${vital.status === 'Normal' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${vital.status}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="viewVitals('${vital.id}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        // View vitals details
        function viewVitals(id) {
            showAlert(`Viewing vital signs ${id}`, 'success');
            // Here you would typically open a modal or navigate to a details page
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