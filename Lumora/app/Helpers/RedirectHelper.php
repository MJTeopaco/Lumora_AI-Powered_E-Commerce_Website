<?php
// app/Helpers/RedirectHelper.php

namespace App\Helpers;

class RedirectHelper {
    
    /**
     * Redirect to a specific path using base_url() to handle subfolders
     */
    public static function redirect($path) {
        // 1. Check if the path is already a full URL (e.g., http://google.com)
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            header("Location: {$path}");
        } else {
            // 2. If it's a relative path, wrap it in base_url()
            // This ensures '/login' becomes 'http://ngrok.../public/login'
            $url = base_url($path);
            header("Location: {$url}");
        }
        exit();
    }
    
    public static function redirectWithError($message, $tab, $step = '') {
        $params = [
            'status' => 'error',
            'message' => urlencode($message),
            'tab' => $tab,
            'step' => $step
        ];
        // Note: We pass the relative path here. The redirect() method above will fix it.
        $url = '/login?' . http_build_query($params);
        self::redirect($url);
    }

    public static function redirectWithSuccess($message, $tab, $step = '') {
        $params = [
            'status' => 'success',
            'message' => urlencode($message),
            'tab' => $tab,
            'step' => $step
        ];
        $url = '/login?' . http_build_query($params);
        self::redirect($url);
    }

    public static function redirectToIndexSuccess($message) {
        $params = [
            'status' => 'success',
            'message' => urlencode($message)
        ];
        $url = '/?' . http_build_query($params);
        self::redirect($url);
    }
}