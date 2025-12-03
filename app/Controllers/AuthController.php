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
use App\Core\Controller;
use DateTime;

class AuthController extends Controller {
    
    protected $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function showLogin() {
        if (Session::has('user_id')) {
            RedirectHelper::redirect('/');
        }
        
        $tokenModel = new RememberMeToken();
        if ($tokenModel->validate()) {
            RedirectHelper::redirect('/');
        }

        $data = [
            'status' => $_GET['status'] ?? null,
            'message' => isset($_GET['message']) ? urldecode($_GET['message']) : null,
            'tab' => $_GET['tab'] ?? 'login',
            'step' => $_GET['step'] ?? 'credentials',
        ];
        
        require __DIR__ . '/../../app/Views/layouts/auth/login.view.php';
    }

    public function handleLoginStep1() {
        $this->verifyCsrfToken(); // CSRF Check

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
                Session::set('login_otp_attempts', 0);

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
                    RedirectHelper::redirectWithError("Invalid credentials.", 'login', 'credentials');
                }
            }
        } else {
            RedirectHelper::redirectWithError('Invalid credentials', 'login', 'credentials');
        }
    }

    public function handleLoginStep2() {
        $this->verifyCsrfToken(); // CSRF Check

        $otp = Request::post('otp');

        if (empty($otp) || !preg_match('/^[0-9]{6}$/', $otp)) {
            RedirectHelper::redirectWithError('OTP must be 6 digits.', 'login', 'otp');
        }
        if (!Session::has('login_otp') || !Session::has('login_user_id_pending')) {
            RedirectHelper::redirectWithError('Session expired. Please login again.', 'login', 'credentials');
        }
        if (time() > Session::get('login_otp_expiry')) {
            Session::unset('login_otp');
            Session::unset('login_otp_attempts');
            RedirectHelper::redirectWithError('OTP expired. Please login again.', 'login', 'credentials');
        }

        $attempts = (int) Session::get('login_otp_attempts', 0);
        if ($attempts >= 3) {
            Session::unset('login_user_id_pending');
            Session::unset('login_remember_me_pending');
            Session::unset('login_username_pending');
            Session::unset('login_otp');
            Session::unset('login_otp_expiry');
            Session::unset('login_otp_attempts');
            RedirectHelper::redirectWithError('Too many invalid OTP attempts. Please try again.', 'login', 'credentials');
        }

        if ($otp != Session::get('login_otp')) {
            Session::set('login_otp_attempts', $attempts + 1);
            $remaining = 3 - ($attempts + 1);
            RedirectHelper::redirectWithError("Invalid OTP.", 'login', 'otp');
        }

        Session::regenerate();
        Session::set('user_id', Session::get('login_user_id_pending'));
        Session::set('username', Session::get('login_username_pending'));
        Session::set('last_activity', time()); 

        if (Session::get('login_remember_me_pending') === true) {
            $tokenModel = new RememberMeToken();
            $tokenModel->create(Session::get('user_id'));
        }

        Session::unset('login_user_id_pending');
        Session::unset('login_remember_me_pending');
        Session::unset('login_username_pending');
        Session::unset('login_otp');
        Session::unset('login_otp_expiry');
        Session::unset('login_otp_attempts');

        $role = $this->userModel->getUserRoles(Session::get('user_id'));
        
        if (in_array('admin', $role)) {
            RedirectHelper::redirect('/admin/dashboard'); 
        } else {
            RedirectHelper::redirect('/'); 
        }
    }

    public function handleRegisterStep1() {
        $this->verifyCsrfToken(); // CSRF Check

        $email = Request::post('email');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            RedirectHelper::redirectWithError('Please enter a valid email address', 'register', 'email');
        }
        
        if ($this->userModel->findByEmail($email)) {
             RedirectHelper::redirectWithError('This email is already registered. Please login.', 'register', 'email');
        }

        $token = bin2hex(random_bytes(32));
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+3 minutes'));
        
        Session::set('reg_email', $email);
        Session::set('reg_token', $token);
        Session::set('reg_token_expiry', $tokenExpiry);

        if (EmailHelper::sendRegistrationActivationEmail($email, $token)) {
            RedirectHelper::redirectWithSuccess('Activation link sent! Please check your email.', 'register', 'pending');
        } else {
            RedirectHelper::redirectWithError("Couldn't send activation email. Please try again.", 'register', 'email');
        }
    }

    public function verifyEmail() {
        // GET request, no CSRF token needed for verification link usually, but token serves as validation
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            RedirectHelper::redirectWithError('Invalid verification link', 'register', 'email');
        }
        
        if (!Session::has('reg_token') || Session::get('reg_token') !== $token) {
            RedirectHelper::redirectWithError('Invalid or expired verification link', 'register', 'email');
        }
        
        if (!Session::has('reg_token_expiry') || time() > strtotime(Session::get('reg_token_expiry'))) {
            Session::unset('reg_email');
            Session::unset('reg_token');
            Session::unset('reg_token_expiry');
            RedirectHelper::redirectWithError('Verification link expired. Please start over.', 'register', 'email');
        }
        
        Session::set('reg_email_verified', true);
        RedirectHelper::redirectWithSuccess('Email verified! Please complete the security check.', 'register', 'captcha');
    }

    public function handleRegisterStep3() {
        $this->verifyCsrfToken(); // CSRF Check

        if (!Session::get('reg_email_verified')) {
            RedirectHelper::redirectWithError('Please verify your email first.', 'register', 'email');
        }

        $recaptchaResponse = Request::post('g-recaptcha-response');
        
        if (!ValidationHelper::validateRecaptcha($recaptchaResponse)) {
             RedirectHelper::redirectWithError('reCAPTCHA verification failed. Please try again.', 'register', 'captcha');
        }

        Session::set('reg_captcha_verified', true);
        RedirectHelper::redirectWithSuccess('Security check passed! Almost done.', 'register', 'details');
    }

    public function handleRegisterStep4() {
        $this->verifyCsrfToken(); // CSRF Check

        if (!Session::get('reg_email_verified') || !Session::get('reg_captcha_verified')) {
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
            $user = $this->userModel->findByEmail($email);
            $this->userModel->markEmailVerified($user['user_id']);
            
            EmailHelper::sendWelcomeEmail($email, $data['username']);
            
            Session::unset('reg_email');
            Session::unset('reg_token');
            Session::unset('reg_token_expiry');
            Session::unset('reg_email_verified');
            Session::unset('reg_captcha_verified');

            RedirectHelper::redirectWithSuccess('Registration successful! Please login.', 'login', 'credentials');
        } else {
            RedirectHelper::redirectWithError('Registration failed. Please try again.', 'register', 'details');
        }
    }
    
    public function handleForgotStep1() {
        $this->verifyCsrfToken(); // CSRF Check
        
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
        Session::set('forgot_otp_attempts', 0);

        if (EmailHelper::sendForgotPasswordOTPEmail($email, $otp, $user['username'])) {
            RedirectHelper::redirectWithSuccess('Password reset code sent to your email.', 'forgot', 'otp');
        } else {
            RedirectHelper::redirectWithError("Couldn't send reset code. Please try again.", 'forgot', 'email');
        }
    }

    public function handleForgotStep2() {
        $this->verifyCsrfToken(); // CSRF Check

        $otp = Request::post('otp');
        if (empty($otp) || !preg_match('/^[0-9]{6}$/', $otp)) {
            RedirectHelper::redirectWithError('OTP must be 6 digits.', 'forgot', 'otp');
        }
        if (!Session::has('forgot_otp') || !Session::has('forgot_email')) {
            RedirectHelper::redirectWithError('Session expired. Please start over.', 'forgot', 'email');
        }
        if (time() > Session::get('forgot_otp_expiry')) {
            Session::unset('forgot_otp');
            Session::unset('forgot_otp_attempts');
            RedirectHelper::redirectWithError('OTP expired. Please start over.', 'forgot', 'email');
        }

        $attempts = (int) Session::get('forgot_otp_attempts', 0);
        if ($attempts >= 3) {
            Session::unset('forgot_email');
            Session::unset('forgot_user_id');
            Session::unset('forgot_username');
            Session::unset('forgot_otp');
            Session::unset('forgot_otp_expiry');
            Session::unset('forgot_otp_attempts');
            RedirectHelper::redirectWithError('Too many invalid OTP attempts. Please start over.', 'forgot', 'email');
        }

        if ($otp != Session::get('forgot_otp')) {
            Session::set('forgot_otp_attempts', $attempts + 1);
            $remaining = 3 - ($attempts + 1);
            RedirectHelper::redirectWithError("Invalid OTP.", 'forgot', 'otp');
        }

        Session::unset('forgot_otp');
        Session::unset('forgot_otp_expiry');
        Session::unset('forgot_otp_attempts');
        Session::set('forgot_otp_verified', true);

        RedirectHelper::redirectWithSuccess('Code verified! Create your new password.', 'forgot', 'reset');
    }

    public function handleForgotStep3() {
        $this->verifyCsrfToken(); // CSRF Check

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
            
            Session::unset('forgot_email');
            Session::unset('forgot_user_id');
            Session::unset('forgot_username');
            Session::unset('forgot_otp_verified');

            RedirectHelper::redirectWithSuccess('Password reset successful! Please login.', 'login', 'credentials');
        } else {
            RedirectHelper::redirectWithError('Failed to reset password. Please try again.', 'forgot', 'reset');
        }
    }
    
    private function checkResendCooldown($sessionKey) {
        if (Session::has($sessionKey)) {
            $timeElapsed = time() - Session::get($sessionKey);
            if ($timeElapsed < 120) {
                return (120 - $timeElapsed);
            }
        }
        return 0;
    }
    
    private function jsonResponse($success, $message) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit();
    }
    
    // Note: Resend logic typically via AJAX, might not send CSRF in all setups, 
    // but standard practice is to include it in headers. For now, assuming simple fetch without CSRF on these read-only/safe ops or add it.
    // Since these send emails (action), they SHOULD be protected.
    // You'll need to update JS to send CSRF token for these too.
    public function resendLoginOtp() {
        // $this->verifyCsrfToken(); // Enable if JS is updated to send token
        
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
        Session::set('login_otp_attempts', 0);
        
        if (EmailHelper::sendLoginOTPEmail($user['email'], $otp, $user['username'])) {
            $this->jsonResponse(true, 'A new code has been sent.');
        } else {
            $this->jsonResponse(false, 'Failed to send code. Try again.');
        }
    }

    public function resendForgotOtp() {
        // $this->verifyCsrfToken(); // Enable if JS is updated
        
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
        Session::set('forgot_otp_attempts', 0);
        
        if (EmailHelper::sendForgotPasswordOTPEmail(Session::get('forgot_email'), $otp, Session::get('forgot_username'))) {
            $this->jsonResponse(true, 'A new code has been sent.');
        } else {
            $this->jsonResponse(false, 'Failed to send code. Try again.');
        }
    }

    public function logout() {
        // Logout should be protected
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/');
            return;
        }
        
        $userId = Session::get('user_id');
        
        if (isset($_COOKIE['remember_me'])) {
            $tokenModel = new RememberMeToken();
            $tokenModel->deleteBySelector($_COOKIE['remember_me']);
            
            setcookie('remember_me', '', time() - 3600, '/', '', false, true);
        }
        
        Session::destroy();
        
        RedirectHelper::redirect('/?status=success&message=' . urlencode('You have been logged out successfully'));
    }
}