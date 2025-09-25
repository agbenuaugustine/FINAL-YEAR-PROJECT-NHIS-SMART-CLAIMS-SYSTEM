<?php
// Include secure authentication middleware
require_once __DIR__ . '/secure_auth.php';

/**
 * Claims Page
 * 
 * Displays and manages insurance claims
 */

// Include authentication
require_once __DIR__ . '/auth.php';

// Get current user
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claims Management - Smart Claims</title>
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
        }
        
        /* App container */
        .app-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem;
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
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
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
        
        /* Page header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .page-actions {
            display: flex;
            gap: 0.75rem;
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
        
        .btn-outline {
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        
        .btn-outline:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .btn-icon {
            margin-right: 0.5rem;
        }
        
        /* Table */
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table th {
            font-weight: 600;
            color: var(--text-secondary);
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .table tr:last-child td {
            border-bottom: none;
        }
        
        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: rgba(255, 149, 0, 0.1);
            color: var(--warning-color);
        }
        
        .status-approved {
            background-color: rgba(52, 199, 89, 0.1);
            color: var(--success-color);
        }
        
        .status-rejected {
            background-color: rgba(255, 59, 48, 0.1);
            color: var(--danger-color);
        }
        
        /* Search and filters */
        .search-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .search-input {
            flex: 1;
            position: relative;
        }
        
        .search-input input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            color: var(--text-primary);
            background-color: var(--card-bg);
            transition: all 0.2s ease;
        }
        
        .search-input input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.2);
        }
        
        .search-input i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }
        
        .filter-select {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            color: var(--text-primary);
            background-color: var(--card-bg);
            transition: all 0.2s ease;
            min-width: 150px;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.2);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 1.5rem;
        }
        
        .pagination-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            margin: 0 0.25rem;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .pagination-item:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .pagination-item.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Mobile styles */
        @media (max-width: 768px) {
            .app-nav {
                display: none;
            }
            
            .mobile-nav {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background-color: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border-top: 1px solid var(--border-color);
                padding: 0.75rem 0.5rem;
                z-index: 100;
                justify-content: space-around;
            }
            
            .mobile-nav-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                font-size: 0.75rem;
                color: var(--text-secondary);
                padding: 0.5rem;
                border-radius: 8px;
            }
            
            .mobile-nav-item.active {
                color: var(--primary-color);
            }
            
            .mobile-nav-item i {
                font-size: 1.25rem;
                margin-bottom: 0.25rem;
            }
            
            .app-container {
                padding-bottom: 5rem;
            }
            
            .search-container {
                flex-direction: column;
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
            background: linear-gradient(135deg, #0071e3, #34c759);
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
            <a href="dashboard.php" class="nav-item">
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
            <a href="claims.php" class="nav-item active">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Claims</span>
            </a>
            <a href="settings.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>
        
        <!-- Page Header -->
        <div class="page-header">
            <h2 class="page-title">Claims Management</h2>
            
            <div class="page-actions">
                <a href="new-claim.php" class="btn btn-primary">
                    <i class="fas fa-plus btn-icon"></i>
                    New Claim
                </a>
            </div>
        </div>
        
        <!-- Search and Filters -->
        <div class="search-container">
            <div class="search-input">
                <i class="fas fa-search"></i>
                <input type="text" id="search-input" placeholder="Search by claim number, patient name or NHIS number...">
            </div>
            
            <select class="filter-select" id="status-filter">
                <option value="">All Statuses</option>
                <option value="Draft">Draft</option>
                <option value="Submitted">Submitted</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
                <option value="Paid">Paid</option>
            </select>
        </div>
        
        <!-- Claims Table -->
        <div class="card">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Claim Number</th>
                            <th>Patient</th>
                            <th>NHIS Number</th>
                            <th>Visit Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="claims-table">
                        <tr>
                            <td colspan="7" class="text-center py-4">Loading claims...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="pagination" id="pagination">
                <!-- Pagination will be generated by JavaScript -->
            </div>
        </div>
    </div>
    
    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <a href="dashboard.php" class="mobile-nav-item">
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
        <a href="claims.php" class="mobile-nav-item active">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Claims</span>
        </a>
        <a href="settings.php" class="mobile-nav-item">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize variables
            let currentPage = 1;
            let totalPages = 1;
            let searchTerm = '';
            let statusFilter = '';
            
            // Load claims on page load
            loadClaims();
            
            // Add event listeners
            document.getElementById('search-input').addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    searchTerm = this.value.trim();
                    currentPage = 1;
                    loadClaims();
                }
            });
            
            document.getElementById('status-filter').addEventListener('change', function() {
                statusFilter = this.value;
                currentPage = 1;
                loadClaims();
            });
            
            // Function to load claims
            function loadClaims() {
                const tableBody = document.getElementById('claims-table');
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Loading claims...</td></tr>';
                
                // Build query parameters
                let params = `page=${currentPage}`;
                if (searchTerm) params += `&search=${encodeURIComponent(searchTerm)}`;
                if (statusFilter) params += `&status=${encodeURIComponent(statusFilter)}`;
                
                // Fetch claims from API
                fetch(`/smartclaimsCL/api/claims.php?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            displayClaims(data.data.claims);
                            updatePagination(data.data.pagination);
                        } else {
                            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Failed to load claims</td></tr>';
                            console.error('Error loading claims:', data.message);
                        }
                    })
                    .catch(error => {
                        tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Failed to load claims</td></tr>';
                        console.error('Error loading claims:', error);
                    });
            }
            
            // Function to display claims
            function displayClaims(claims) {
                const tableBody = document.getElementById('claims-table');
                
                if (!claims || claims.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No claims found</td></tr>';
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
                    
                    const visitDate = new Date(claim.visit_date).toLocaleDateString();
                    
                    html += `
                        <tr>
                            <td>${claim.claim_number}</td>
                            <td>${claim.patient_name}</td>
                            <td>${claim.patient_nhis || 'N/A'}</td>
                            <td>${visitDate}</td>
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
            
            // Function to update pagination
            function updatePagination(pagination) {
                const paginationContainer = document.getElementById('pagination');
                totalPages = pagination.total_pages;
                
                if (totalPages <= 1) {
                    paginationContainer.innerHTML = '';
                    return;
                }
                
                let html = '';
                
                // Previous button
                html += `
                    <div class="pagination-item ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}" 
                         ${currentPage > 1 ? 'onclick="changePage(' + (currentPage - 1) + ')"' : ''}>
                        <i class="fas fa-chevron-left"></i>
                    </div>
                `;
                
                // Page numbers
                const maxPages = 5;
                let startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
                let endPage = Math.min(totalPages, startPage + maxPages - 1);
                
                if (endPage - startPage + 1 < maxPages) {
                    startPage = Math.max(1, endPage - maxPages + 1);
                }
                
                for (let i = startPage; i <= endPage; i++) {
                    html += `
                        <div class="pagination-item ${i === currentPage ? 'active' : ''}" 
                             onclick="changePage(${i})">
                            ${i}
                        </div>
                    `;
                }
                
                // Next button
                html += `
                    <div class="pagination-item ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''}" 
                         ${currentPage < totalPages ? 'onclick="changePage(' + (currentPage + 1) + ')"' : ''}>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                `;
                
                paginationContainer.innerHTML = html;
            }
            
            // Make changePage function global
            window.changePage = function(page) {
                currentPage = page;
                loadClaims();
                window.scrollTo(0, 0);
            };
        });
    </script>
</body>
</html>