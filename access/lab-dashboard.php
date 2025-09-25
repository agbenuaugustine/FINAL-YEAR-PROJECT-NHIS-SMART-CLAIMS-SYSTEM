<?php
/**
 * Laboratory Department Dashboard
 * 
 * Dashboard for laboratory staff - Stage 2-3 of NHIS Claims Workflow
 * Primary responsibility: Lab Orders & Test Results
 */

// Include secure authentication middleware
require_once __DIR__ . '/secure_auth.php';

// Define department configuration
define('DEPARTMENT_CONFIG', [
    'name' => 'Laboratory Dashboard',
    'code' => 'LAB',
    'icon' => 'fas fa-flask',
    'description' => 'Laboratory Services - Lab Orders, Test Processing & Results',
    'workflow_stage' => 2, // Can handle stages 2-3
    'primary_functions' => ['lab_orders', 'test_processing', 'results_entry'],
    'next_stage' => 'diagnosis_medication',
    'allowed_roles' => ['hospital_admin', 'admin', 'lab_technician', 'doctor'],
    'primary_action' => [
        'label' => 'Process Lab Orders',
        'url' => 'lab-orders.php',
        'icon' => 'fas fa-vial'
    ]
]);

// Mock data for demonstration - replace with database queries
$lab_stats = [
    'pending_tests' => 24,
    'in_progress' => 8,
    'completed_today' => 45,
    'critical_results' => 3,
    'overdue_tests' => 2
];

$pending_lab_orders = [
    [
        'id' => 1,
        'patient_name' => 'John Doe',
        'patient_number' => 'PAT001',
        'test_name' => 'Complete Blood Count',
        'ordered_by' => 'Dr. Smith',
        'order_time' => '08:30 AM',
        'priority' => 'Normal',
        'specimen_status' => 'Collected'
    ],
    [
        'id' => 2,
        'patient_name' => 'Jane Wilson',
        'patient_number' => 'PAT002',
        'test_name' => 'Blood Glucose',
        'ordered_by' => 'Dr. Johnson',
        'order_time' => '09:15 AM',
        'priority' => 'Urgent',
        'specimen_status' => 'Pending'
    ],
    [
        'id' => 3,
        'patient_name' => 'Robert Brown',
        'patient_number' => 'PAT003',
        'test_name' => 'Malaria Test',
        'ordered_by' => 'Dr. Davis',
        'order_time' => '10:00 AM',
        'priority' => 'Critical',
        'specimen_status' => 'Collected'
    ]
];

$critical_results = [
    [
        'patient' => 'Mary Johnson',
        'test' => 'Blood Glucose',
        'result' => '15.2 mmol/L',
        'normal_range' => '3.9-6.1 mmol/L',
        'status' => 'Critical High'
    ],
    [
        'patient' => 'David Wilson',
        'test' => 'Hemoglobin',
        'result' => '6.2 g/dL',
        'normal_range' => '13.5-17.5 g/dL',
        'status' => 'Critical Low'
    ]
];

// Capture department-specific content
ob_start();
?>

<!-- Lab Department Statistics -->
<div class="card">
    <h2 class="card-title text-xl font-bold mb-4">
        <i class="fas fa-chart-line mr-2"></i>
        Laboratory Statistics
    </h2>
    <div class="card-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ff9500, #ff6b35);">
                <i class="fas fa-vial"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $lab_stats['pending_tests']; ?></div>
                <div class="stat-label">Pending Tests</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #0071e3, #5ac8fa);">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $lab_stats['in_progress']; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #34c759, #30d158);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $lab_stats['completed_today']; ?></div>
                <div class="stat-label">Completed Today</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ff3b30, #ff6b6b);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $lab_stats['critical_results']; ?></div>
                <div class="stat-label">Critical Results</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #af52de, #bf5af2);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $lab_stats['overdue_tests']; ?></div>
                <div class="stat-label">Overdue Tests</div>
            </div>
        </div>
    </div>
</div>

<!-- Lab Primary Functions -->
<div class="card">
    <h2 class="card-title text-xl font-bold mb-4">
        <i class="fas fa-tasks mr-2"></i>
        Laboratory Functions (Stage 2-3)
    </h2>
    <div class="card-grid">
        <a href="lab-orders.php" class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #0071e3, #5ac8fa);">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value text-lg">Lab Orders</div>
                <div class="stat-label">Receive & process lab orders from doctors</div>
                <div class="text-xs text-green-600 mt-1">
                    <i class="fas fa-arrow-left"></i> From: Service Requisition
                </div>
            </div>
        </a>
        
        <a href="specimen-collection.php" class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #34c759, #30d158);">
                <i class="fas fa-syringe"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value text-lg">Specimen Collection</div>
                <div class="stat-label">Collect & label patient specimens</div>
                <div class="text-xs text-blue-600 mt-1">
                    <i class="fas fa-arrow-right"></i> Process Tests
                </div>
            </div>
        </a>
        
        <a href="test-processing.php" class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #af52de, #bf5af2);">
                <i class="fas fa-microscope"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value text-lg">Test Processing</div>
                <div class="stat-label">Run tests & analyze specimens</div>
                <div class="text-xs text-blue-600 mt-1">
                    <i class="fas fa-arrow-right"></i> Enter Results
                </div>
            </div>
        </a>
        
        <a href="results-entry.php" class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ff9500, #ff6b35);">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value text-lg">Results Entry</div>
                <div class="stat-label">Enter test results & flag critical values</div>
                <div class="text-xs text-green-600 mt-1">
                    <i class="fas fa-arrow-right"></i> Next: Diagnosis & Medication
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Pending Lab Orders -->
<div class="card">
    <h2 class="card-title text-xl font-bold mb-4">
        <i class="fas fa-flask mr-2"></i>
        Pending Lab Orders
    </h2>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Patient #</th>
                    <th>Test Name</th>
                    <th>Ordered By</th>
                    <th>Order Time</th>
                    <th>Priority</th>
                    <th>Specimen Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_lab_orders as $order): ?>
                <tr>
                    <td class="font-semibold"><?php echo htmlspecialchars($order['patient_name']); ?></td>
                    <td class="font-mono"><?php echo htmlspecialchars($order['patient_number']); ?></td>
                    <td><?php echo htmlspecialchars($order['test_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['ordered_by']); ?></td>
                    <td><?php echo htmlspecialchars($order['order_time']); ?></td>
                    <td>
                        <span class="status-badge <?php 
                            echo $order['priority'] == 'Critical' ? 'status-urgent' : 
                                ($order['priority'] == 'Urgent' ? 'status-in-progress' : 'status-waiting');
                        ?>">
                            <?php echo $order['priority']; ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $order['specimen_status'] == 'Collected' ? 'status-completed' : 'status-waiting'; ?>">
                            <?php echo $order['specimen_status']; ?>
                        </span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="lab-order-details.php?id=<?php echo $order['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($order['specimen_status'] == 'Pending'): ?>
                            <a href="collect-specimen.php?id=<?php echo $order['id']; ?>" 
                               class="text-green-600 hover:text-green-800" title="Collect Specimen">
                                <i class="fas fa-syringe"></i>
                            </a>
                            <?php else: ?>
                            <a href="process-test.php?id=<?php echo $order['id']; ?>" 
                               class="text-purple-600 hover:text-purple-800" title="Process Test">
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

<!-- Critical Results -->
<?php if (!empty($critical_results)): ?>
<div class="card border-red-200 bg-red-50">
    <h2 class="card-title text-xl font-bold mb-4 text-red-700">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        Critical Results - Immediate Action Required
    </h2>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Test</th>
                    <th>Result</th>
                    <th>Normal Range</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($critical_results as $result): ?>
                <tr class="bg-red-100 border-red-200">
                    <td class="font-semibold"><?php echo htmlspecialchars($result['patient']); ?></td>
                    <td><?php echo htmlspecialchars($result['test']); ?></td>
                    <td class="font-bold text-red-700"><?php echo htmlspecialchars($result['result']); ?></td>
                    <td class="text-sm text-gray-600"><?php echo htmlspecialchars($result['normal_range']); ?></td>
                    <td>
                        <span class="status-badge status-urgent">
                            <?php echo $result['status']; ?>
                        </span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="notify-doctor.php?patient=<?php echo urlencode($result['patient']); ?>" 
                               class="text-red-600 hover:text-red-800" title="Notify Doctor">
                                <i class="fas fa-phone"></i>
                            </a>
                            <a href="print-result.php?patient=<?php echo urlencode($result['patient']); ?>" 
                               class="text-blue-600 hover:text-blue-800" title="Print Result">
                                <i class="fas fa-print"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Workflow Guide for Laboratory -->
<div class="card">
    <h2 class="card-title text-xl font-bold mb-4">
        <i class="fas fa-route mr-2"></i>
        Laboratory Workflow Guide
    </h2>
    <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-r-lg">
        <h3 class="font-semibold text-purple-800 mb-2">Stage 2-3: Laboratory Responsibilities</h3>
        <ol class="list-decimal list-inside text-purple-700 space-y-2">
            <li><strong>Receive Lab Orders:</strong> Process lab orders from Service Requisition (Stage 2)</li>
            <li><strong>Specimen Collection:</strong> Collect and properly label patient specimens</li>
            <li><strong>Test Processing:</strong> Run laboratory tests using appropriate procedures</li>
            <li><strong>Results Entry:</strong> Enter test results and flag critical values</li>
            <li><strong>Hand-off:</strong> Send results to Diagnosis & Medication (Stage 4)</li>
        </ol>
        <div class="mt-3 p-3 bg-white rounded border border-purple-200">
            <p class="text-sm text-purple-600">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Critical Results:</strong> Immediately notify the ordering physician for any critical values. 
                Do not wait for routine reporting processes.
            </p>
        </div>
    </div>
</div>

<?php
// Store the captured content in global variable for template
$GLOBALS['department_content'] = ob_get_clean();

// Include the standardized template
require_once __DIR__ . '/includes/department_dashboard_template.php';
?>