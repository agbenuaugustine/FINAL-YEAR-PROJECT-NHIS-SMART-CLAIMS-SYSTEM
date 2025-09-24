# Manual Database Setup Guide

If you're getting errors with the automated setup script, you can set up the database manually using these steps.

## Option 1: Using phpMyAdmin (Recommended for XAMPP users)

### Step 1: Access phpMyAdmin
1. Start XAMPP
2. Open your browser and go to `http://localhost/phpmyadmin`
3. Login (usually no password required for local XAMPP)

### Step 2: Create Database
1. Click on "Databases" tab
2. Enter database name: `smartclaims`
3. Select Collation: `utf8mb4_unicode_ci`
4. Click "Create"

### Step 3: Import Schema
1. Click on the `smartclaims` database (left sidebar)
2. Click on "Import" tab
3. Click "Choose File" and select: `/smartclaimsCL/database/enhanced_schema_safe.sql`
4. Click "Go" to import

### Step 4: Verify Setup
- Check that all tables were created (should see 20+ tables)
- Look for sample data in `hospitals`, `departments`, and `users` tables

## Option 2: Using MySQL Command Line

### Step 1: Open Command Prompt
1. Open Command Prompt as Administrator
2. Navigate to MySQL bin directory (usually `C:\xampp\mysql\bin`)

### Step 2: Connect to MySQL
```bash
mysql -u root -p
```
(Press Enter if no password, or enter your MySQL password)

### Step 3: Create Database
```sql
CREATE DATABASE smartclaims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartclaims;
```

### Step 4: Import Schema
```bash
source C:/xampp/htdocs/smartclaimsCL/database/enhanced_schema_safe.sql;
```

## Option 3: Manual Table Creation (If import fails)

If the SQL import fails, you can create tables manually by copying and pasting sections from the `enhanced_schema_safe.sql` file:

### Step 1: Create Core Tables First
1. Copy and run the `hospitals` table creation statement
2. Create the `departments` table
3. Create the `users` table
4. Create the `role_permissions` table

### Step 2: Create Dependent Tables
Continue with the remaining tables in order:
- `patients`
- `visits`
- `vital_signs`
- `diagnoses`
- `prescriptions`
- `lab_orders`
- `claims`
- etc.

### Step 3: Insert Sample Data
Copy and run the INSERT statements for:
- Hospitals
- Departments
- Users
- Role permissions
- Sample medications, lab tests, etc.

## Troubleshooting Common Issues

### Error: "DROP DATABASE statements are disabled"
- **Solution**: Use the `enhanced_schema_safe.sql` file instead of `enhanced_schema.sql`

### Error: "Access denied for user 'root'"
- **Solution**: Check your MySQL credentials in the setup script
- Update the database configuration with the correct username/password

### Error: "Can't connect to MySQL server"
- **Solution**: Make sure MySQL is running in XAMPP
- Check if the MySQL service is started

### Error: "Table already exists"
- **Solution**: The safe schema will drop existing tables first
- Or manually drop the `smartclaims` database and recreate it

### Error: "Foreign key constraint fails"
- **Solution**: Make sure tables are created in the correct order
- The schema is designed to handle dependencies properly

## Verification Steps

After successful setup, verify these items:

### 1. Check Tables
Run this query to see all tables:
```sql
SHOW TABLES;
```
You should see 20+ tables including:
- hospitals
- departments
- users
- patients
- visits
- claims
- etc.

### 2. Check Sample Data
```sql
SELECT * FROM hospitals;
SELECT * FROM users;
SELECT * FROM departments LIMIT 10;
```

### 3. Test Login
- Go to `http://localhost/smartclaimsCL`
- Try logging in with:
  - Username: `superadmin`, Password: `admin123`
  - Username: `kbth_admin`, Password: `admin123`

## Default Login Credentials

After successful setup, you can login with:

**Superadmin Account:**
- Username: `superadmin`
- Password: `admin123`
- Role: System Administrator

**Hospital Admin Accounts:**
- Username: `kbth_admin`, Password: `admin123` (Korle Bu Teaching Hospital)
- Username: `kath_admin`, Password: `admin123` (Komfo Anokye Teaching Hospital)
- Username: `ridge_admin`, Password: `admin123` (Ridge Hospital)

**Department Staff Accounts:**
- Username: `kbth_doctor1`, Password: `admin123` (Doctor at KBTH)
- Username: `kbth_nurse1`, Password: `admin123` (Nurse at KBTH)
- Username: `kbth_pharmacist1`, Password: `admin123` (Pharmacist at KBTH)

## Getting Help

If you continue to experience issues:

1. **Check Error Logs**: Look at MySQL error logs for specific error messages
2. **Verify File Paths**: Ensure the schema file path is correct
3. **Check Permissions**: Make sure MySQL has proper file permissions
4. **Database Version**: Ensure you're using MySQL 5.7+ or MariaDB 10.2+
5. **PHP Version**: Ensure you're using PHP 7.4+ with PDO MySQL extension

## Next Steps

Once the database is set up successfully:

1. **Update Configuration**: Check `api/config/database.php` for correct credentials
2. **Test Access**: Login to the system and explore different roles
3. **Customize Settings**: Update system settings as needed
4. **Add Your Data**: Start adding your hospital's actual data
5. **Configure Backups**: Set up regular database backups

## Security Notes

**Important**: Change the default passwords immediately after setup:

1. Login as superadmin
2. Go to user management
3. Update all default passwords
4. Enable additional security features as needed

The system includes comprehensive security features including:
- Role-based access control
- Audit logging
- Session management
- Data encryption
- SQL injection prevention