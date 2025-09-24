<?php
// Simple debug for services API
$servicesUrl = 'http://localhost/smartclaimsCLive/api/services-api.php?action=services';

echo "<h1>Services API Debug</h1>";
echo "<h2>Fetching from: $servicesUrl</h2>";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json',
        'timeout' => 30
    ]
]);

$response = file_get_contents($servicesUrl, false, $context);

if ($response === false) {
    echo "<h2>❌ Failed to fetch services</h2>";
    echo "<p>Error: " . error_get_last()['message'] . "</p>";
} else {
    echo "<h2>✅ Response received</h2>";
    echo "<h3>Raw Response:</h3>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    $data = json_decode($response, true);
    if ($data) {
        echo "<h3>Parsed Data:</h3>";
        echo "<pre>" . print_r($data, true) . "</pre>";
        
        if (isset($data['data'])) {
            echo "<h3>Services Summary:</h3>";
            foreach ($data['data'] as $category => $services) {
                echo "<h4>" . ucfirst($category) . " Services: " . count($services) . "</h4>";
                if (count($services) > 0) {
                    echo "<table border='1' cellpadding='5'>";
                    echo "<tr><th>ID</th><th>Name</th><th>Tariff</th><th>NHIS Tariff</th><th>Private Price</th></tr>";
                    foreach (array_slice($services, 0, 5) as $service) {
                        echo "<tr>";
                        echo "<td>" . ($service['id'] ?? 'N/A') . "</td>";
                        echo "<td>" . ($service['name'] ?? 'N/A') . "</td>";
                        echo "<td>" . ($service['tariff'] ?? 'N/A') . "</td>";
                        echo "<td>" . ($service['nhis_tariff'] ?? 'N/A') . "</td>";
                        echo "<td>" . ($service['private_price'] ?? 'N/A') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            }
        }
    } else {
        echo "<h3>❌ Failed to parse JSON</h3>";
        echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
    }
}
?>