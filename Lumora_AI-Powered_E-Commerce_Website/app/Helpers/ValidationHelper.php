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
        define('RECAPTCHA_SECRET_KEY', '6LdpbwwsAAAAAOOGp4wXg-a0PJ1hg7sRgDkHmjGy');

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

    /**
     * Validate profile data
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validateProfile($data, $isUpdate = false) {
        $errors = [];
        
        // Full Name validation (optional but if provided, must be valid)
        if (!empty($data['full_name'])) {
            $fullName = trim($data['full_name']);
            
            if (strlen($fullName) < 2) {
                $errors['full_name'] = 'Full name must be at least 2 characters long';
            } elseif (strlen($fullName) > 255) {
                $errors['full_name'] = 'Full name must not exceed 255 characters';
            } elseif (!preg_match("/^[a-zA-Z\s\-\.]+$/u", $fullName)) {
                $errors['full_name'] = 'Full name can only contain letters, spaces, hyphens, and periods';
            }
        }
        
        // Phone Number validation (optional but if provided, must be valid)
        if (!empty($data['phone_number'])) {
            $phone = trim($data['phone_number']);
            
            // Remove common formatting characters
            $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
            
            // Formats: 09XXXXXXXXX, +639XXXXXXXXX, 639XXXXXXXXX, or landline formats
            if (!preg_match('/^(\+?63|0)?[89]\d{9}$/', $cleanPhone) && 
                !preg_match('/^(\+?63|0)?[2-9]\d{7,8}$/', $cleanPhone)) {
                $errors['phone_number'] = 'Please enter a valid phone number';
            }
        }
        
        // Gender validation (optional but if provided, must be valid)
        if (!empty($data['gender'])) {
            $validGenders = ['Male', 'Female', 'Other', 'Prefer not to say'];
            if (!in_array($data['gender'], $validGenders)) {
                $errors['gender'] = 'Please select a valid gender option';
            }
        }
        
        // Birth Date validation (optional but if provided, must be valid)
        if (!empty($data['birth_date'])) {
            $birthDate = $data['birth_date'];
            
            // Check if valid date format
            if (!self::isValidDate($birthDate)) {
                $errors['birth_date'] = 'Please enter a valid date in YYYY-MM-DD format';
            } else {
                // Check if date is not in the future
                $today = new \DateTime();
                $birth = new \DateTime($birthDate);
                
                if ($birth > $today) {
                    $errors['birth_date'] = 'Birth date cannot be in the future';
                }
                
                // Check minimum age (e.g., 13 years old)
                $age = $today->diff($birth)->y;
                if ($age < 13) {
                    $errors['birth_date'] = 'You must be at least 13 years old to use this platform';
                }
                
                // Check maximum age (e.g., 120 years - sanity check)
                if ($age > 120) {
                    $errors['birth_date'] = 'Please enter a valid birth date';
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate profile picture upload
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public static function validateProfilePicture($file) {
        // Check if file was uploaded
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['valid' => true, 'error' => null]; // No file uploaded, which is okay
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'File upload failed. Please try again.'];
        }
        
        // Check file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'Profile picture must be smaller than 5MB'];
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['valid' => false, 'error' => 'Profile picture must be a JPEG, PNG, GIF, or WebP image'];
        }
        
        // Check image dimensions (optional - max 2000x2000px)
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'error' => 'Invalid image file'];
        }
        
        list($width, $height) = $imageInfo;
        if ($width > 2000 || $height > 2000) {
            return ['valid' => false, 'error' => 'Profile picture dimensions must not exceed 2000x2000 pixels'];
        }
        
        return ['valid' => true, 'error' => null];
    }
    
    /**
     * Check if date is valid
     */
    private static function isValidDate($date, $format = 'Y-m-d') {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email format
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}