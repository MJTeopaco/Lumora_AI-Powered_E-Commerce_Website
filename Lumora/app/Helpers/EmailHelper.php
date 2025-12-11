<?php
namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {

    private static function configureMailer() {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'lumora.auth@gmail.com';
        $mail->Password   = getenv('MAIL_PASSWORD') ?: 'gxyp utms klzi pwuf'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom('lumora.auth@gmail.com', 'Lumora');
        return $mail;
    }

    /**
     * Load an email view and return the HTML content
     */
    private static function renderEmailView($viewPath, $data = []) {
        // Extract variables for use in the view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        require __DIR__ . '/../../app/Views/layouts/emails/' . $viewPath;
        
        // Get the buffered content and clean the buffer
        return ob_get_clean();
    }

    public static function sendRegistrationOTPEmail($email, $otp) {
        try {
            $mail = self::configureMailer();
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email - Lumora Registration';
            $mail->Body    = self::renderEmailView('registration-otp.php', ['otp' => $otp]);
            $mail->AltBody = "Welcome to Lumora! Your verification code is: $otp\nThis code will expire in 2 minutes.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public static function sendLoginOTPEmail($email, $otp, $username) {
        try {
            $mail = self::configureMailer();
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your Login Verification Code - Lumora';
            $mail->Body    = self::renderEmailView('login-otp.php', [
                'otp' => $otp,
                'username' => $username
            ]);
            $mail->AltBody = "Hello $username,\nYour login verification code is: $otp\nThis code will expire in 2 minutes.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public static function sendWelcomeEmail($email, $username) {
        try {
            $mail = self::configureMailer();
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Lumora!';
            $mail->Body    = self::renderEmailView('welcome.php', ['username' => $username]);
            $mail->AltBody = "Welcome to Lumora, $username!\nThank you for joining our community.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public static function sendForgotPasswordOTPEmail($email, $otp, $username) {
        try {
            $mail = self::configureMailer();
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Code - Lumora';
            $mail->Body    = self::renderEmailView('forgot-password-otp.php', [
                'otp' => $otp,
                'username' => $username
            ]);
            $mail->AltBody = "Hello $username,\nYour password reset code is: $otp\nThis code will expire in 2 minutes.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public static function sendPasswordChangedEmail($email, $username) {
        try {
            $mail = self::configureMailer();
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Changed Successfully - Lumora';
            $mail->Body    = self::renderEmailView('password-changed.php', ['username' => $username]);
            $mail->AltBody = "Hello $username,\nYour password has been successfully changed.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }
    public static function sendRegistrationActivationEmail($email, $token) {
    try {
        $mail = self::configureMailer();
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Activate Your Lumora Account';
        
$activationLink = "http://lumora.test/auth/verify-email?token=" . $token;        $mail->Body = self::renderEmailView('registration-activation.php', [
            'activationLink' => $activationLink
        ]);
        $mail->AltBody = "Welcome to Lumora! Click this link to activate your account: $activationLink (valid for 24 hours)";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        return false;
    }
}
}