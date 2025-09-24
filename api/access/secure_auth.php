<?php
/**
 * Secure Authentication Middleware
 * This file must be included at the top of all protected pages
 * Handles session management, authentication, and access control
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Get the application base path dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$app_path = dirname(dirname(dirname($script_name))); // Go up 3 levels from api/access/
$base_url = $protocol . '://' . $host . $app_path;
$login_url = $app_path . '/index.php';

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || !isset($_SESSION['last_activity'])) {
    // Redirect to login page
    header('Location: ' . $login_url);
    exit();
}

// Check session timeout (30 minutes)
$timeout = 1800; // 30 minutes in seconds
if (time() - $_SESSION['last_activity'] > $timeout) {
    // Session expired, destroy session and redirect to login
    session_unset();
    session_destroy();
    header('Location: ' . $login_url . '?error=session_expired');
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Get user data
$user = $_SESSION['user'];
$role = $_SESSION['user']['role'] ?? 'user';

// Define allowed roles for different pages (updated for enhanced system)
$allowed_roles = [
    'dashboard' => ['superadmin', 'hospital_admin', 'admin', 'doctor', 'nurse', 'pharmacist', 'lab_technician', 'claims_officer', 'receptionist', 'records_officer', 'finance_officer'],
    'patient-registration' => ['superadmin', 'hospital_admin', 'admin', 'doctor', 'nurse', 'receptionist', 'records_officer'],
    'visits' => ['superadmin', 'hospital_admin', 'admin', 'doctor', 'nurse', 'receptionist'],
    'vital-signs' => ['superadmin', 'hospital_admin', 'admin', 'doctor', 'nurse'],
    'diagnosis.php' => ['superadmin', 'hospital_admin', 'admin', 'doctor'],
    'claims' => ['superadmin', 'hospital_admin', 'admin', 'claims_officer', 'doctor'],
    'settings.php' => ['superadmin', 'hospital_admin', 'admin']
];

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Check if user has permission to access this page
if (isset($allowed_roles[$current_page])) {
    if (!in_array($role, $allowed_roles[$current_page])) {
        // User doesn't have permission for this page
        header('Location: unauthorized');
        exit();
    }
}

// Additional security checks
// Log session data for debugging
error_log("Session user data in secure_auth: " . json_encode($_SESSION['user'] ?? 'not set'));

if (!isset($_SESSION['user']['is_active'])) {
    error_log("User is_active not set in session");
    session_unset();
    session_destroy();
    header('Location: ' . $login_url . '?error=account_deactivated');
    exit();
} else if ($_SESSION['user']['is_active'] != 1 && $_SESSION['user']['is_active'] !== '1') {
    error_log("User is_active value: " . $_SESSION['user']['is_active'] . " (type: " . gettype($_SESSION['user']['is_active']) . ")");
    // User account is deactivated
    session_unset();
    session_destroy();
    header('Location: ' . $login_url . '?error=account_deactivated');
    exit();
}

// CSRF protection (optional - can be enhanced)
// Allow specific admin pages to handle their own CSRF validation
$current_page = basename($_SERVER['PHP_SELF']);
$skip_csrf_pages = ['user-management']; // Pages that handle their own CSRF

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($current_page, $skip_csrf_pages)) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // CSRF token mismatch
        header('Location: ' . $login_url . '?error=csrf_error');
        exit();
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Enhanced function to check if user has specific permission
function hasPermission($permission) {
    global $role, $user;
    
    // Enhanced permission system with all departments and roles for Smart Claims NHIS
    $permissions = [
        'superadmin' => ['*'], // Superadmin has all permissions
        'hospital_admin' => [
            // Hospital management
            'manage_hospital_settings', 'manage_departments', 'manage_users', 'view_hospital_reports',
            'manage_hospital_data', 'approve_claims', 'manage_inventory', 'view_financial_reports',
            // All patient and clinical permissions
            'view_patients', 'register_patients', 'edit_patient_info', 'view_patient_history',
            'manage_visits', 'record_vital_signs', 'make_diagnosis', 'prescribe_medication',
            'order_lab_tests', 'view_lab_results', 'perform_lab_tests', 'dispense_medication',
            'view_prescriptions', 'process_claims', 'submit_claims', 'view_claims_reports',
            'generate_reports', 'export_data', 'backup_hospital_data'
        ],
        'department_head' => [
            // Department management
            'manage_department_users', 'view_department_reports', 'approve_department_requests',
            'manage_department_inventory', 'schedule_department_staff',
            // Clinical permissions based on department
            'view_patients', 'register_patients', 'edit_patient_info', 'view_patient_history',
            'manage_visits', 'record_vital_signs', 'make_diagnosis', 'prescribe_medication',
            'order_lab_tests', 'view_lab_results', 'view_prescriptions', 'generate_reports'
        ],
        'admin' => [
            // System administration (hospital level)
            'manage_users', 'view_system_reports', 'manage_system_settings', 
            'backup_system', 'view_audit_logs',
            // Clinical permissions
            'view_patients', 'register_patients', 'edit_patient_info', 'view_patient_history',
            'manage_visits', 'record_vital_signs', 'make_diagnosis', 'prescribe_medication',
            'order_lab_tests', 'view_lab_results', 'view_prescriptions', 'process_claims',
            'generate_reports', 'export_data'
        ],
        // Clinical Roles
        'doctor' => [
            'view_patients', 'register_patients', 'edit_patient_info', 'view_patient_history',
            'manage_visits', 'record_vital_signs', 'make_diagnosis', 'prescribe_medication',
            'order_lab_tests', 'view_lab_results', 'order_services', 'view_radiology_results',
            'create_referrals', 'view_prescriptions', 'generate_medical_reports'
        ],
        'nurse' => [
            'view_patients', 'register_patients', 'edit_patient_info', 'manage_visits',
            'record_vital_signs', 'view_prescriptions', 'administer_medication',
            'assist_procedures', 'patient_education', 'wound_care', 'triage_patients'
        ],
        'pharmacist' => [
            'view_patients', 'view_prescriptions', 'dispense_medication', 'manage_inventory',
            'check_drug_interactions', 'counsel_patients', 'manage_pharmacy_stock',
            'generate_pharmacy_reports', 'verify_prescriptions'
        ],
        'lab_technician' => [
            'view_patients', 'view_lab_orders', 'perform_lab_tests', 'manage_lab_orders',
            'view_lab_results', 'enter_lab_results', 'manage_lab_equipment',
            'generate_lab_reports', 'quality_control'
        ],
        'radiologist' => [
            'view_patients', 'view_radiology_orders', 'perform_radiology_procedures',
            'interpret_radiology_results', 'generate_radiology_reports', 'manage_radiology_equipment'
        ],
        'physiotherapist' => [
            'view_patients', 'manage_visits', 'record_vital_signs', 'create_treatment_plans',
            'record_therapy_sessions', 'assess_physical_function'
        ],
        // Administrative Roles
        'claims_officer' => [
            'view_patients', 'process_claims', 'submit_claims', 'approve_claims',
            'view_claims_reports', 'verify_claim_documents', 'communicate_with_nhia',
            'generate_claims_reports', 'track_claim_status'
        ],
        'receptionist' => [
            'view_patients', 'register_patients', 'edit_patient_info', 'manage_visits',
            'schedule_appointments', 'check_in_patients', 'collect_patient_information',
            'handle_patient_inquiries', 'manage_waiting_list'
        ],
        'records_officer' => [
            'view_patients', 'register_patients', 'edit_patient_info', 'view_patient_history',
            'manage_medical_records', 'archive_records', 'retrieve_records',
            'ensure_record_compliance', 'generate_statistical_reports'
        ],
        'finance_officer' => [
            'view_patients', 'view_claims_reports', 'view_financial_reports', 'process_payments',
            'manage_accounts', 'generate_financial_reports', 'track_revenue',
            'manage_billing', 'export_financial_data'
        ],
        'cashier' => [
            'view_patients', 'process_payments', 'handle_cash_transactions',
            'issue_receipts', 'balance_cash_drawer', 'process_insurance_claims',
            'handle_patient_billing'
        ],
        'it_support' => [
            'manage_system_settings', 'view_system_logs', 'backup_system',
            'troubleshoot_technical_issues', 'manage_user_accounts', 'system_maintenance',
            'data_backup_restoration', 'network_management'
        ]
    ];
    
    return isset($permissions[$role]) && 
           (in_array('*', $permissions[$role]) || in_array($permission, $permissions[$role]));
}

// Function to require specific permission
function requirePermission($permission) {
    if (!hasPermission($permission)) {
        header('Location: unauthorized');
        exit();
    }
}

// Function to check if user can access specific hospital data
function canAccessHospital($hospital_id) {
    global $user, $role;
    
    // Superadmin can access all hospitals
    if ($role === 'superadmin') {
        return true;
    }
    
    // Users can only access their own hospital's data
    return isset($user['hospital_id']) && $user['hospital_id'] == $hospital_id;
}

// Function to check if user is superadmin
function isSuperAdmin() {
    global $role;
    return $role === 'superadmin';
}

// Function to check if user is hospital admin
function isHospitalAdmin() {
    global $role;
    return $role === 'hospital_admin';
}

// Function to get user's hospital ID
function getUserHospitalId() {
    global $user;
    return isset($user['hospital_id']) ? $user['hospital_id'] : null;
}

// Enhanced function to log user activity
function logActivity($action, $details = '', $request_data = []) {
    global $user;
    
    // Enhanced logging - can be extended to database later
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $user['id'] ?? 'unknown',
        'username' => $user['username'] ?? 'unknown',
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    if (!empty($request_data)) {
        $log_entry['request_data'] = json_encode($request_data);
    }
    
    error_log("ACTIVITY_LOG: " . json_encode($log_entry));
}
?>