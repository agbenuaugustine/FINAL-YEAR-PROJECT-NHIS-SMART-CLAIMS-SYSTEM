# Smart Claims NHIS - Department Access Control Guide

## Overview
The Smart Claims NHIS system now implements proper **role-based access control** and **data segregation** to ensure each department only sees data relevant to their workflow stage. This enhances security, improves efficiency, and follows healthcare data protection best practices.

## How to Test Department Access

### 1. Role Selector (Testing Only)
- **URL:** `http://localhost/smartclaimsCL/role-selector.php`
- Select different roles to see how data segregation works
- Each role will redirect to their appropriate department dashboard

### 2. Available Roles & Departments

| Role | Department | Access Level | Can See |
|------|------------|--------------|---------|
| **Hospital Admin** | ADMIN | Full Access | All patients, all departments, all stages |
| **Doctor** | OPD | Stage 1,2,4 | Patients in registration, consultation, diagnosis |  
| **Nurse** | OPD | Stage 1,3 | Patients in registration, vital signs |
| **Receptionist** | OPD | Stage 1 | Patients in registration only |
| **Lab Technician** | LAB | Stage 2,3 | Only patients with lab orders |
| **Pharmacist** | PHARMACY | Stage 4 | Only patients with prescriptions |
| **Claims Officer** | CLAIMS | Stage 5 | Patients ready for claims processing |

### 3. Workflow Stages

The system follows a **sequential 5-stage workflow**:

1. **Stage 1: Client Registration** (OPD)
   - Register new patients
   - Verify NHIS status
   - Initial triage

2. **Stage 2: Service Requisition** (OPD/Lab/Radiology)
   - Request lab tests
   - Order radiology
   - Service planning

3. **Stage 3: Vital Signs & Tests** (Nursing/Lab)
   - Record vital signs
   - Perform tests
   - Clinical assessments

4. **Stage 4: Diagnosis & Medication** (Clinical/Pharmacy)
   - Clinical diagnosis
   - Prescribe medication
   - Treatment plans

5. **Stage 5: Claims Processing** (Claims)
   - Generate NHIS claims
   - Submit to NHIA
   - Track reimbursements

## Data Segregation Rules

### OPD Department
- **Can Access:** Patients in stages 1, 2, and 4
- **Cannot See:** Lab results from other patients, pharmacy inventory details
- **Workflow:** Registration → Consultation → Discharge/Referral

### Laboratory Department  
- **Can Access:** Only patients with lab orders
- **Cannot See:** Patients without lab requisitions, prescription details
- **Workflow:** Receive Orders → Collect Specimens → Process Tests → Report Results

### Pharmacy Department
- **Can Access:** Only patients with prescriptions
- **Cannot See:** Patients without prescriptions, lab results
- **Workflow:** Receive Prescriptions → Verify → Dispense → Counsel

### Claims Department
- **Can Access:** Patients who completed clinical care (stage 4+)
- **Cannot See:** Patients still in active treatment
- **Workflow:** Collect Documentation → Generate Claims → Submit to NHIA

## Security Features

### 1. Access Control
- ✅ Role-based permissions
- ✅ Department-specific data filtering
- ✅ Workflow stage validation
- ✅ Unauthorized access prevention

### 2. Data Protection
- ✅ Patient data segregation
- ✅ Department-specific dashboards
- ✅ Audit trails (can be enhanced)
- ✅ Session management

### 3. User Experience
- ✅ Intuitive department dashboards
- ✅ Clear workflow indicators
- ✅ Professional interface design
- ✅ Responsive mobile support

## Testing Instructions

### Step 1: Test Hospital Admin Access
```
1. Go to: http://localhost/smartclaimsCL/role-selector.php
2. Select "Hospital Administrator"
3. Verify you can access all departments:
   - OPD Dashboard
   - Lab Dashboard  
   - Pharmacy Dashboard
   - Claims Processing
4. Check that you see all patient data
```

### Step 2: Test Department Staff Access
```
1. Select "Lab Technician" role
2. Verify you only see:
   - Lab Dashboard
   - Patients with lab orders
   - Lab-specific functions
3. Try accessing pharmacy dashboard - should get "Access Denied"
```

### Step 3: Test Data Filtering
```
1. Login as "Pharmacist"
2. Verify you only see:
   - Patients with prescriptions
   - Pharmacy inventory alerts
   - Medication dispensing functions
3. Confirm you cannot see lab results or OPD patient queue
```

## File Structure

### Core Access Control Files
```
api/access/
├── department_controller.php      # Main access control logic
├── includes/
│   └── department_dashboard_template.php  # Standardized template
├── opd-dashboard.php             # OPD department dashboard
├── lab-dashboard.php             # Lab department dashboard  
├── pharmacy-dashboard-new.php    # Pharmacy department dashboard
├── unauthorized.php              # Access denied page
└── secure_auth.php              # Authentication middleware
```

### Testing Files
```
role-selector.php                 # Role testing interface
DEPARTMENT_ACCESS_GUIDE.md        # This guide
```

## Benefits Achieved

### 1. Security Enhancement
- **Data Segregation:** Each department only sees relevant data
- **Role-Based Access:** Permissions aligned with job responsibilities
- **Audit Trail:** Track who accessed what data (can be expanded)

### 2. Workflow Efficiency  
- **Stage-Based Process:** Clear workflow progression
- **Department Focus:** Staff see only their tasks
- **Reduced Errors:** Less confusion from irrelevant data

### 3. NHIS Compliance
- **Professional Structure:** Follows healthcare IT standards
- **Data Protection:** Meets privacy requirements
- **Audit Ready:** Supports compliance reporting

### 4. User Experience
- **Intuitive Interface:** Department-specific dashboards
- **Mobile Responsive:** Works on all devices
- **Professional Design:** Modern, clean interface

## Next Steps for Production

### 1. Database Integration
- Replace mock data with actual database queries
- Implement patient workflow status tracking
- Add proper audit logging

### 2. Enhanced Security
- Add two-factor authentication
- Implement password policies
- Add session encryption

### 3. Advanced Features
- Real-time notifications between departments
- Mobile app for staff
- Integration with NHIA systems

### 4. Monitoring & Analytics
- Department performance dashboards
- Workflow bottleneck identification
- Claims success rate tracking

## Support

For technical support or questions about the access control system:
- Check the unauthorized access page for role-specific guidance
- Contact system administrator for permission changes
- Review this guide for workflow understanding

---

**Smart Claims NHIS** - Streamlining healthcare administration with secure, efficient digital solutions.