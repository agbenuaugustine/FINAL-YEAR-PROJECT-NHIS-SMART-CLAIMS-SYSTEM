<?php
/**
 * Populate Medications Table
 * 
 * This script populates the medications table with common NHIS-approved medications
 */

require_once 'api/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    
    echo "<h2>Populating Medications Table with NHIS-Approved Medications</h2>";
    
    // Check if medications table has data
    $checkStmt = $conn->query("SELECT COUNT(*) as count FROM medications");
    $count = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count > 0) {
        echo "<p>⚠️ Medications table already has $count records. Skipping insertion.</p>";
        echo "<p>If you want to re-populate, please run: DELETE FROM medications; first</p>";
        exit;
    }
    
    // Common NHIS-approved medications
    $medications = [
        // Analgesics
        ['Paracetamol', 'Analgesics/Antipyretics', 'Paracetamol', 'Pain relief and fever reduction', 'Tablet', '500mg', 'Various', true, true, 0.50],
        ['Ibuprofen', 'NSAIDs', 'Ibuprofen', 'Anti-inflammatory and pain relief', 'Tablet', '400mg', 'Various', true, true, 1.20],
        ['Aspirin', 'NSAIDs', 'Acetylsalicylic Acid', 'Pain relief and anti-inflammatory', 'Tablet', '75mg', 'Various', true, true, 0.30],
        ['Diclofenac', 'NSAIDs', 'Diclofenac Sodium', 'Anti-inflammatory pain relief', 'Tablet', '50mg', 'Various', true, true, 0.80],
        
        // Antibiotics
        ['Amoxicillin', 'Antibiotics', 'Amoxicillin', 'Bacterial infections treatment', 'Capsule', '500mg', 'Various', true, true, 2.50],
        ['Cotrimoxazole', 'Antibiotics', 'Sulfamethoxazole/Trimethoprim', 'Bacterial infections treatment', 'Tablet', '480mg', 'Various', true, true, 1.80],
        ['Doxycycline', 'Antibiotics', 'Doxycycline', 'Broad spectrum antibiotic', 'Capsule', '100mg', 'Various', true, true, 3.20],
        ['Ciprofloxacin', 'Antibiotics', 'Ciprofloxacin', 'Fluoroquinolone antibiotic', 'Tablet', '500mg', 'Various', true, true, 4.50],
        ['Metronidazole', 'Antibiotics', 'Metronidazole', 'Anaerobic bacterial infections', 'Tablet', '400mg', 'Various', true, true, 1.50],
        
        // Cardiovascular
        ['Lisinopril', 'ACE Inhibitors', 'Lisinopril', 'Hypertension treatment', 'Tablet', '10mg', 'Various', true, true, 2.80],
        ['Amlodipine', 'Calcium Channel Blockers', 'Amlodipine', 'Hypertension and angina', 'Tablet', '5mg', 'Various', true, true, 1.90],
        ['Atenolol', 'Beta Blockers', 'Atenolol', 'Hypertension and heart conditions', 'Tablet', '50mg', 'Various', true, true, 1.40],
        ['Furosemide', 'Diuretics', 'Furosemide', 'Fluid retention and hypertension', 'Tablet', '40mg', 'Various', true, true, 0.90],
        ['Digoxin', 'Cardiac Glycosides', 'Digoxin', 'Heart failure and arrhythmias', 'Tablet', '0.25mg', 'Various', true, true, 1.20],
        
        // Diabetes
        ['Metformin', 'Antidiabetics', 'Metformin HCl', 'Type 2 diabetes management', 'Tablet', '500mg', 'Various', true, true, 1.60],
        ['Glibenclamide', 'Antidiabetics', 'Glibenclamide', 'Type 2 diabetes treatment', 'Tablet', '5mg', 'Various', true, true, 0.80],
        ['Insulin', 'Antidiabetics', 'Human Insulin', 'Diabetes insulin therapy', 'Injection', '100IU/ml', 'Various', true, true, 45.00],
        
        // Respiratory
        ['Salbutamol', 'Bronchodilators', 'Salbutamol', 'Asthma and COPD relief', 'Inhaler', '100mcg', 'Various', true, true, 12.50],
        ['Prednisolone', 'Corticosteroids', 'Prednisolone', 'Anti-inflammatory steroid', 'Tablet', '5mg', 'Various', true, true, 2.20],
        ['Codeine', 'Antitussives', 'Codeine Phosphate', 'Cough suppressant', 'Tablet', '15mg', 'Various', true, true, 1.80],
        
        // Gastrointestinal
        ['Omeprazole', 'Proton Pump Inhibitors', 'Omeprazole', 'Acid reflux and ulcers', 'Capsule', '20mg', 'Various', true, true, 3.50],
        ['Ranitidine', 'H2 Antagonists', 'Ranitidine', 'Gastric acid reduction', 'Tablet', '150mg', 'Various', true, true, 1.20],
        ['ORS', 'Electrolyte Solutions', 'Oral Rehydration Salts', 'Dehydration treatment', 'Powder', '20.5g', 'Various', true, false, 0.60],
        
        // Antimalarials
        ['Artemether/Lumefantrine', 'Antimalarials', 'Artemether/Lumefantrine', 'Malaria treatment', 'Tablet', '20/120mg', 'Various', true, true, 8.50],
        ['Chloroquine', 'Antimalarials', 'Chloroquine Phosphate', 'Malaria treatment', 'Tablet', '250mg', 'Various', true, true, 1.80],
        
        // Vitamins and Supplements
        ['Folic Acid', 'Vitamins', 'Folic Acid', 'Folate deficiency treatment', 'Tablet', '5mg', 'Various', true, true, 0.40],
        ['Iron Sulfate', 'Minerals', 'Ferrous Sulfate', 'Iron deficiency anemia', 'Tablet', '200mg', 'Various', true, true, 0.80],
        ['Vitamin B Complex', 'Vitamins', 'B-Complex Vitamins', 'Vitamin B deficiency', 'Tablet', 'Standard', 'Various', true, false, 1.20],
        
        // Antihistamines
        ['Chlorpheniramine', 'Antihistamines', 'Chlorpheniramine Maleate', 'Allergic reactions', 'Tablet', '4mg', 'Various', true, true, 0.60],
        ['Cetirizine', 'Antihistamines', 'Cetirizine HCl', 'Allergic rhinitis and urticaria', 'Tablet', '10mg', 'Various', true, true, 1.80],
        
        // Topical
        ['Gentamicin Cream', 'Topical Antibiotics', 'Gentamicin', 'Skin infections', 'Cream', '0.1%', 'Various', true, true, 3.20],
        ['Hydrocortisone Cream', 'Topical Corticosteroids', 'Hydrocortisone', 'Skin inflammation', 'Cream', '1%', 'Various', true, true, 2.80]
    ];
    
    $sql = "INSERT INTO medications (name, category, generic_name, description, dosage_form, strength, manufacturer, nhis_covered, requires_prescription, unit_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    $insertedCount = 0;
    
    foreach ($medications as $med) {
        try {
            $stmt->execute($med);
            $insertedCount++;
        } catch (Exception $e) {
            echo "<p>❌ Error inserting {$med[0]}: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<p>✅ Successfully inserted $insertedCount medications into the database.</p>";
    echo "<p>📋 Medications include categories:</p>";
    echo "<ul>";
    echo "<li>Analgesics & NSAIDs</li>";
    echo "<li>Antibiotics</li>";
    echo "<li>Cardiovascular medications</li>";
    echo "<li>Antidiabetic drugs</li>";
    echo "<li>Respiratory medications</li>";
    echo "<li>Gastrointestinal drugs</li>";
    echo "<li>Antimalarials</li>";
    echo "<li>Vitamins & Supplements</li>";
    echo "<li>Antihistamines</li>";
    echo "<li>Topical preparations</li>";
    echo "</ul>";
    
    echo "<p>🎯 All medications are marked as NHIS-covered and ready for prescription.</p>";
    echo "<p>Now try searching for medications like 'Paracetamol', 'Amoxicillin', 'Metformin', etc.</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>