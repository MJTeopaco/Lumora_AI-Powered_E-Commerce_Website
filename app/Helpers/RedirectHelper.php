<?php
// app/Helpers/RedirectHelper.php

namespace App\Helpers;

class RedirectHelper {
    
    public static function redirect($path) {
        header("Location: {$path}");
        exit();
    }
    
    public static function redirectWithError($message, $tab, $step = '') {
        $params = [
            'status' => 'error',
            'message' => urlencode($message),
            'tab' => $tab,
            'step' => $step
        ];
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
        $url = '/?' . http_build_query($params); // Redirect to dashboard
        self::redirect($url);
    }
}