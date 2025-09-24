<?php
/**
 * Reports API Endpoint
 * 
 * Provides real-time data for the reports dashboard
 * Handles AJAX requests for dynamic updates
 */

require_once __DIR__ . '/secure_auth.php';

if (!hasPermission('view_claims_reports') && !hasPermission('generate_reports') && $role !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $current_hospital_id = getUserHospitalId();

    switch ($input['action']) {
        case 'get_real_time_stats':
            $date_range = $input['date_range'] ?? 'last_30_days';
            $department_filter = $input['department'] ?? 'all';
            $status_filter = $input['status_filter'] ?? 'all';
            
            // Build filters
            $hospital_filter = '';
            $params = [];
            
            if (!isSuperAdmin()) {
                $hospital_filter = ' AND c.hospital_id = ?';
                $params[] = $current_hospital_id;
            }
            
            // Date range filter
            $date_filter = '';
            switch ($date_range) {
                case 'last_7_days':
                    $date_filter = ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'last_30_days':
                    $date_filter = ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
                case 'last_90_days':
                    $date_filter = ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)';
                    break;
                case 'last_year':
                    $date_filter = ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                    break;
            }
            
            // Department filter
            $dept_filter = '';
            if ($department_filter !== 'all') {
                $dept_filter = ' AND c.department = ?';
                $params[] = $department_filter;
            }
            
            // Status filter
            $status_filter_sql = '';
            if ($status_filter !== 'all') {
                $status_filter_sql = ' AND c.status = ?';
                $params[] = $status_filter;
            }
            
            // Fetch real-time statistics
            $stats_query = "SELECT 
                              COUNT(*) as total_claims,
                              COUNT(CASE WHEN c.status = 'approved' THEN 1 END) as approved_claims,
                              COUNT(CASE WHEN c.status = 'pending' THEN 1 END) as pending_claims,
                              COUNT(CASE WHEN c.status = 'rejected' THEN 1 END) as rejected_claims,
                              COUNT(CASE WHEN c.status = 'paid' THEN 1 END) as paid_claims,
                              COUNT(CASE WHEN c.status = 'processing' THEN 1 END) as processing_claims,
                              COALESCE(SUM(CASE WHEN c.status = 'approved' THEN c.total_amount END), 0) as total_revenue,
                              COALESCE(AVG(CASE WHEN c.status = 'approved' AND c.processing_time_hours > 0 THEN c.processing_time_hours END), 0) as avg_processing_time
                            FROM claims c 
                            WHERE 1=1 {$hospital_filter} {$date_filter} {$dept_filter} {$status_filter_sql}";
            
            $stmt = $db->prepare($stats_query);
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }
            
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stats) {
                // Calculate derived metrics
                $stats['approval_rate'] = $stats['total_claims'] > 0 ? 
                    round(($stats['approved_claims'] / $stats['total_claims']) * 100, 1) : 0;
                
                $stats['efficiency_rate'] = $stats['avg_processing_time'] > 0 ? 
                    max(0, 100 - min(100, $stats['avg_processing_time'] * 2)) : 95;
                
                // Convert to proper types
                $stats['total_claims'] = (int)$stats['total_claims'];
                $stats['approved_claims'] = (int)$stats['approved_claims'];
                $stats['pending_claims'] = (int)$stats['pending_claims'];
                $stats['rejected_claims'] = (int)$stats['rejected_claims'];
                $stats['paid_claims'] = (int)$stats['paid_claims'];
                $stats['processing_claims'] = (int)$stats['processing_claims'];
                $stats['total_revenue'] = (float)$stats['total_revenue'];
                $stats['avg_processing_time'] = (float)$stats['avg_processing_time'];
            }
            
            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'timestamp' => date('Y-m-d H:i:s'),
                'filters' => [
                    'date_range' => $date_range,
                    'department' => $department_filter,
                    'status' => $status_filter
                ]
            ]);
            break;
            
        case 'get_chart_data':
            $date_range = $input['date_range'] ?? 'last_30_days';
            $chart_type = $input['chart_type'] ?? 'claims_trend';
            
            // Build date filter
            $date_filter = '';
            switch ($date_range) {
                case 'last_7_days':
                    $date_filter = ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'last_30_days':
                    $date_filter = ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
                case 'last_90_days':
                    $date_filter = ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)';
                    break;
                case 'last_year':
                    $date_filter = ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                    break;
            }
            
            $hospital_filter = '';
            $params = [];
            
            if (!isSuperAdmin()) {
                $hospital_filter = ' AND c.hospital_id = ?';
                $params[] = $current_hospital_id;
            }
            
            switch ($chart_type) {
                case 'claims_trend':
                    // Get claims trend for last 6 months
                    $trend_query = "SELECT 
                                     DATE_FORMAT(c.created_at, '%Y-%m') as month,
                                     COUNT(*) as total_claims,
                                     COUNT(CASE WHEN c.status = 'approved' THEN 1 END) as approved_claims
                                   FROM claims c 
                                   WHERE 1=1 {$hospital_filter} {$date_filter}
                                   GROUP BY DATE_FORMAT(c.created_at, '%Y-%m')
                                   ORDER BY month DESC 
                                   LIMIT 6";
                    
                    $stmt = $db->prepare($trend_query);
                    if (!empty($params)) {
                        $stmt->execute($params);
                    } else {
                        $stmt->execute();
                    }
                    
                    $trend_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $trend_data,
                        'chart_type' => $chart_type
                    ]);
                    break;
                    
                case 'department_performance':
                    // Get department performance
                    $dept_query = "SELECT 
                                   c.department,
                                   COUNT(*) as claims_count
                                 FROM claims c 
                                 WHERE 1=1 {$hospital_filter} {$date_filter}
                                 GROUP BY c.department 
                                 ORDER BY claims_count DESC";
                    
                    $stmt = $db->prepare($dept_query);
                    if (!empty($params)) {
                        $stmt->execute($params);
                    } else {
                        $stmt->execute();
                    }
                    
                    $dept_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $dept_data,
                        'chart_type' => $chart_type
                    ]);
                    break;
                    
                case 'status_distribution':
                    // Get status distribution
                    $status_query = "SELECT 
                                     c.status,
                                     COUNT(*) as count
                                   FROM claims c 
                                   WHERE 1=1 {$hospital_filter} {$date_filter}
                                   GROUP BY c.status 
                                   ORDER BY count DESC";
                    
                    $stmt = $db->prepare($status_query);
                    if (!empty($params)) {
                        $stmt->execute($params);
                    } else {
                        $stmt->execute();
                    }
                    
                    $status_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $status_data,
                        'chart_type' => $chart_type
                    ]);
                    break;
                    
                case 'processing_time':
                    // Get processing time trend
                    $processing_query = "SELECT 
                                         DATE_FORMAT(c.created_at, '%Y-%m-%d') as date,
                                         AVG(c.processing_time_hours) as avg_processing_time
                                       FROM claims c 
                                       WHERE 1=1 {$hospital_filter} {$date_filter} AND c.processing_time_hours > 0
                                       GROUP BY DATE_FORMAT(c.created_at, '%Y-%m-%d')
                                       ORDER BY date DESC 
                                       LIMIT 28";
                    
                    $stmt = $db->prepare($processing_query);
                    if (!empty($params)) {
                        $stmt->execute($params);
                    } else {
                        $stmt->execute();
                    }
                    
                    $processing_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $processing_data,
                        'chart_type' => $chart_type
                    ]);
                    break;
                    
                case 'top_diagnoses':
                    // Get top diagnoses
                    $diagnosis_query = "SELECT 
                                         c.diagnosis,
                                         COUNT(*) as count
                                       FROM claims c 
                                       WHERE 1=1 {$hospital_filter} {$date_filter} AND c.diagnosis IS NOT NULL
                                       GROUP BY c.diagnosis 
                                       ORDER BY count DESC 
                                       LIMIT 10";
                    
                    $stmt = $db->prepare($diagnosis_query);
                    if (!empty($params)) {
                        $stmt->execute($params);
                    } else {
                        $stmt->execute();
                    }
                    
                    $diagnosis_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $diagnosis_data,
                        'chart_type' => $chart_type
                    ]);
                    break;
                    
                case 'revenue_trend':
                    // Get revenue trend
                    $revenue_query = "SELECT 
                                       DATE_FORMAT(c.created_at, '%Y-%m') as month,
                                       SUM(c.total_amount) as revenue
                                     FROM claims c 
                                     WHERE 1=1 {$hospital_filter} {$date_filter} AND c.status = 'approved'
                                     GROUP BY DATE_FORMAT(c.created_at, '%Y-%m')
                                     ORDER BY month DESC 
                                     LIMIT 6";
                    
                    $stmt = $db->prepare($revenue_query);
                    if (!empty($params)) {
                        $stmt->execute($params);
                    } else {
                        $stmt->execute();
                    }
                    
                    $revenue_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $revenue_data,
                        'chart_type' => $chart_type
                    ]);
                    break;
                    
                case 'system_usage':
                    // Get system usage by hour (simulated based on claims creation time)
                    $usage_query = "SELECT 
                                     HOUR(c.created_at) as hour,
                                     COUNT(*) as activity_count
                                   FROM claims c 
                                   WHERE 1=1 {$hospital_filter} {$date_filter}
                                   GROUP BY HOUR(c.created_at) 
                                   ORDER BY hour";
                    
                    $stmt = $db->prepare($usage_query);
                    if (!empty($params)) {
                        $stmt->execute($params);
                    } else {
                        $stmt->execute();
                    }
                    
                    $usage_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $usage_data,
                        'chart_type' => $chart_type
                    ]);
                    break;
                    
                default:
                    echo json_encode([
                        'success' => false,
                        'error' => 'Unknown chart type'
                    ]);
                    break;
            }
            break;
            
        case 'get_detailed_reports':
            $date_range = $input['date_range'] ?? 'last_30_days';
            $department_filter = $input['department'] ?? 'all';
            $status_filter = $input['status_filter'] ?? 'all';
            $limit = $input['limit'] ?? 50;
            $offset = $input['offset'] ?? 0;
            
            // Build filters
            $hospital_filter = '';
            $params = [];
            
            if (!isSuperAdmin()) {
                $hospital_filter = ' AND c.hospital_id = ?';
                $params[] = $current_hospital_id;
            }
            
            // Date range filter
            $date_filter = '';
            switch ($date_range) {
                case 'last_7_days':
                    $date_filter = ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'last_30_days':
                    $date_filter = ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
                case 'last_90_days':
                    $date_filter = ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)';
                    break;
                case 'last_year':
                    $date_filter = ' AND c.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                    break;
            }
            
            // Department filter
            $dept_filter = '';
            if ($department_filter !== 'all') {
                $dept_filter = ' AND c.department = ?';
                $params[] = $department_filter;
            }
            
            // Status filter
            $status_filter_sql = '';
            if ($status_filter !== 'all') {
                $status_filter_sql = ' AND c.status = ?';
                $params[] = $status_filter;
            }
            
            // Fetch detailed reports
            $reports_query = "SELECT 
                               c.created_at as date,
                               CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                               p.nhis_number as nhis,
                               c.diagnosis,
                               c.department,
                               c.total_amount as amount,
                               c.status,
                               COALESCE(c.processing_time_hours, 0) as processing_time
                             FROM claims c
                             LEFT JOIN patients p ON c.patient_id = p.id
                             WHERE 1=1 {$hospital_filter} {$date_filter} {$dept_filter} {$status_filter_sql}
                             ORDER BY c.created_at DESC
                             LIMIT ? OFFSET ?";
            
            $params[] = (int)$limit;
            $params[] = (int)$offset;
            
            $stmt = $db->prepare($reports_query);
            $stmt->execute($params);
            $reports_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $count_query = "SELECT COUNT(*) as total
                            FROM claims c
                            LEFT JOIN patients p ON c.patient_id = p.id
                            WHERE 1=1 {$hospital_filter} {$date_filter} {$dept_filter} {$status_filter_sql}";
            
            $count_params = array_slice($params, 0, -2); // Remove limit and offset
            $count_stmt = $db->prepare($count_query);
            if (!empty($count_params)) {
                $count_stmt->execute($count_params);
            } else {
                $count_stmt->execute();
            }
            $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo json_encode([
                'success' => true,
                'data' => $reports_data,
                'total_count' => (int)$total_count,
                'limit' => (int)$limit,
                'offset' => (int)$offset,
                'filters' => [
                    'date_range' => $date_range,
                    'department' => $department_filter,
                    'status' => $status_filter
                ]
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Unknown action'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log("Reports API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
?>
