<?php
// app/Core/functions.php

if (!function_exists('base_url')) {
    function base_url($path = '') {
        // 1. Determine protocol
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $protocol = 'https';
        } elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }
        
        // 2. Determine Host (CRITICAL FIX FOR NGROK)
        // If Ngrok is forwarding the host, use that. Otherwise use the local host.
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else {
            $host = $_SERVER['HTTP_HOST'];
        }
        
        // 3. Determine script directory
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $dir = dirname($scriptName);
        
        // Normalize slashes
        $dir = str_replace('\\', '/', $dir);
        $dir = rtrim($dir, '/');
        
        // 4. Construct base URL
        $baseUrl = $protocol . "://" . $host . $dir;
        
        // 5. Append path
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

// Debug Helper (Optional but useful)
if (!function_exists('dd')) {
    function dd($data) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        die();
    }
}