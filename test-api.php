<!DOCTYPE html>
<html>
<head>
    <title>Claims API Test</title>
</head>
<body>
    <h1>Claims API Test</h1>
    <div id="results"></div>
    
    <script>
        async function testAPI() {
            const results = document.getElementById('results');
            
            try {
                results.innerHTML += '<p>Testing Claims API...</p>';
                
                // Test 1: Get claimable consultations
                const response1 = await fetch('api/claims-api.php?action=get_claimable_consultations');
                const result1 = await response1.json();
                results.innerHTML += `<h3>Test 1 - Get Claimable Consultations:</h3><pre>${JSON.stringify(result1, null, 2)}</pre>`;
                
                // Test 2: Get analytics
                const response2 = await fetch('api/claims-api.php?action=get_claims_analytics');
                const result2 = await response2.json();
                results.innerHTML += `<h3>Test 2 - Get Analytics:</h3><pre>${JSON.stringify(result2, null, 2)}</pre>`;
                
                // Test 3: Get claims
                const response3 = await fetch('api/claims-api.php?action=get_claims');
                const result3 = await response3.json();
                results.innerHTML += `<h3>Test 3 - Get Claims:</h3><pre>${JSON.stringify(result3, null, 2)}</pre>`;
                
            } catch (error) {
                results.innerHTML += `<p style="color: red;">Error: ${error.message}</p>`;
                console.error('API Test Error:', error);
            }
        }
        
        // Run test when page loads
        window.onload = testAPI;
    </script>
</body>
</html>