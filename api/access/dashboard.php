<?php
/**
 * Dashboard Page
 * 
 * Main dashboard for the application
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
    <title>Dashboard - Smart Claims NHIS</title>
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
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            width: 100%;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            width: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-right: 1.25rem;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-info {
            flex: 1;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
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
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px; /* Ensures table doesn't get too compressed */
        }
        
        .table th {
            background: rgba(248, 250, 252, 0.9);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .table td {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            color: var(--text-primary);
            font-size: 0.95rem;
        }
        
        .table tr:hover {
            background: rgba(248, 250, 252, 0.5);
        }
        
        .status-badge {
            padding: 0.35rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .status-pending {
            background: linear-gradient(135deg, #fff3cd, #ffeeba);
            color: #856404;
        }
        
        .status-approved {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
        }
        
        .status-rejected {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0062c3;
        }
        
        .btn-icon {
            margin-right: 0.5rem;
        }
        
        /* User menu */
        .user-menu {
            position: relative;
        }
        
        .user-button {
            display: flex;
            align-items: center;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .user-button:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            color: #666;
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
            0% {
                transform: translate(0, 0) rotate(0deg);
            }
            50% {
                transform: translate(50px, 50px) rotate(10deg);
            }
            100% {
                transform: translate(0, 0) rotate(0deg);
            }
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
            height: 4.5rem; /* Fixed height for consistency */
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

        @media (max-width: 768px) {
            .app-nav {
                display: none;
            }
            
            .mobile-nav {
                display: flex;
                justify-content: space-around;
            }

            .app-container {
                padding: 0.5rem;
                padding-bottom: 5.5rem; /* Space for mobile nav */
                max-width: 100%;
                overflow-x: hidden;
            }

            .app-header {
                padding: 0.5rem 0;
                margin-bottom: 1rem;
                flex-wrap: wrap;
            }

            .app-title {
                font-size: 1.25rem;
            }

            .app-logo {
                width: 32px;
                height: 32px;
                font-size: 1rem;
            }

            .card {
                padding: 1rem;
                margin-bottom: 1rem;
                width: 100%;
                box-sizing: border-box;
            }

            .card-title {
                font-size: 1rem;
                margin-bottom: 0.75rem;
            }

            .card-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                width: 100%;
            }
            
            .table th, .table td {
                padding: 0.75rem 1rem;
                font-size: 0.85rem;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .stat-icon {
                width: 45px;
                height: 45px;
                font-size: 1.25rem;
                margin-right: 0.75rem;
            }
            
            .stat-value {
                font-size: 1.25rem;
            }
            
            .mobile-nav-item span {
                font-size: 0.65rem;
            }
        }
        
        /* Extra small devices */
        @media (max-width: 480px) {
            .app-container {
                padding: 0.5rem;
            }
            
            .card {
                padding: 0.875rem;
            }
            
            .stat-card {
                padding: 0.875rem;
            }
            
            .stat-icon {
                width: 40px;
                height: 40px;
            }
            
            .mobile-nav-item i {
                font-size: 1.1rem;
            }
            
            .mobile-nav-item span {
                font-size: 0.6rem;
            }
        }

            .stat-card {
                padding: 1.25rem;
            }
            
            /* Ensure content doesn't get hidden behind bottom nav */
            main {
                margin-bottom: 1rem;
            }
            
            /* Adjust last card to have more bottom margin */
            .card:last-of-type {
                margin-bottom: 1.5rem;
            }
        }
        
        /* Additional breakpoint for smaller devices */
        @media (max-width: 480px) {
            .app-container {
                padding: 0.5rem;
                padding-bottom: 5rem;
            }
            
            .card {
                padding: 1rem;
                border-radius: 12px;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
                margin-right: 0.75rem;
            }
            
            .stat-value {
                font-size: 1.1rem;
            }
            
            .btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }

            .stat-icon {
                width: 48px;
                height: 48px;
                font-size: 1.5rem;
                margin-right: 1rem;
            }

            .stat-value {
                font-size: 1.25rem;
            }

            .stat-label {
                font-size: 0.8rem;
            }

            .welcome-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .welcome-header .apple-btn {
                width: 100%;
                justify-content: center;
            }

            .table-container {
                margin: 0 -1.25rem;
                width: calc(100% + 2.5rem);
                border-radius: 0;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
                scrollbar-width: thin; /* For Firefox */
            }
            
            .table-container::-webkit-scrollbar {
                height: 6px;
            }
            
            .table-container::-webkit-scrollbar-thumb {
                background-color: rgba(0, 0, 0, 0.2);
                border-radius: 3px;
            }

            .table {
                font-size: 0.875rem;
                min-width: 600px;
                table-layout: fixed; /* Helps with column widths */
            }

            .table th, .table td {
                padding: 0.75rem;
                word-break: break-word; /* Prevents text overflow */
                white-space: nowrap;
            }

            .table th:first-child, .table td:first-child {
                padding-left: 1.25rem;
            }

            .table th:last-child, .table td:last-child {
                padding-right: 1.25rem;
            }

            .status-badge {
                padding: 0.25rem 0.75rem;
                font-size: 0.75rem;
            }

            .btn-sm {
                padding: 0.4rem 0.75rem;
                font-size: 0.75rem;
            }

            .user-button span {
                display: none;
            }

            .user-button {
                padding: 0.25rem;
            }

            .user-avatar {
                margin-right: 0;
            }

            .form-control {
                font-size: 0.875rem;
                padding: 0.75rem;
            }

            .space-y-4 > div {
                padding: 0.75rem;
            }

            .space-y-4 span {
                font-size: 0.875rem;
            }

            .space-y-4 .text-lg {
                font-size: 1rem;
            }
        }

        @media (max-width: 380px) {
            .app-container {
                padding: 0.5rem;
                padding-bottom: 5rem;
            }

            .card {
                padding: 1rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
                margin-right: 0.75rem;
            }

            .stat-value {
                font-size: 1.1rem;
            }

            .stat-label {
                font-size: 0.75rem;
            }

            .mobile-nav-item {
                font-size: 0.65rem;
                padding: 0.35rem 0.2rem;
            }

            .mobile-nav-item i {
                font-size: 1.1rem;
            }
            
            /* Adjust spacing for mobile nav */
            .mobile-nav {
                padding: 0.5rem 0.2rem;
                height: 4.2rem;
            }
            
            /* Ensure content has enough bottom padding */
            .app-container {
                padding-bottom: 5rem;
            }
            
            /* Make buttons more touch-friendly */
            .btn {
                min-height: 44px; /* Apple's recommended minimum touch target size */
            }
        }

        /* Apple-style Button */
        .apple-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #0071e3, #42a1ec);
            color: white;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 113, 227, 0.3);
            border: none;
            cursor: pointer;
        }

        .apple-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.4);
            background: linear-gradient(135deg, #0077ed, #42a1ec);
        }

        .apple-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 113, 227, 0.3);
        }

        .apple-btn i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        .welcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
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
            <a href="dashboard.php" class="nav-item active">
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
            <a href="claims-processing.php" class="nav-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Claims Processing</span>
            </a>
            <a href="reports.php" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
            <?php if (isSuperAdmin()): ?>
            <a href="hospital-management.php" class="nav-item">
                <i class="fas fa-hospital"></i>
                <span>Hospital Management</span>
            </a>
            <?php endif; ?>
        </nav>
        
        <!-- Main Content -->
        <main>
            <!-- Welcome Card -->
            <div class="card">
                <div class="welcome-header">
                    <div>
                        <h2 class="card-title text-2xl font-bold mb-2">Welcome to Smart Claims NHIS, <?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?>!</h2>
                        <p class="text-secondary text-lg">Streamlining NHIS claims processing for Ghanaian health facilities</p>
                        <p class="text-sm text-gray-600 mt-2">Efficient • Accurate • NHIA Compliant</p>
                    </div>
                    <a href="client-registration.php" class="apple-btn">
                        <i class="fas fa-user-medical"></i>
                        Register New Client
                    </a>
                </div>
            </div>
            
            <!-- NHIS Claims Workflow -->
            <div class="card">
                <h2 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-sitemap mr-2"></i>
                    NHIS Claims Workflow
                </h2>
                <div class="card-grid">
                    <a href="client-registration.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #0071e3, #5ac8fa);">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Client Registration</div>
                            <div class="stat-label">Capture patient demographics & NHIS details</div>
                        </div>
                    </a>
                    
                    <a href="service-requisition.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #34c759, #30d158);">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Service Requisition</div>
                            <div class="stat-label">OPD, Lab, Pharmacy with auto-tariffs</div>
                        </div>
                    </a>
                    
                    <a href="vital-signs.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ff3b30, #ff6b6b);">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Vital Signs</div>
                            <div class="stat-label">Temperature, BP, pulse & more</div>
                        </div>
                    </a>
                    
                    <a href="diagnosis-medication.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #af52de, #bf5af2);">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Diagnosis & Medication</div>
                            <div class="stat-label">ICD-10 codes linked to prescriptions</div>
                        </div>
                    </a>
                    
                    <a href="claims-processing.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ff9500, #ffcc00);">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Claims Processing</div>
                            <div class="stat-label">Generate NHIS-compliant forms</div>
                        </div>
                    </a>
                    
                    <a href="reports.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #007aff, #5ac8fa);">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Reports & Analytics</div>
                            <div class="stat-label">Performance metrics & insights</div>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- Department Dashboards -->
            <?php if (!isSuperAdmin()): // Only show departments for hospital staff ?>
            <div class="card">
                <h2 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-building mr-2"></i>
                    Department Dashboards
                </h2>
                <div class="card-grid">
                    <?php if (in_array($role, ['hospital_admin', 'department_head', 'admin', 'doctor', 'nurse', 'receptionist'])): ?>
                    <a href="opd-dashboard.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #34d399);">
                            <i class="fas fa-hospital-user"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">OPD</div>
                            <div class="stat-label">Outpatient Department</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($role, ['hospital_admin', 'department_head', 'admin', 'lab_technician', 'doctor'])): ?>
                    <a href="lab-dashboard.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #059669, #10b981);">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Laboratory</div>
                            <div class="stat-label">Lab Tests & Results</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($role, ['hospital_admin', 'department_head', 'admin', 'pharmacist', 'doctor'])): ?>
                    <a href="pharmacy-dashboard.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Pharmacy</div>
                            <div class="stat-label">Medications & Dispensing</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($role, ['hospital_admin', 'department_head', 'admin', 'claims_officer', 'finance_officer'])): ?>
                    <a href="claims-dashboard.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #4f46e5, #6366f1);">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Claims Processing</div>
                            <div class="stat-label">NHIS Claims Management</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($role, ['hospital_admin', 'department_head', 'admin', 'finance_officer', 'cashier'])): ?>
                    <a href="finance-dashboard.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #059669, #10b981);">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Finance</div>
                            <div class="stat-label">Financial Management</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($role, ['hospital_admin', 'department_head', 'admin', 'records_officer', 'doctor', 'nurse'])): ?>
                    <a href="records-dashboard.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Records</div>
                            <div class="stat-label">Medical Records Management</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($role, ['hospital_admin', 'department_head', 'admin', 'radiologist'])): ?>
                    <a href="#" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #dc2626, #ef4444);">
                            <i class="fas fa-x-ray"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Radiology</div>
                            <div class="stat-label">Imaging & Diagnostics</div>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($role, ['hospital_admin', 'department_head', 'admin'])): ?>
                    <a href="#" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ea580c, #f97316);">
                            <i class="fas fa-ambulance"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Emergency</div>
                            <div class="stat-label">Emergency Department</div>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- User Management (Hospital Admin) -->
            <?php if (in_array($role, ['hospital_admin', 'admin', 'superadmin'])): ?>
            <div class="card">
                <h2 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-users-cog mr-2"></i>
                    User Management
                </h2>
                <div class="card-grid">
                    <a href="user-management.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #60a5fa);">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Create Users</div>
                            <div class="stat-label">Add new staff & assign departments</div>
                        </div>
                    </a>
                    
                    <a href="user-management.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #34d399);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Manage Staff</div>
                            <div class="stat-label">Edit roles & permissions</div>
                        </div>
                    </a>
                    
                    <a href="#" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Reports</div>
                            <div class="stat-label">System & performance reports</div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Hospital Management (Superadmin Only) -->
            <?php if (isSuperAdmin()): ?>
            <div class="card">
                <h2 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-hospital mr-2"></i>
                    Hospital Management
                </h2>
                <div class="card-grid">
                    <a href="hospital-management.php" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #dc2626, #ef4444);">
                            <i class="fas fa-hospital"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">Hospital Registration</div>
                            <div class="stat-label">Approve & Manage Hospitals</div>
                        </div>
                    </a>
                    
                    <a href="#" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #4f46e5, #6366f1);">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">System Users</div>
                            <div class="stat-label">Manage System Users</div>
                        </div>
                    </a>
                    
                    <a href="#" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #059669, #10b981);">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">System Reports</div>
                            <div class="stat-label">National Health Analytics</div>
                        </div>
                    </a>
                    
                    <a href="#" class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-xl">System Settings</div>
                            <div class="stat-label">Configure System Parameters</div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- NHIS Performance Metrics -->
            <div class="card">
                <h2 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-chart-line mr-2"></i>
                    NHIS Performance Metrics
                </h2>
                <div class="card-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #0071e3, #5ac8fa);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="total-patients-stat">--</div>
                            <div class="stat-label">Registered NHIS Clients</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #34c759, #30d158);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="processing-time-stat">--</div>
                            <div class="stat-label">Avg. Processing Time (hrs)</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ff9500, #ffcc00);">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="pending-claims-stat">--</div>
                            <div class="stat-label">Pending Claims (NHIA)</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #af52de, #bf5af2);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="approval-rate-stat">--</div>
                            <div class="stat-label">Approval Rate (%)</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #007aff, #5ac8fa);">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="total-tariff-stat">--</div>
                            <div class="stat-label">Total Tariffs (₵)</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ff3b30, #ff6b6b);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="rejected-claims-stat">--</div>
                            <div class="stat-label">Rejected Claims</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent NHIS Activity -->
            <div class="card-grid">
                <!-- Recent Client Registrations -->
                <div class="card">
                    <h2 class="card-title text-xl font-bold mb-4">
                        <i class="fas fa-user-plus mr-2"></i>
                        Recent Client Registrations
                    </h2>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Client Name</th>
                                    <th>NHIS Number</th>
                                    <th>Policy Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="recent-patients-table">
                                <tr>
                                    <td colspan="5" class="text-center py-4">Loading recent clients...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Recent NHIS Claims -->
                <div class="card">
                    <h2 class="card-title text-xl font-bold mb-4">
                        <i class="fas fa-file-medical mr-2"></i>
                        Recent NHIS Claims
                    </h2>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Claim No.</th>
                                    <th>Client</th>
                                    <th>Service Type</th>
                                    <th>Tariff (₵)</th>
                                    <th>NHIA Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="recent-claims-table">
                                <tr>
                                    <td colspan="6" class="text-center py-4">Loading recent claims...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Department Integration Status -->
            <div class="card">
                <h2 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-network-wired mr-2"></i>
                    Department Integration Status
                </h2>
                <div class="card-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #34c759, #30d158);">
                            <i class="fas fa-clinic-medical"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="opd-visits-stat">--</div>
                            <div class="stat-label">OPD Visits Today</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #007aff, #5ac8fa);">
                            <i class="fas fa-vials"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="lab-tests-stat">--</div>
                            <div class="stat-label">Lab Tests Processed</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ff9500, #ffcc00);">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="pharmacy-dispensed-stat">--</div>
                            <div class="stat-label">Medications Dispensed</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- NHIS Tools & Features -->
            <div class="card-grid">
                <!-- NHIS Client Search -->
                <div class="card">
                    <h2 class="card-title text-xl font-bold mb-4">
                        <i class="fas fa-search mr-2"></i>
                        NHIS Client Search
                    </h2>
                    <div class="relative">
                        <input type="text" class="form-control w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" placeholder="Search by name or NHIS number...">
                        <button class="btn btn-primary absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="mt-3 text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Search registered NHIS clients with real-time verification
                    </div>
                </div>
                
                <!-- Claims Processing Summary -->
                <div class="card">
                    <h2 class="card-title text-xl font-bold mb-4">
                        <i class="fas fa-clipboard-check mr-2"></i>
                        Claims Processing Summary
                    </h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg">
                            <span class="text-secondary font-medium">
                                <i class="fas fa-file-alt mr-2"></i>
                                Total Claims Submitted
                            </span>
                            <span class="font-bold text-lg" id="total-claims-summary">--</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg">
                            <span class="text-secondary font-medium">
                                <i class="fas fa-clock mr-2"></i>
                                Awaiting NHIA Approval
                            </span>
                            <span class="font-bold text-lg" id="pending-approval-summary">--</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gradient-to-r from-green-50 to-green-100 rounded-lg">
                            <span class="text-secondary font-medium">
                                <i class="fas fa-check-circle mr-2"></i>
                                Approved This Month
                            </span>
                            <span class="font-bold text-lg" id="approved-month-summary">--</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg">
                            <span class="text-secondary font-medium">
                                <i class="fas fa-money-bill-wave mr-2"></i>
                                Reimbursement Amount
                            </span>
                            <span class="font-bold text-lg" id="total-amount-summary">--</span>
                        </div>
                    </div>
                </div>
                
                <!-- System Benefits Tracker -->
                <div class="card">
                    <h2 class="card-title text-xl font-bold mb-4">
                        <i class="fas fa-trophy mr-2"></i>
                        System Benefits Tracker
                    </h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-gradient-to-r from-green-50 to-green-100 rounded-lg">
                            <span class="text-secondary font-medium">
                                <i class="fas fa-tachometer-alt mr-2"></i>
                                Processing Efficiency
                            </span>
                            <span class="font-bold text-lg text-green-600">↑ 85%</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg">
                            <span class="text-secondary font-medium">
                                <i class="fas fa-bullseye mr-2"></i>
                                Accuracy Rate
                            </span>
                            <span class="font-bold text-lg text-blue-600">97.2%</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg">
                            <span class="text-secondary font-medium">
                                <i class="fas fa-dollar-sign mr-2"></i>
                                Cost Savings
                            </span>
                            <span class="font-bold text-lg text-purple-600">₵45,000</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg">
                            <span class="text-secondary font-medium">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Time Reduction
                            </span>
                            <span class="font-bold text-lg text-orange-600">Weeks → Hours</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Mobile Navigation -->
        <div class="mobile-nav">
            <a href="dashboard.php" class="mobile-nav-item active">
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
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch dashboard data
            fetchDashboardData();
            
            // Fetch recent patients
            fetchRecentPatients();
            
            // Fetch recent claims
            fetchRecentClaims();
            
            // Setup patient search
            setupPatientSearch();
        });
        
        // Fetch dashboard data from API
        function fetchDashboardData() {
            fetch('../dashboard.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        updateDashboardStats(data.data);
                    } else {
                        console.error('Error fetching dashboard data:', data.message);
                        // Set default values if data fetch fails
                        setDefaultDashboardValues();
                    }
                })
                .catch(error => {
                    console.error('Error fetching dashboard data:', error);
                    // Set default values if data fetch fails
                    setDefaultDashboardValues();
                });
        }
        
        // Set default values for dashboard if API fails
        function setDefaultDashboardValues() {
            document.getElementById('total-patients-stat').textContent = '0';
            document.getElementById('processing-time-stat').textContent = '0';
            document.getElementById('pending-claims-stat').textContent = '0';
            document.getElementById('approval-rate-stat').textContent = '0';
            document.getElementById('total-tariff-stat').textContent = '₵0';
            document.getElementById('rejected-claims-stat').textContent = '0';
            document.getElementById('opd-visits-stat').textContent = '0';
            document.getElementById('lab-tests-stat').textContent = '0';
            document.getElementById('pharmacy-dispensed-stat').textContent = '0';
            document.getElementById('total-claims-summary').textContent = '0';
            document.getElementById('pending-approval-summary').textContent = '0';
            document.getElementById('approved-month-summary').textContent = '0';
            document.getElementById('total-amount-summary').textContent = '₵0';
        }
        
        // Update dashboard statistics
        function updateDashboardStats(data) {
            try {
                // Update NHIS client count
                document.getElementById('total-patients-stat').textContent = 
                    data && data.patients && data.patients.total ? data.patients.total : 0;
                
                // Update processing time (calculate average)
                const processingTime = data && data.claims && data.claims.avg_processing_time ? 
                    data.claims.avg_processing_time : 0;
                document.getElementById('processing-time-stat').textContent = processingTime;
                
                // Update pending claims count
                const pendingClaims = data && data.claims && data.claims.by_status && data.claims.by_status.Submitted ? 
                    data.claims.by_status.Submitted.count : 0;
                document.getElementById('pending-claims-stat').textContent = pendingClaims;
                
                // Calculate approval rate
                const approvedClaims = data && data.claims && data.claims.by_status && data.claims.by_status.Approved ? 
                    data.claims.by_status.Approved.count : 0;
                const rejectedClaims = data && data.claims && data.claims.by_status && data.claims.by_status.Rejected ? 
                    data.claims.by_status.Rejected.count : 0;
                const totalProcessed = approvedClaims + rejectedClaims;
                const approvalRate = totalProcessed > 0 ? Math.round((approvedClaims / totalProcessed) * 100) : 0;
                document.getElementById('approval-rate-stat').textContent = approvalRate;
                
                // Update total tariffs
                const totalTariff = data && data.claims && data.claims.total_amount ? 
                    data.claims.total_amount.toLocaleString() : '0';
                document.getElementById('total-tariff-stat').textContent = totalTariff;
                
                // Update rejected claims
                document.getElementById('rejected-claims-stat').textContent = rejectedClaims;
                
                // Update department integration stats
                const opdVisits = data && data.departments && data.departments.opd ? data.departments.opd.today : 0;
                document.getElementById('opd-visits-stat').textContent = opdVisits;
                
                const labTests = data && data.departments && data.departments.lab ? data.departments.lab.processed : 0;
                document.getElementById('lab-tests-stat').textContent = labTests;
                
                const pharmacyDispensed = data && data.departments && data.departments.pharmacy ? data.departments.pharmacy.dispensed : 0;
                document.getElementById('pharmacy-dispensed-stat').textContent = pharmacyDispensed;
                
                // Update claims summary
                const totalClaims = data && data.claims && data.claims.by_status ? 
                    Object.values(data.claims.by_status).reduce((sum, status) => sum + (status.count || 0), 0) : 0;
                document.getElementById('total-claims-summary').textContent = totalClaims;
                document.getElementById('pending-approval-summary').textContent = pendingClaims;
                
                const monthlyApproved = data && data.claims && data.claims.this_month ? data.claims.this_month.count : 0;
                document.getElementById('approved-month-summary').textContent = monthlyApproved;
                
                const totalAmount = data && data.claims && data.claims.total_amount ? 
                    '₵' + data.claims.total_amount.toLocaleString() : '₵0';
                document.getElementById('total-amount-summary').textContent = totalAmount;
            } catch (error) {
                console.error('Error updating dashboard stats:', error);
                setDefaultDashboardValues();
            }
        }
        
        // Fetch recent patients
        function fetchRecentPatients() {
            fetch('../patients.php?limit=5')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        updateRecentPatientsTable(data.data.patients);
                    } else {
                        console.error('Error fetching recent patients:', data.message);
                        document.getElementById('recent-patients-table').innerHTML = 
                            '<tr><td colspan="5" class="text-center py-4">Failed to load recent patients</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching recent patients:', error);
                    document.getElementById('recent-patients-table').innerHTML = 
                        '<tr><td colspan="5" class="text-center py-4">Failed to load recent patients. Please try refreshing the page.</td></tr>';
                });
        }
        
        // Update recent patients table
        function updateRecentPatientsTable(patients) {
            const tableBody = document.getElementById('recent-patients-table');
            
            if (!patients || patients.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4">No recent patients found</td></tr>';
                return;
            }
            
            let html = '';
            
            patients.forEach(patient => {
                const date = new Date(patient.created_at).toLocaleDateString();
                
                html += `
                    <tr>
                        <td>${date}</td>
                        <td>${patient.first_name} ${patient.last_name}</td>
                        <td>${patient.nhis_number || 'N/A'}</td>
                        <td><span class="status-badge status-approved">Active</span></td>
                        <td>
                            <a href="patient.php?id=${patient.id}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                `;
            });
            
            tableBody.innerHTML = html;
        }
        
        // Fetch recent claims
        function fetchRecentClaims() {
            fetch('claims.php?limit=5')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        updateRecentClaimsTable(data.data.claims);
                    } else {
                        console.error('Error fetching recent claims:', data.message);
                        document.getElementById('recent-claims-table').innerHTML = 
                            '<tr><td colspan="5" class="text-center py-4">Failed to load recent claims</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching recent claims:', error);
                    document.getElementById('recent-claims-table').innerHTML = 
                        '<tr><td colspan="5" class="text-center py-4">Failed to load recent claims. Please try refreshing the page.</td></tr>';
                });
        }
        
        // Update recent claims table
        function updateRecentClaimsTable(claims) {
            const tableBody = document.getElementById('recent-claims-table');
            
            if (!claims || claims.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No recent claims found</td></tr>';
                return;
            }
            
            let html = '';
            
            claims.forEach(claim => {
                let statusClass = 'status-pending';
                
                if (claim.status === 'Approved' || claim.status === 'Paid') {
                    statusClass = 'status-approved';
                } else if (claim.status === 'Rejected') {
                    statusClass = 'status-rejected';
                }
                
                // Determine service type icon
                let serviceIcon = 'fas fa-clinic-medical';
                if (claim.service_type && claim.service_type.toLowerCase().includes('lab')) {
                    serviceIcon = 'fas fa-vials';
                } else if (claim.service_type && claim.service_type.toLowerCase().includes('pharmacy')) {
                    serviceIcon = 'fas fa-pills';
                }
                
                html += `
                    <tr>
                        <td>${claim.claim_number}</td>
                        <td>${claim.patient_name}</td>
                        <td>
                            <i class="${serviceIcon} mr-1"></i>
                            ${claim.service_type || 'OPD'}
                        </td>
                        <td>₵${parseFloat(claim.total_amount).toLocaleString()}</td>
                        <td><span class="status-badge ${statusClass}">${claim.status}</span></td>
                        <td>
                            <a href="claim.php?id=${claim.id}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                `;
            });
            
            tableBody.innerHTML = html;
        }
        
        // Setup NHIS client search
        function setupPatientSearch() {
            const searchInput = document.querySelector('input[placeholder="Search by name or NHIS number..."]');
            const searchButton = searchInput.nextElementSibling;
            
            searchButton.addEventListener('click', function() {
                const searchTerm = searchInput.value.trim();
                if (searchTerm.length > 0) {
                    window.location.href = `client-search.php?search=${encodeURIComponent(searchTerm)}`;
                }
            });
            
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = searchInput.value.trim();
                    if (searchTerm.length > 0) {
                        window.location.href = `client-search.php?search=${encodeURIComponent(searchTerm)}`;
                    }
                }
            });
        }
        
        // User dropdown menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuButton = document.getElementById('userMenuButton');
            const userDropdown = document.getElementById('userDropdown');
            
            // Toggle dropdown when clicking the user menu button
            userMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>