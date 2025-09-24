# Smart Claims: NHIS Claims Administration System

## Project Overview
Smart Claims is a comprehensive system developed to streamline NHIS (National Health Insurance Scheme) claims processing in Ghanaian health facilities. It replaces error-prone manual methods with an automated, efficient digital solution.

## Developed by
- Alhassan Mohammed
- Nkansah Francis
- Millicentia Quist
- Abedi Sakyi
- Awuah Manu Kwadwo

## Institution
University of Education, Winneba (College of Technology Education, Kumasi)

## Supervisor
Mr. Maxwell Dorbgefu Jnr.

## Year
2018

## Key Features
1. **Modules**:
   - Client Registration: Captures patient demographics and NHIS details.
   - Service Requisition: Tracks services (OPD, lab, pharmacy) with auto-generated tariffs.
   - Vital Signs: Records patient vitals (temperature, blood pressure, etc.).
   - Diagnosis & Medication: Links diagnoses (ICD-10 codes) to prescriptions.
   - Claims Processing: Generates NHIS-compliant claim forms automatically.

2. **Technologies Used**:
   - Frontend: HTML5, Tailwind CSS, Angular 17+
   - Backend: PHP (vanilla) + MySQL
   - Mobile: Ionic for Android app wrapper

3. **Benefits**:
   - Efficiency: Reduces claims processing time from weeks to hours.
   - Accuracy: Minimizes manual errors in tariffs, calculations, and data entry.
   - Cost Savings: Lowers paperwork and storage costs.
   - Data Centralization: Improves inter-department communication (OPD, lab, pharmacy).

## Setup Instructions
1. Clone this repository
2. Set up the database using the SQL script in the `database` folder
3. Configure the backend connection in `api/config/database.php`
4. Install frontend dependencies with `npm install`
5. Run the Angular app with `ng serve`
6. For mobile development, use Ionic commands in the `mobile` directory