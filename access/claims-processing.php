<?php
/**
 * Claims Processing Page
 * 
 * Generate NHIS-compliant claim forms automatically
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
    <title>Claims Processing - Smart Claims NHIS</title>
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
        
        /* Claims workflow */
        .workflow-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .workflow-step {
            background: linear-gradient(135deg, rgba(0, 113, 227, 0.05), rgba(66, 161, 236, 0.05));
            border: 1px solid rgba(0, 113, 227, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .workflow-step.completed {
            background: linear-gradient(135deg, rgba(52, 199, 89, 0.1), rgba(48, 209, 88, 0.1));
            border-color: rgba(52, 199, 89, 0.3);
        }
        
        .workflow-step.active {
            background: linear-gradient(135deg, rgba(255, 149, 0, 0.1), rgba(255, 204, 0, 0.1));
            border-color: rgba(255, 149, 0, 0.3);
        }
        
        .workflow-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
        
        .workflow-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .workflow-description {
            font-size: 0.9rem;
            color: var(--text-secondary);
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
        
        .form-control:disabled {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        
        /* Claim summary */
        .claim-summary {
            background: linear-gradient(135deg, rgba(26, 91, 138, 0.1), rgba(44, 143, 184, 0.1));
            border: 1px solid rgba(26, 91, 138, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .summary-row:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--nhis-primary);
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
        
        .btn-danger {
            background: linear-gradient(135deg, #ff3b30, #ff6b6b);
            color: white;
            box-shadow: 0 2px 8px rgba(255, 59, 48, 0.3);
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        /* Status badges */
        .status-badge {
            padding: 0.35rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .status-draft {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            color: #495057;
        }
        
        .status-submitted {
            background: linear-gradient(135deg, #fff3cd, #ffeeba);
            color: #856404;
        }
        
        .status-processing {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            color: #0c5460;
        }
        
        .status-approved {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
        }
        
        .status-rejected {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
        }
        
        .status-paid {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
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
        
        /* NHIA compliance indicator */
        .compliance-indicator {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .compliance-pass {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 1px solid #28a745;
        }
        
        .compliance-fail {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 1px solid #dc3545;
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
            
            .workflow-steps {
                grid-template-columns: 1fr;
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
            <a href="vital-signs.php" class="nav-item">
                <i class="fas fa-heartbeat"></i>
                <span>Vital Signs</span>
            </a>
            <a href="diagnosis-medication.php" class="nav-item">
                <i class="fas fa-stethoscope"></i>
                <span>Diagnosis & Medication</span>
            </a>
            <a href="claims-processing.php" class="nav-item active">
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
                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                            NHIS Claims Processing
                        </h2>
                        <p class="text-secondary text-lg">Generate and submit NHIS-compliant claim forms automatically</p>
                        <p class="text-sm text-gray-600 mt-2">Streamlined workflow from consultation to reimbursement</p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="btn btn-secondary" onclick="importConsultation()">
                            <i class="fas fa-file-import mr-2"></i>
                            Import Consultation
                        </button>
                        <button class="btn btn-warning" onclick="bulkProcess()">
                            <i class="fas fa-layer-group mr-2"></i>
                            Bulk Process
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer"></div>

            <!-- Claims Processing Workflow -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-route mr-2"></i>
                    Claims Processing Workflow
                </h3>
                
                <div class="workflow-steps">
                    <div class="workflow-step completed" id="step-1">
                        <div class="workflow-icon" style="background: linear-gradient(135deg, #34c759, #30d158);">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="workflow-title">Patient Verification</div>
                        <div class="workflow-description">NHIS eligibility confirmed</div>
                    </div>
                    
                    <div class="workflow-step completed" id="step-2">
                        <div class="workflow-icon" style="background: linear-gradient(135deg, #34c759, #30d158);">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="workflow-title">Services Rendered</div>
                        <div class="workflow-description">OPD, Lab, Pharmacy documented</div>
                    </div>
                    
                    <div class="workflow-step active" id="step-3">
                        <div class="workflow-icon" style="background: linear-gradient(135deg, #ff9500, #ffcc00);">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="workflow-title">Claim Generation</div>
                        <div class="workflow-description">Auto-generate NHIS forms</div>
                    </div>
                    
                    <div class="workflow-step" id="step-4">
                        <div class="workflow-icon" style="background: linear-gradient(135deg, #86868b, #a8a8a8);">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div class="workflow-title">NHIA Submission</div>
                        <div class="workflow-description">Submit to NHIA portal</div>
                    </div>
                    
                    <div class="workflow-step" id="step-5">
                        <div class="workflow-icon" style="background: linear-gradient(135deg, #86868b, #a8a8a8);">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="workflow-title">Reimbursement</div>
                        <div class="workflow-description">Payment processing</div>
                    </div>
                </div>
            </div>

            <!-- Consultation Selection -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-search mr-2"></i>
                    Select Consultation for Claims
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="consultation_search" class="form-label">Search Consultation</label>
                        <div class="relative">
                            <input type="text" 
                                   id="consultation_search" 
                                   class="form-control pr-10" 
                                   placeholder="Enter consultation ID or patient name">
                            <button class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-500" onclick="searchConsultation()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_filter" class="form-label">Date Range</label>
                        <select id="date_filter" class="form-control" onchange="filterConsultations()">
                            <option value="">All dates</option>
                            <option value="today">Today</option>
                            <option value="week">This week</option>
                            <option value="month">This month</option>
                            <option value="custom">Custom range</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status_filter" class="form-label">Status Filter</label>
                        <select id="status_filter" class="form-control" onchange="filterConsultations()">
                            <option value="">All statuses</option>
                            <option value="completed">Completed</option>
                            <option value="pending_claims">Pending Claims</option>
                            <option value="claimed">Already Claimed</option>
                        </select>
                    </div>
                </div>
                
                <!-- Selected Consultation Info -->
                <div id="selectedConsultationInfo" class="hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-800 mb-2">Selected Consultation</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                            <div><strong>ID:</strong> <span id="consultation_id">-</span></div>
                            <div><strong>Patient:</strong> <span id="consultation_patient">-</span></div>
                            <div><strong>Date:</strong> <span id="consultation_date">-</span></div>
                            <div><strong>Physician:</strong> <span id="consultation_physician">-</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NHIA Compliance Check -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-shield-check mr-2"></i>
                    NHIA Compliance Verification
                </h3>
                
                <div id="compliance_results" class="space-y-3">
                    <div class="compliance-indicator compliance-pass">
                        <i class="fas fa-check-circle mr-3 text-xl"></i>
                        <div>
                            <strong>Patient Eligibility:</strong> Active NHIS membership verified
                        </div>
                    </div>
                    
                    <div class="compliance-indicator compliance-pass">
                        <i class="fas fa-check-circle mr-3 text-xl"></i>
                        <div>
                            <strong>Service Coverage:</strong> All services covered under NHIS
                        </div>
                    </div>
                    
                    <div class="compliance-indicator compliance-pass">
                        <i class="fas fa-check-circle mr-3 text-xl"></i>
                        <div>
                            <strong>Diagnosis Codes:</strong> Valid ICD-10 codes verified
                        </div>
                    </div>
                    
                    <div class="compliance-indicator compliance-pass">
                        <i class="fas fa-check-circle mr-3 text-xl"></i>
                        <div>
                            <strong>Medication Formulary:</strong> All medications in NHIS formulary
                        </div>
                    </div>
                    
                    <div class="compliance-indicator compliance-pass">
                        <i class="fas fa-check-circle mr-3 text-xl"></i>
                        <div>
                            <strong>Tariff Calculation:</strong> Auto-calculated based on NHIA rates
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center text-green-800">
                        <i class="fas fa-thumbs-up mr-2"></i>
                        <strong>Compliance Status: PASSED</strong>
                    </div>
                    <p class="text-sm text-green-700 mt-1">All requirements met for NHIA submission</p>
                </div>
            </div>

            <!-- Claim Summary -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-calculator mr-2"></i>
                    Claim Summary & Tariff Breakdown
                </h3>
                
                <div class="claim-summary">
                    <div class="summary-row">
                        <span>OPD Consultation Fee:</span>
                        <span>₵45.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Laboratory Tests (3 items):</span>
                        <span>₵67.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Medications (4 items):</span>
                        <span>₵85.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Procedures/Treatments:</span>
                        <span>₵35.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Administrative Fee:</span>
                        <span>₵8.00</span>
                    </div>
                    <div class="summary-row">
                        <span><strong>Total Claim Amount:</strong></span>
                        <span><strong>₵240.00</strong></span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">₵240.00</div>
                        <div class="text-sm text-blue-800">Total Claim</div>
                    </div>
                    <div class="text-center p-3 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">₵216.00</div>
                        <div class="text-sm text-green-800">NHIA Reimbursement (90%)</div>
                    </div>
                    <div class="text-center p-3 bg-orange-50 rounded-lg">
                        <div class="text-2xl font-bold text-orange-600">₵24.00</div>
                        <div class="text-sm text-orange-800">Patient Co-payment (10%)</div>
                    </div>
                </div>
            </div>

            <!-- Claim Form Generation -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-file-alt mr-2"></i>
                    NHIS Claim Form Generation
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="claim_number" class="form-label">Claim Number</label>
                        <input type="text" 
                               id="claim_number" 
                               class="form-control" 
                               value="CLM-2024-001234" 
                               readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="submission_date" class="form-label">Submission Date</label>
                        <input type="date" 
                               id="submission_date" 
                               class="form-control" 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="claim_type" class="form-label">Claim Type</label>
                        <select id="claim_type" class="form-control">
                            <option value="OPD">Outpatient (OPD)</option>
                            <option value="Emergency">Emergency</option>
                            <option value="Maternity">Maternity</option>
                            <option value="Specialist">Specialist Consultation</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority" class="form-label">Priority</label>
                        <select id="priority" class="form-control">
                            <option value="Normal">Normal</option>
                            <option value="Urgent">Urgent</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="additional_notes" class="form-label">Additional Notes (Optional)</label>
                    <textarea id="additional_notes" 
                              class="form-control" 
                              rows="3" 
                              placeholder="Any additional information for NHIA reviewers"></textarea>
                </div>
                
                <div class="flex justify-between items-center mt-6">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Forms are auto-generated using NHIA approved templates
                    </div>
                    <div class="flex space-x-3">
                        <button class="btn btn-secondary" onclick="previewClaim()">
                            <i class="fas fa-eye mr-2"></i>
                            Preview Form
                        </button>
                        <button class="btn btn-warning" onclick="generateClaim()">
                            <i class="fas fa-file-download mr-2"></i>
                            Generate PDF
                        </button>
                        <button class="btn btn-success" onclick="submitClaim()">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Submit to NHIA
                        </button>
                    </div>
                </div>
            </div>

            <!-- Claims Status Tracking -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-tracking mr-2"></i>
                    Recent Claims Status
                </h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Claim No.</th>
                                <th>Patient</th>
                                <th>Submission Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Last Update</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="claims_table">
                            <tr>
                                <td colspan="7" class="text-center py-4">Loading claims data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bulk Operations -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-layer-group mr-2"></i>
                    Bulk Claims Operations
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow cursor-pointer" onclick="bulkGenerate()">
                        <i class="fas fa-file-export text-3xl text-blue-500 mb-2"></i>
                        <h4 class="font-semibold mb-1">Bulk Generate</h4>
                        <p class="text-sm text-gray-600">Generate multiple claim forms</p>
                    </div>
                    
                    <div class="text-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow cursor-pointer" onclick="bulkSubmit()">
                        <i class="fas fa-upload text-3xl text-green-500 mb-2"></i>
                        <h4 class="font-semibold mb-1">Bulk Submit</h4>
                        <p class="text-sm text-gray-600">Submit multiple claims to NHIA</p>
                    </div>
                    
                    <div class="text-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow cursor-pointer" onclick="statusUpdate()">
                        <i class="fas fa-sync text-3xl text-orange-500 mb-2"></i>
                        <h4 class="font-semibold mb-1">Status Update</h4>
                        <p class="text-sm text-gray-600">Check NHIA processing status</p>
                    </div>
                </div>
            </div>

            <!-- Analytics Dashboard -->
            <div class="card">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Claims Analytics
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">156</div>
                        <div class="text-sm text-blue-800">Total Claims (Month)</div>
                        <div class="text-xs text-gray-600 mt-1">↑ 12% from last month</div>
                    </div>
                    
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">89%</div>
                        <div class="text-sm text-green-800">Approval Rate</div>
                        <div class="text-xs text-gray-600 mt-1">↑ 3% improvement</div>
                    </div>
                    
                    <div class="text-center p-4 bg-orange-50 rounded-lg">
                        <div class="text-2xl font-bold text-orange-600">5.2 days</div>
                        <div class="text-sm text-orange-800">Avg. Processing Time</div>
                        <div class="text-xs text-gray-600 mt-1">↓ 2.1 days faster</div>
                    </div>
                    
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">₵45,670</div>
                        <div class="text-sm text-purple-800">Monthly Reimbursement</div>
                        <div class="text-xs text-gray-600 mt-1">↑ ₵5,200 increase</div>
                    </div>
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
            <a href="vital-signs.php" class="mobile-nav-item">
                <i class="fas fa-heartbeat"></i>
                <span>Vitals</span>
            </a>
            <a href="diagnosis-medication.php" class="mobile-nav-item">
                <i class="fas fa-stethoscope"></i>
                <span>Diagnosis</span>
            </a>
            <a href="claims-processing.php" class="mobile-nav-item active">
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
        let currentConsultation = null;
        let claimData = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the page
            initializePage();
            loadClaimsData();
            
            // Auto-generate claim number
            generateClaimNumber();
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

            // Setup consultation search
            document.getElementById('consultation_search').addEventListener('input', function() {
                if (this.value.length >= 3) {
                    searchConsultation();
                }
            });
        }

        // Generate unique claim number
        function generateClaimNumber() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const sequence = String(Math.floor(Math.random() * 999999)).padStart(6, '0');
            
            document.getElementById('claim_number').value = `CLM-${year}${month}-${sequence}`;
        }

        // Search consultation
        function searchConsultation() {
            const searchTerm = document.getElementById('consultation_search').value.trim();
            
            if (searchTerm.length < 3) {
                showAlert('Please enter at least 3 characters to search', 'warning');
                return;
            }
            
            // Simulate API call
            setTimeout(() => {
                // Mock consultation data
                const mockConsultation = {
                    id: 'CONS-2024-001',
                    patient: 'Grace Mensah',
                    date: '2024-01-15',
                    physician: 'Dr. Kwame Asante',
                    diagnosis: 'J06.9 - Acute upper respiratory infection',
                    medications: ['Paracetamol 500mg', 'Amoxicillin 500mg'],
                    services: ['OPD Consultation', 'Full Blood Count', 'Chest X-Ray'],
                    totalAmount: 240.00
                };
                
                selectConsultation(mockConsultation);
            }, 1000);
        }

        // Select consultation for claims
        function selectConsultation(consultation) {
            currentConsultation = consultation;
            
            // Show consultation info
            document.getElementById('selectedConsultationInfo').classList.remove('hidden');
            document.getElementById('consultation_id').textContent = consultation.id;
            document.getElementById('consultation_patient').textContent = consultation.patient;
            document.getElementById('consultation_date').textContent = consultation.date;
            document.getElementById('consultation_physician').textContent = consultation.physician;
            
            // Update workflow steps
            updateWorkflowStep(3, 'active');
            
            showAlert('Consultation selected for claims processing', 'success');
        }

        // Update workflow step status
        function updateWorkflowStep(stepNumber, status) {
            const step = document.getElementById(`step-${stepNumber}`);
            step.className = `workflow-step ${status}`;
            
            const icon = step.querySelector('.workflow-icon');
            if (status === 'completed') {
                icon.style.background = 'linear-gradient(135deg, #34c759, #30d158)';
                icon.innerHTML = '<i class="fas fa-check"></i>';
            } else if (status === 'active') {
                icon.style.background = 'linear-gradient(135deg, #ff9500, #ffcc00)';
            }
        }

        // Preview claim before submission
        function previewClaim() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            showAlert('Generating claim preview...', 'info');
            
            // Simulate preview generation
            setTimeout(() => {
                showAlert('Claim preview generated successfully!', 'success');
                // Here you would typically open a modal with the preview
                
                // For demo, show a simple preview alert
                const previewData = `
NHIS CLAIM FORM PREVIEW
━━━━━━━━━━━━━━━━━━━━━━━━

Claim Number: ${document.getElementById('claim_number').value}
Patient: ${currentConsultation.patient}
Date: ${currentConsultation.date}
Physician: ${currentConsultation.physician}

Services:
• OPD Consultation - ₵45.00
• Laboratory Tests - ₵67.00
• Medications - ₵85.00
• Procedures - ₵35.00
• Admin Fee - ₵8.00

Total Amount: ₵240.00
NHIA Reimbursement: ₵216.00
Patient Co-payment: ₵24.00
                `;
                
                alert(previewData);
            }, 1500);
        }

        // Generate claim PDF
        function generateClaim() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            showAlert('Generating NHIS claim form...', 'warning');
            
            // Simulate PDF generation
            setTimeout(() => {
                showAlert('Claim form generated successfully! Download started.', 'success');
                
                // Here you would typically trigger actual PDF download
                // For demo purposes, we'll just show success
                
            }, 2000);
        }

        // Submit claim to NHIA
        function submitClaim() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            // Validate required fields
            const claimNumber = document.getElementById('claim_number').value;
            const submissionDate = document.getElementById('submission_date').value;
            const claimType = document.getElementById('claim_type').value;
            
            if (!claimNumber || !submissionDate || !claimType) {
                showAlert('Please fill in all required fields', 'warning');
                return;
            }
            
            showAlert('Submitting claim to NHIA portal...', 'warning');
            
            // Create claim data
            claimData = {
                claimNumber: claimNumber,
                consultation: currentConsultation,
                submissionDate: submissionDate,
                claimType: claimType,
                priority: document.getElementById('priority').value,
                totalAmount: 240.00,
                status: 'Submitted',
                submittedBy: '<?php echo htmlspecialchars($user['full_name'] ?? 'Current User'); ?>'
            };
            
            // Simulate submission
            setTimeout(() => {
                showAlert(`Claim submitted successfully! Reference: ${claimNumber}`, 'success');
                
                // Update workflow
                updateWorkflowStep(3, 'completed');
                updateWorkflowStep(4, 'active');
                
                // Add to claims table
                addClaimToTable(claimData);
                
                // Reset form for next claim
                setTimeout(() => {
                    if (confirm('Would you like to process another claim?')) {
                        resetClaimForm();
                    }
                }, 2000);
            }, 3000);
        }

        // Add claim to the table
        function addClaimToTable(claim) {
            const tableBody = document.getElementById('claims_table');
            
            // Remove "loading" row if it exists
            const loadingRow = tableBody.querySelector('td[colspan="7"]');
            if (loadingRow) {
                loadingRow.parentElement.remove();
            }
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${claim.claimNumber}</td>
                <td>${claim.consultation.patient}</td>
                <td>${claim.submissionDate}</td>
                <td>₵${claim.totalAmount.toFixed(2)}</td>
                <td><span class="status-badge status-submitted">Submitted</span></td>
                <td>Just now</td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="trackClaim('${claim.claimNumber}')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-secondary btn-sm ml-1" onclick="downloadClaim('${claim.claimNumber}')">
                        <i class="fas fa-download"></i>
                    </button>
                </td>
            `;
            
            // Insert at the beginning of the table
            tableBody.insertBefore(row, tableBody.firstChild);
        }

        // Reset claim form
        function resetClaimForm() {
            currentConsultation = null;
            document.getElementById('selectedConsultationInfo').classList.add('hidden');
            document.getElementById('consultation_search').value = '';
            document.getElementById('additional_notes').value = '';
            
            // Reset workflow
            updateWorkflowStep(3, '');
            updateWorkflowStep(4, '');
            
            // Generate new claim number
            generateClaimNumber();
            
            showAlert('Form reset for new claim', 'info');
        }

        // Import consultation
        function importConsultation() {
            // Simulate consultation import
            showAlert('Importing recent consultations...', 'info');
            
            setTimeout(() => {
                const consultations = [
                    { id: 'CONS-001', patient: 'John Doe', date: '2024-01-15' },
                    { id: 'CONS-002', patient: 'Jane Smith', date: '2024-01-14' },
                    { id: 'CONS-003', patient: 'Bob Johnson', date: '2024-01-13' }
                ];
                
                let html = 'Recent Consultations:\n\n';
                consultations.forEach(c => {
                    html += `${c.id} - ${c.patient} (${c.date})\n`;
                });
                
                alert(html);
                showAlert('Consultations imported successfully', 'success');
            }, 1500);
        }

        // Filter consultations
        function filterConsultations() {
            const dateFilter = document.getElementById('date_filter').value;
            const statusFilter = document.getElementById('status_filter').value;
            
            showAlert(`Filtering by: ${dateFilter || 'all dates'}, ${statusFilter || 'all statuses'}`, 'info');
        }

        // Bulk operations
        function bulkProcess() {
            showAlert('Opening bulk processing interface...', 'info');
        }

        function bulkGenerate() {
            showAlert('Starting bulk claim generation...', 'warning');
            
            setTimeout(() => {
                showAlert('Bulk generation completed! 15 claims processed.', 'success');
            }, 3000);
        }

        function bulkSubmit() {
            showAlert('Submitting multiple claims to NHIA...', 'warning');
            
            setTimeout(() => {
                showAlert('Bulk submission completed! 12 claims submitted.', 'success');
            }, 4000);
        }

        function statusUpdate() {
            showAlert('Checking claim status updates from NHIA...', 'info');
            
            setTimeout(() => {
                showAlert('Status updates received: 8 approved, 2 pending review.', 'success');
            }, 2000);
        }

        // Load claims data
        function loadClaimsData() {
            const tableBody = document.getElementById('claims_table');
            
            // Mock claims data
            const mockClaims = [
                {
                    claimNumber: 'CLM-202401-001230',
                    patient: 'Sarah Johnson',
                    submissionDate: '2024-01-14',
                    amount: 180.00,
                    status: 'Approved',
                    lastUpdate: '2024-01-15 09:30'
                },
                {
                    claimNumber: 'CLM-202401-001229',
                    patient: 'Michael Brown',
                    submissionDate: '2024-01-13',
                    amount: 320.50,
                    status: 'Processing',
                    lastUpdate: '2024-01-14 16:45'
                },
                {
                    claimNumber: 'CLM-202401-001228',
                    patient: 'Emma Wilson',
                    submissionDate: '2024-01-12',
                    amount: 95.00,
                    status: 'Paid',
                    lastUpdate: '2024-01-14 11:20'
                }
            ];
            
            tableBody.innerHTML = '';
            
            mockClaims.forEach(claim => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${claim.claimNumber}</td>
                    <td>${claim.patient}</td>
                    <td>${claim.submissionDate}</td>
                    <td>₵${claim.amount.toFixed(2)}</td>
                    <td><span class="status-badge status-${claim.status.toLowerCase()}">${claim.status}</span></td>
                    <td>${claim.lastUpdate}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="trackClaim('${claim.claimNumber}')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-secondary btn-sm ml-1" onclick="downloadClaim('${claim.claimNumber}')">
                            <i class="fas fa-download"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        // Track individual claim
        function trackClaim(claimNumber) {
            showAlert(`Tracking claim ${claimNumber}...`, 'info');
            
            setTimeout(() => {
                alert(`Claim Status: ${claimNumber}
                
Status: Under Review
Submitted: 2024-01-14
Last Update: 2024-01-15 09:30
Expected Resolution: 2024-01-18

Notes: Claim is being processed by NHIA. No action required.`);
            }, 1000);
        }

        // Download claim
        function downloadClaim(claimNumber) {
            showAlert(`Downloading claim ${claimNumber}...`, 'info');
            
            setTimeout(() => {
                showAlert('Claim document downloaded successfully!', 'success');
            }, 1000);
        }

        // Show alert message
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : type === 'info' ? 'info-circle' : 'exclamation-circle'} mr-2"></i>
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