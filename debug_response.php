<?php
// Let's see exactly what the login API is returning
$url = 'http://localhost/smartclaimsCL/api/login.php';
$data = json_encode(['username' => 'test', 'password' => 'test']);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $data
    ]
]);

echo "<h2>Login API Response Debug</h2>";
echo "<h3>Request Data:</h3>";
echo "<pre>" . htmlspecialchars($data) . "</pre>";

echo "<h3>Raw Response:</h3>";
$response = file_get_contents($url, false, $context);
echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<h3>Response Length:</h3>";
echo strlen($response) . " characters";

echo "<h3>First 10 characters (hex):</h3>";
for ($i = 0; $i < min(10, strlen($response)); $i++) {
    echo dechex(ord($response[$i])) . " ";
}

// Test the register API too
echo "<hr>";
echo "<h2>Register API Response Debug</h2>";
$url2 = 'http://localhost/smartclaimsCL/api/hospital-register.php';
$data2 = json_encode(['test' => 'data']);

$context2 = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $data2
    ]
]);

$response2 = file_get_contents($url2, false, $context2);
echo "<h3>Raw Response:</h3>";
echo "<pre>" . htmlspecialchars($response2) . "</pre>";
?>