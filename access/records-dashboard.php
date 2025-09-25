<?php
/**
 * Records Department Dashboard
 * 
 * Dashboard for records officers and medical records management staff
 */

// Include secure authentication middleware
require_once __DIR__ . '/secure_auth.php';

// Check if user has permission to access Records
$allowed_roles = ['hospital_admin', 'department_head', 'admin', 'records_officer', 'doctor', 'nurse'];
if (!in_array($role, $allowed_roles)) {
    header('Location: unauthorized.php');
    exit();
}

// Mock data for demonstration - will be replaced with database queries
$records_stats = [
    'total_patients' => 2847,
    'new_registrations_today' => 23,
    'active_files' => 1892,
    'archived_files' => 955,
    'pending_updates' => 15,
    'compliance_score' => 98.5
];

$recent_registrations = [
    [
        'id' => 1,
        'patient_number' => 'PAT2024156',
        'name' => 'Sarah Johnson',
        'nhis_number' => 'NHIS789123',
        'age' => 34,
        'gender' => 'Female',
        'registration_time' => '10:30 AM',
        'registered_by' => 'Jane Doe',
        'status' => 'Complete'
    ],
    [
        'id' => 2,
        'patient_number' => 'PAT2024157',
        'name' => 'Michael Brown',
        'nhis_number' => 'NHIS456789',
        'age' => 28,
        'gender' => 'Male',
        'registration_time' => '11:15 AM',
        'registered_by' => 'John Smith',
        'status' => 'Pending Verification'
    ],
    [
        'id' => 3,
        'patient_number' => 'PAT2024158',
        'name' => 'Grace Osei',
        'nhis_number' => 'NHIS321654',
        'age' => 45,
        'gender' => 'Female',
        'registration_time' => '12:00 PM',
        'registered_by' => 'Mary Wilson',
        'status' => 'Complete'
    ]
];

$pending_updates = [
    ['patient' => 'John Doe', 'type' => 'Address Update', 'requested_by' => 'Patient', 'priority' => 'Normal'],
    ['patient' => 'Jane Smith', 'type' => 'NHIS Number Update', 'requested_by' => 'Dr. Brown', 'priority' => 'High'],
    ['patient' => 'Robert Wilson', 'type' => 'Emergency Contact', 'requested_by' => 'Nurse Lisa', 'priority' => 'Normal'],
    ['patient' => 'Mary Johnson', 'type' => 'Medical History', 'requested_by' => 'Dr. Davis', 'priority' => 'High']
];

$file_requests = [
    ['patient' => 'David Lee', 'patient_number' => 'PAT2024100', 'requested_by' => 'Dr. Smith', 'department' => 'OPD', 'time' => '2 hours ago'],
    ['patient' => 'Lisa White', 'patient_number' => 'PAT2024089', 'requested_by' => 'Lab Tech', 'department' => 'Laboratory', 'time' => '3 hours ago'],
    ['patient' => 'James Brown', 'patient_number' => 'PAT2024076', 'requested_by' => 'Pharmacist', 'department' => 'Pharmacy', 'time' => '4 hours ago']
];

$compliance_alerts = [
    ['message' => '5 patient files require mandatory updates', 'type' => 'warning', 'due_date' => '2024-01-20'],
    ['message' => 'Monthly archive backup completed successfully', 'type' => 'success', 'due_date' => '2024-01-15'],
    ['message' => '2 files exceed maximum retention period', 'type' => 'error', 'due_date' => '2024-01-18']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records Dashboard - Smart Claims NHIS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .status-complete { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-incomplete { background: #fee2e2; color: #991b1b; }
        .priority-high { background: #fee2e2; color: #991b1b; }
        .priority-normal { background: #e6f3ff; color: #0066cc; }
        .compliance-success { background: #d1fae5; border-left: 4px solid #10b981; }
        .compliance-warning { background: #fef3c7; border-left: 4px solid #f59e0b; }
        .compliance-error { background: #fee2e2; border-left: 4px solid #ef4444; }
        .search-highlight { background-color: #fef3c7; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-folder-open text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-xl font-semibold text-gray-900">Records Management Dashboard</h1>
                        <p class="text-sm text-gray-500"><?php echo date('l, F j, Y'); ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-user-circle mr-1"></i>
                        <?php echo htmlspecialchars($user['full_name']); ?>
                        <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                            <?php echo ucwords(str_replace('_', ' ', $role)); ?>
                        </span>
                    </div>
                    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-1"></i> Main Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Compliance Alerts -->
        <?php if (count($compliance_alerts) > 0): ?>
        <div class="mb-6 space-y-2">
            <?php foreach ($compliance_alerts as $alert): ?>
            <div class="compliance-<?php echo $alert['type']; ?> p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-<?php echo $alert['type'] === 'success' ? 'check-circle' : ($alert['type'] === 'warning' ? 'exclamation-triangle' : 'times-circle'); ?> 
                           text-<?php echo $alert['type'] === 'success' ? 'green' : ($alert['type'] === 'warning' ? 'yellow' : 'red'); ?>-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm">
                            <strong>Records Alert:</strong> <?php echo $alert['message']; ?>
                            <?php if (isset($alert['due_date'])): ?>
                                <span class="text-xs ml-2">(Due: <?php echo date('M j, Y', strtotime($alert['due_date'])); ?>)</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                    <?php if (hasPermission('register_patients')): ?>
                    <a href="client-registration.php" class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <i class="fas fa-user-plus text-blue-600 mr-3"></i>
                        <span class="text-sm font-medium text-blue-700">New Patient</span>
                    </a>
                    <?php endif; ?>
                    
                    <a href="#" class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        <i class="fas fa-search text-green-600 mr-3"></i>
                        <span class="text-sm font-medium text-green-700">Find Patient</span>
                    </a>
                    
                    <?php if (hasPermission('edit_patient_info')): ?>
                    <a href="#" class="flex items-center p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                        <i class="fas fa-edit text-purple-600 mr-3"></i>
                        <span class="text-sm font-medium text-purple-700">Update Record</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('archive_records')): ?>
                    <a href="#" class="flex items-center p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                        <i class="fas fa-archive text-orange-600 mr-3"></i>
                        <span class="text-sm font-medium text-orange-700">Archive Files</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('generate_reports')): ?>
                    <a href="#" class="flex items-center p-3 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                        <i class="fas fa-chart-bar text-indigo-600 mr-3"></i>
                        <span class="text-sm font-medium text-indigo-700">Reports</span>
                    </a>
                    <?php endif; ?>
                    
                    <a href="#" class="flex items-center p-3 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                        <i class="fas fa-shield-alt text-red-600 mr-3"></i>
                        <span class="text-sm font-medium text-red-700">Compliance</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mb-8">
            <div class="stat-card bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Patients</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($records_stats['total_patients']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-plus text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">New Today</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $records_stats['new_registrations_today']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-folder text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Files</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($records_stats['active_files']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-archive text-gray-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Archived</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($records_stats['archived_files']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Updates</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $records_stats['pending_updates']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Compliance</p>
                            <p class="text-2xl font-semibold text-green-600"><?php echo $records_stats['compliance_score']; ?>%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Registrations -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-user-plus mr-2 text-blue-600"></i>
                            Recent Patient Registrations
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered By</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($recent_registrations as $registration): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($registration['name']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($registration['patient_number']); ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($registration['nhis_number']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo $registration['age']; ?> years, <?php echo $registration['gender']; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($registration['registered_by']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo $registration['registration_time']; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full status-<?php echo strtolower(str_replace(' ', '-', $registration['status'])); ?>">
                                                <?php echo $registration['status']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button class="text-blue-600 hover:text-blue-900" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if (hasPermission('edit_patient_info')): ?>
                                                <button class="text-green-600 hover:text-green-900" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php endif; ?>
                                                <button class="text-purple-600 hover:text-purple-900" title="Print Card">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Pending Updates -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-edit mr-2 text-orange-600"></i>
                            Pending Updates
                        </h3>
                        <div class="space-y-3">
                            <?php foreach ($pending_updates as $update): ?>
                            <div class="p-3 border rounded-lg">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($update['patient']); ?></div>
                                <div class="text-sm text-gray-600"><?php echo htmlspecialchars($update['type']); ?></div>
                                <div class="text-xs text-gray-500 mb-2">Requested by: <?php echo htmlspecialchars($update['requested_by']); ?></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs px-2 py-1 rounded-full priority-<?php echo strtolower($update['priority']); ?>">
                                        <?php echo $update['priority']; ?>
                                    </span>
                                    <div class="flex space-x-1">
                                        <button class="text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700">
                                            Update
                                        </button>
                                        <button class="text-xs bg-gray-600 text-white px-2 py-1 rounded hover:bg-gray-700">
                                            View
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- File Requests -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-file-alt mr-2 text-green-600"></i>
                            File Requests
                        </h3>
                        <div class="space-y-3">
                            <?php foreach ($file_requests as $request): ?>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-folder text-green-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($request['patient']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($request['patient_number']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo htmlspecialchars($request['requested_by']); ?> - <?php echo htmlspecialchars($request['department']); ?>
                                        <span class="ml-2"><?php echo $request['time']; ?></span>
                                    </p>
                                </div>
                                <button class="text-sm bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">
                                    Retrieve
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Search -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-search mr-2 text-blue-600"></i>
                            Quick Patient Search
                        </h3>
                        <div class="space-y-3">
                            <input type="text" id="patient-search" placeholder="Search by name, number, or NHIS..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="grid grid-cols-2 gap-2">
                                <button class="text-sm bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">
                                    <i class="fas fa-search mr-1"></i> Search
                                </button>
                                <button class="text-sm bg-gray-600 text-white px-3 py-2 rounded hover:bg-gray-700">
                                    <i class="fas fa-qrcode mr-1"></i> QR Scan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Statistics -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-chart-pie mr-2 text-purple-600"></i>
                            Record Statistics
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">This Week</span>
                                <span class="text-sm font-medium text-gray-900">156 new patients</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">This Month</span>
                                <span class="text-sm font-medium text-gray-900">734 new patients</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Average per day</span>
                                <span class="text-sm font-medium text-gray-900">23 patients</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Data accuracy</span>
                                <span class="text-sm font-medium text-green-600">99.2%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CSRF Token for AJAX requests -->
    <script>
        const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
        
        // Patient search functionality
        document.getElementById('patient-search').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                performSearch(this.value);
            }
        });
        
        function performSearch(query) {
            if (query.length >= 3) {
                console.log('Searching for:', query);
                // Implement search functionality
                highlightSearchResults(query);
            }
        }
        
        function highlightSearchResults(query) {
            // Highlight matching results in the table
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query.toLowerCase())) {
                    row.classList.add('search-highlight');
                } else {
                    row.classList.remove('search-highlight');
                }
            });
        }
        
        // Auto-refresh for pending updates
        setInterval(function() {
            console.log('Checking for pending updates...');
            // Implement auto-refresh logic
        }, 60000); // Check every minute
        
        // File tracking system
        function trackFileMovement(patientId, location) {
            console.log('Tracking file movement for patient:', patientId, 'to location:', location);
            // Implement file tracking logic
        }
        
        // Compliance monitoring
        function checkCompliance() {
            console.log('Running compliance check...');
            // Implement compliance monitoring
        }
        
        // Run compliance check every hour
        setInterval(checkCompliance, 3600000);
    </script>
</body>
</html>