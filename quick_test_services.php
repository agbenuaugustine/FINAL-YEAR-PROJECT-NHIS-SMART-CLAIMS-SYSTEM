<?php
// Quick test to check if services API returns proper prices
echo "<h1>Services API Quick Test</h1>";

// Test the services API
$serviceUrl = "http://localhost/smartclaimsCLive/api/services-api.php?action=services&debug=1";

echo "<h2>Testing: $serviceUrl</h2>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $serviceUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    echo "<p style='color: red;'>❌ Failed to fetch services API</p>";
} else {
    echo "<p style='color: green;'>✅ HTTP Status: $httpCode</p>";
    
    $data = json_decode($response, true);
    if ($data) {
        echo "<h3>API Response Status: " . $data['status'] . "</h3>";
        
        if (isset($data['debug'])) {
            echo "<h3>Debug Information:</h3>";
            echo "<pre>" . print_r($data['debug'], true) . "</pre>";
        }
        
        if (isset($data['data'])) {
            echo "<h3>Services Summary:</h3>";
            foreach ($data['data'] as $category => $services) {
                echo "<h4>" . ucfirst($category) . " (" . count($services) . " services)</h4>";
                
                if (count($services) > 0) {
                    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
                    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Code</th><th>Tariff</th><th>Category</th></tr>";
                    
                    foreach (array_slice($services, 0, 5) as $service) {
                        $tariffColor = $service['tariff'] > 0 ? 'green' : 'red';
                        echo "<tr>";
                        echo "<td>" . $service['id'] . "</td>";
                        echo "<td>" . $service['name'] . "</td>";
                        echo "<td>" . $service['code'] . "</td>";
                        echo "<td style='color: $tariffColor; font-weight: bold;'>₵" . number_format($service['tariff'], 2) . "</td>";
                        echo "<td>" . $service['category'] . "</td>";
                        echo "</tr>";
                    }
                    
                    if (count($services) > 5) {
                        echo "<tr><td colspan='5' style='text-align: center; font-style: italic;'>... and " . (count($services) - 5) . " more</td></tr>";
                    }
                    
                    echo "</table>";
                }
            }
        }
        
        // Show raw JSON for debugging
        echo "<h3>Raw JSON Response:</h3>";
        echo "<textarea style='width: 100%; height: 200px;'>" . json_encode($data, JSON_PRETTY_PRINT) . "</textarea>";
        
    } else {
        echo "<p style='color: red;'>❌ Failed to decode JSON response</p>";
        echo "<h3>Raw Response:</h3>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
}

echo "<hr>";
echo "<p><a href='test_services_prices.html'>Test with HTML Interface</a> | <a href='api/access/service-requisition.php'>Go to Service Requisition</a></p>";
?>