<?php
// app/Helpers/EncryptionHelper.php

namespace App\Helpers;

class EncryptionHelper {
    
    private static $cipher = "AES-256-CBC";
    
    /**
     * Get encryption key from environment or generate one
     */
    private static function getKey() {
        // Store this in your .env file or config
        // NEVER commit this to version control
        $key = getenv('ENCRYPTION_KEY');
        
        if (!$key) {
            // Fallback - but you should set this in .env
            $key = 'd0edcd8cb2fc276ae11e7baac5b31998';
        }
        
        return $key;
    }
    
    /**
     * Encrypt sensitive data
     * 
     * @param string $data Plain text data to encrypt
     * @return string Encrypted data (base64 encoded)
     */
    public static function encrypt($data) {
        if (empty($data)) {
            return $data;
        }
        
        $key = self::getKey();
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        $encrypted = openssl_encrypt(
            $data,
            self::$cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        // Combine IV and encrypted data, then base64 encode
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     * 
     * @param string $encryptedData Encrypted data (base64 encoded)
     * @return string Decrypted plain text
     */
    public static function decrypt($encryptedData) {
        if (empty($encryptedData)) {
            return $encryptedData;
        }
        
        $key = self::getKey();
        $data = base64_decode($encryptedData);
        
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            self::$cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        return $decrypted;
    }
    
    /**
     * Mask sensitive data for display (show last 4 digits)
     * 
     * @param string $data Data to mask
     * @param int $visibleChars Number of characters to show at end
     * @return string Masked data
     */
    public static function mask($data, $visibleChars = 4) {
        if (empty($data) || strlen($data) <= $visibleChars) {
            return $data;
        }
        
        $maskLength = strlen($data) - $visibleChars;
        return str_repeat('*', $maskLength) . substr($data, -$visibleChars);
    }
    
    /**
     * Generate a secure encryption key (run once, store in .env)
     * 
     * @return string 32-character encryption key
     */
    public static function generateKey() {
        return bin2hex(random_bytes(16)); // 32 characters
    }
}