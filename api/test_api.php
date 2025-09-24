<?php
/**
 * Simple API test script
 */

$testEndpoints = [
    'test' => '../claims-api.php?action=test',
    'get_claimable_consultations' => '../claims-api.php?action=get_claimable_consultations',
    'search_consultations' => '../claims-api.php?action=search_consultations&q=kwame'
];

echo "<h1>API Test Results</h1>";

foreach ($testEndpoints as $name => $url) {
    echo "<h3>Testing: $name</h3>";
    
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    if ($data) {
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        echo json_encode($data, JSON_PRETTY_PRINT);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>Failed to get response</p>";
        echo "<p>Raw response: " . htmlspecialchars($response) . "</p>";
    }
    
    echo "<hr>";
}
?>