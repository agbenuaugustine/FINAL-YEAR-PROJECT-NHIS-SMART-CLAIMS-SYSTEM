<?php
/**
 * Department Access Controller
 * 
 * Controls which data each department can see based on their role and workflow stage
 * Implements proper data segregation for NHIS Claims workflow
 */

require_once __DIR__ . '/secure_auth.php';

class DepartmentController {
    
    /**
     * Define department role mappings
     */
    private static $department_roles = [
        'OPD' => ['doctor', 'nurse', 'receptionist'],
        'LAB' => ['lab_technician', 'doctor'],
        'PHARMACY' => ['pharmacist', 'doctor'],
        'RADIOLOGY' => ['radiologist', 'doctor'],
        'PHYSIOTHERAPY' => ['physiotherapist', 'doctor'],
        'CLAIMS' => ['claims_officer', 'finance_officer'],
        'ADMIN' => ['hospital_admin', 'admin', 'department_head']
    ];
    
    /**
     * Define workflow stages and which departments can access each stage
     */
    private static $workflow_access = [
        1 => ['OPD'], // Client Registration
        2 => ['OPD', 'LAB', 'RADIOLOGY'], // Service Requisition  
        3 => ['OPD', 'LAB', 'RADIOLOGY'], // Vital Signs & Tests
        4 => ['OPD', 'PHARMACY'], // Diagnosis & Medication
        5 => ['CLAIMS'] // Claims Processing
    ];
    
    /**
     * Get user's primary department based on role
     */
    public static function getUserDepartment($role) {
        foreach (self::$department_roles as $dept => $roles) {
            if (in_array($role, $roles)) {
                return $dept;
            }
        }
        return 'GENERAL'; // Default for unmatched roles
    }
    
    /**
     * Check if user can access specific department data
     */
    public static function canAccessDepartment($user_role, $department) {
        // Hospital admin and admin can access all departments
        if (in_array($user_role, ['hospital_admin', 'admin', 'superadmin'])) {
            return true;
        }
        
        // Check if user's role is allowed in the department
        if (isset(self::$department_roles[$department])) {
            return in_array($user_role, self::$department_roles[$department]);
        }
        
        // Allow access but filter data based on role
        return true;
    }
    
    /**
     * Get workflow stages accessible to user's department
     */
    public static function getAccessibleStages($user_role) {
        $user_dept = self::getUserDepartment($user_role);
        $accessible_stages = [];
        
        // Hospital admin can access all stages
        if (in_array($user_role, ['hospital_admin', 'admin', 'superadmin'])) {
            return [1, 2, 3, 4, 5];
        }
        
        foreach (self::$workflow_access as $stage => $departments) {
            if (in_array($user_dept, $departments)) {
                $accessible_stages[] = $stage;
            }
        }
        
        return $accessible_stages;
    }
    
    /**
     * Filter patient data based on user's department access
     */
    public static function filterPatientData($patients, $user_role) {
        $user_dept = self::getUserDepartment($user_role);
        $accessible_stages = self::getAccessibleStages($user_role);
        
        // Hospital admin sees all data
        if (in_array($user_role, ['hospital_admin', 'admin', 'superadmin'])) {
            return $patients;
        }
        
        $filtered_patients = [];
        foreach ($patients as $patient) {
            // Filter based on current workflow stage
            $patient_stage = $patient['current_stage'] ?? 1;
            
            if (in_array($patient_stage, $accessible_stages)) {
                // Additional filtering based on department-specific criteria
                switch ($user_dept) {
                    case 'OPD':
                        // OPD sees patients in registration, service requisition, or diagnosis stages
                        if (in_array($patient_stage, [1, 2, 4])) {
                            $filtered_patients[] = $patient;
                        }
                        break;
                    
                    case 'LAB':
                        // Lab sees only patients with lab orders
                        if (isset($patient['has_lab_orders']) && $patient['has_lab_orders']) {
                            $filtered_patients[] = $patient;
                        }
                        break;
                    
                    case 'PHARMACY':
                        // Pharmacy sees only patients with prescriptions
                        if (isset($patient['has_prescriptions']) && $patient['has_prescriptions']) {
                            $filtered_patients[] = $patient;
                        }
                        break;
                    
                    case 'CLAIMS':
                        // Claims sees patients ready for claims processing
                        if ($patient_stage >= 4) {
                            $filtered_patients[] = $patient;
                        }
                        break;
                    
                    default:
                        $filtered_patients[] = $patient;
                }
            }
        }
        
        return $filtered_patients;
    }
    
    /**
     * Get department-specific navigation menu
     */
    public static function getDepartmentNavigation($user_role) {
        $user_dept = self::getUserDepartment($user_role);
        $accessible_stages = self::getAccessibleStages($user_role);
        
        $navigation = [
            'dashboard' => [
                'url' => 'dashboard.php',
                'label' => 'Main Dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'accessible' => true
            ]
        ];
        
        // Add stage-specific navigation
        if (in_array(1, $accessible_stages)) {
            $navigation['registration'] = [
                'url' => 'client-registration.php',
                'label' => 'Client Registration',
                'icon' => 'fas fa-user-plus',
                'accessible' => true
            ];
        }
        
        if (in_array(2, $accessible_stages)) {
            $navigation['requisition'] = [
                'url' => 'service-requisition.php',
                'label' => 'Service Requisition',
                'icon' => 'fas fa-clipboard-list',
                'accessible' => true
            ];
        }
        
        if (in_array(3, $accessible_stages)) {
            $navigation['vitals'] = [
                'url' => 'vital-signs.php',
                'label' => 'Vital Signs',
                'icon' => 'fas fa-heartbeat',
                'accessible' => true
            ];
        }
        
        if (in_array(4, $accessible_stages)) {
            $navigation['diagnosis'] = [
                'url' => 'diagnosis-medication.php',
                'label' => 'Diagnosis & Medication',
                'icon' => 'fas fa-stethoscope',
                'accessible' => true
            ];
        }
        
        if (in_array(5, $accessible_stages)) {
            $navigation['claims'] = [
                'url' => 'claims-processing.php',
                'label' => 'Claims Processing',
                'icon' => 'fas fa-file-invoice-dollar',
                'accessible' => true
            ];
        }
        
        // Add department-specific dashboards
        switch ($user_dept) {
            case 'OPD':
                $navigation['opd_dashboard'] = [
                    'url' => 'opd-dashboard.php',
                    'label' => 'OPD Dashboard',
                    'icon' => 'fas fa-user-md',
                    'accessible' => true
                ];
                break;
            
            case 'LAB':
                $navigation['lab_dashboard'] = [
                    'url' => 'lab-dashboard.php',
                    'label' => 'Lab Dashboard',
                    'icon' => 'fas fa-flask',
                    'accessible' => true
                ];
                break;
            
            case 'PHARMACY':
                $navigation['pharmacy_dashboard'] = [
                    'url' => 'pharmacy-dashboard.php',
                    'label' => 'Pharmacy Dashboard',
                    'icon' => 'fas fa-pills',
                    'accessible' => true
                ];
                break;
        }
        
        // Hospital admin gets access to all dashboards
        if (in_array($user_role, ['hospital_admin', 'admin', 'superadmin'])) {
            $navigation['opd_dashboard'] = [
                'url' => 'opd-dashboard.php',
                'label' => 'OPD Dashboard',
                'icon' => 'fas fa-user-md',
                'accessible' => true
            ];
            $navigation['lab_dashboard'] = [
                'url' => 'lab-dashboard.php',
                'label' => 'Lab Dashboard',
                'icon' => 'fas fa-flask',
                'accessible' => true
            ];
            $navigation['pharmacy_dashboard'] = [
                'url' => 'pharmacy-dashboard.php',
                'label' => 'Pharmacy Dashboard',
                'icon' => 'fas fa-pills',
                'accessible' => true
            ];
            $navigation['hospital_management'] = [
                'url' => 'hospital-management.php',
                'label' => 'Hospital Management',
                'icon' => 'fas fa-hospital',
                'accessible' => true
            ];
        }
        
        return $navigation;
    }
    
    /**
     * Redirect user to their department dashboard after login
     */
    public static function redirectToDepartmentDashboard($user_role) {
        $user_dept = self::getUserDepartment($user_role);
        
        switch ($user_dept) {
            case 'OPD':
                return 'opd-dashboard.php';
            case 'LAB':
                return 'lab-dashboard.php';
            case 'PHARMACY':
                return 'pharmacy-dashboard.php';
            case 'CLAIMS':
                return 'claims-dashboard.php';
            case 'ADMIN':
                return 'dashboard.php'; // Main dashboard for admin roles
            default:
                return 'dashboard.php';
        }
    }
    
    /**
     * Get department-specific data filters
     */
    public static function getDepartmentDataFilters($user_role) {
        $user_dept = self::getUserDepartment($user_role);
        
        $filters = [
            'show_all_patients' => in_array($user_role, ['hospital_admin', 'admin', 'superadmin']),
            'department' => $user_dept,
            'accessible_stages' => self::getAccessibleStages($user_role),
            'role' => $user_role
        ];
        
        return $filters;
    }
}

// Global functions for easy access
function getUserDepartment() {
    global $role;
    return DepartmentController::getUserDepartment($role);
}

function canAccessDepartment($department) {
    global $role;
    return DepartmentController::canAccessDepartment($role, $department);
}

function getDepartmentNavigation() {
    global $role;
    return DepartmentController::getDepartmentNavigation($role);
}

function isHospitalAdmin() {
    global $role;
    return in_array($role, ['hospital_admin', 'admin', 'superadmin']);
}

function isSuperAdmin() {
    global $role;
    return $role === 'superadmin';
}
?>