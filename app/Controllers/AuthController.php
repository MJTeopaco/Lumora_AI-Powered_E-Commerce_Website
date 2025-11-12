<?php
// app/Controllers/AuthController.php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Session;
use App\Helpers\EmailHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\ValidationHelper;
use App\Models\User;
use App\Models\RememberMeToken;
use DateTime;

class AuthController extends \App\Core\Controller {
    
    protected $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Show the login/register page.
     */
    public function showLogin() {
        // If already logged in, redirect to dashboard
        if (Session::has('user_id')) {
            RedirectHelper::redirect('/');
        }
        
        // Check for remember me cookie
        $tokenModel = new RememberMeToken();
        if ($tokenModel->validate()) {
            RedirectHelper::redirect('/'); // Validation successful, redirect
        }

        // Pass any error/success messages from URL
        $data = [
            'status' => $_GET['status'] ?? null,
            'message' => isset($_GET['message']) ? urldecode($_GET['message']) : null,
            'tab' => $_GET['tab'] ?? 'login',
            'step' => $_GET['step'] ?? 'credentials',
        ];
        
        // Load the view
        require_once __DIR__ . '/../../views/auth/login.php';
    }

    /**
     * Handle Login - Step 1: Credentials
     */
    public function handleLoginStep1() {
        $identifier = Request::post('identifier');
        $password = Request::post('password');
        $remember_me = Request::post('remember_me') === 'on';

        if (empty($identifier) || empty($password)) {
            RedirectHelper::redirectWithError('Email/Username and Password are required.', 'login', 'credentials');
        }

        $user = $this->userModel->findByIdentifier($identifier);

        if ($user) {
            if ($user['lockout_until'] && new DateTime() < new DateTime($user['lockout_until'])) {
                $lockout_time = (new DateTime($user['lockout_until']))->format('g:i A');
                RedirectHelper::redirectWithError("Account locked. Please try again after $lockout_time.", 'login', 'credentials');
            }

            if (password_verify($password, $user['password'])) {
                $this->userModel->resetLoginAttempts($user['user_id']);

                $otp = rand(100000, 999999);
                Session::set('login_user_id_pending', $user['user_id']);
                Session::set('login_remember_me_pending', $remember_me);
                Session::set('login_username_pending', $user['username']);
                Session::set('login_otp', $otp);
                Session::set('login_otp_expiry', time() + 120);

                if(EmailHelper::sendLoginOTPEmail($user['email'], $otp, $user['username'])) {
                    RedirectHelper::redirectWithSuccess('Verification code sent to your email.', 'login', 'otp');
                } else {
                    RedirectHelper::redirectWithError("Couldn't send verification code. Try again.", 'login', 'credentials');
                }
            } else {
                $new_attempts = $user['failed_login_attempts'] + 1;
                $isLocked = $this->userModel->incrementLoginAttempts($user['user_id'], $new_attempts);
                
                if ($isLocked) {
                    RedirectHelper::redirectWithError('Account locked for 15 minutes.', 'login', 'credentials');
                } else {
                    $attempts_remaining = 3 - $new_attempts;
                    RedirectHelper::redirectWithError("Invalid credentials. $attempts_remaining attempt(s) remaining.", 'login', 'credentials');
                }
            }
        } else {
            RedirectHelper::redirectWithError('Invalid credentials', 'login', 'credentials');
        }
    }

    /**
     * Handle Login - Step 2: Verify OTP
     */
    public function handleLoginStep2() {
        $otp = Request::post('otp');

        if (empty($otp) || !preg_match('/^[0-9]{6}$/', $otp)) {
            RedirectHelper::redirectWithError('OTP must be 6 digits.', 'login', 'otp');
        }
        if (!Session::has('login_otp') || !Session::has('login_user_id_pending')) {
            RedirectHelper::redirectWithError('Session expired. Please login again.', 'login', 'credentials');
        }
        if (time() > Session::get('login_otp_expiry')) {
            Session::unset('login_otp');
            RedirectHelper::redirectWithError('OTP expired. Please login again.', 'login', 'credentials');
        }
        if ($otp != Session::get('login_otp')) {
            RedirectHelper::redirectWithError('Invalid OTP.', 'login', 'otp');
        }

        Session::regenerate();
        Session::set('user_id', Session::get('login_user_id_pending'));
        Session::set('username', Session::get('login_username_pending'));

        if (Session::get('login_remember_me_pending') === true) {
            $tokenModel = new RememberMeToken();
            $tokenModel->create(Session::get('user_id'));
        }

        // Clean up pending session data
        Session::unset('login_user_id_pending');
        Session::unset('login_remember_me_pending');
        Session::unset('login_username_pending');
        Session::unset('login_otp');
        Session::unset('login_otp_expiry');

        RedirectHelper::redirect('/'); // Redirect to dashboard
    }

    /**
     * Handle Register - Step 1: Email
     */
    public function handleRegisterStep1() {
        $email = Request::post('email');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            RedirectHelper::redirectWithError('Please enter a valid email address', 'register', 'email');
        }
        
        if ($this->userModel->findByEmail($email)) {
             RedirectHelper::redirectWithError('This email is already registered. Please login.', 'register', 'email');
        }

        $otp = rand(100000, 999999);
        Session::set('reg_email', $email);
        Session::set('reg_otp', $otp);
        Session::set('reg_otp_expiry', time() + 120);

        if (EmailHelper::sendRegistrationOTPEmail($email, $otp)) {
            RedirectHelper::redirectWithSuccess('Verification code sent! Please check your email.', 'register', 'otp');
        } else {
            RedirectHelper::redirectWithError("Couldn't send verification code. Please try again.", 'register', 'email');
        }
    }

    /**
     * Handle Register - Step 2: Verify OTP
     */
    public function handleRegisterStep2() {
        $otp = Request::post('otp');

        if (empty($otp) || !preg_match('/^[0-9]{6}$/', $otp)) {
            RedirectHelper::redirectWithError('OTP must be 6 digits.', 'register', 'otp');
        }
        if (!Session::has('reg_otp') || !Session::has('reg_email')) {
            RedirectHelper::redirectWithError('Session expired. Please start over.', 'register', 'email');
        }
        if (time() > Session::get('reg_otp_expiry')) {
            Session::unset('reg_otp');
            RedirectHelper::redirectWithError('OTP expired. Please start over.', 'register', 'email');
        }
        if ($otp != Session::get('reg_otp')) {
            RedirectHelper::redirectWithError('Invalid OTP.', 'register', 'otp');
        }

        Session::unset('reg_otp');
        Session::unset('reg_otp_expiry');
        Session::set('reg_otp_verified', true);
        
        RedirectHelper::redirectWithSuccess('Email verified! Please complete the security check.', 'register', 'captcha');
    }

    /**
     * Handle Register - Step 3: Verify reCAPTCHA
     */
    public function handleRegisterStep3() {
        if (!Session::get('reg_otp_verified')) {
            RedirectHelper::redirectWithError('Session expired. Please start over.', 'register', 'email');
        }

        $recaptchaResponse = Request::post('g-recaptcha-response');
        
        if (!ValidationHelper::validateRecaptcha($recaptchaResponse)) {
             RedirectHelper::redirectWithError('reCAPTCHA verification failed. Please try again.', 'register', 'captcha');
        }

        Session::set('reg_captcha_verified', true);
        RedirectHelper::redirectWithSuccess('Security check passed! Almost done.', 'register', 'details');
    }

    /**
     * Handle Register - Step 4: Finalize Details
     */
    public function handleRegisterStep4() {
        if (!Session::get('reg_otp_verified') || !Session::get('reg_captcha_verified')) {
            RedirectHelper::redirectWithError('Session expired. Please start over.', 'register', 'email');
        }

        $data = Request::allPost();
        $email = Session::get('reg_email');
        $errors = ValidationHelper::validateRegistrationStep4($data);

        if (!empty($errors)) {
            RedirectHelper::redirectWithError(implode('|', $errors), 'register', 'details');
        }

        if ($this->userModel->findByUsername($data['username'])) {
            RedirectHelper::redirectWithError('This username is already taken.', 'register', 'details');
        }

        if ($this->userModel->create($data['username'], $email, $data['password'])) {
            EmailHelper::sendWelcomeEmail($email, $data['username']);
            
            // Clean up session
            Session::unset('reg_email');
            Session::unset('reg_otp_verified');
            Session::unset('reg_captcha_verified');

            RedirectHelper::redirectWithSuccess('Registration successful! Please login.', 'login', 'credentials');
        } else {
            RedirectHelper::redirectWithError('Registration failed. Please try again.', 'register', 'details');
        }
    }
    
    /**
     * Handle Forgot Password - Step 1: Email
     */
    public function handleForgotStep1() {
        $email = Request::post('email');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            RedirectHelper::redirectWithError('Please enter a valid email address', 'forgot', 'email');
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            RedirectHelper::redirectWithError('No account found with this email.', 'forgot', 'email');
        }

        $otp = rand(100000, 999999);
        Session::set('forgot_email', $email);
        Session::set('forgot_user_id', $user['user_id']);
        Session::set('forgot_username', $user['username']);
        Session::set('forgot_otp', $otp);
        Session::set('forgot_otp_expiry', time() + 120);

        if (EmailHelper::sendForgotPasswordOTPEmail($email, $otp, $user['username'])) {
            RedirectHelper::redirectWithSuccess('Password reset code sent to your email.', 'forgot', 'otp');
        } else {
            RedirectHelper::redirectWithError("Couldn't send reset code. Please try again.", 'forgot', 'email');
        }
    }

    /**
     * Handle Forgot Password - Step 2: Verify OTP
     */
    public function handleForgotStep2() {
        $otp = Request::post('otp');
        if (empty($otp) || !preg_match('/^[0-9]{6}$/', $otp)) {
            RedirectHelper::redirectWithError('OTP must be 6 digits.', 'forgot', 'otp');
        }
        if (!Session::has('forgot_otp') || !Session::has('forgot_email')) {
            RedirectHelper::redirectWithError('Session expired. Please start over.', 'forgot', 'email');
        }
        if (time() > Session::get('forgot_otp_expiry')) {
            Session::unset('forgot_otp');
            RedirectHelper::redirectWithError('OTP expired. Please start over.', 'forgot', 'email');
        }
        if ($otp != Session::get('forgot_otp')) {
            RedirectHelper::redirectWithError('Invalid OTP.', 'forgot', 'otp');
        }

        Session::unset('forgot_otp');
        Session::unset('forgot_otp_expiry');
        Session::set('forgot_otp_verified', true);

        RedirectHelper::redirectWithSuccess('Code verified! Create your new password.', 'forgot', 'reset');
    }

    /**
     * Handle Forgot Password - Step 3: Reset Password
     */
    public function handleForgotStep3() {
        if (!Session::get('forgot_otp_verified') || !Session::get('forgot_user_id')) {
            RedirectHelper::redirectWithError('Session expired. Please start over.', 'forgot', 'email');
        }

        $data = Request::allPost();
        $errors = ValidationHelper::validatePasswordReset($data);

        if (!empty($errors)) {
            RedirectHelper::redirectWithError(implode('|', $errors), 'forgot', 'reset');
        }

        if ($this->userModel->updatePassword(Session::get('forgot_user_id'), $data['password'])) {
            EmailHelper::sendPasswordChangedEmail(Session::get('forgot_email'), Session::get('forgot_username'));
            
            // Clean up session
            Session::unset('forgot_email');
            Session::unset('forgot_user_id');
            Session::unset('forgot_username');
            Session::unset('forgot_otp_verified');

            RedirectHelper::redirectWithSuccess('Password reset successful! Please login.', 'login', 'credentials');
        } else {
            RedirectHelper::redirectWithError('Failed to reset password. Please try again.', 'forgot', 'reset');
        }
    }
    
    // --- AJAX OTP RESEND HANDLERS ---
    
    private function checkResendCooldown($sessionKey) {
        if (Session::has($sessionKey)) {
            $timeElapsed = time() - Session::get($sessionKey);
            if ($timeElapsed < 120) {
                return (120 - $timeElapsed); // Return remaining seconds
            }
        }
        return 0; // No cooldown
    }
    
    private function jsonResponse($success, $message) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit();
    }
    
    public function resendLoginOtp() {
        if (!Session::has('login_user_id_pending')) {
            $this->jsonResponse(false, 'Session expired. Please login again.');
        }
        if ($remaining = $this->checkResendCooldown('login_otp_resend_time')) {
            $this->jsonResponse(false, "Please wait " . ceil($remaining / 60) . " more minute(s).");
        }
        
        $user = $this->userModel->findById(Session::get('login_user_id_pending'));
        $otp = rand(100000, 999999);
        Session::set('login_otp', $otp);
        Session::set('login_otp_expiry', time() + 120);
        Session::set('login_otp_resend_time', time());
        
        if (EmailHelper::sendLoginOTPEmail($user['email'], $otp, $user['username'])) {
            $this->jsonResponse(true, 'A new code has been sent.');
        } else {
            $this->jsonResponse(false, 'Failed to send code. Try again.');
        }
    }

    public function resendRegisterOtp() {
        if (!Session::has('reg_email')) {
            $this->jsonResponse(false, 'Session expired. Please start over.');
        }
        if ($remaining = $this->checkResendCooldown('reg_otp_resend_time')) {
            $this->jsonResponse(false, "Please wait " . ceil($remaining / 60) . " more minute(s).");
        }
        
        $otp = rand(100000, 999999);
        Session::set('reg_otp', $otp);
        Session::set('reg_otp_expiry', time() + 120);
        Session::set('reg_otp_resend_time', time());
        
        if (EmailHelper::sendRegistrationOTPEmail(Session::get('reg_email'), $otp)) {
            $this->jsonResponse(true, 'A new code has been sent.');
        } else {
            $this->jsonResponse(false, 'Failed to send code. Try again.');
        }
    }
    
    public function resendForgotOtp() {
        if (!Session::has('forgot_email')) {
            $this->jsonResponse(false, 'Session expired. Please start over.');
        }
        if ($remaining = $this->checkResendCooldown('forgot_otp_resend_time')) {
            $this->jsonResponse(false, "Please wait " . ceil($remaining / 60) . " more minute(s).");
        }
        
        $otp = rand(100000, 999999);
        Session::set('forgot_otp', $otp);
        Session::set('forgot_otp_expiry', time() + 120);
        Session::set('forgot_otp_resend_time', time());
        
        if (EmailHelper::sendForgotPasswordOTPEmail(Session::get('forgot_email'), $otp, Session::get('forgot_username'))) {
            $this->jsonResponse(true, 'A new code has been sent.');
        } else {
            $this->jsonResponse(false, 'Failed to send code. Try again.');
        }
    }
}