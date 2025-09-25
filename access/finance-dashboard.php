<?php
/**
 * Finance Department Dashboard
 * 
 * Dashboard for finance officers and financial management staff
 */

// Include secure authentication middleware
require_once __DIR__ . '/secure_auth.php';

// Check if user has permission to access Finance
$allowed_roles = ['hospital_admin', 'department_head', 'admin', 'finance_officer', 'cashier'];
if (!in_array($role, $allowed_roles)) {
    header('Location: unauthorized.php');
    exit();
}

// Mock data for demonstration - will be replaced with database queries
$finance_stats = [
    'monthly_revenue' => 145230.50,
    'pending_payments' => 23450.75,
    'outstanding_claims' => 67890.25,
    'cash_on_hand' => 15670.50,
    'monthly_expenses' => 89540.30,
    'net_income' => 55690.20
];

$recent_transactions = [
    [
        'id' => 1,
        'type' => 'NHIS Payment',
        'amount' => 12500.50,
        'description' => 'Claims payment batch #CLB2024001',
        'date' => '2024-01-15 14:30',
        'status' => 'Completed'
    ],
    [
        'id' => 2,
        'type' => 'Patient Payment',
        'amount' => 150.00,
        'description' => 'Co-payment for PAT001',
        'date' => '2024-01-15 12:15',
        'status' => 'Completed'
    ],
    [
        'id' => 3,
        'type' => 'Expense',
        'amount' => -2500.00,
        'description' => 'Medical supplies purchase',
        'date' => '2024-01-15 10:00',
        'status' => 'Completed'
    ]
];

$pending_approvals = [
    ['description' => 'Equipment purchase request', 'amount' => 25000.00, 'requestor' => 'Lab Department', 'priority' => 'High'],
    ['description' => 'Staff overtime payment', 'amount' => 3500.00, 'requestor' => 'HR Department', 'priority' => 'Normal'],
    ['description' => 'Utility bill payment', 'amount' => 1200.00, 'requestor' => 'Admin', 'priority' => 'High']
];

$monthly_breakdown = [
    'nhis_revenue' => 89650.30,
    'private_revenue' => 55580.20,
    'staff_salaries' => 45000.00,
    'medical_supplies' => 25540.30,
    'utilities' => 8500.00,
    'maintenance' => 5000.00,
    'other_expenses' => 5500.00
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Dashboard - Smart Claims NHIS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .positive-amount { color: #059669; }
        .negative-amount { color: #dc2626; }
        .priority-high { background: #fee2e2; color: #991b1b; }
        .priority-normal { background: #e6f3ff; color: #0066cc; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .chart-container { position: relative; height: 300px; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-xl font-semibold text-gray-900">Finance Dashboard</h1>
                        <p class="text-sm text-gray-500"><?php echo date('F Y'); ?> Financial Overview</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-user-circle mr-1"></i>
                        <?php echo htmlspecialchars($user['full_name']); ?>
                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
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
        <!-- Quick Actions -->
        <div class="mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <?php if (hasPermission('process_payments')): ?>
                    <a href="#" class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        <i class="fas fa-credit-card text-green-600 mr-3"></i>
                        <span class="text-sm font-medium text-green-700">Process Payment</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('generate_reports')): ?>
                    <a href="#" class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <i class="fas fa-file-alt text-blue-600 mr-3"></i>
                        <span class="text-sm font-medium text-blue-700">Generate Report</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('manage_accounts')): ?>
                    <a href="#" class="flex items-center p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                        <i class="fas fa-calculator text-purple-600 mr-3"></i>
                        <span class="text-sm font-medium text-purple-700">Reconcile</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('manage_billing')): ?>
                    <a href="#" class="flex items-center p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                        <i class="fas fa-file-invoice-dollar text-orange-600 mr-3"></i>
                        <span class="text-sm font-medium text-orange-700">Billing</span>
                    </a>
                    <?php endif; ?>
                    
                    <a href="#" class="flex items-center p-3 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                        <i class="fas fa-chart-pie text-indigo-600 mr-3"></i>
                        <span class="text-sm font-medium text-indigo-700">Analytics</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Key Financial Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mb-8">
            <div class="stat-card bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Monthly Revenue</p>
                            <p class="text-lg font-semibold text-green-600">₵<?php echo number_format($finance_stats['monthly_revenue'], 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Payments</p>
                            <p class="text-lg font-semibold text-yellow-600">₵<?php echo number_format($finance_stats['pending_payments'], 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Outstanding Claims</p>
                            <p class="text-lg font-semibold text-blue-600">₵<?php echo number_format($finance_stats['outstanding_claims'], 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-wallet text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Cash on Hand</p>
                            <p class="text-lg font-semibold text-purple-600">₵<?php echo number_format($finance_stats['cash_on_hand'], 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-credit-card text-red-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Monthly Expenses</p>
                            <p class="text-lg font-semibold text-red-600">₵<?php echo number_format($finance_stats['monthly_expenses'], 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line text-indigo-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Net Income</p>
                            <p class="text-lg font-semibold text-indigo-600">₵<?php echo number_format($finance_stats['net_income'], 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Transactions -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-exchange-alt mr-2 text-green-600"></i>
                            Recent Transactions
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($recent_transactions as $transaction): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($transaction['type']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($transaction['description']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium <?php echo $transaction['amount'] >= 0 ? 'positive-amount' : 'negative-amount'; ?>">
                                                ₵<?php echo number_format(abs($transaction['amount']), 2); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M j, Y g:i A', strtotime($transaction['date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full status-<?php echo strtolower($transaction['status']); ?>">
                                                <?php echo $transaction['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Revenue vs Expenses Chart -->
                <div class="bg-white shadow-sm rounded-lg mt-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                            Revenue vs Expenses Breakdown
                        </h3>
                        <div class="chart-container">
                            <canvas id="revenueExpensesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Pending Approvals -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-clock mr-2 text-orange-600"></i>
                            Pending Approvals
                        </h3>
                        <div class="space-y-3">
                            <?php foreach ($pending_approvals as $approval): ?>
                            <div class="p-3 border rounded-lg">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($approval['description']); ?></div>
                                <div class="text-sm text-gray-600">₵<?php echo number_format($approval['amount'], 2); ?></div>
                                <div class="text-xs text-gray-500 mb-2"><?php echo htmlspecialchars($approval['requestor']); ?></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs px-2 py-1 rounded-full priority-<?php echo strtolower($approval['priority']); ?>">
                                        <?php echo $approval['priority']; ?>
                                    </span>
                                    <div class="flex space-x-1">
                                        <button class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">
                                            Approve
                                        </button>
                                        <button class="text-xs bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700">
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-chart-pie mr-2 text-purple-600"></i>
                            Monthly Summary
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">NHIS Revenue</span>
                                <span class="text-sm font-medium positive-amount">₵<?php echo number_format($monthly_breakdown['nhis_revenue'], 2); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Private Revenue</span>
                                <span class="text-sm font-medium positive-amount">₵<?php echo number_format($monthly_breakdown['private_revenue'], 2); ?></span>
                            </div>
                            <hr class="my-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Staff Salaries</span>
                                <span class="text-sm font-medium negative-amount">₵<?php echo number_format($monthly_breakdown['staff_salaries'], 2); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Medical Supplies</span>
                                <span class="text-sm font-medium negative-amount">₵<?php echo number_format($monthly_breakdown['medical_supplies'], 2); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Utilities</span>
                                <span class="text-sm font-medium negative-amount">₵<?php echo number_format($monthly_breakdown['utilities'], 2); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Maintenance</span>
                                <span class="text-sm font-medium negative-amount">₵<?php echo number_format($monthly_breakdown['maintenance'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cash Flow Forecast -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            <i class="fas fa-crystal-ball mr-2 text-indigo-600"></i>
                            Cash Flow Forecast
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Next Week</p>
                                    <p class="text-xs text-gray-500">Expected inflow</p>
                                </div>
                                <span class="text-sm font-medium positive-amount">₵15,500</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Next Month</p>
                                    <p class="text-xs text-gray-500">Projected revenue</p>
                                </div>
                                <span class="text-sm font-medium positive-amount">₵125,000</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Upcoming Bills</p>
                                    <p class="text-xs text-gray-500">Due this month</p>
                                </div>
                                <span class="text-sm font-medium negative-amount">₵45,200</span>
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
        
        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue vs Expenses Chart
            const ctx = document.getElementById('revenueExpensesChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['NHIS Revenue', 'Private Revenue', 'Staff Salaries', 'Medical Supplies', 'Utilities', 'Other Expenses'],
                    datasets: [{
                        data: [
                            <?php echo $monthly_breakdown['nhis_revenue']; ?>,
                            <?php echo $monthly_breakdown['private_revenue']; ?>,
                            <?php echo $monthly_breakdown['staff_salaries']; ?>,
                            <?php echo $monthly_breakdown['medical_supplies']; ?>,
                            <?php echo $monthly_breakdown['utilities']; ?>,
                            <?php echo $monthly_breakdown['other_expenses']; ?>
                        ],
                        backgroundColor: [
                            '#10b981', '#3b82f6', '#ef4444', '#f59e0b', '#8b5cf6', '#6b7280'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        });
        
        // Auto-refresh financial data
        setInterval(function() {
            console.log('Refreshing financial data...');
            // You can implement actual data refresh here
        }, 300000); // Refresh every 5 minutes
        
        // Payment processing functionality
        function processPayment(transactionId) {
            console.log('Processing payment for transaction:', transactionId);
            // Implement payment processing logic
        }
        
        // Generate financial report
        function generateReport(reportType) {
            console.log('Generating report:', reportType);
            // Implement report generation logic
        }
    </script>
</body>
</html>