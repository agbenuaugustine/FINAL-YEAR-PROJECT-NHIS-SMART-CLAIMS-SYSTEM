<?php
// Print requisition page
require_once 'api/config/database.php';
session_start();

$visitId = $_GET['visit_id'] ?? null;

if (!$visitId) {
    die('Visit ID is required');
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get visit details (reuse the same query from the API)
    $visitQuery = "SELECT v.*, p.first_name, p.middle_name, p.last_name, p.nhis_number, 
                          p.date_of_birth, p.gender, p.phone_primary, p.policy_status,
                          u.full_name as created_by_name
                   FROM visits v
                   LEFT JOIN patients p ON v.patient_id = p.id
                   LEFT JOIN users u ON v.created_by = u.id
                   WHERE v.id = ?";
    
    $visitStmt = $conn->prepare($visitQuery);
    $visitStmt->execute([$visitId]);
    $visit = $visitStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$visit) {
        die('Visit not found');
    }
    
    // Get services
    $servicesQuery = "SELECT so.*, s.code, s.name, s.category, s.description,
                            s.nhis_covered, s.nhis_tariff, s.private_price,
                            CASE 
                                WHEN s.nhis_covered = 1 THEN s.nhis_tariff 
                                ELSE s.private_price 
                            END as tariff
                     FROM service_orders so
                     LEFT JOIN services s ON so.service_id = s.id
                     WHERE so.visit_id = ?
                     ORDER BY so.ordered_at";
    
    $servicesStmt = $conn->prepare($servicesQuery);
    $servicesStmt->execute([$visitId]);
    $services = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format patient info
    $fullName = trim($visit['first_name'] . ' ' . ($visit['middle_name'] ? $visit['middle_name'] . ' ' : '') . $visit['last_name']);
    
    // Calculate age
    $age = '';
    if ($visit['date_of_birth']) {
        $birthDate = new DateTime($visit['date_of_birth']);
        $now = new DateTime();
        $age = $now->diff($birthDate)->y . ' years';
    }
    
    // Calculate totals
    $totalTariff = array_sum(array_column($services, 'tariff'));
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Service Requisition - <?php echo $visit['visit_number']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-box {
            border: 1px solid #000;
            padding: 10px;
        }
        .info-box h4 {
            margin: 0 0 10px 0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
        <hr>
    </div>
    
    <div class="header">
        <h1>NHIS SERVICE REQUISITION FORM</h1>
        <h2>Smart Claims Management System</h2>
        <p><strong>Visit Number:</strong> <?php echo htmlspecialchars($visit['visit_number']); ?></p>
    </div>
    
    <div class="info-grid">
        <div class="info-box">
            <h4>Patient Information</h4>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($fullName); ?></p>
            <p><strong>NHIS Number:</strong> <?php echo htmlspecialchars($visit['nhis_number'] ?? 'N/A'); ?></p>
            <p><strong>Age:</strong> <?php echo htmlspecialchars($age); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($visit['gender'] ?? 'N/A'); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($visit['phone_primary'] ?? 'N/A'); ?></p>
            <p><strong>Policy Status:</strong> <?php echo htmlspecialchars($visit['policy_status'] ?? 'Unknown'); ?></p>
        </div>
        
        <div class="info-box">
            <h4>Visit Information</h4>
            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($visit['visit_date'])); ?></p>
            <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($visit['visit_date'])); ?></p>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($visit['visit_type']); ?></p>
            <p><strong>Priority:</strong> <?php echo htmlspecialchars($visit['priority']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($visit['status']); ?></p>
            <p><strong>Created By:</strong> <?php echo htmlspecialchars($visit['created_by_name'] ?? 'System'); ?></p>
        </div>
    </div>
    
    <?php if ($visit['chief_complaint']): ?>
    <div class="section">
        <h4>Chief Complaint:</h4>
        <p><?php echo htmlspecialchars($visit['chief_complaint']); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="section">
        <h4>Requested Services (<?php echo count($services); ?>)</h4>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Service Name</th>
                    <th>Category</th>
                    <th>NHIS Covered</th>
                    <th>Tariff (₵)</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                <tr>
                    <td><?php echo htmlspecialchars($service['code']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($service['name']); ?>
                        <?php if ($service['description']): ?>
                            <br><small><?php echo htmlspecialchars($service['description']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($service['category']); ?></td>
                    <td><?php echo $service['nhis_covered'] ? 'Yes' : 'No'; ?></td>
                    <td style="text-align: right;"><?php echo number_format($service['tariff'], 2); ?></td>
                    <td><?php echo htmlspecialchars($service['notes'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="4"><strong>TOTAL TARIFF</strong></td>
                    <td style="text-align: right;"><strong>₵<?php echo number_format($totalTariff, 2); ?></strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <p><strong>Date Printed:</strong> <?php echo date('F j, Y \a\t h:i A'); ?></p>
        <p><strong>Printed By:</strong> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'System User'); ?></p>
    </div>
    
    <div class="section">
        <p style="font-size: 10px; color: #666;">
            This is a computer-generated document from Smart Claims Management System.
            For inquiries, please contact the facility administration.
        </p>
    </div>
</body>
</html>