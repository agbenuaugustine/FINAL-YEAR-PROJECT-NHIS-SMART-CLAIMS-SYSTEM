<?php
/**
 * OPD (Outpatient Department) Dashboard
 * 
 * Dashboard for OPD staff - Stage 1 of NHIS Claims Workflow
 * Primary responsibility: Client Registration & Initial Consultation
 */

// Include secure authentication middleware
require_once __DIR__ . '/secure_auth.php';

// Check if user has access to OPD dashboard
if (!in_array($role, ['hospital_admin', 'admin', 'superadmin', 'doctor', 'nurse', 'receptionist'])) {
    header('Location: unauthorized.php');
    exit();
}

// Mock data for demonstration - replace with database queries
$today_stats = [
    'total_patients' => 45,
    'waiting_patients' => 12,
    'in_progress' => 8,
    'completed' => 25,
    'new_registrations' => 18
];

$recent_patients = [
    [
        'id' => 1,
        'patient_number' => 'PAT001',
        'name' => 'John Doe',
        'nhis_number' => 'NHIS12345',
        'visit_time' => '08:30 AM',
        'status' => 'Waiting',
        'priority' => 'Normal',
        'complaint' => 'Fever and headache'
    ],
    [
        'id' => 2,
        'patient_number' => 'PAT002', 
        'name' => 'Jane Smith',
        'nhis_number' => 'NHIS67890',
        'visit_time' => '09:15 AM',
        'status' => 'In Progress',
        'priority' => 'Urgent',
        'complaint' => 'Chest pain'
    ],
    [
        'id' => 3,
        'patient_number' => 'PAT003',
        'name' => 'Robert Johnson',
        'nhis_number' => 'NHIS11111',
        'visit_time' => '10:00 AM',
        'status' => 'Completed',
        'priority' => 'Normal',
        'complaint' => 'Routine check-up'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPD Dashboard - Smart Claims NHIS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-waiting { background-color: #fef3c7; color: #92400e; }
        .status-in-progress { background-color: #dbeafe; color: #1e40af; }
        .status-completed { background-color: #d1fae5; color: #065f46; }
        .status-urgent { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>

<body class="bg-gray-50 font-sans">
    <div class="container mx-auto p-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-user-md text-blue-600 mr-2"></i>
                        OPD Dashboard
                    </h1>
                    <p class="text-gray-600 mt-1">Outpatient Department - Client Registration & Initial Consultation</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">Stage 1</span>
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($user['full_name']); ?></span>
                    <a href="dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Main Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-sm stat-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-800"><?php echo $today_stats['total_patients']; ?></p>
                        <p class="text-gray-600">Total Patients</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm stat-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-800"><?php echo $today_stats['waiting_patients']; ?></p>
                        <p class="text-gray-600">Waiting</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm stat-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-play text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-800"><?php echo $today_stats['in_progress']; ?></p>
                        <p class="text-gray-600">In Progress</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm stat-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-800"><?php echo $today_stats['completed']; ?></p>
                        <p class="text-gray-600">Completed</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm stat-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                        <i class="fas fa-user-plus text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-800"><?php echo $today_stats['new_registrations']; ?></p>
                        <p class="text-gray-600">New Registrations</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="client-registration.php" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow stat-card">
                <div class="text-center">
                    <div class="p-4 bg-blue-100 text-blue-600 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-user-plus text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Register New Client</h3>
                    <p class="text-gray-600 text-sm">Register new patients and verify NHIS status</p>
                </div>
            </a>

            <a href="vital-signs.php" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow stat-card">
                <div class="text-center">
                    <div class="p-4 bg-green-100 text-green-600 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-heartbeat text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Vital Signs</h3>
                    <p class="text-gray-600 text-sm">Record patient vital signs and basic assessment</p>
                </div>
            </a>

            <a href="service-requisition.php" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow stat-card">
                <div class="text-center">
                    <div class="p-4 bg-purple-100 text-purple-600 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Service Requisition</h3>
                    <p class="text-gray-600 text-sm">Request lab tests and other services</p>
                </div>
            </a>
        </div>

        <!-- Recent Patients -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-users text-blue-600 mr-2"></i>
                Recent Patients
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="pb-3 font-semibold text-gray-700">Patient #</th>
                            <th class="pb-3 font-semibold text-gray-700">Name</th>
                            <th class="pb-3 font-semibold text-gray-700">NHIS Number</th>
                            <th class="pb-3 font-semibold text-gray-700">Visit Time</th>
                            <th class="pb-3 font-semibold text-gray-700">Status</th>
                            <th class="pb-3 font-semibold text-gray-700">Priority</th>
                            <th class="pb-3 font-semibold text-gray-700">Complaint</th>
                            <th class="pb-3 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_patients as $patient): ?>
                        <tr class="border-b last:border-b-0">
                            <td class="py-3 font-mono"><?php echo htmlspecialchars($patient['patient_number']); ?></td>
                            <td class="py-3 font-semibold"><?php echo htmlspecialchars($patient['name']); ?></td>
                            <td class="py-3 font-mono text-sm"><?php echo htmlspecialchars($patient['nhis_number']); ?></td>
                            <td class="py-3"><?php echo htmlspecialchars($patient['visit_time']); ?></td>
                            <td class="py-3">
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $patient['status'])); ?>">
                                    <?php echo $patient['status']; ?>
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="status-badge <?php echo $patient['priority'] == 'Urgent' ? 'status-urgent' : 'status-waiting'; ?>">
                                    <?php echo $patient['priority']; ?>
                                </span>
                            </td>
                            <td class="py-3 text-sm"><?php echo htmlspecialchars($patient['complaint']); ?></td>
                            <td class="py-3">
                                <div class="flex space-x-2">
                                    <a href="patient-details.php?id=<?php echo $patient['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-800" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($patient['status'] == 'Waiting'): ?>
                                    <a href="vital-signs.php?patient_id=<?php echo $patient['id']; ?>" 
                                       class="text-green-600 hover:text-green-800" title="Start Consultation">
                                        <i class="fas fa-play"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Workflow Guide -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-route text-blue-600 mr-2"></i>
                OPD Workflow Guide
            </h2>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                <h3 class="font-semibold text-blue-800 mb-2">Stage 1: OPD Responsibilities</h3>
                <ol class="list-decimal list-inside text-blue-700 space-y-2">
                    <li><strong>Client Registration:</strong> Register new patients, verify NHIS status, and capture demographics</li>
                    <li><strong>Triage:</strong> Assess patient priority and direct to appropriate care level</li>
                    <li><strong>Initial Consultation:</strong> Doctor examines patient and determines next steps</li>
                    <li><strong>Hand-off:</strong> Complete OPD stage and send patient to Service Requisition (Stage 2)</li>
                </ol>
                <div class="mt-3 p-3 bg-white rounded border border-blue-200">
                    <p class="text-sm text-blue-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Next Stage:</strong> After OPD completion, patients proceed to Service Requisition where lab tests, 
                        pharmacy orders, and other services are requested based on clinical findings.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
