# Smart Claims NHIS - Enhanced System Documentation

## Overview

The Smart Claims NHIS system has been enhanced with comprehensive departmental structure, hospital management capabilities, and role-based access control. This document outlines all the new features, departments, and permissions implemented.

## System Architecture

### 1. **Three-Tier System Structure**

1. **Superadmin Level** - National oversight and hospital approval
2. **Hospital Level** - Individual hospital management
3. **Department Level** - Specialized healthcare departments

### 2. **Database Enhancements**

- **Hospitals Table**: Manages hospital registrations and approvals
- **Departments Table**: Organizes hospital departments
- **Enhanced Users Table**: Multi-role support with hospital/department assignment
- **Role Permissions Table**: Granular permission management
- **Audit Logs**: Comprehensive activity tracking

## User Roles and Permissions

### **Superadmin**
- **Purpose**: National system oversight
- **Permissions**: ALL permissions (*)
- **Responsibilities**:
  - Approve/reject hospital registrations
  - Monitor system-wide performance
  - Manage system configurations
  - View national analytics

### **Hospital Admin**
- **Purpose**: Overall hospital management
- **Permissions**: All hospital-specific permissions
- **Responsibilities**:
  - Manage hospital departments
  - Oversee staff and users
  - Monitor hospital performance
  - Approve major claims

### **Department Head**
- **Purpose**: Department-level management
- **Permissions**: Department-specific management
- **Responsibilities**:
  - Manage department staff
  - Oversee department operations
  - Approve department requests
  - Generate department reports

### **Clinical Roles**

#### **Doctor**
- Patient management (view, register, edit)
- Visit management
- Vital signs recording
- Diagnosis and medication prescriptions
- Lab test ordering
- Medical report generation

#### **Nurse**
- Patient registration and management
- Visit management
- Vital signs recording
- Medication administration assistance
- Patient education and wound care
- Patient triage

#### **Pharmacist**
- Prescription verification and dispensing
- Drug interaction checking
- Patient counseling
- Inventory management
- Pharmacy reporting

#### **Lab Technician**
- Lab test performance
- Result entry and management
- Quality control
- Equipment management
- Lab reporting

#### **Radiologist**
- Radiology procedure performance
- Image interpretation
- Radiology reporting
- Equipment management

### **Administrative Roles**

#### **Claims Officer**
- Claims processing and submission
- NHIA communication
- Claims tracking and verification
- Claims reporting

#### **Finance Officer**
- Financial management
- Payment processing
- Financial reporting
- Account management

#### **Records Officer**
- Medical records management
- Patient registration
- Record archiving and retrieval
- Compliance monitoring

#### **Receptionist**
- Patient check-in and registration
- Appointment scheduling
- Patient inquiries
- Waiting list management

#### **Cashier**
- Payment processing
- Receipt generation
- Cash management
- Billing assistance

#### **IT Support**
- System maintenance
- User account management
- Technical troubleshooting
- Data backup and restoration

## Department Dashboards

### 1. **OPD (Outpatient Department) Dashboard**
- **File**: `opd-dashboard.php`
- **Features**:
  - Patient queue management
  - Vital signs tracking
  - Appointment scheduling
  - Quick patient registration
  - Real-time status updates

### 2. **Laboratory Dashboard**
- **File**: `lab-dashboard.php`
- **Features**:
  - Pending test orders
  - Critical results alerts
  - Equipment status monitoring
  - Quality control tracking
  - Test result entry

### 3. **Pharmacy Dashboard**
- **File**: `pharmacy-dashboard.php`
- **Features**:
  - Prescription queue
  - Inventory management
  - Stock alerts
  - Dispensing workflow
  - Revenue tracking

### 4. **Claims Processing Dashboard**
- **File**: `claims-dashboard.php`
- **Features**:
  - Claims queue management
  - NHIA status tracking
  - Submission workflow
  - Performance metrics
  - NHIA updates

### 5. **Finance Dashboard**
- **File**: `finance-dashboard.php`
- **Features**:
  - Financial overview
  - Payment processing
  - Revenue tracking
  - Expense management
  - Financial reporting

### 6. **Records Management Dashboard**
- **File**: `records-dashboard.php`
- **Features**:
  - Patient registration
  - File management
  - Compliance monitoring
  - Search functionality
  - Statistical reporting

## Hospital Management

### **Hospital Registration Process**
1. Hospital submits registration application
2. Superadmin reviews application
3. Approval/rejection with reasons
4. Automatic department creation upon approval
5. Hospital admin account creation

### **Department Structure**
Each approved hospital automatically gets:
- Out Patient Department (OPD)
- Emergency Department
- Laboratory
- Pharmacy
- Radiology
- Internal Medicine
- Surgery
- Pediatrics
- Obstetrics & Gynecology
- Records
- Finance
- Claims Processing

## Security Features

### **Authentication & Authorization**
- Session-based authentication
- Role-based access control
- CSRF protection
- IP tracking and logging
- Automatic session timeout

### **Data Security**
- Hospital data isolation
- Audit trail for all actions
- Encrypted sensitive data
- Secure password hashing
- SQL injection prevention

### **Compliance**
- NHIA standards compliance
- Medical data privacy
- Audit logging for regulatory requirements
- Data retention policies
- Backup and recovery procedures

## Installation and Setup

### **Prerequisites**
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for dependencies)

### **Installation Steps**
1. Clone/download the Smart Claims system
2. Configure database settings in `api/config/database.php`
3. Run `setup_enhanced_database.php` to create the enhanced database
4. Access the system via your web browser
5. Login with default credentials:
   - **Superadmin**: `superadmin` / `admin123`
   - **Hospital Admins**: `kbth_admin`, `kath_admin`, `ridge_admin` / `admin123`

### **Configuration**
- Update database credentials
- Configure SMTP settings for notifications
- Set up backup schedules
- Configure audit log retention
- Set system-wide parameters

## API Endpoints

### **Authentication**
- `POST /api/login.php` - User authentication
- `POST /api/logout.php` - User logout
- `POST /api/validate-token.php` - Token validation

### **Patient Management**
- `GET /api/patients.php` - List patients
- `POST /api/patients.php` - Create patient
- `PUT /api/patients.php` - Update patient
- `DELETE /api/patients.php` - Delete patient

### **Visit Management**
- `GET /api/visits.php` - List visits
- `POST /api/visits.php` - Create visit
- `PUT /api/visits.php` - Update visit

### **Vital Signs**
- `GET /api/vital-signs.php` - Get vital signs
- `POST /api/vital-signs.php` - Record vital signs

### **Claims Processing**
- `GET /api/claims.php` - List claims
- `POST /api/claims.php` - Create claim
- `PUT /api/claims.php` - Update claim status

## Workflow Integration

### **NHIS Claims Workflow**
1. **Client Registration** - Capture patient demographics and NHIS details
2. **Service Requisition** - Track services (OPD, lab, pharmacy) with auto-generated tariffs
3. **Vital Signs** - Record patient vitals (temperature, blood pressure, etc.)
4. **Diagnosis & Medication** - Link diagnoses (ICD-10 codes) to prescriptions
5. **Claims Processing** - Generate NHIS-compliant claim forms automatically

### **Department Integration**
- Seamless data flow between departments
- Real-time status updates
- Shared patient records
- Coordinated care planning
- Integrated reporting

## Reporting and Analytics

### **Department Reports**
- OPD performance metrics
- Laboratory turnaround times
- Pharmacy inventory and sales
- Claims submission statistics
- Financial summaries

### **Hospital Reports**
- Overall performance dashboard
- Revenue and expense analysis
- Staff productivity metrics
- Patient satisfaction scores
- NHIA compliance reports

### **System Reports (Superadmin)**
- National health statistics
- Hospital performance comparisons
- System usage analytics
- Compliance monitoring
- Security audit reports

## Maintenance and Support

### **Regular Maintenance**
- Database optimization
- Log file management
- Security updates
- Performance monitoring
- Backup verification

### **Troubleshooting**
- Check system logs in `audit_logs` table
- Verify user permissions
- Test database connectivity
- Review security settings
- Contact IT support for technical issues

### **Updates and Upgrades**
- Follow semantic versioning
- Test in staging environment
- Backup before updates
- Monitor post-update performance
- Document all changes

## Support and Contact

For technical support, configuration assistance, or feature requests:

- **System Documentation**: This file and inline code comments
- **Database Schema**: `/database/enhanced_schema.sql`
- **Setup Guide**: `setup_enhanced_database.php`
- **Security Guidelines**: `/api/access/SECURITY_README.md`

## Version History

- **v2.0** - Enhanced system with hospital management, departments, and comprehensive RBAC
- **v1.0** - Original Smart Claims NHIS system

---

**Note**: This enhanced system maintains backward compatibility while adding powerful new features for comprehensive healthcare management. All existing Smart Claims functionality remains intact and has been enhanced with improved security, better organization, and more detailed reporting capabilities.