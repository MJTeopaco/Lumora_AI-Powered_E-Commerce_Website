<?php
// app/Helpers/ValidationHelper.php

namespace App\Helpers;

class ValidationHelper {
    
    public static function validateRegistrationStep4($data) {
        $errors = [];
        
        if (empty($data['username'])) $errors[] = 'Username is required';
        if (empty($data['password'])) $errors[] = 'Password is required';
        if (empty($data['password_confirm'])) $errors[] = 'Please confirm your password';
        if ($data['password'] !== $data['password_confirm']) $errors[] = 'Passwords do not match';
        if (empty($data['terms'])) $errors[] = 'You must agree to the Terms & Conditions';

        if (!empty($data['username']) && !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $data['username'])) {
            $errors[] = 'Username must be 3-20 characters (letters, numbers, underscore only)';
        }

        if (!empty($data['password']) && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $data['password'])) {
            $errors[] = 'Password must contain at least 8 characters with uppercases, lowercases, and numbers';
        }
        
        return $errors;
    }

    public static function validatePasswordReset($data) {
        $errors = [];

        if (empty($data['password'])) $errors[] = 'Password is required';
        if (empty($data['password_confirm'])) $errors[] = 'Please confirm your password';
        if ($data['password'] !== $data['password_confirm']) $errors[] = 'Passwords do not match';

        if (!empty($data['password']) && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $data['password'])) {
            $errors[] = 'Password must contain at least 8 characters with uppercases, lowercases, and numbers';
        }
        
        return $errors;
    }

    public static function validateRecaptcha($recaptchaResponse) {
        define('RECAPTCHA_SECRET_KEY', '6Le_BgksAAAAAEGm96K_YzGFnYCQiLXs87k2b-OA');

        if (empty($recaptchaResponse)) {
            return false;
        }

        $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => RECAPTCHA_SECRET_KEY,
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($verifyURL, false, $context);
        $responseData = json_decode($result);

        return $responseData->success;
    }
}