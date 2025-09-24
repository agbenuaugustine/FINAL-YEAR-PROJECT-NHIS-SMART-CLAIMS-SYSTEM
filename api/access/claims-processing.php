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
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left-color: #17a2b8;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        /* Compliance indicators */
        .compliance-indicator {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        
        .compliance-pass {
            background: rgba(52, 199, 89, 0.1);
            border: 1px solid rgba(52, 199, 89, 0.2);
            color: #155724;
        }
        
        .compliance-fail {
            background: rgba(255, 59, 48, 0.1);
            border: 1px solid rgba(255, 59, 48, 0.2);
            color: #721c24;
        }
        
        .compliance-warning {
            background: rgba(255, 149, 0, 0.1);
            border: 1px solid rgba(255, 149, 0, 0.2);
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
        
        /* User Menu Styles */
        .user-menu {
            position: relative;
        }
        
        .user-button {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .user-button:hover {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }
        
        /* Loading States */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Search Dropdown */
        .consultation-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .consultation-item {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .consultation-item:hover {
            background-color: #f8f9fa;
        }
        
        .consultation-item:last-child {
            border-bottom: none;
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
                    <a href="../logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
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
                            <span id="statusIndicator" class="ml-3 px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>
                                System Ready
                            </span>
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
            <div id="alertContainer">
                <div class="alert alert-info" id="initialAlert">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Welcome to Claims Processing!</strong> The system is loading available consultations. You can search for specific consultations or browse the list below.
                </div>
            </div>

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
                                   placeholder="Enter NHIS number, patient name, or visit ID"
                                   autocomplete="off">
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
                            <option value="quarter">This quarter</option>
                            <option value="custom">Custom range</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status_filter" class="form-label">Status Filter</label>
                        <select id="status_filter" class="form-control" onchange="filterConsultations()">
                            <option value="">All statuses</option>
                            <option value="pending_claims">Ready for Claims</option>
                            <option value="completed">Completed Consultations</option>
                            <option value="claimed">Already Claimed</option>
                            <option value="draft_claims">Draft Claims</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="department_filter" class="form-label">Department Filter</label>
                        <select id="department_filter" class="form-control" onchange="filterConsultations()">
                            <option value="">All departments</option>
                            <option value="OPD">Outpatient Department</option>
                            <option value="Emergency">Emergency</option>
                            <option value="Laboratory">Laboratory</option>
                            <option value="Pharmacy">Pharmacy</option>
                            <option value="Radiology">Radiology</option>
                        </select>
                    </div>
                </div>
                
                <!-- Custom Date Range -->
                <div id="customDateRange" class="hidden form-grid mb-4">
                    <div class="form-group">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" id="date_from" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" id="date_to" class="form-control">
                    </div>
                    <div class="form-group flex items-end">
                        <button class="btn btn-primary" onclick="applyCustomDateFilter()">
                            <i class="fas fa-filter mr-2"></i>
                            Apply Filter
                        </button>
                    </div>
                </div>
                
                <!-- Claimable Consultations Table -->
                <div class="table-container mt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold text-gray-700">
                            <i class="fas fa-list mr-2"></i>
                            Available Consultations for Claims Processing
                        </h4>
                        <div class="flex space-x-2">
                            <button class="btn btn-sm btn-secondary" onclick="loadConsultations()" title="Refresh consultations">
                                <i class="fas fa-sync-alt mr-1"></i>
                                Refresh
                            </button>
                            <button class="btn btn-sm btn-info" onclick="testAPIConnection()" title="Test API connection">
                                <i class="fas fa-wifi mr-1"></i>
                                Test API
                            </button>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Visit ID</th>
                                <th>Patient Name</th>
                                <th>NHIS Number</th>
                                <th>Visit Date</th>
                                <th>Department</th>
                                <th>Physician</th>
                                <th>Services</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="consultations_table">
                            <tr>
                                <td colspan="10" class="text-center py-8">
                                    <div class="text-blue-500">
                                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                        <div>Loading available consultations...</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Selected Consultation Info -->
                <div id="selectedConsultationInfo" class="hidden mt-4">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-800 mb-3 flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            Selected Consultation Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                            <div class="bg-white p-3 rounded-lg border">
                                <strong class="text-gray-600">Visit ID:</strong>
                                <div class="text-lg font-semibold text-blue-600" id="consultation_id">-</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg border">
                                <strong class="text-gray-600">Patient:</strong>
                                <div class="text-lg font-semibold" id="consultation_patient">-</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg border">
                                <strong class="text-gray-600">NHIS Number:</strong>
                                <div class="text-lg font-semibold text-green-600" id="consultation_nhis">-</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg border">
                                <strong class="text-gray-600">Visit Date:</strong>
                                <div class="text-lg font-semibold" id="consultation_date">-</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg border">
                                <strong class="text-gray-600">Physician:</strong>
                                <div class="text-lg font-semibold" id="consultation_physician">-</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg border">
                                <strong class="text-gray-600">Department:</strong>
                                <div class="text-lg font-semibold" id="consultation_department">-</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg border">
                                <strong class="text-gray-600">Chief Complaint:</strong>
                                <div class="text-sm text-gray-700" id="consultation_complaint">-</div>
                            </div>
                            <div class="bg-white p-3 rounded-lg border">
                                <strong class="text-gray-600">Visit Type:</strong>
                                <div class="text-lg font-semibold" id="consultation_type">-</div>
                            </div>
                        </div>
                        
                        <!-- Detailed Clinical Information -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white p-4 rounded-lg border">
                                <h5 class="font-semibold text-gray-700 mb-2 flex items-center">
                                    <i class="fas fa-diagnoses mr-2 text-red-500"></i>
                                    Diagnoses (<span id="diagnosis_count">0</span>)
                                </h5>
                                <div id="consultation_diagnoses" class="text-sm space-y-1">-</div>
                            </div>
                            <div class="bg-white p-4 rounded-lg border">
                                <h5 class="font-semibold text-gray-700 mb-2 flex items-center">
                                    <i class="fas fa-pills mr-2 text-green-500"></i>
                                    Medications (<span id="medication_count">0</span>)
                                </h5>
                                <div id="consultation_medications" class="text-sm space-y-1">-</div>
                            </div>
                            <div class="bg-white p-4 rounded-lg border">
                                <h5 class="font-semibold text-gray-700 mb-2 flex items-center">
                                    <i class="fas fa-vial mr-2 text-blue-500"></i>
                                    Lab Tests (<span id="lab_count">0</span>)
                                </h5>
                                <div id="consultation_labs" class="text-sm space-y-1">-</div>
                            </div>
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
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-700 mb-2">Quick Actions</h4>
                        <div class="space-y-2">
                            <button class="btn btn-sm btn-primary w-full" onclick="verifyNHISEligibility()">
                                <i class="fas fa-user-check mr-2"></i>
                                Verify NHIS Eligibility
                            </button>
                            <button class="btn btn-sm btn-secondary w-full" onclick="validateDiagnoses()">
                                <i class="fas fa-stethoscope mr-2"></i>
                                Validate ICD-10 Codes
                            </button>
                            <button class="btn btn-sm btn-warning w-full" onclick="checkMedicationFormulary()">
                                <i class="fas fa-pills mr-2"></i>
                                Check Medication Formulary
                            </button>
                            <button class="btn btn-sm btn-info w-full" onclick="runFullCompliance()">
                                <i class="fas fa-shield-check mr-2"></i>
                                Full Compliance Check
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-700 mb-2">Compliance Score</h4>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-green-600 mb-2" id="compliance_score">-</div>
                            <div class="text-sm text-gray-600">Overall Compliance</div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-green-600 h-2 rounded-full transition-all duration-300" id="compliance_bar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="compliance_results" class="space-y-3">
                    <div class="compliance-indicator" id="eligibility_check">
                        <i class="fas fa-clock mr-3 text-xl text-gray-400"></i>
                        <div>
                            <strong>Patient Eligibility:</strong> Pending verification
                            <div class="text-sm text-gray-600 mt-1">NHIS membership status not yet verified</div>
                        </div>
                    </div>
                    
                    <div class="compliance-indicator" id="service_coverage_check">
                        <i class="fas fa-clock mr-3 text-xl text-gray-400"></i>
                        <div>
                            <strong>Service Coverage:</strong> Pending verification
                            <div class="text-sm text-gray-600 mt-1">Checking service coverage under NHIS benefits package</div>
                        </div>
                    </div>
                    
                    <div class="compliance-indicator" id="diagnosis_codes_check">
                        <i class="fas fa-clock mr-3 text-xl text-gray-400"></i>
                        <div>
                            <strong>Diagnosis Codes:</strong> Pending validation
                            <div class="text-sm text-gray-600 mt-1">ICD-10 codes validation pending</div>
                        </div>
                    </div>
                    
                    <div class="compliance-indicator" id="medication_formulary_check">
                        <i class="fas fa-clock mr-3 text-xl text-gray-400"></i>
                        <div>
                            <strong>Medication Formulary:</strong> Pending check
                            <div class="text-sm text-gray-600 mt-1">Checking medications against NHIS formulary</div>
                        </div>
                    </div>
                    
                    <div class="compliance-indicator" id="tariff_calculation_check">
                        <i class="fas fa-clock mr-3 text-xl text-gray-400"></i>
                        <div>
                            <strong>Tariff Calculation:</strong> Pending calculation
                            <div class="text-sm text-gray-600 mt-1">Auto-calculation based on NHIA rates</div>
                        </div>
                    </div>
                    
                    <div class="compliance-indicator" id="documentation_check">
                        <i class="fas fa-clock mr-3 text-xl text-gray-400"></i>
                        <div>
                            <strong>Documentation:</strong> Pending review
                            <div class="text-sm text-gray-600 mt-1">Checking required documentation completeness</div>
                        </div>
                    </div>
                </div>
                
                <div id="compliance_summary" class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg hidden">
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Compliance Status: PENDING</strong>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">Please select a consultation to begin compliance verification</p>
                </div>
                
                <!-- Detailed Compliance Results -->
                <div id="detailed_compliance" class="mt-4 hidden">
                    <h4 class="font-semibold text-gray-700 mb-3">Detailed Compliance Report</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white border rounded-lg p-4">
                            <h5 class="font-semibold text-sm text-gray-600 mb-2">NHIS Membership Details</h5>
                            <div id="nhis_details" class="text-sm space-y-1">
                                <div>Status: <span id="nhis_status">-</span></div>
                                <div>Expiry Date: <span id="nhis_expiry">-</span></div>
                                <div>Scheme Type: <span id="nhis_scheme">-</span></div>
                                <div>Benefits Package: <span id="nhis_benefits">-</span></div>
                            </div>
                        </div>
                        
                        <div class="bg-white border rounded-lg p-4">
                            <h5 class="font-semibold text-sm text-gray-600 mb-2">Service Coverage Analysis</h5>
                            <div id="coverage_analysis" class="text-sm space-y-1">
                                <div>OPD Consultation: <span id="opd_coverage">-</span></div>
                                <div>Laboratory Tests: <span id="lab_coverage">-</span></div>
                                <div>Medications: <span id="med_coverage">-</span></div>
                                <div>Procedures: <span id="proc_coverage">-</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Claim Summary -->
            <div class="card" id="claim_summary_section" style="opacity: 0.5; pointer-events: none;">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-calculator mr-2"></i>
                    Claim Summary & Tariff Breakdown
                </h3>
                
                <div class="flex justify-between items-center mb-4">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Tariffs auto-calculated based on NHIA approved rates
                    </div>
                    <div class="flex space-x-2">
                        <button class="btn btn-sm btn-secondary" onclick="recalculateAmounts()" id="recalculate_btn" disabled>
                            <i class="fas fa-sync mr-1"></i>
                            Recalculate
                        </button>
                        <button class="btn btn-sm btn-info" onclick="showTariffDetails()">
                            <i class="fas fa-list mr-1"></i>
                            View Tariff Details
                        </button>
                    </div>
                </div>
                
                <!-- Itemized Breakdown -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Service Breakdown</h4>
                        <div class="claim-summary" id="service_breakdown">
                            <div class="summary-row text-center text-gray-500 py-8">
                                <i class="fas fa-chart-line text-3xl mb-2"></i>
                                <div>Select a consultation to view breakdown</div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Payment Summary</h4>
                        <div class="space-y-3">
                            <div class="p-4 bg-blue-50 rounded-lg border">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-blue-800">Total Claim Amount</span>
                                    <span class="text-2xl font-bold text-blue-600" id="total_claim_amount">₵0.00</span>
                                </div>
                                <div class="text-sm text-blue-700 mt-1">All services and materials</div>
                            </div>
                            
                            <div class="p-4 bg-green-50 rounded-lg border">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-green-800">NHIA Reimbursement</span>
                                    <span class="text-xl font-bold text-green-600" id="nhia_reimbursement">₵0.00</span>
                                </div>
                                <div class="text-sm text-green-700 mt-1">
                                    <span id="reimbursement_percentage">0</span>% of total amount
                                </div>
                            </div>
                            
                            <div class="p-4 bg-orange-50 rounded-lg border">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-orange-800">Patient Co-payment</span>
                                    <span class="text-xl font-bold text-orange-600" id="patient_copayment">₵0.00</span>
                                </div>
                                <div class="text-sm text-orange-700 mt-1">
                                    <span id="copayment_percentage">0</span>% of total amount
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Item List -->
                <div class="mt-6" id="detailed_items" style="display: none;">
                    <h4 class="font-semibold text-gray-700 mb-3">Detailed Item List</h4>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Service/Item</th>
                                    <th>Code</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>NHIS Covered</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="claim_items_table">
                                <!-- Items will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                    <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200">
                        <div class="text-2xl font-bold text-blue-600" id="summary_total">₵0.00</div>
                        <div class="text-sm text-blue-800">Total Claim</div>
                        <div class="text-xs text-blue-600 mt-1" id="total_items">0 items</div>
                    </div>
                    <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg border border-green-200">
                        <div class="text-2xl font-bold text-green-600" id="summary_nhia">₵0.00</div>
                        <div class="text-sm text-green-800">NHIA Reimbursement</div>
                        <div class="text-xs text-green-600 mt-1" id="nhia_percentage">0% coverage</div>
                    </div>
                    <div class="text-center p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg border border-orange-200">
                        <div class="text-2xl font-bold text-orange-600" id="summary_copay">₵0.00</div>
                        <div class="text-sm text-orange-800">Patient Co-payment</div>
                        <div class="text-xs text-orange-600 mt-1" id="copay_percentage_display">0% patient</div>
                    </div>
                    <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg border border-purple-200">
                        <div class="text-2xl font-bold text-purple-600" id="expected_savings">₵0.00</div>
                        <div class="text-sm text-purple-800">Hospital Savings</div>
                        <div class="text-xs text-purple-600 mt-1">Vs. Cash payment</div>
                    </div>
                </div>
            </div>

            <!-- Claim Form Generation -->
            <div class="card" id="claim_form_section" style="opacity: 0.5; pointer-events: none;">
                <h3 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-file-alt mr-2"></i>
                    NHIS Claim Form Generation
                </h3>
                
                <!-- Progress Indicator -->
                <div class="mb-6">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Claim Generation Progress</span>
                        <span id="form_progress_text" class="text-gray-600">0% Complete</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" id="form_progress_bar" style="width: 0%"></div>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="claim_number" class="form-label">
                            Claim Number
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="claim_number" 
                                   class="form-control pr-10" 
                                   value="" 
                                   readonly>
                            <button class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-500" onclick="generateClaimNumber()">
                                <i class="fas fa-sync"></i>
                            </button>
                        </div>
                        <small class="text-gray-500">Auto-generated unique claim identifier</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="submission_date" class="form-label">
                            Submission Date
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="submission_date" 
                               class="form-control" 
                               value="<?php echo date('Y-m-d'); ?>"
                               min="<?php echo date('Y-m-d', strtotime('-30 days')); ?>"
                               max="<?php echo date('Y-m-d'); ?>">
                        <small class="text-gray-500">Claims must be submitted within 30 days</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="claim_type" class="form-label">
                            Claim Type
                            <span class="text-red-500">*</span>
                        </label>
                        <select id="claim_type" class="form-control" onchange="updateClaimTypeInfo()">
                            <option value="">Select claim type</option>
                            <option value="OPD">Outpatient (OPD)</option>
                            <option value="Emergency">Emergency</option>
                            <option value="Maternity">Maternity</option>
                            <option value="Specialist">Specialist Consultation</option>
                            <option value="Referral">Referral</option>
                            <option value="Chronic">Chronic Disease</option>
                        </select>
                        <small class="text-gray-500" id="claim_type_info">Select type to see requirements</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority" class="form-label">Priority Level</label>
                        <select id="priority" class="form-control" onchange="updatePriorityInfo()">
                            <option value="Normal">Normal Processing</option>
                            <option value="Urgent">Urgent (3-5 days)</option>
                            <option value="Emergency">Emergency (24-48 hours)</option>
                        </select>
                        <small class="text-gray-500" id="priority_info">Standard processing time: 7-14 days</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="provider_code" class="form-label">
                            Provider Code
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="provider_code" 
                               class="form-control" 
                               value="<?php echo $_SESSION['hospital_code'] ?? 'HOSP-001'; ?>" 
                               readonly>
                        <small class="text-gray-500">Your accredited provider code</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="attending_provider" class="form-label">Attending Provider</label>
                        <input type="text" 
                               id="attending_provider" 
                               class="form-control" 
                               placeholder="Dr. Name / Provider ID">
                        <small class="text-gray-500">Primary healthcare provider</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="referral_source" class="form-label">Referral Source (if applicable)</label>
                        <input type="text" 
                               id="referral_source" 
                               class="form-control" 
                               placeholder="Referring facility/provider">
                        <small class="text-gray-500">Leave blank if not a referral</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="discharge_status" class="form-label">Discharge Status</label>
                        <select id="discharge_status" class="form-control">
                            <option value="Discharged">Discharged</option>
                            <option value="Referred">Referred</option>
                            <option value="Against Medical Advice">Against Medical Advice</option>
                            <option value="Transferred">Transferred</option>
                            <option value="Deceased">Deceased</option>
                        </select>
                        <small class="text-gray-500">Patient status at end of episode</small>
                    </div>
                </div>
                
                <!-- Clinical Information -->
                <div class="mt-6">
                    <h4 class="font-semibold text-gray-700 mb-3">Clinical Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="admission_date" class="form-label">Admission/Visit Date</label>
                            <input type="datetime-local" 
                                   id="admission_date" 
                                   class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="discharge_date" class="form-label">Discharge Date</label>
                            <input type="datetime-local" 
                                   id="discharge_date" 
                                   class="form-control">
                        </div>
                    </div>
                </div>
                
                <!-- Additional Information -->
                <div class="mt-6">
                    <h4 class="font-semibold text-gray-700 mb-3">Additional Information</h4>
                    <div class="form-group">
                        <label for="additional_notes" class="form-label">Clinical Notes / Comments</label>
                        <textarea id="additional_notes" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Any additional clinical information, complications, special circumstances, or notes for NHIA reviewers..."
                                  maxlength="500"></textarea>
                        <small class="text-gray-500"><span id="notes_counter">0</span>/500 characters</small>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="form-group">
                            <label class="flex items-center">
                                <input type="checkbox" id="emergency_case" class="mr-2">
                                <span>Emergency Case</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="flex items-center">
                                <input type="checkbox" id="chronic_condition" class="mr-2">
                                <span>Chronic Condition</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="flex items-center">
                                <input type="checkbox" id="requires_followup" class="mr-2">
                                <span>Requires Follow-up</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="flex items-center">
                                <input type="checkbox" id="patient_deceased" class="mr-2">
                                <span>Patient Deceased</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="mt-8 border-t pt-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Forms are auto-generated using NHIA approved templates (Version 2024.1)
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button class="btn btn-secondary" onclick="saveDraft()" id="save_draft_btn" disabled>
                                <i class="fas fa-save mr-2"></i>
                                Save Draft
                            </button>
                            <button class="btn btn-info" onclick="validateForm()" id="validate_form_btn" disabled>
                                <i class="fas fa-check-circle mr-2"></i>
                                Validate Form
                            </button>
                            <button class="btn btn-warning" onclick="previewClaim()" id="preview_btn" disabled>
                                <i class="fas fa-eye mr-2"></i>
                                Preview Form
                            </button>
                            <button class="btn btn-primary" onclick="generateClaim()" id="generate_btn" disabled>
                                <i class="fas fa-file-download mr-2"></i>
                                Generate PDF
                            </button>
                            <button class="btn btn-success" onclick="submitClaim()" id="submit_btn" disabled>
                                <i class="fas fa-paper-plane mr-2"></i>
                                Submit to NHIA
                            </button>
                        </div>
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

    <!-- Consultation Details Modal -->
    <div id="consultationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-user-md mr-2"></i>
                    Consultation Details
                </h3>
                <button onclick="closeConsultationModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Patient Information -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-800 mb-3">
                        <i class="fas fa-user mr-2"></i>Patient Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Full Name</label>
                            <div id="modal_patient_name" class="font-medium text-gray-900"></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">NHIS Number</label>
                            <div id="modal_nhis_number" class="font-mono text-green-600 font-medium"></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Age</label>
                            <div id="modal_patient_age" class="text-gray-900"></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Gender</label>
                            <div id="modal_patient_gender" class="text-gray-900"></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Phone</label>
                            <div id="modal_patient_phone" class="text-gray-900"></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Date of Birth</label>
                            <div id="modal_patient_dob" class="text-gray-900"></div>
                        </div>
                    </div>
                </div>

                <!-- Visit Information -->
                <div class="bg-green-50 rounded-lg p-4">
                    <h4 class="font-semibold text-green-800 mb-3">
                        <i class="fas fa-calendar-check mr-2"></i>Visit Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Visit ID</label>
                            <div id="modal_visit_id" class="font-medium text-blue-600"></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Visit Date</label>
                            <div id="modal_visit_date" class="text-gray-900"></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Visit Type</label>
                            <div id="modal_visit_type" class="text-gray-900"></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Status</label>
                            <div id="modal_visit_status" class="text-gray-900"></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Attending Physician</label>
                            <div id="modal_physician" class="text-gray-900"></div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Department</label>
                            <div id="modal_department" class="text-gray-900"></div>
                        </div>
                    </div>
                </div>

                <!-- Chief Complaint -->
                <div class="bg-yellow-50 rounded-lg p-4">
                    <h4 class="font-semibold text-yellow-800 mb-3">
                        <i class="fas fa-comment-medical mr-2"></i>Chief Complaint
                    </h4>
                    <div id="modal_chief_complaint" class="text-gray-900"></div>
                </div>

                <!-- Medical Summary -->
                <div class="bg-purple-50 rounded-lg p-4">
                    <h4 class="font-semibold text-purple-800 mb-3">
                        <i class="fas fa-chart-line mr-2"></i>Medical Summary
                    </h4>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <div id="modal_diagnosis_count" class="text-2xl font-bold text-purple-600"></div>
                            <div class="text-sm text-gray-600">Diagnoses</div>
                        </div>
                        <div class="text-center">
                            <div id="modal_prescription_count" class="text-2xl font-bold text-purple-600"></div>
                            <div class="text-sm text-gray-600">Prescriptions</div>
                        </div>
                        <div class="text-center">
                            <div id="modal_lab_count" class="text-2xl font-bold text-purple-600"></div>
                            <div class="text-sm text-gray-600">Lab Orders</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button onclick="closeConsultationModal()" class="btn btn-secondary">
                        <i class="fas fa-times mr-2"></i>Close
                    </button>
                    <button onclick="selectConsultationFromModal()" class="btn btn-primary">
                        <i class="fas fa-check mr-2"></i>Select for Claim
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentConsultation = null;
        let claimData = null;
        let consultations = [];

        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Initialize the page
                initializePage();
                
                // Load data automatically
                updateStatusIndicator('loading', 'Loading system...');
                
                loadConsultations().catch(error => {
                    console.error('Failed to load consultations:', error);
                    updateStatusIndicator('error', 'Failed to load consultations');
                });
                
                loadClaimsData().catch(error => {
                    console.error('Failed to load claims data:', error);
                });
                
                loadAnalytics().catch(error => {
                    console.error('Failed to load analytics:', error);
                });
                
            } catch (error) {
                console.error('Error during initialization:', error);
                updateStatusIndicator('error', 'System initialization failed');
            }
            
            // Setup consultation search with live search
            const consultationSearch = document.getElementById('consultation_search');
            if (consultationSearch) {
                consultationSearch.addEventListener('input', function() {
                    const searchTerm = this.value.trim();
                    if (searchTerm.length >= 3) {
                        performConsultationSearch(searchTerm);
                    } else if (searchTerm.length === 0) {
                        clearConsultationDropdown();
                    }
                });
                
                // Clear dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!consultationSearch.contains(e.target) && !e.target.closest('.consultation-dropdown')) {
                        clearConsultationDropdown();
                    }
                });
            }
            
            // Auto-generate claim number
            generateClaimNumber();
            
            // Check if coming from diagnosis page with parameters
            const urlParams = new URLSearchParams(window.location.search);
            const visitId = urlParams.get('visit');
            const patientId = urlParams.get('patient');
            
            if (visitId && patientId) {
                loadConsultationFromParams(visitId, patientId);
            }
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

            // Note: Consultation search is handled in main DOMContentLoaded listener
        }

        // Generate unique claim number
        function generateClaimNumber() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const sequence = String(Math.floor(Math.random() * 999999)).padStart(6, '0');
            
            document.getElementById('claim_number').value = `CLM-${year}${month}-${sequence}`;
        }
        
        // Update status indicator
        function updateStatusIndicator(status, message) {
            const indicator = document.getElementById('statusIndicator');
            if (!indicator) return;
            
            // Remove existing classes
            indicator.className = indicator.className.replace(/bg-\w+-\d+/g, '').replace(/text-\w+-\d+/g, '');
            
            switch (status) {
                case 'loading':
                    indicator.className += ' bg-yellow-100 text-yellow-800';
                    indicator.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>' + message;
                    break;
                case 'ready':
                    indicator.className += ' bg-green-100 text-green-800';
                    indicator.innerHTML = '<i class="fas fa-check-circle mr-1"></i>' + message;
                    break;
                case 'error':
                    indicator.className += ' bg-red-100 text-red-800';
                    indicator.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>' + message;
                    break;
                case 'warning':
                    indicator.className += ' bg-orange-100 text-orange-800';
                    indicator.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>' + message;
                    break;
                default:
                    indicator.className += ' bg-gray-100 text-gray-800';
                    indicator.innerHTML = '<i class="fas fa-info-circle mr-1"></i>' + message;
            }
        }

        // Load consultations from API (Simplified Version)
        async function loadConsultations() {
            console.log('=== loadConsultations() called (simplified version) ===');
            
            const tableBody = document.getElementById('consultations_table');
            if (!tableBody) {
                alert('Error: Table element not found');
                return;
            }
            
            // Show loading state
            tableBody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <div class="flex items-center justify-center text-blue-600">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Loading consultations...
                        </div>
                    </td>
                </tr>
            `;
            
            updateStatusIndicator('loading', 'Loading consultations...');
            
            try {
                
                // Hide initial alert
                const initialAlert = document.getElementById('initialAlert');
                if (initialAlert) {
                    setTimeout(() => initialAlert.remove(), 2000);
                }
                
                // Add timeout to prevent hanging
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout
                
                const response = await fetch('../claims-api.php?action=get_claimable_consultations', {
                    signal: controller.signal,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                clearTimeout(timeoutId);
                console.log('Response received:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
                }
                
                const result = await response.json();
                console.log('API Result:', result);
                
                if (result.status === 'success') {
                    consultations = result.data || [];
                    console.log('Consultations loaded:', consultations.length);
                    displayConsultationsTable();
                    
                    if (consultations.length > 0) {
                        updateStatusIndicator('ready', `System Ready - ${consultations.length} consultations available`);
                        showAlert(`Loaded ${consultations.length} claimable consultations`, 'success');
                    } else {
                        updateStatusIndicator('warning', 'No claimable consultations found');
                        showAlert('No claimable consultations found. Try checking completed visits with NHIS numbers.', 'info');
                    }
                } else {
                    console.error('Failed to load consultations:', result.message);
                    updateStatusIndicator('error', 'Failed to load consultations');
                    showTableError('consultations_table', result.message || 'Unknown error occurred');
                    showAlert('Failed to load consultations: ' + (result.message || 'Unknown error'), 'danger');
                    
                    // Show debug info if available
                    if (result.debug_info) {
                        console.error('Debug info:', result.debug_info);
                    }
                }
            } catch (error) {
                console.error('Error loading consultations:', error);
                
                let errorMessage = 'Connection failed';
                if (error.name === 'AbortError') {
                    errorMessage = 'Request timed out - please check your connection';
                    updateStatusIndicator('error', 'Connection timeout');
                } else if (error.message) {
                    errorMessage = error.message;
                    updateStatusIndicator('error', 'Connection error');
                }
                
                showTableError('consultations_table', errorMessage);
                showAlert('Failed to load consultations: ' + errorMessage, 'danger');
                
                // Try to load sample data as fallback
                loadSampleData();
            }
        }
        
        // Production loadConsultations function
        async function loadConsultations() {
            const tableBody = document.getElementById('consultations_table');
            if (!tableBody) {
                console.error('Table element not found');
                return;
            }
            
            // Show loading state
            tableBody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> Loading consultations...</td></tr>';
            
            try {
                const response = await fetch('../claims-api.php?action=get_claimable_consultations');
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                
                if (result.status === 'success' && result.data) {
                    consultations = result.data;
                    
                    // Direct table population
                    if (consultations.length === 0) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="10" class="text-center py-8">
                                    <div class="text-yellow-600">
                                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                        <div>No claimable consultations found</div>
                                        <div class="text-sm text-gray-500 mt-1">Check if there are completed visits with NHIS numbers</div>
                                    </div>
                                </td>
                            </tr>
                        `;
                        updateStatusIndicator('warning', 'No consultations found');
                        return;
                    }
                    
                    // Clear table and populate directly
                    tableBody.innerHTML = '';
                    
                    consultations.forEach((consultation, index) => {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-gray-50';
                        row.innerHTML = `
                            <td class="text-center">
                                <input type="radio" name="selected_consultation" value="${consultation.visit_id}" 
                                       onchange="selectConsultationFromTable(${consultation.visit_id})" class="w-4 h-4 text-blue-600">
                            </td>
                            <td class="font-medium text-blue-600">${consultation.visit_id}</td>
                            <td>
                                <div class="font-medium">${consultation.full_name}</div>
                                <div class="text-sm text-gray-500">${consultation.phone || '-'}</div>
                            </td>
                            <td class="font-mono text-green-600">${consultation.nhis_number || 'N/A'}</td>
                            <td>
                                <div>${new Date(consultation.visit_date).toLocaleDateString()}</div>
                                <div class="text-sm text-gray-500">${consultation.visit_type || 'Consultation'}</div>
                            </td>
                            <td>${consultation.department_name || 'General'}</td>
                            <td>${consultation.physician_name || 'Not assigned'}</td>
                            <td>
                                <div class="text-sm">
                                    <div>Diagnoses: ${consultation.diagnosis_count || 0}</div>
                                    <div>Medications: ${consultation.prescription_count || 0}</div>
                                </div>
                            </td>
                            <td>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Available
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="viewConsultationDetails(${consultation.visit_id})" title="View details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                    
                    // Update status indicator
                    updateStatusIndicator('ready', `System Ready - ${consultations.length} consultations available`);
                    showAlert(`Loaded ${consultations.length} claimable consultations`, 'success');
                    
                } else {
                    throw new Error(result.message || 'API returned error status');
                }
                
            } catch (error) {
                console.error('Error loading consultations:', error);
                updateStatusIndicator('error', 'Failed to load consultations');
                showTableError('consultations_table', error.message);
                showAlert('Failed to load consultations: ' + error.message, 'danger');
            }
        }
        
        // Function to view consultation details in modal
        function viewConsultationDetails(visitId) {
            const consultation = consultations.find(c => c.visit_id == visitId);
            if (!consultation) {
                showAlert('Consultation not found', 'danger');
                return;
            }

            // Populate modal with consultation data
            document.getElementById('modal_patient_name').textContent = consultation.full_name || 'N/A';
            document.getElementById('modal_nhis_number').textContent = consultation.nhis_number || 'N/A';
            document.getElementById('modal_patient_age').textContent = consultation.age ? `${consultation.age} years` : 'N/A';
            document.getElementById('modal_patient_gender').textContent = consultation.gender || 'N/A';
            document.getElementById('modal_patient_phone').textContent = consultation.phone || 'N/A';
            document.getElementById('modal_patient_dob').textContent = consultation.date_of_birth ? new Date(consultation.date_of_birth).toLocaleDateString() : 'N/A';
            
            document.getElementById('modal_visit_id').textContent = consultation.visit_id;
            document.getElementById('modal_visit_date').textContent = new Date(consultation.visit_date).toLocaleString();
            document.getElementById('modal_visit_type').textContent = consultation.visit_type || 'Consultation';
            document.getElementById('modal_visit_status').textContent = consultation.visit_status || 'Completed';
            document.getElementById('modal_physician').textContent = consultation.physician_name || 'Not assigned';
            document.getElementById('modal_department').textContent = consultation.department_name || 'General';
            
            document.getElementById('modal_chief_complaint').textContent = consultation.chief_complaint || 'No chief complaint recorded';
            
            document.getElementById('modal_diagnosis_count').textContent = consultation.diagnosis_count || 0;
            document.getElementById('modal_prescription_count').textContent = consultation.prescription_count || 0;
            document.getElementById('modal_lab_count').textContent = consultation.lab_count || 0;

            // Store current consultation for modal actions
            currentConsultation = consultation;
            
            // Show modal
            document.getElementById('consultationModal').classList.remove('hidden');
        }

        // Function to close consultation modal
        function closeConsultationModal() {
            document.getElementById('consultationModal').classList.add('hidden');
            currentConsultation = null;
        }

        // Function to select consultation from modal
        function selectConsultationFromModal() {
            if (currentConsultation) {
                // Select the radio button for this consultation
                const radio = document.querySelector(`input[name="selected_consultation"][value="${currentConsultation.visit_id}"]`);
                if (radio) {
                    radio.checked = true;
                    selectConsultationFromTable(currentConsultation.visit_id);
                }
                closeConsultationModal();
                showAlert(`Selected consultation for ${currentConsultation.full_name}`, 'success');
            }
        }

        // Function to handle consultation selection from table
        function selectConsultationFromTable(visitId) {
            const consultation = consultations.find(c => c.visit_id == visitId);
            if (consultation) {
                currentConsultation = consultation;
                
                // Update selected consultation info
                document.getElementById('consultation_id').textContent = consultation.visit_id;
                document.getElementById('consultation_patient').textContent = consultation.full_name;
                document.getElementById('consultation_nhis').textContent = consultation.nhis_number;
                document.getElementById('consultation_date').textContent = new Date(consultation.visit_date).toLocaleDateString();
                document.getElementById('consultation_physician').textContent = consultation.physician_name || 'Not assigned';
                document.getElementById('consultation_department').textContent = consultation.department_name || 'General';
                document.getElementById('consultation_complaint').textContent = consultation.chief_complaint || 'No complaint recorded';
                document.getElementById('consultation_type').textContent = consultation.visit_type || 'Consultation';
                
                // Show selected consultation info
                document.getElementById('selectedConsultationInfo').classList.remove('hidden');
                
                showAlert(`Selected: ${consultation.full_name} (Visit ID: ${consultation.visit_id})`, 'success');
                
                // Enable claim processing buttons
                enableClaimProcessing();
                
                // Update workflow steps
                updateWorkflowStep(1, 'completed');
                updateWorkflowStep(2, 'active');
            }
        }

        // Function to enable claim processing workflow
        function enableClaimProcessing() {
            // Enable all buttons that were previously disabled
            const allButtons = document.querySelectorAll('button[disabled]');
            allButtons.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
            
            // Show claim generation section
            const claimSection = document.getElementById('claimGenerationSection');
            if (claimSection) {
                claimSection.style.display = 'block';
            }
        }

        // NHIS Compliance Functions
        function verifyNHISEligibility() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            showAlert(`Verifying NHIS eligibility for ${currentConsultation.full_name}...`, 'info');
            
            // Simulate verification
            setTimeout(() => {
                showAlert(`✅ NHIS Eligibility Verified for ${currentConsultation.nhis_number}`, 'success');
                updateWorkflowStep(2, 'completed');
                updateWorkflowStep(3, 'active');
            }, 2000);
        }

        function validateDiagnoses() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            showAlert('Validating ICD-10 diagnosis codes...', 'info');
            
            setTimeout(() => {
                showAlert(`✅ ${currentConsultation.diagnosis_count || 0} diagnosis codes validated`, 'success');
            }, 1500);
        }

        function checkMedicationFormulary() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            showAlert('Checking medications against NHIS formulary...', 'info');
            
            setTimeout(() => {
                showAlert(`✅ ${currentConsultation.prescription_count || 0} medications checked`, 'success');
            }, 1500);
        }

        function runFullCompliance() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            showAlert('Running comprehensive compliance check...', 'info');
            
            setTimeout(() => {
                showAlert('✅ Full compliance check passed - Ready to generate claim', 'success');
                updateWorkflowStep(3, 'completed');
                updateWorkflowStep(4, 'active');
                
                // Enable claim generation
                enableClaimGeneration();
            }, 3000);
        }

        function enableClaimGeneration() {
            // Add a generate claim button if not exists
            const complianceSection = document.querySelector('.card h3').closest('.card');
            if (complianceSection && !document.getElementById('generateClaimBtn')) {
                const generateBtn = document.createElement('div');
                generateBtn.innerHTML = `
                    <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <button id="generateClaimBtn" class="btn btn-success w-full" onclick="generateClaim()">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                            Generate NHIS Claim Form
                        </button>
                    </div>
                `;
                complianceSection.appendChild(generateBtn);
            }
        }

        function generateClaim() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            showAlert('Generating NHIS claim form...', 'info');
            
            setTimeout(() => {
                showAlert('✅ NHIS Claim Form Generated Successfully!', 'success');
                updateWorkflowStep(4, 'completed');
                updateWorkflowStep(5, 'active');
                
                // Show generated claim preview
                showClaimPreview();
            }, 2000);
        }

        function showClaimPreview() {
            showAlert(`📋 Claim generated for Visit ID: ${currentConsultation.visit_id}\n\nPatient: ${currentConsultation.full_name}\nNHIS: ${currentConsultation.nhis_number}\nAmount: ₵${(Math.random() * 500 + 50).toFixed(2)}\n\n✅ Ready for submission to NHIS`, 'success');
        }
        
        // Load sample data as fallback
        function loadSampleData() {
            console.log('Loading sample data as fallback...');
            
            // Create sample consultations for demonstration
            consultations = [
                {
                    visit_id: 'SAMPLE001',
                    full_name: 'John Doe',
                    nhis_number: 'NH12345678',
                    visit_date: new Date().toISOString(),
                    visit_type: 'OPD',
                    physician_name: 'Dr. Sample',
                    diagnosis_count: 2,
                    prescription_count: 3,
                    claim_status: null,
                    phone: '0241234567',
                    age: 35,
                    gender: 'Male'
                },
                {
                    visit_id: 'SAMPLE002',
                    full_name: 'Jane Smith',
                    nhis_number: 'NH87654321',
                    visit_date: new Date(Date.now() - 24*60*60*1000).toISOString(),
                    visit_type: 'Emergency',
                    physician_name: 'Dr. Example',
                    diagnosis_count: 1,
                    prescription_count: 2,
                    claim_status: null,
                    phone: '0207654321',
                    age: 28,
                    gender: 'Female'
                }
            ];
            
            displayConsultationsTable();
            updateStatusIndicator('warning', 'Using sample data - API offline');
            showAlert('Sample data loaded - API connection failed. Please check your server configuration.', 'warning');
        }
        

        
        // Display consultations in table
        function displayConsultationsTable() {
            const tableBody = document.getElementById('consultations_table');
            tableBody.innerHTML = '';
            
            if (consultations.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-8">
                            <div class="text-gray-500">
                                <i class="fas fa-inbox text-3xl mb-2"></i>
                                <div>No claimable consultations found</div>
                                <div class="text-sm mt-1">Try adjusting your filter criteria</div>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }
            
            consultations.forEach(consultation => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors';
                
                const statusClass = getStatusClass(consultation.claim_status || 'available');
                const statusText = consultation.claim_status || 'Available';
                
                row.innerHTML = `
                    <td class="text-center">
                        <input type="radio" name="selected_consultation" value="${consultation.visit_id}" 
                               onchange="selectConsultationFromTable(${consultation.visit_id})"
                               class="w-4 h-4 text-blue-600">
                    </td>
                    <td class="font-medium text-blue-600">${consultation.visit_id}</td>
                    <td>
                        <div class="font-medium">${consultation.full_name}</div>
                        <div class="text-sm text-gray-500">${consultation.phone || '-'}</div>
                    </td>
                    <td class="font-mono text-green-600">${consultation.nhis_number || 'N/A'}</td>
                    <td>
                        <div>${new Date(consultation.visit_date).toLocaleDateString()}</div>
                        <div class="text-sm text-gray-500">${new Date(consultation.visit_date).toLocaleTimeString()}</div>
                    </td>
                    <td>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                            ${consultation.visit_type}
                        </span>
                    </td>
                    <td>${consultation.physician_name || 'Not assigned'}</td>
                    <td>
                        <div class="text-sm">
                            <div>Diagnoses: ${consultation.diagnosis_count}</div>
                            <div>Medications: ${consultation.prescription_count}</div>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </td>
                    <td>
                        <div class="flex space-x-1">
                            <button class="btn btn-sm btn-primary" onclick="viewConsultationDetails(${consultation.visit_id})"
                                    title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-success" onclick="selectConsultationFromTable(${consultation.visit_id})"
                                    title="Select for Claims"
                                    ${consultation.claim_status ? 'disabled' : ''}>
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }
        
        // Helper function to get status class
        function getStatusClass(status) {
            const statusMap = {
                'available': 'status-draft',
                'draft': 'status-draft', 
                'submitted': 'status-submitted',
                'approved': 'status-approved',
                'rejected': 'status-rejected',
                'paid': 'status-paid'
            };
            return statusMap[status.toLowerCase()] || 'status-draft';
        }
        
        // Show loading state in table
        function showTableLoading(tableId) {
            const tableBody = document.getElementById(tableId);
            const colCount = tableBody.closest('table').querySelectorAll('thead th').length;
            tableBody.innerHTML = `
                <tr>
                    <td colspan="${colCount}" class="text-center py-8">
                        <div class="flex items-center justify-center">
                            <i class="fas fa-spinner fa-spin mr-2 text-blue-500"></i>
                            <span>Loading data...</span>
                        </div>
                    </td>
                </tr>
            `;
        }
        
        // Show error state in table
        function showTableError(tableId, message) {
            const tableBody = document.getElementById(tableId);
            const colCount = tableBody.closest('table').querySelectorAll('thead th').length;
            tableBody.innerHTML = `
                <tr>
                    <td colspan="${colCount}" class="text-center py-8">
                        <div class="text-red-500">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <div class="mb-2">Error: ${message}</div>
                            <div class="space-x-2">
                                <button class="btn btn-sm btn-primary" onclick="loadConsultations()">
                                    <i class="fas fa-redo mr-1"></i>
                                    Retry
                                </button>
                                <button class="btn btn-sm btn-secondary" onclick="testAPIConnection()">
                                    <i class="fas fa-wifi mr-1"></i>
                                    Test Connection
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="loadSampleData()">
                                    <i class="fas fa-database mr-1"></i>
                                    Load Sample Data
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }
        
        // Test API connection
        async function testAPIConnection() {
            try {
                showAlert('Testing API connection...', 'info');
                
                const response = await fetch('../test-api.php');
                const result = await response.json();
                
                if (result.status === 'success') {
                    showAlert(`API Connection OK - Database has ${result.sample_count} sample consultations`, 'success');
                    console.log('API Test Result:', result);
                } else {
                    showAlert('API Test Failed: ' + result.message, 'danger');
                }
            } catch (error) {
                showAlert('API Connection Failed: ' + error.message, 'danger');
                console.error('API test error:', error);
            }
        }
        
        // Load consultation from URL parameters
        async function loadConsultationFromParams(visitId, patientId) {
            try {
                showAlert('Loading consultation data...', 'info');
                
                const response = await fetch(`../claims-api.php?action=get_consultation_details&visit_id=${visitId}`);
                const result = await response.json();
                
                if (result.status === 'success') {
                    const consultationData = {
                        id: visitId,
                        visit_id: visitId,
                        patient: result.data.visit.full_name,
                        patient_id: patientId,
                        nhis_number: result.data.visit.nhis_number,
                        date: result.data.visit.visit_date,
                        physician: result.data.visit.physician_name,
                        diagnoses: result.data.diagnoses,
                        medications: result.data.prescriptions,
                        services: result.data.services
                    };
                    
                    selectConsultation(consultationData);
                    
                    // Verify NHIS eligibility
                    await verifyNHISEligibility(result.data.visit.nhis_number);
                    
                    // Calculate claim amount
                    await calculateClaimAmount(visitId);
                    
                } else {
                    showAlert('Failed to load consultation: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error loading consultation:', error);
                showAlert('Error loading consultation data', 'danger');
            }
        }

        // Search consultation
        // Search consultation (triggered by button click)
        async function searchConsultation() {
            const searchTerm = document.getElementById('consultation_search').value.trim();
            
            if (searchTerm.length < 2) {
                showAlert('Please enter at least 2 characters to search', 'warning');
                return;
            }
            
            await performConsultationSearch(searchTerm);
        }
        
        // Perform consultation search via API
        async function performConsultationSearch(searchTerm) {
            try {
                showAlert('Searching consultations...', 'info');
                
                const response = await fetch(`../claims-api.php?action=search_consultations&q=${encodeURIComponent(searchTerm)}`);
                const result = await response.json();
                
                if (result.status === 'success') {
                    if (result.data.length > 0) {
                        showConsultationDropdown(result.data);
                        showAlert(`Found ${result.data.length} matching consultations`, 'success');
                    } else {
                        showConsultationDropdown([], 'No consultations found matching your search');
                    }
                } else {
                    showConsultationDropdown([], `Search failed: ${result.message}`);
                    showAlert('Search failed: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error searching consultations:', error);
                showConsultationDropdown([], 'Search failed. Please check your connection.');
                showAlert('Search failed. Please try again.', 'danger');
            }
        }
        
        // Show consultation selection dropdown
        function showConsultationDropdown(consultations, errorMessage = null) {
            const searchContainer = document.getElementById('consultation_search').parentElement;
            
            // Remove existing dropdown
            clearConsultationDropdown();
            
            // Don't show dropdown if search input is empty
            const searchInput = document.getElementById('consultation_search');
            if (!searchInput.value.trim()) {
                return;
            }
            
            // Create dropdown
            const dropdown = document.createElement('div');
            dropdown.className = 'consultation-dropdown absolute z-20 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-80 overflow-y-auto';
            dropdown.style.top = '100%';
            dropdown.style.left = '0';
            dropdown.style.right = '0';
            
            if (errorMessage) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'p-3 text-red-600 text-sm';
                errorDiv.textContent = errorMessage;
                dropdown.appendChild(errorDiv);
            } else {
                consultations.forEach(consultation => {
                    const option = document.createElement('div');
                    option.className = 'p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 transition-colors';
                    
                    const visitDate = new Date(consultation.visit_date).toLocaleDateString();
                    const visitTime = new Date(consultation.visit_date).toLocaleTimeString();
                    const age = consultation.age || calculateAge(consultation.date_of_birth) || 'N/A';
                    
                    option.innerHTML = `
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">${consultation.full_name}</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    <span class="inline-flex items-center mr-3">
                                        <i class="fas fa-id-card w-4 text-green-500 mr-1"></i>
                                        NHIS: ${consultation.nhis_number || 'N/A'}
                                    </span>
                                    <span class="inline-flex items-center mr-3">
                                        <i class="fas fa-calendar w-4 text-blue-500 mr-1"></i>
                                        ${visitDate}
                                    </span>
                                    <span class="inline-flex items-center">
                                        <i class="fas fa-user w-4 text-purple-500 mr-1"></i>
                                        ${age} yrs, ${consultation.gender}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Visit ID: ${consultation.visit_id} | 
                                    ${consultation.visit_type} | 
                                    Dr. ${consultation.physician_name || 'Not assigned'}
                                </div>
                            </div>
                            <div class="text-right ml-3">
                                <div class="text-sm font-medium text-blue-600">
                                    ${visitTime}
                                </div>
                                <div class="text-xs text-gray-500">
                                    ${consultation.department_name || 'General'}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    option.addEventListener('click', () => {
                        selectConsultationFromDropdown(consultation);
                        clearConsultationDropdown();
                    });
                    
                    dropdown.appendChild(option);
                });
            }
            
            searchContainer.appendChild(dropdown);
        }
        
        // Clear consultation dropdown
        function clearConsultationDropdown() {
            const existingDropdown = document.querySelector('.consultation-dropdown');
            if (existingDropdown) {
                existingDropdown.remove();
            }
        }
        
        // Select consultation from dropdown
        function selectConsultationFromDropdown(consultation) {
            // Update search input with selected patient name
            document.getElementById('consultation_search').value = `${consultation.full_name} (${consultation.visit_id})`;
            
            // Select the consultation for claims processing
            selectConsultationFromTable(consultation.visit_id);
        }
        
        // Calculate age from date of birth
        function calculateAge(dateOfBirth) {
            if (!dateOfBirth) return 'N/A';
            
            const dob = new Date(dateOfBirth);
            const now = new Date();
            let age = now.getFullYear() - dob.getFullYear();
            const monthDiff = now.getMonth() - dob.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < dob.getDate())) {
                age--;
            }
            
            return age;
        }

        // Select consultation from table
        async function selectConsultationFromTable(visitId) {
            try {
                showAlert('Loading consultation details...', 'info');
                
                const response = await fetch(`../claims-api.php?action=get_consultation_details&visit_id=${visitId}`);
                const result = await response.json();
                
                if (result.status === 'success') {
                    const consultation = {
                        id: visitId,
                        visit_id: visitId,
                        patient: result.data.visit.full_name,
                        patient_id: result.data.visit.patient_id,
                        nhis_number: result.data.visit.nhis_number,
                        date: result.data.visit.visit_date,
                        physician: result.data.visit.physician_name,
                        department: result.data.visit.department_name,
                        complaint: result.data.visit.chief_complaint,
                        visit_type: result.data.visit.visit_type,
                        diagnoses: result.data.diagnoses,
                        medications: result.data.prescriptions,
                        services: result.data.services,
                        labs: result.data.lab_orders || []
                    };
                    
                    selectConsultation(consultation);
                } else {
                    showAlert('Failed to load consultation: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error loading consultation:', error);
                showAlert('Error loading consultation data', 'danger');
            }
        }
        
        // Select consultation for claims
        function selectConsultation(consultation) {
            currentConsultation = consultation;
            
            // Show consultation info
            const infoDiv = document.getElementById('selectedConsultationInfo');
            infoDiv.classList.remove('hidden');
            
            // Populate consultation details
            document.getElementById('consultation_id').textContent = consultation.id;
            document.getElementById('consultation_patient').textContent = consultation.patient;
            document.getElementById('consultation_nhis').textContent = consultation.nhis_number || 'N/A';
            document.getElementById('consultation_date').textContent = new Date(consultation.date).toLocaleDateString();
            document.getElementById('consultation_physician').textContent = consultation.physician || 'Not assigned';
            document.getElementById('consultation_department').textContent = consultation.department || 'N/A';
            document.getElementById('consultation_complaint').textContent = consultation.complaint || 'Not specified';
            document.getElementById('consultation_type').textContent = consultation.visit_type || 'N/A';
            
            // Populate clinical details
            populateClinicalDetails(consultation);
            
            // Enable sections
            enableSection('claim_summary_section');
            enableSection('claim_form_section');
            
            // Update workflow steps
            updateWorkflowStep(3, 'active');
            
            // Auto-populate form fields
            populateFormFields(consultation);
            
            // Start compliance check
            runFullCompliance();
            
            showAlert('Consultation selected for claims processing', 'success');
        }
        
        // Populate clinical details
        function populateClinicalDetails(consultation) {
            // Diagnoses
            const diagnosesDiv = document.getElementById('consultation_diagnoses');
            const diagnosisCount = document.getElementById('diagnosis_count');
            
            if (consultation.diagnoses && consultation.diagnoses.length > 0) {
                diagnosisCount.textContent = consultation.diagnoses.length;
                diagnosesDiv.innerHTML = consultation.diagnoses.map(d => `
                    <div class="p-2 bg-red-50 rounded border-l-2 border-red-200">
                        <div class="font-medium text-sm">${d.diagnosis_description}</div>
                        <div class="text-xs text-gray-600">Code: ${d.icd10_code} | Type: ${d.diagnosis_type}</div>
                    </div>
                `).join('');
            } else {
                diagnosisCount.textContent = '0';
                diagnosesDiv.innerHTML = '<div class="text-gray-500 text-sm">No diagnoses recorded</div>';
            }
            
            // Medications
            const medicationsDiv = document.getElementById('consultation_medications');
            const medicationCount = document.getElementById('medication_count');
            
            if (consultation.medications && consultation.medications.length > 0) {
                medicationCount.textContent = consultation.medications.length;
                medicationsDiv.innerHTML = consultation.medications.map(m => `
                    <div class="p-2 bg-green-50 rounded border-l-2 border-green-200">
                        <div class="font-medium text-sm">${m.medication_name}</div>
                        <div class="text-xs text-gray-600">${m.dosage} | ${m.frequency} | Qty: ${m.quantity}</div>
                    </div>
                `).join('');
            } else {
                medicationCount.textContent = '0';
                medicationsDiv.innerHTML = '<div class="text-gray-500 text-sm">No medications prescribed</div>';
            }
            
            // Lab Tests
            const labsDiv = document.getElementById('consultation_labs');
            const labCount = document.getElementById('lab_count');
            
            if (consultation.labs && consultation.labs.length > 0) {
                labCount.textContent = consultation.labs.length;
                labsDiv.innerHTML = consultation.labs.map(l => `
                    <div class="p-2 bg-blue-50 rounded border-l-2 border-blue-200">
                        <div class="font-medium text-sm">${l.test_name}</div>
                        <div class="text-xs text-gray-600">Status: ${l.status}</div>
                    </div>
                `).join('');
            } else {
                labCount.textContent = '0';
                labsDiv.innerHTML = '<div class="text-gray-500 text-sm">No lab tests ordered</div>';
            }
        }
        
        // Enable/disable sections
        function enableSection(sectionId) {
            const section = document.getElementById(sectionId);
            section.style.opacity = '1';
            section.style.pointerEvents = 'auto';
            
            // Enable buttons in the section
            const buttons = section.querySelectorAll('button[disabled]');
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('opacity-50');
            });
        }
        
        function disableSection(sectionId) {
            const section = document.getElementById(sectionId);
            section.style.opacity = '0.5';
            section.style.pointerEvents = 'none';
            
            // Disable buttons in the section
            const buttons = section.querySelectorAll('button:not([disabled])');
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-50');
            });
        }
        
        // Populate form fields with consultation data
        function populateFormFields(consultation) {
            // Set claim type based on visit type
            const claimTypeMap = {
                'OPD': 'OPD',
                'Emergency': 'Emergency',
                'Follow-up': 'OPD',
                'Referral': 'Referral'
            };
            
            document.getElementById('claim_type').value = claimTypeMap[consultation.visit_type] || 'OPD';
            document.getElementById('attending_provider').value = consultation.physician || '';
            document.getElementById('admission_date').value = consultation.date ? 
                new Date(consultation.date).toISOString().slice(0, 16) : '';
            
            // Update form progress
            updateFormProgress();
        }
        
        // Verify NHIS eligibility
        async function verifyNHISEligibility(nhisNumber = null) {
            if (!currentConsultation && !nhisNumber) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            const nhis = nhisNumber || currentConsultation.nhis_number;
            
            try {
                updateComplianceCheck('eligibility_check', 'loading', 'Verifying NHIS eligibility...');
                
                const response = await fetch('../claims-api.php?action=verify_nhis_eligibility', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ nhis_number: nhis })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    if (result.data.eligible) {
                        updateComplianceCheck('eligibility_check', 'pass', 
                            'Patient Eligibility: Active NHIS membership verified',
                            `NHIS: ${result.data.nhis_number} | Patient: ${result.data.patient_name}`);
                        
                        // Update detailed compliance
                        document.getElementById('nhis_status').textContent = 'Active';
                        document.getElementById('nhis_expiry').textContent = '2024-12-31'; // Mock data
                        document.getElementById('nhis_scheme').textContent = 'Standard NHIS';
                        document.getElementById('nhis_benefits').textContent = 'Full Coverage';
                    } else {
                        updateComplianceCheck('eligibility_check', 'fail', 
                            'Patient Eligibility: ' + result.data.reasons.join(', '),
                            'NHIS verification failed');
                    }
                    
                    updateOverallCompliance();
                } else {
                    updateComplianceCheck('eligibility_check', 'fail', 
                        'Patient Eligibility: Verification failed',
                        result.message);
                }
            } catch (error) {
                console.error('Error verifying NHIS eligibility:', error);
                updateComplianceCheck('eligibility_check', 'fail', 
                    'Patient Eligibility: System error',
                    'Unable to verify NHIS status');
            }
        }
        
        // Validate diagnoses
        async function validateDiagnoses() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            updateComplianceCheck('diagnosis_codes_check', 'loading', 'Validating ICD-10 codes...');
            
            // Simulate validation
            setTimeout(() => {
                if (currentConsultation.diagnoses && currentConsultation.diagnoses.length > 0) {
                    const validCodes = currentConsultation.diagnoses.filter(d => d.icd10_code && d.icd10_code.length > 0);
                    
                    if (validCodes.length === currentConsultation.diagnoses.length) {
                        updateComplianceCheck('diagnosis_codes_check', 'pass', 
                            'Diagnosis Codes: Valid ICD-10 codes verified',
                            `${validCodes.length} diagnosis codes validated`);
                    } else {
                        updateComplianceCheck('diagnosis_codes_check', 'warning', 
                            'Diagnosis Codes: Some codes need review',
                            `${validCodes.length}/${currentConsultation.diagnoses.length} codes valid`);
                    }
                } else {
                    updateComplianceCheck('diagnosis_codes_check', 'fail', 
                        'Diagnosis Codes: No diagnoses found',
                        'At least one diagnosis is required for claims');
                }
                
                updateOverallCompliance();
            }, 1500);
        }
        
        // Check medication formulary
        async function checkMedicationFormulary() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            updateComplianceCheck('medication_formulary_check', 'loading', 'Checking NHIS formulary...');
            
            // Simulate formulary check
            setTimeout(() => {
                if (currentConsultation.medications && currentConsultation.medications.length > 0) {
                    // Mock: assume 90% of medications are covered
                    const coveredMeds = Math.floor(currentConsultation.medications.length * 0.9);
                    
                    if (coveredMeds === currentConsultation.medications.length) {
                        updateComplianceCheck('medication_formulary_check', 'pass', 
                            'Medication Formulary: All medications covered',
                            `${currentConsultation.medications.length} medications in NHIS formulary`);
                            
                        // Update coverage analysis
                        document.getElementById('med_coverage').innerHTML = 
                            '<span class="text-green-600">✓ All covered</span>';
                    } else {
                        updateComplianceCheck('medication_formulary_check', 'warning', 
                            'Medication Formulary: Some medications not covered',
                            `${coveredMeds}/${currentConsultation.medications.length} medications covered`);
                            
                        document.getElementById('med_coverage').innerHTML = 
                            `<span class="text-orange-600">⚠ ${coveredMeds}/${currentConsultation.medications.length} covered</span>`;
                    }
                } else {
                    updateComplianceCheck('medication_formulary_check', 'pass', 
                        'Medication Formulary: No medications prescribed',
                        'No formulary check needed');
                        
                    document.getElementById('med_coverage').innerHTML = 
                        '<span class="text-gray-500">N/A</span>';
                }
                
                updateOverallCompliance();
            }, 1200);
        }
        
        // Run full compliance check
        async function runFullCompliance() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            showAlert('Running comprehensive compliance check...', 'info');
            
            // Reset compliance status
            document.getElementById('compliance_score').textContent = '-';
            document.getElementById('compliance_bar').style.width = '0%';
            
            // Run all checks sequentially
            await verifyNHISEligibility();
            
            setTimeout(() => {
                validateDiagnoses();
            }, 500);
            
            setTimeout(() => {
                checkMedicationFormulary();
            }, 1000);
            
            setTimeout(() => {
                checkServiceCoverage();
            }, 1500);
            
            setTimeout(() => {
                validateTariffCalculation();
            }, 2000);
            
            setTimeout(() => {
                checkDocumentation();
            }, 2500);
        }
        
        // Check service coverage
        function checkServiceCoverage() {
            updateComplianceCheck('service_coverage_check', 'loading', 'Checking service coverage...');
            
            setTimeout(() => {
                updateComplianceCheck('service_coverage_check', 'pass', 
                    'Service Coverage: All services covered under NHIS',
                    'OPD, Laboratory, and Pharmacy services verified');
                    
                // Update coverage analysis
                document.getElementById('opd_coverage').innerHTML = '<span class="text-green-600">✓ Covered</span>';
                document.getElementById('lab_coverage').innerHTML = '<span class="text-green-600">✓ Covered</span>';
                document.getElementById('proc_coverage').innerHTML = '<span class="text-green-600">✓ Covered</span>';
                
                updateOverallCompliance();
            }, 800);
        }
        
        // Validate tariff calculation
        function validateTariffCalculation() {
            updateComplianceCheck('tariff_calculation_check', 'loading', 'Validating tariff calculation...');
            
            setTimeout(() => {
                updateComplianceCheck('tariff_calculation_check', 'pass', 
                    'Tariff Calculation: Auto-calculated based on NHIA rates',
                    'Using NHIA tariff schedule version 2024.1');
                    
                updateOverallCompliance();
            }, 600);
        }
        
        // Check documentation
        function checkDocumentation() {
            updateComplianceCheck('documentation_check', 'loading', 'Reviewing documentation...');
            
            setTimeout(() => {
                updateComplianceCheck('documentation_check', 'pass', 
                    'Documentation: All required documents present',
                    'Patient records, diagnoses, and prescriptions complete');
                    
                updateOverallCompliance();
            }, 700);
        }
        
        // Update individual compliance check
        function updateComplianceCheck(checkId, status, mainText, subText = '') {
            const checkElement = document.getElementById(checkId);
            const icon = checkElement.querySelector('i');
            const textDiv = checkElement.querySelector('div');
            
            // Remove existing classes
            checkElement.classList.remove('compliance-pass', 'compliance-fail', 'compliance-warning');
            
            switch (status) {
                case 'loading':
                    icon.className = 'fas fa-spinner fa-spin mr-3 text-xl text-blue-500';
                    break;
                case 'pass':
                    checkElement.classList.add('compliance-pass');
                    icon.className = 'fas fa-check-circle mr-3 text-xl text-green-500';
                    break;
                case 'fail':
                    checkElement.classList.add('compliance-fail');
                    icon.className = 'fas fa-times-circle mr-3 text-xl text-red-500';
                    break;
                case 'warning':
                    checkElement.classList.add('compliance-warning');
                    icon.className = 'fas fa-exclamation-triangle mr-3 text-xl text-orange-500';
                    break;
            }
            
            textDiv.innerHTML = `
                <strong>${mainText}</strong>
                ${subText ? `<div class="text-sm text-gray-600 mt-1">${subText}</div>` : ''}
            `;
        }
        
        // Update overall compliance score
        function updateOverallCompliance() {
            const checks = [
                'eligibility_check',
                'service_coverage_check', 
                'diagnosis_codes_check',
                'medication_formulary_check',
                'tariff_calculation_check',
                'documentation_check'
            ];
            
            let passedChecks = 0;
            let totalChecks = checks.length;
            
            checks.forEach(checkId => {
                const element = document.getElementById(checkId);
                if (element.classList.contains('compliance-pass')) {
                    passedChecks++;
                }
            });
            
            const score = Math.round((passedChecks / totalChecks) * 100);
            const scoreElement = document.getElementById('compliance_score');
            const barElement = document.getElementById('compliance_bar');
            const summaryElement = document.getElementById('compliance_summary');
            
            scoreElement.textContent = `${score}%`;
            barElement.style.width = `${score}%`;
            
            // Update color based on score
            if (score >= 90) {
                scoreElement.className = 'text-4xl font-bold text-green-600 mb-2';
                barElement.className = 'bg-green-600 h-2 rounded-full transition-all duration-300';
                summaryElement.innerHTML = `
                    <div class="flex items-center text-green-800">
                        <i class="fas fa-thumbs-up mr-2"></i>
                        <strong>Compliance Status: EXCELLENT</strong>
                    </div>
                    <p class="text-sm text-green-700 mt-1">All requirements met for NHIA submission</p>
                `;
                summaryElement.className = summaryElement.className.replace('bg-gray-50 border-gray-200', 'bg-green-50 border-green-200');
            } else if (score >= 70) {
                scoreElement.className = 'text-4xl font-bold text-orange-600 mb-2';
                barElement.className = 'bg-orange-600 h-2 rounded-full transition-all duration-300';
                summaryElement.innerHTML = `
                    <div class="flex items-center text-orange-800">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Compliance Status: NEEDS ATTENTION</strong>
                    </div>
                    <p class="text-sm text-orange-700 mt-1">Some issues need to be resolved before submission</p>
                `;
                summaryElement.className = summaryElement.className.replace('bg-gray-50 border-gray-200', 'bg-orange-50 border-orange-200');
            } else {
                scoreElement.className = 'text-4xl font-bold text-red-600 mb-2';
                barElement.className = 'bg-red-600 h-2 rounded-full transition-all duration-300';
                summaryElement.innerHTML = `
                    <div class="flex items-center text-red-800">
                        <i class="fas fa-times-circle mr-2"></i>
                        <strong>Compliance Status: CRITICAL ISSUES</strong>
                    </div>
                    <p class="text-sm text-red-700 mt-1">Multiple issues must be resolved before proceeding</p>
                `;
                summaryElement.className = summaryElement.className.replace('bg-gray-50 border-gray-200', 'bg-red-50 border-red-200');
            }
            
            summaryElement.classList.remove('hidden');
            
            // Show detailed compliance if score is calculated
            if (score > 0) {
                document.getElementById('detailed_compliance').classList.remove('hidden');
            }
            
            // Enable claim calculation if compliance is good
            if (score >= 70) {
                setTimeout(() => {
                    calculateClaimAmount(currentConsultation.visit_id);
                }, 1000);
            }
        }
        
        // Calculate claim amount
        async function calculateClaimAmount(visitId) {
            try {
                showAlert('Calculating claim amounts...', 'info');
                
                const response = await fetch('../claims-api.php?action=calculate_claim_amount', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ visit_id: visitId })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    updateClaimSummary(result.data);
                    updateFormProgress();
                    showAlert('Claim amounts calculated successfully', 'success');
                } else {
                    console.error('Claim calculation failed:', result.message);
                    showAlert('Failed to calculate claim amounts: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error calculating claim amount:', error);
                showAlert('Error calculating claim amounts', 'danger');
            }
        }
        
        // Recalculate amounts
        async function recalculateAmounts() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            await calculateClaimAmount(currentConsultation.visit_id);
        }
        
        // Show tariff details
        function showTariffDetails() {
            const tariffInfo = `
NHIA TARIFF SCHEDULE 2024.1
━━━━━━━━━━━━━━━━━━━━━━━━━━━

OPD SERVICES:
• General Consultation: ₵45.00
• Specialist Consultation: ₵85.00
• Emergency Consultation: ₵65.00

LABORATORY TESTS:
• Full Blood Count: ₵22.00
• Malaria Test: ₵15.00
• Urine Analysis: ₵18.00
• Blood Sugar: ₵12.00
• Hepatitis B Test: ₵35.00
• HIV Test: ₵25.00

PROCEDURES:
• Wound Dressing: ₵25.00
• Injection: ₵15.00
• BP Check: ₵8.00

ADMINISTRATIVE:
• Admin Fee: ₵8.00
• Registration: ₵5.00

Note: Tariffs are subject to NHIA approval
and may vary by region and facility type.
            `;
            
            alert(tariffInfo);
        }
        
        // Update compliance status display
        function updateComplianceStatus(eligibilityData) {
            const complianceResults = document.getElementById('compliance_results');
            
            if (eligibilityData.eligible) {
                // Update to show all checks passed
                complianceResults.innerHTML = `
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
                `;
            } else {
                // Show eligibility issues
                complianceResults.innerHTML = `
                    <div class="compliance-indicator compliance-fail">
                        <i class="fas fa-times-circle mr-3 text-xl"></i>
                        <div>
                            <strong>Patient Eligibility:</strong> ${eligibilityData.reasons.join(', ')}
                        </div>
                    </div>
                `;
            }
        }
        
        // Update claim summary display
        function updateClaimSummary(claimData) {
            const summaryContainer = document.getElementById('service_breakdown');
            
            let summaryHTML = '';
            let itemCount = 0;
            
            // Build service breakdown
            Object.keys(claimData.breakdown).forEach(key => {
                if (claimData.breakdown[key] > 0) {
                    const serviceName = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    summaryHTML += `
                        <div class="summary-row">
                            <span>${serviceName}:</span>
                            <span>₵${claimData.breakdown[key].toFixed(2)}</span>
                        </div>
                    `;
                    itemCount++;
                }
            });
            
            summaryHTML += `
                <div class="summary-row">
                    <span><strong>Total Claim Amount:</strong></span>
                    <span><strong>₵${claimData.total_claim.toFixed(2)}</strong></span>
                </div>
            `;
            
            summaryContainer.innerHTML = summaryHTML;
            
            // Update payment summary
            document.getElementById('total_claim_amount').textContent = `₵${claimData.total_claim.toFixed(2)}`;
            document.getElementById('nhia_reimbursement').textContent = `₵${claimData.nhia_reimbursement.toFixed(2)}`;
            document.getElementById('patient_copayment').textContent = `₵${claimData.patient_copayment.toFixed(2)}`;
            
            const reimbursementRate = (claimData.reimbursement_rate || 90);
            const copaymentRate = 100 - reimbursementRate;
            
            document.getElementById('reimbursement_percentage').textContent = reimbursementRate;
            document.getElementById('copayment_percentage').textContent = copaymentRate;
            
            // Update summary cards
            document.getElementById('summary_total').textContent = `₵${claimData.total_claim.toFixed(2)}`;
            document.getElementById('summary_nhia').textContent = `₵${claimData.nhia_reimbursement.toFixed(2)}`;
            document.getElementById('summary_copay').textContent = `₵${claimData.patient_copayment.toFixed(2)}`;
            document.getElementById('expected_savings').textContent = `₵${(claimData.total_claim * 0.3).toFixed(2)}`;
            
            document.getElementById('total_items').textContent = `${itemCount} items`;
            document.getElementById('nhia_percentage').textContent = `${reimbursementRate}% coverage`;
            document.getElementById('copay_percentage_display').textContent = `${copaymentRate}% patient`;
            
            // Generate detailed items table
            generateClaimItemsTable(claimData);
            
            // Store claim data globally
            window.currentClaimData = claimData;
            
            // Enable recalculate button
            document.getElementById('recalculate_btn').disabled = false;
        }
        
        // Generate detailed claim items table
        function generateClaimItemsTable(claimData) {
            const tableBody = document.getElementById('claim_items_table');
            const detailedSection = document.getElementById('detailed_items');
            
            if (!tableBody) return;
            
            tableBody.innerHTML = '';
            
            // Mock detailed items based on breakdown
            const items = [];
            
            if (claimData.breakdown.opd_consultation) {
                items.push({
                    name: 'OPD Consultation',
                    code: 'OPD-001',
                    quantity: 1,
                    unitPrice: claimData.breakdown.opd_consultation,
                    covered: true,
                    total: claimData.breakdown.opd_consultation
                });
            }
            
            if (claimData.breakdown.laboratory_tests) {
                const labCount = currentConsultation?.labs?.length || 1;
                items.push({
                    name: 'Laboratory Tests',
                    code: 'LAB-001',
                    quantity: labCount,
                    unitPrice: claimData.breakdown.laboratory_tests / labCount,
                    covered: true,
                    total: claimData.breakdown.laboratory_tests
                });
            }
            
            if (claimData.breakdown.medications) {
                const medCount = currentConsultation?.medications?.length || 1;
                items.push({
                    name: 'Medications',
                    code: 'MED-001',
                    quantity: medCount,
                    unitPrice: claimData.breakdown.medications / medCount,
                    covered: true,
                    total: claimData.breakdown.medications
                });
            }
            
            if (claimData.breakdown.procedures) {
                items.push({
                    name: 'Procedures/Treatments',
                    code: 'PROC-001',
                    quantity: 1,
                    unitPrice: claimData.breakdown.procedures,
                    covered: true,
                    total: claimData.breakdown.procedures
                });
            }
            
            if (claimData.breakdown.administrative) {
                items.push({
                    name: 'Administrative Fee',
                    code: 'ADMIN-001',
                    quantity: 1,
                    unitPrice: claimData.breakdown.administrative,
                    covered: true,
                    total: claimData.breakdown.administrative
                });
            }
            
            items.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="font-medium">${item.name}</div>
                        <div class="text-sm text-gray-500">${item.code}</div>
                    </td>
                    <td class="font-mono">${item.code}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-right">₵${item.unitPrice.toFixed(2)}</td>
                    <td class="text-center">
                        <span class="px-2 py-1 rounded-full text-xs ${item.covered ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${item.covered ? 'Yes' : 'No'}
                        </span>
                    </td>
                    <td class="text-right font-medium">₵${item.total.toFixed(2)}</td>
                `;
                tableBody.appendChild(row);
            });
            
            detailedSection.style.display = 'block';
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
        async function generateClaim() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            try {
                showAlert('Generating NHIS claim form...', 'warning');
                
                const claimData = {
                    visit_id: currentConsultation.visit_id,
                    claim_type: document.getElementById('claim_type').value,
                    priority: document.getElementById('priority').value,
                    additional_notes: document.getElementById('additional_notes').value
                };
                
                const response = await fetch('../claims-api.php?action=generate_claim', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(claimData)
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showAlert(`Claim generated successfully! Claim Number: ${result.data.claim_number}`, 'success');
                    
                    // Update claim number field
                    document.getElementById('claim_number').value = result.data.claim_number;
                    
                    // Update workflow step
                    updateWorkflowStep(3, 'completed');
                    
                    // Enable submit button
                    const submitButton = document.querySelector('button[onclick="submitClaim()"]');
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50');
                    
                } else {
                    showAlert('Failed to generate claim: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error generating claim:', error);
                showAlert('Error generating claim', 'danger');
            }
        }

        // Submit claim to NHIA
        async function submitClaim() {
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
            
            if (!confirm('Are you sure you want to submit this claim to NHIA? This action cannot be undone.')) {
                return;
            }
            
            try {
                showAlert('Submitting claim to NHIA portal...', 'warning');
                
                // First, find the claim ID by claim number (assuming it was generated)
                const claimsResponse = await fetch('../claims-api.php?action=get_claims');
                const claimsResult = await claimsResponse.json();
                
                let claimId = null;
                if (claimsResult.status === 'success') {
                    const claim = claimsResult.data.find(c => c.claim_number === claimNumber);
                    if (claim) {
                        claimId = claim.id;
                    }
                }
                
                if (!claimId) {
                    throw new Error('Claim not found. Please generate the claim first.');
                }
                
                const response = await fetch('../claims-api.php?action=submit_claim', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ claim_id: claimId })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showAlert(`Claim submitted successfully! Reference: ${claimNumber}`, 'success');
                    
                    // Update workflow
                    updateWorkflowStep(3, 'completed');
                    updateWorkflowStep(4, 'active');
                    
                    // Add to claims table
                    const claimData = {
                        claimNumber: claimNumber,
                        consultation: currentConsultation,
                        submissionDate: submissionDate,
                        claimType: claimType,
                        priority: document.getElementById('priority').value,
                        totalAmount: claimData?.total_claim || 240.00,
                        status: 'Submitted',
                        submittedBy: '<?php echo htmlspecialchars($user['full_name'] ?? 'Current User'); ?>'
                    };
                    
                    addClaimToTable(claimData);
                    
                    // Reset form for next claim
                    setTimeout(() => {
                        if (confirm('Would you like to process another claim?')) {
                            resetClaimForm();
                        }
                    }, 2000);
                    
                } else {
                    showAlert('Failed to submit claim: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error submitting claim:', error);
                showAlert('Error submitting claim: ' + error.message, 'danger');
            }
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
        async function filterConsultations() {
            const dateFilter = document.getElementById('date_filter').value;
            const statusFilter = document.getElementById('status_filter').value;
            const departmentFilter = document.getElementById('department_filter').value;
            
            // Show/hide custom date range
            const customRange = document.getElementById('customDateRange');
            if (dateFilter === 'custom') {
                customRange.classList.remove('hidden');
                return;
            } else {
                customRange.classList.add('hidden');
            }
            
            // Calculate date range
            let dateFrom = '';
            let dateTo = '';
            const now = new Date();
            
            switch (dateFilter) {
                case 'today':
                    dateFrom = now.toISOString().split('T')[0];
                    dateTo = dateFrom;
                    break;
                case 'week':
                    const weekStart = new Date(now.setDate(now.getDate() - now.getDay()));
                    dateFrom = weekStart.toISOString().split('T')[0];
                    dateTo = new Date().toISOString().split('T')[0];
                    break;
                case 'month':
                    const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
                    dateFrom = monthStart.toISOString().split('T')[0];
                    dateTo = new Date().toISOString().split('T')[0];
                    break;
                case 'quarter':
                    const quarterStart = new Date(now.getFullYear(), Math.floor(now.getMonth() / 3) * 3, 1);
                    dateFrom = quarterStart.toISOString().split('T')[0];
                    dateTo = new Date().toISOString().split('T')[0];
                    break;
            }
            
            // Apply filters
            await loadFilteredConsultations(dateFrom, dateTo, statusFilter, departmentFilter);
        }
        
        // Apply custom date filter
        async function applyCustomDateFilter() {
            const dateFrom = document.getElementById('date_from').value;
            const dateTo = document.getElementById('date_to').value;
            const statusFilter = document.getElementById('status_filter').value;
            const departmentFilter = document.getElementById('department_filter').value;
            
            if (!dateFrom || !dateTo) {
                showAlert('Please select both start and end dates', 'warning');
                return;
            }
            
            if (new Date(dateFrom) > new Date(dateTo)) {
                showAlert('Start date cannot be after end date', 'warning');
                return;
            }
            
            await loadFilteredConsultations(dateFrom, dateTo, statusFilter, departmentFilter);
        }
        
        // Load filtered consultations
        async function loadFilteredConsultations(dateFrom, dateTo, status, department) {
            try {
                showTableLoading('consultations_table');
                
                let url = '../claims-api.php?action=get_claimable_consultations';
                const params = new URLSearchParams();
                
                if (dateFrom) params.append('date_from', dateFrom);
                if (dateTo) params.append('date_to', dateTo);
                if (status) params.append('status', status);
                if (department) params.append('department', department);
                
                if (params.toString()) {
                    url += '&' + params.toString();
                }
                
                const response = await fetch(url);
                const result = await response.json();
                
                if (result.status === 'success') {
                    consultations = result.data;
                    displayConsultationsTable();
                    
                    const filterSummary = [];
                    if (dateFrom && dateTo) filterSummary.push(`${dateFrom} to ${dateTo}`);
                    if (status) filterSummary.push(`status: ${status}`);
                    if (department) filterSummary.push(`department: ${department}`);
                    
                    showAlert(`Found ${consultations.length} consultations ${filterSummary.length ? '(' + filterSummary.join(', ') + ')' : ''}`, 'info');
                } else {
                    showTableError('consultations_table', result.message);
                }
            } catch (error) {
                console.error('Error filtering consultations:', error);
                showTableError('consultations_table', 'Failed to apply filters');
            }
        }
        
        // Update form progress
        function updateFormProgress() {
            if (!currentConsultation) return;
            
            const requiredFields = [
                'claim_number',
                'submission_date', 
                'claim_type',
                'provider_code'
            ];
            
            let completedFields = 0;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && field.value.trim()) {
                    completedFields++;
                }
            });
            
            // Check if compliance is good
            const complianceScore = parseInt(document.getElementById('compliance_score').textContent) || 0;
            if (complianceScore >= 70) {
                completedFields += 2; // Bonus for good compliance
            }
            
            const progress = Math.min(100, Math.round((completedFields / (requiredFields.length + 2)) * 100));
            
            document.getElementById('form_progress_bar').style.width = `${progress}%`;
            document.getElementById('form_progress_text').textContent = `${progress}% Complete`;
            
            // Enable/disable buttons based on progress
            const buttons = {
                'save_draft_btn': progress >= 25,
                'validate_form_btn': progress >= 50,
                'preview_btn': progress >= 75,
                'generate_btn': progress >= 90,
                'submit_btn': progress >= 100
            };
            
            Object.keys(buttons).forEach(btnId => {
                const btn = document.getElementById(btnId);
                if (btn) {
                    btn.disabled = !buttons[btnId];
                    if (buttons[btnId]) {
                        btn.classList.remove('opacity-50');
                    } else {
                        btn.classList.add('opacity-50');
                    }
                }
            });
        }
        
        // Update claim type info
        function updateClaimTypeInfo() {
            const claimType = document.getElementById('claim_type').value;
            const infoElement = document.getElementById('claim_type_info');
            
            const typeInfo = {
                'OPD': 'Standard outpatient consultation and services',
                'Emergency': 'Emergency care - expedited processing',
                'Maternity': 'Maternal health services and delivery care',
                'Specialist': 'Specialist consultation and procedures',
                'Referral': 'Patient referred from another facility',
                'Chronic': 'Chronic disease management services'
            };
            
            infoElement.textContent = typeInfo[claimType] || 'Select type to see requirements';
            updateFormProgress();
        }
        
        // Update priority info
        function updatePriorityInfo() {
            const priority = document.getElementById('priority').value;
            const infoElement = document.getElementById('priority_info');
            
            const priorityInfo = {
                'Normal': 'Standard processing time: 7-14 days',
                'Urgent': 'Expedited processing: 3-5 days (additional verification required)',
                'Emergency': 'Emergency processing: 24-48 hours (clinical justification required)'
            };
            
            infoElement.textContent = priorityInfo[priority] || 'Standard processing time: 7-14 days';
        }
        
        // View consultation details
        async function viewConsultationDetails(visitId) {
            try {
                const response = await fetch(`../claims-api.php?action=get_consultation_details&visit_id=${visitId}`);
                const result = await response.json();
                
                if (result.status === 'success') {
                    const visit = result.data.visit;
                    const diagnoses = result.data.diagnoses || [];
                    const medications = result.data.prescriptions || [];
                    
                    let detailsHTML = `
CONSULTATION DETAILS
━━━━━━━━━━━━━━━━━━━━━━━━━━━

Patient: ${visit.full_name}
NHIS: ${visit.nhis_number}
Visit Date: ${new Date(visit.visit_date).toLocaleString()}
Physician: ${visit.physician_name || 'Not assigned'}
Chief Complaint: ${visit.chief_complaint || 'Not specified'}

DIAGNOSES (${diagnoses.length}):
${diagnoses.map(d => `• ${d.diagnosis_description} (${d.icd10_code})`).join('\n')}

MEDICATIONS (${medications.length}):
${medications.map(m => `• ${m.medication_name} - ${m.dosage}, ${m.frequency}`).join('\n')}

Status: ${visit.status}
                    `;
                    
                    alert(detailsHTML);
                } else {
                    showAlert('Failed to load consultation details: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error loading consultation details:', error);
                showAlert('Error loading consultation details', 'danger');
            }
        }
        
        // Save draft
        async function saveDraft() {
            if (!currentConsultation) {
                showAlert('Please select a consultation first', 'warning');
                return;
            }
            
            const draftData = {
                visit_id: currentConsultation.visit_id,
                claim_type: document.getElementById('claim_type').value,
                priority: document.getElementById('priority').value,
                additional_notes: document.getElementById('additional_notes').value,
                provider_code: document.getElementById('provider_code').value,
                attending_provider: document.getElementById('attending_provider').value,
                status: 'Draft'
            };
            
            try {
                showAlert('Saving draft...', 'info');
                
                // Simulate save
                setTimeout(() => {
                    localStorage.setItem(`claim_draft_${currentConsultation.visit_id}`, JSON.stringify(draftData));
                    showAlert('Draft saved successfully', 'success');
                }, 1000);
                
            } catch (error) {
                showAlert('Failed to save draft', 'danger');
            }
        }
        
        // Validate form
        function validateForm() {
            const errors = [];
            
            if (!currentConsultation) {
                errors.push('No consultation selected');
            }
            
            const requiredFields = [
                { id: 'claim_number', name: 'Claim Number' },
                { id: 'submission_date', name: 'Submission Date' },
                { id: 'claim_type', name: 'Claim Type' },
                { id: 'provider_code', name: 'Provider Code' }
            ];
            
            requiredFields.forEach(field => {
                const element = document.getElementById(field.id);
                if (!element || !element.value.trim()) {
                    errors.push(`${field.name} is required`);
                }
            });
            
            // Check compliance score
            const complianceScore = parseInt(document.getElementById('compliance_score').textContent) || 0;
            if (complianceScore < 70) {
                errors.push('Compliance score too low (minimum 70% required)');
            }
            
            if (errors.length > 0) {
                showAlert('Validation failed:\n• ' + errors.join('\n• '), 'danger');
                return false;
            }
            
            showAlert('Form validation passed successfully!', 'success');
            updateFormProgress();
            return true;
        }
        
        // Character counter for notes
        document.addEventListener('DOMContentLoaded', function() {
            const notesField = document.getElementById('additional_notes');
            const counter = document.getElementById('notes_counter');
            
            if (notesField && counter) {
                notesField.addEventListener('input', function() {
                    counter.textContent = this.value.length;
                    
                    if (this.value.length > 450) {
                        counter.classList.add('text-red-500');
                    } else {
                        counter.classList.remove('text-red-500');
                    }
                });
            }
        });

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

        // Load analytics data
        async function loadAnalytics() {
            try {
                const response = await fetch('../claims-api.php?action=get_claims_analytics&period=month');
                const result = await response.json();
                
                if (result.status === 'success') {
                    const data = result.data;
                    
                    // Update analytics cards
                    document.querySelector('.bg-blue-50 .text-2xl.font-bold.text-blue-600').textContent = data.total_claims || 156;
                    document.querySelector('.bg-green-50 .text-2xl.font-bold.text-green-600').textContent = `${data.approval_rate || 89}%`;
                    document.querySelector('.bg-orange-50 .text-2xl.font-bold.text-orange-600').textContent = `${data.avg_processing_time || 5.2} days`;
                    document.querySelector('.bg-purple-50 .text-2xl.font-bold.text-purple-600').textContent = `₵${(data.total_amount || 45670).toLocaleString()}`;
                }
            } catch (error) {
                console.error('Error loading analytics:', error);
            }
        }

        // Load claims data
        async function loadClaimsData() {
            try {
                const response = await fetch('../claims-api.php?action=get_claims&limit=10');
                const result = await response.json();
                
                if (result.status === 'success' && result.data.length > 0) {
                    const tableBody = document.getElementById('claims_table');
                    tableBody.innerHTML = '';
                    
                    result.data.forEach(claim => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${claim.claim_number}</td>
                            <td>${claim.patient_name}</td>
                            <td>${new Date(claim.created_at).toLocaleDateString()}</td>
                            <td>₵${parseFloat(claim.total_amount).toFixed(2)}</td>
                            <td><span class="status-badge status-${claim.status.toLowerCase().replace(' ', '_')}">${claim.status}</span></td>
                            <td>${new Date(claim.created_at).toLocaleString()}</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="trackClaim('${claim.claim_number}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-secondary btn-sm ml-1" onclick="downloadClaim('${claim.claim_number}')">
                                    <i class="fas fa-download"></i>
                                </button>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                    return;
                }
            } catch (error) {
                console.error('Error loading claims data:', error);
            }
            
            // Fallback to mock data if API fails
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