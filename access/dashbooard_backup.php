<?php
/**
 * Dashboard Page
 * 
 * Main dashboard for the application
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: /smartclaimsCL/index.php');
    exit();
}

$user = $_SESSION['user'];
$role = $_SESSION['user']['role'] ?? 'user';
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
            font-size: 0.7rem;
            transition: all 0.2s ease;
            width: 20%;
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
            
            <div class="user-menu">
                <div class="user-button">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="hidden md:inline"><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></span>
                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                </div>
            </div>
        </header>
        
        <!-- Navigation -->
        <nav class="app-nav">
            <a href="dashboard.php" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="patient-registration.php" class="nav-item">
                <i class="fas fa-user-plus"></i>
                <span>Patients</span>
            </a>
            <a href="visits.php" class="nav-item">
                <i class="fas fa-clipboard-list"></i>
                <span>Visits</span>
            </a>
            <a href="vital-signs.php" class="nav-item">
                <i class="fas fa-heartbeat"></i>
                <span>Vitals</span>
            </a>
            <a href="diagnosis.php" class="nav-item">
                <i class="fas fa-stethoscope"></i>
                <span>Diagnosis</span>
            </a>
            <a href="claims.php" class="nav-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Claims</span>
            </a>
            <a href="settings.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>
        
        <!-- Main Content -->
        <main>
            <!-- Welcome Card -->
            <div class="card">
                <div class="welcome-header">
                    <div>
                        <h2 class="card-title text-2xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?>!</h2>
                        <p class="text-secondary text-lg">Here's your comprehensive dashboard overview.</p>
                    </div>
                    <a href="patient-registration.php" class="apple-btn">
                        <i class="fas fa-plus"></i>
                        Add New Patient
                    </a>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card-grid">
                <a href="patient-registration.php" class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #0071e3, #5ac8fa);">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value text-xl">Register Patient</div>
                        <div class="stat-label">Add new patient to the system</div>
                    </div>
                </a>
                
                <a href="new-visit.php" class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #34c759, #30d158);">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value text-xl">New Visit</div>
                        <div class="stat-label">Record patient visit</div>
                    </div>
                </a>
                
                <a href="submit-claim.php" class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ff9500, #ffcc00);">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value text-xl">Submit Claim</div>
                        <div class="stat-label">Process NHIS claim</div>
                    </div>
                </a>
            </div>
            
            <!-- Statistics Overview -->
            <div class="card">
                <h2 class="card-title text-xl font-bold mb-4">Statistics Overview</h2>
                <div class="card-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #0071e3, #5ac8fa);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="total-patients-stat">--</div>
                            <div class="stat-label">Total Patients</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #34c759, #30d158);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="active-visits-stat">--</div>
                            <div class="stat-label">Active Visits</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #ff9500, #ffcc00);">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="pending-claims-stat">--</div>
                            <div class="stat-label">Pending Claims</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #af52de, #bf5af2);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-2xl" id="approved-claims-stat">--</div>
                            <div class="stat-label">Approved Claims</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity and Claims -->
            <div class="card-grid">
                <!-- Recent Patient Registrations -->
                <div class="card">
                    <h2 class="card-title text-xl font-bold mb-4">Recent Patient Registrations</h2>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient Name</th>
                                    <th>NHIS Number</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="recent-patients-table">
                                <tr>
                                    <td colspan="5" class="text-center py-4">Loading recent patients...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Recent Claims -->
                <div class="card">
                    <h2 class="card-title text-xl font-bold mb-4">Recent Claims</h2>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Claim ID</th>
                                    <th>Patient</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="recent-claims-table">
                                <tr>
                                    <td colspan="5" class="text-center py-4">Loading recent claims...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Additional Features -->
            <div class="card-grid">
                <!-- Patient Search -->
                <div class="card">
                    <h2 class="card-title text-xl font-bold mb-4">Quick Patient Search</h2>
                    <div class="relative">
                        <input type="text" class="form-control w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" placeholder="Search by name or NHIS number...">
                        <button class="btn btn-primary absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Claims Summary -->
                <div class="card">
                    <h2 class="card-title text-xl font-bold mb-4">Claims Summary</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-secondary font-medium">Total Claims</span>
                            <span class="font-bold text-lg" id="total-claims-summary">--</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-secondary font-medium">Pending Approval</span>
                            <span class="font-bold text-lg" id="pending-approval-summary">--</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-secondary font-medium">Approved This Month</span>
                            <span class="font-bold text-lg" id="approved-month-summary">--</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-secondary font-medium">Total Amount</span>
                            <span class="font-bold text-lg" id="total-amount-summary">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Mobile Navigation -->
        <div class="mobile-nav">
            <a href="dashboard.php" class="mobile-nav-item active">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="patient-registration.php" class="mobile-nav-item">
                <i class="fas fa-user-plus"></i>
                <span>Patients</span>
            </a>
            <a href="visits.php" class="mobile-nav-item">
                <i class="fas fa-clipboard-list"></i>
                <span>Visits</span>
            </a>
            <a href="claims.php" class="mobile-nav-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Claims</span>
            </a>
            <a href="settings.php" class="mobile-nav-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
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
            fetch('/smartclaimsCL/api/dashboard.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        updateDashboardStats(data.data);
                    } else {
                        console.error('Error fetching dashboard data:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching dashboard data:', error);
                });
        }
        
        // Update dashboard statistics
        function updateDashboardStats(data) {
            // Update patient count
            document.getElementById('total-patients-stat').textContent = data.patients.total;
            
            // Update visits count
            const activeVisits = data.visits && data.visits.active ? data.visits.active : 0;
            document.getElementById('active-visits-stat').textContent = activeVisits;
            
            // Update claims counts
            const pendingClaims = data.claims && data.claims.by_status && data.claims.by_status.Submitted ? 
                data.claims.by_status.Submitted.count : 0;
            document.getElementById('pending-claims-stat').textContent = pendingClaims;
            
            const approvedClaims = data.claims && data.claims.by_status && data.claims.by_status.Approved ? 
                data.claims.by_status.Approved.count : 0;
            document.getElementById('approved-claims-stat').textContent = approvedClaims;
            
            // Update claims summary
            const totalClaims = Object.values(data.claims.by_status || {}).reduce((sum, status) => sum + status.count, 0);
            document.getElementById('total-claims-summary').textContent = totalClaims;
            document.getElementById('pending-approval-summary').textContent = pendingClaims;
            
            const monthlyApproved = data.claims.this_month ? data.claims.this_month.count : 0;
            document.getElementById('approved-month-summary').textContent = monthlyApproved;
            
            const totalAmount = data.claims.total_amount ? 
                '₵' + data.claims.total_amount.toLocaleString() : '₵0';
            document.getElementById('total-amount-summary').textContent = totalAmount;
        }
        
        // Fetch recent patients
        function fetchRecentPatients() {
            fetch('/smartclaimsCL/api/patients.php?limit=5')
                .then(response => response.json())
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
                        '<tr><td colspan="5" class="text-center py-4">Failed to load recent patients</td></tr>';
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
            fetch('/smartclaimsCL/api/claims.php?limit=5')
                .then(response => response.json())
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
                        '<tr><td colspan="5" class="text-center py-4">Failed to load recent claims</td></tr>';
                });
        }
        
        // Update recent claims table
        function updateRecentClaimsTable(claims) {
            const tableBody = document.getElementById('recent-claims-table');
            
            if (!claims || claims.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4">No recent claims found</td></tr>';
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
                
                html += `
                    <tr>
                        <td>${claim.claim_number}</td>
                        <td>${claim.patient_name}</td>
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
        
        // Setup patient search
        function setupPatientSearch() {
            const searchInput = document.querySelector('input[placeholder="Search by name or NHIS number..."]');
            const searchButton = searchInput.nextElementSibling;
            
            searchButton.addEventListener('click', function() {
                const searchTerm = searchInput.value.trim();
                if (searchTerm.length > 0) {
                    window.location.href = `patient-search.php?search=${encodeURIComponent(searchTerm)}`;
                }
            });
            
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = searchInput.value.trim();
                    if (searchTerm.length > 0) {
                        window.location.href = `patient-search.php?search=${encodeURIComponent(searchTerm)}`;
                    }
                }
            });
        }
    </script>
</body>
</html>