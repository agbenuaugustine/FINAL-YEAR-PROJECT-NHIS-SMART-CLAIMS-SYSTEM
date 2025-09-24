# Updated Registration & Login System Guide

This guide covers the enhanced registration and login system that now works with the new database schema.

## 🎯 What's Been Updated

### 1. Enhanced Hospital Registration (`register.php`)
- **Complete redesign** to match the new database schema
- **Comprehensive hospital information** collection including:
  - Hospital details (name, code, type, category)
  - Location information (region, district, city, postal address)
  - NHIA accreditation details
  - Contact person information
  - Administrator account creation

### 2. New Hospital Registration API (`api/hospital-register.php`)
- **Dedicated endpoint** for hospital registrations
- **Automatic creation** of:
  - Hospital record in `hospitals` table
  - Default departments (OPD, Emergency, Lab, Pharmacy, etc.)
  - Hospital administrator user account
- **Email notifications** to both hospital and system admin
- **Transaction safety** with rollback on errors

### 3. Enhanced Login System (`index.php` + `api/login.php`)
- **Role-based redirection** after successful login
- **Enhanced session management** with hospital and department info
- **Improved error handling** and user feedback
- **Support for new user roles**:
  - `superadmin` - System administrator
  - `hospital_admin` - Hospital administrator
  - `doctor` - Medical doctor
  - `nurse` - Nursing staff
  - `lab_technician` - Laboratory technician
  - `pharmacist` - Pharmacy staff
  - `records_officer` - Medical records officer
  - `finance_officer` - Finance staff
  - `claims_officer` - Claims processing officer

### 4. Updated User Model (`api/models/User.php`)
- **New database fields** support:
  - `hospital_id` - Links user to hospital
  - `department_id` - Links user to department
  - `employee_id` - Employee identification
  - `phone` - Contact phone number
  - `profile_image` - User profile picture
  - `date_of_birth` - Birth date
  - `employment_date` - Employment start date

### 5. Enhanced Email System (`api/utils/Mailer.php`)
- **New hospital registration emails** with professional templates
- **Detailed registration confirmation** for hospitals
- **Admin notification emails** for new registrations
- **Rich HTML templates** with proper styling

## 🔧 Technical Improvements

### Database Integration
- **Full compatibility** with the enhanced database schema
- **Proper foreign key relationships** between hospitals, departments, and users
- **Automatic department creation** for new hospitals
- **Data integrity** with transaction management

### Security Enhancements
- **Input validation** and sanitization
- **SQL injection protection**
- **Password hashing** with PHP's secure methods
- **Session security** improvements

### User Experience
- **Responsive design** that works on all devices
- **Real-time validation** feedback
- **Loading states** during form submission
- **Success/error messaging** with animations
- **Role-appropriate dashboard routing**

## 🚀 How to Use

### For Hospital Registration:
1. Go to `/smartclaimsCL/register.php`
2. Fill in all hospital information
3. Create administrator credentials
4. Submit and wait for approval
5. Check email for confirmation

### For User Login:
1. Go to `/smartclaimsCL/` (main page)
2. Use provided credentials or approved hospital admin account
3. System automatically redirects based on user role
4. Access role-appropriate dashboard features

### For Testing:
1. Visit `/smartclaimsCL/test_enhanced_login.php`
2. Check database connectivity and sample data
3. Test login functionality directly
4. View user accounts and their roles

## 📊 Default Sample Data

The system comes with pre-configured sample data:

### Sample Hospitals:
- **Korle Bu Teaching Hospital** (KBTH)
- **Komfo Anokye Teaching Hospital** (KATH)
- **Ridge Hospital** (RIDGE)

### Sample Users:
- **superadmin** / admin123 (System Administrator)
- **kbth_admin** / admin123 (Hospital Administrator - KBTH)
- **kbth_doctor1** / admin123 (Doctor - KBTH)
- **kbth_nurse1** / admin123 (Nurse - KBTH)
- **kbth_pharmacist1** / admin123 (Pharmacist - KBTH)

### Default Departments (Created for each hospital):
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

## 🔐 Role-Based Access

### Superadmin
- **Full system access**
- Hospital management
- User management across all hospitals
- System configuration
- Reports and analytics

### Hospital Admin
- **Hospital-level management**
- Department management
- Staff user accounts
- Hospital-specific reports
- Settings configuration

### Clinical Staff (Doctors/Nurses)
- **Patient management**
- Service requisitions
- Diagnoses and prescriptions
- Clinical workflows

### Support Staff
- **Department-specific functions**
- Lab results (Lab Technicians)
- Pharmacy dispensing (Pharmacists)
- Medical records (Records Officers)
- Financial operations (Finance Officers)
- Claims processing (Claims Officers)

## 📧 Email Notifications

### Hospital Registration
- **Confirmation email** sent to hospital admin
- **Notification email** sent to system admin
- **Professional templates** with complete registration details
- **Action buttons** for quick access

### Features:
- Rich HTML formatting
- Mobile-responsive design
- Branded styling
- Clear call-to-action buttons
- Comprehensive registration details

## 🛠️ Configuration

### Email Settings (in `api/utils/Mailer.php`):
```php
$this->mailer->Host = 'mail.electicast.com';
$this->mailer->Username = 'samuel@electicast.com';
$this->mailer->Password = 'waxtron@123?';
```

### Database Settings (in `api/config/database.php`):
```php
private $host = "localhost";
private $db_name = "smartclaims";
private $username = "root";
private $password = "";
```

## 🐛 Troubleshooting

### Common Issues:

#### 1. Hospital Registration Fails
- **Check**: Database connection
- **Verify**: All required fields are filled
- **Ensure**: Hospital code is unique
- **Confirm**: Admin username is unique

#### 2. Login Issues
- **Verify**: User account is active (`is_active = 1`)
- **Check**: Correct username/password
- **Ensure**: User is linked to a hospital (except superadmin)
- **Confirm**: Hospital is active

#### 3. Email Not Sending
- **Check**: SMTP configuration in Mailer.php
- **Verify**: Email credentials are correct
- **Ensure**: Firewall allows SMTP traffic
- **Note**: Registration still works even if email fails

#### 4. Database Errors
- **Run**: `setup_enhanced_database.php` to ensure proper setup
- **Check**: Database exists and tables are created
- **Verify**: Foreign key relationships are intact
- **Test**: Using `test_enhanced_login.php`

## 📝 Next Steps

1. **Test the registration** with a new hospital
2. **Verify email notifications** are working
3. **Test role-based login** with different user types
4. **Customize email templates** with your branding
5. **Configure production email** settings
6. **Set up SSL certificates** for secure communication
7. **Implement additional security** measures as needed

## 📞 Support

If you encounter any issues:

1. Check the error logs in your web server
2. Use the test page (`test_enhanced_login.php`) for diagnostics
3. Verify database setup with the manual setup guide
4. Check email configuration if notifications aren't working

The system is now fully integrated with the enhanced database schema and provides a complete hospital management registration and login experience!