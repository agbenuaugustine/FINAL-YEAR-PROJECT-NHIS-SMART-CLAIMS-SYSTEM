<?php
/**
 * JWT Handler
 * 
 * Handles JWT token generation and validation
 */
class JwtHandler {
    // Secret key for signing tokens
    private $secret_key = "SmartClaimsSecretKey2024";
    
    // Token validity period (in seconds)
    private $token_expiry = 86400; // 24 hours
    
    /**
     * Generate a JWT token
     * 
     * @param array $payload Data to encode in the token
     * @return string Generated JWT token
     */
    public function generateToken($payload) {
        // Create token header
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);
        
        // Add expiry time to payload
        $payload['exp'] = time() + $this->token_expiry;
        $payload['iat'] = time();
        
        // Encode header and payload
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        
        // Create signature
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret_key, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        return $jwt;
    }
    
    /**
     * Validate a JWT token
     * 
     * @param string $token JWT token to validate
     * @return mixed Decoded payload if valid, false otherwise
     */
    public function validateToken($token) {
        // Split token into parts
        $tokenParts = explode('.', $token);
        
        // Check if token has three parts
        if (count($tokenParts) != 3) {
            return false;
        }
        
        // Get header, payload and signature
        $header = $tokenParts[0];
        $payload = $tokenParts[1];
        $signatureProvided = $tokenParts[2];
        
        // Verify signature
        $signature = hash_hmac('sha256', $header . "." . $payload, $this->secret_key, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        // Check if signatures match
        if ($base64UrlSignature !== $signatureProvided) {
            return false;
        }
        
        // Decode payload
        $decodedPayload = json_decode($this->base64UrlDecode($payload));
        
        // Check if token has expired
        if (isset($decodedPayload->exp) && $decodedPayload->exp < time()) {
            return false;
        }
        
        return $decodedPayload;
    }
    
    /**
     * Base64Url encode
     * 
     * @param string $data Data to encode
     * @return string Base64Url encoded string
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64Url decode
     * 
     * @param string $data Data to decode
     * @return string Decoded data
     */
    private function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
?>