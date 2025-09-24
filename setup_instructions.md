# Smart Claims NHIS System - Setup Instructions

## 🚀 Complete Installation Guide

### Step 1: Database Setup

1. **Open phpMyAdmin** (usually at `http://localhost/phpmyadmin`)

2. **Create Database:**
   - Click "New" or "Databases"
   - Database name: `smartclaims`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"

3. **Import Fresh Schema:**
   - Select the `smartclaims` database
   - Click "SQL" tab
   - Copy and paste the entire content from `database/fresh_schema.sql`
   - Click "Go" to execute

### Step 2: Verify Database Tables

After running the SQL script, you should see these tables created:

✅ **Core Tables:**
- `users` (with sample admin user)
- `patients` (with all form fields)
- `visits`
- `vital_signs` (with all new fields)
- `diagnoses`
- `prescriptions`
- `lab_orders`
- `service_orders`
- `claims`
- `claim_items`

✅ **Reference Tables:**
- `icd10_codes` (with sample codes)
- `medications` (with sample drugs)
- `lab_tests` (with sample tests)
- `services` (with sample services)

✅ **System Tables:**
- `appointments`
- `system_settings`
- `notifications`
- `activity_logs`
- `audit_logs`

### Step 3: Test Database Connection

1. **Check Database Config:**
   - File: `api/config/database.php`
   - Verify settings:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'smartclaims');
     define('DB_USER', 'root');
     define('DB_PASS', ''); // Change if you have MySQL password
     ```

2. **Test Connection:**
   - Visit: `http://localhost/smartclaimsCL/api/patients.php`
   - Should return JSON response (even if empty array)

### Step 4: Login and Test Features

1. **Login Credentials:**
   - **Username:** `admin`
   - **Password:** `admin123`
   - **URL:** `http://localhost/smartclaimsCL/api/access/login.php`
   
2. **Test Each Feature:**

   **✅ Client Registration:**
   - Navigate to Client Registration
   - Fill all form fields
   - Submit form
   - Check if data is saved in `patients` table

   **✅ Vital Signs:**
   - Search for a patient (use mock data initially)
   - Fill vital signs form
   - Submit form
   - Check if data is saved in `vital_signs` table

   **✅ Other Modules:**
   - Service Requisition
   - Diagnosis & Medication
   - Claims Processing
   - Reports

### Step 5: Verify API Endpoints

Test these API endpoints in your browser or Postman:

1. **Patients API:**
   ```
   GET: /smartclaimsCL/api/patients.php
   POST: /smartclaimsCL/api/patients.php (with JSON data)
   ```

2. **Vital Signs API:**
   ```
   GET: /smartclaimsCL/api/vital-signs-api.php
   POST: /smartclaimsCL/api/vital-signs-api.php (with JSON data)
   ```

## 🔧 Current Implementation Status

### ✅ **What's Working:**

1. **Database Schema:** Complete with all form fields
2. **Patient Registration:** Fully functional with API connection
3. **Vital Signs Recording:** Fully functional with API connection
4. **User Authentication:** JWT-based authentication system
5. **Mobile Navigation:** Responsive design across all pages
6. **Form Validation:** Frontend and backend validation

### 🚧 **What Needs Implementation:**

1. **Service Requisition Backend:**
   - Create `ServiceController.php`
   - Create `/api/services-api.php` endpoint
   - Connect form to API

2. **Diagnosis & Medication Backend:**
   - Create `DiagnosisController.php`
   - Create `/api/diagnoses-api.php` endpoint
   - Connect form to API

3. **Claims Processing Backend:**
   - Create `ClaimController.php`
   - Create `/api/claims-api.php` endpoint
   - Connect form to API

4. **Reports Backend:**
   - Create `ReportController.php`
   - Create `/api/reports-api.php` endpoint
   - Connect dashboard to real data

## 📋 Default Login Accounts

| Username | Password | Role | Email |
|----------|----------|------|-------|
| admin | admin123 | admin | admin@smartclaims.com |
| dr.mensah | admin123 | doctor | dr.mensah@smartclaims.com |
| dr.asante | admin123 | doctor | dr.asante@smartclaims.com |
| nurse.afia | admin123 | nurse | nurse.afia@smartclaims.com |

## 🗄️ Sample Data Included

- **ICD-10 Codes:** 18 common diagnosis codes
- **Medications:** 10 common drugs with NHIS coverage
- **Lab Tests:** 10 common tests with pricing
- **Services:** 12 common medical services
- **System Settings:** Default facility configuration

## 🔍 Troubleshooting

### Database Connection Issues:
1. Verify XAMPP MySQL is running
2. Check database credentials in `api/config/database.php`
3. Ensure `smartclaims` database exists

### API Not Working:
1. Check browser console for JavaScript errors
2. Verify API endpoint URLs are correct
3. Check PHP error logs in XAMPP

### Form Submission Issues:
1. Check browser Network tab for failed requests
2. Verify JSON payload is correctly formatted
3. Check server response for error messages

## 🎯 Next Steps

1. **Run the fresh schema SQL** to create all tables
2. **Test patient registration** to verify database connection
3. **Test vital signs recording** to verify API functionality
4. **Let me know which module** you'd like me to complete next

The system is now ready with:
- ✅ Full database schema
- ✅ Patient registration (working)
- ✅ Vital signs recording (working)
- ✅ Professional UI with mobile navigation
- ✅ Proper authentication system

Would you like me to complete the remaining modules (Service Requisition, Diagnosis, Claims, Reports) next?