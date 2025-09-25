<?php
/**
 * Department Dashboard Template
 * 
 * Standardized dashboard template for all departments
 * Implements professional workflow logic where departments work in sequence
 */

if (!defined('DEPARTMENT_CONFIG')) {
    die('Direct access not allowed. Use this template through department dashboards.');
}

// Include the department controller for access control
require_once __DIR__ . '/../department_controller.php';

$dept_config = DEPARTMENT_CONFIG;

// Verify user has access to this department
// Hospital admin can access all departments
if (!in_array($role, ['hospital_admin', 'admin', 'superadmin']) && !canAccessDepartment($dept_config['code'])) {
    header('Location: unauthorized.php?dept=' . $dept_config['code']);
    exit();
}

// Workflow stages definition
$workflow_stages = [
    1 => [
        'name' => 'Client Registration',
        'department' => 'OPD',
        'functions' => ['Register new clients', 'Verify NHIS status', 'Initial triage'],
        'next' => 'Service Requisition'
    ],
    2 => [
        'name' => 'Service Requisition', 
        'department' => 'OPD/Clinical',
        'functions' => ['Request services', 'Lab orders', 'Pharmacy requests'],
        'next' => 'Vital Signs'
    ],
    3 => [
        'name' => 'Vital Signs',
        'department' => 'Nursing',
        'functions' => ['Record vital signs', 'Basic assessment', 'Patient preparation'],
        'next' => 'Diagnosis & Medication'
    ],
    4 => [
        'name' => 'Diagnosis & Medication',
        'department' => 'Clinical',
        'functions' => ['Clinical diagnosis', 'Prescribe medication', 'Treatment plans'],
        'next' => 'Claims Processing'
    ],
    5 => [
        'name' => 'Claims Processing',
        'department' => 'Claims',
        'functions' => ['Generate claims', 'NHIA submission', 'Claims tracking'],
        'next' => 'Completed'
    ]
];

// Additional role-based checks are handled by department_controller.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $dept_config['name']; ?> - Smart Claims NHIS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Import exact styles from dashboard.php */
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
        }
        
        body {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--light-bg) 0%, #ffffff 100%);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
        }
        
        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }
        
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.05;
            animation: float 6s ease-in-out infinite;
        }
        
        .bg-shape-1 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            top: -150px;
            right: -150px;
            animation-delay: 0s;
        }
        
        .bg-shape-2 {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, var(--success-color), var(--warning-color));
            bottom: -100px;
            left: -100px;
            animation-delay: 3s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(50px, 50px) rotate(10deg); }
        }
        
        /* Container */
        .app-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        /* Header */
        .app-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem 0;
        }
        
        .app-title {
            display: flex;
            align-items: center;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .app-logo {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            width: 40px;
            height: 40px;
            border-radius: 50%;
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
            text-decoration: none;
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
            text-decoration: none;
            color: inherit;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            color: inherit;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .stat-info {
            flex: 1;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        /* User menu */
        .user-menu {
            position: relative;
        }
        
        .user-button {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            color: var(--text-primary);
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .user-button:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-1px);
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
            font-size: 0.875rem;
        }
        
        /* Workflow indicator */
        .workflow-indicator {
            background: linear-gradient(135deg, var(--nhis-primary), var(--nhis-secondary));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Department badge */
        .dept-badge {
            background: linear-gradient(135deg, var(--success-color), var(--nhis-accent));
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            text-decoration: none;
        }

        .apple-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.4);
            background: linear-gradient(135deg, #0077ed, #42a1ec);
            color: white;
            text-decoration: none;
        }

        .apple-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 113, 227, 0.3);
        }

        .apple-btn i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .app-container {
                padding: 0.5rem;
            }
            
            .app-nav {
                flex-wrap: wrap;
            }
            
            .nav-item {
                flex: 1 1 auto;
                min-width: fit-content;
            }
            
            .card {
                padding: 1rem;
            }
            
            .card-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Table styles */
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table th {
            background: var(--light-bg);
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .table tr:hover {
            background: rgba(0, 113, 227, 0.02);
        }
        
        /* Status badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-waiting {
            background: rgba(255, 149, 0, 0.1);
            color: var(--warning-color);
        }
        
        .status-in-progress {
            background: rgba(0, 113, 227, 0.1);
            color: var(--primary-color);
        }
        
        .status-completed {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success-color);
        }
        
        .status-urgent {
            background: rgba(255, 59, 48, 0.1);
            color: var(--danger-color);
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
                    <i class="<?php echo $dept_config['icon']; ?>"></i>
                </div>
                Smart Claims - <?php echo $dept_config['name']; ?>
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
            <?php 
            $navigation = getDepartmentNavigation();
            foreach ($navigation as $key => $nav_item):
                if ($nav_item['accessible']):
                    $is_current_page = (basename($_SERVER['PHP_SELF']) == basename($nav_item['url']));
            ?>
            <a href="<?php echo $nav_item['url']; ?>" class="nav-item <?php echo $is_current_page ? 'active' : ''; ?>">
                <i class="<?php echo $nav_item['icon']; ?>"></i>
                <span><?php echo $nav_item['label']; ?></span>
            </a>
            <?php 
                endif;
            endforeach; 
            ?>
        </nav>
        
        <!-- Main Content -->
        <main>
            <!-- Department Welcome Card -->
            <div class="card">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="dept-badge"><?php echo $dept_config['code']; ?></span>
                            <span class="workflow-indicator">
                                <i class="fas fa-sitemap"></i>
                                Stage <?php echo $dept_config['workflow_stage']; ?> of 5
                            </span>
                        </div>
                        <h2 class="text-2xl font-bold mb-2"><?php echo $dept_config['name']; ?></h2>
                        <p class="text-lg text-gray-600 mb-2"><?php echo $dept_config['description']; ?></p>
                        <p class="text-sm text-gray-500">Role: <?php echo ucwords(str_replace('_', ' ', $role)); ?> | Hospital Admin oversees all workflow stages</p>
                    </div>
                    <?php if (!empty($dept_config['primary_action'])): ?>
                    <a href="<?php echo $dept_config['primary_action']['url']; ?>" class="apple-btn">
                        <i class="<?php echo $dept_config['primary_action']['icon']; ?>"></i>
                        <?php echo $dept_config['primary_action']['label']; ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Workflow Status -->
            <div class="card">
                <h2 class="card-title text-xl font-bold mb-4">
                    <i class="fas fa-sitemap mr-2"></i>
                    NHIS Claims Workflow Status
                </h2>
                <div class="card-grid">
                    <?php foreach ($workflow_stages as $stage_num => $stage): ?>
                    <div class="stat-card <?php echo ($stage_num == $dept_config['workflow_stage']) ? 'border-2 border-blue-500' : ''; ?>">
                        <div class="stat-icon" style="background: linear-gradient(135deg, 
                            <?php echo ($stage_num <= $dept_config['workflow_stage']) ? '#34c759, #30d158' : '#d1d5db, #9ca3af'; ?>);">
                            <i class="fas fa-<?php 
                                echo $stage_num == 1 ? 'user-plus' : 
                                    ($stage_num == 2 ? 'clipboard-list' : 
                                    ($stage_num == 3 ? 'heartbeat' : 
                                    ($stage_num == 4 ? 'stethoscope' : 'file-invoice-dollar')));
                            ?>"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value text-lg"><?php echo $stage['name']; ?></div>
                            <div class="stat-label"><?php echo $stage['department']; ?></div>
                            <div class="text-xs text-gray-500 mt-1">
                                <?php echo implode(' • ', $stage['functions']); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Department-specific content will be inserted here -->
            <div id="department-content">
                <?php
                // This is where department-specific content will be loaded
                // Each department dashboard will define its own content
                if (isset($GLOBALS['department_content'])) {
                    echo $GLOBALS['department_content'];
                }
                ?>
            </div>

        </main>
    </div>

    <script>
        // User dropdown functionality (same as main dashboard)
        document.getElementById('userMenuButton').addEventListener('click', function() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            
            if (!userMenu.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Department-specific JavaScript can be added here
        console.log('Department Dashboard loaded: <?php echo $dept_config['name']; ?>');
    </script>
</body>
</html>