<?php
/**
 * auth.php - Enhanced Authentication Handler
 * Improved registration flow with better email templates and messaging
 * CAPTCHA step (Step 3) is present but verification is bypassed.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';
session_start();
require_once 'db_connect.php';

$errors = [];

define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_TIME_MINUTES', 15);
define('RECAPTCHA_SECRET_KEY', '6LflqgUsAAAAAOKtr20pv9DJAa1EXZEIOBA3y4lb');


/**
 * Main Controller
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'login_step_1_credentials':
            handleLoginStep1_Credentials();
            break;
        case 'login_step_2_otp':
            handleLoginStep2_VerifyOTP();
            break;
        case 'register_step_1_email':
            handleRegisterStep1_SendOTP();
            break;
        case 'register_step_2_otp':
            handleRegisterStep2_VerifyOTP();
            break;
        case 'register_step_3_captcha':
            handleRegisterStep3_VerifyCAPTCHA();
            break;
        case 'register_step_4_details':
            handleRegisterStep4_Finalize();
            break;
             case 'resend_login_otp':
            handleResendLoginOTP();
            break;
        case 'resend_register_otp':
            handleResendRegisterOTP();
            break;
            case 'forgot_password_step_1_email':
    handleForgotPasswordStep1_SendOTP();
    break;
case 'forgot_password_step_2_verify':
    handleForgotPasswordStep2_VerifyOTP();
    break;
case 'forgot_password_step_3_reset':
    handleForgotPasswordStep3_ResetPassword();
    break;
case 'resend_forgot_password_otp':
    handleResendForgotPasswordOTP();
    break;
        default:
            redirectWithError('Invalid action', 'login', 'credentials');
    }
} else {
    header('Location: ../../frontend/pages/login.html');
    exit();
}

/**
 * Handle Login - Step 1: Credentials & Lockout
 */
function handleLoginStep1_Credentials() {
    global $conn, $errors;
    
    $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] === 'on';


    if (empty($identifier)) $errors[] = 'Email or username is required';
    if (empty($password)) $errors[] = 'Password is required';

    if (!empty($errors)) {
        // Use '|' delimiter for multi-error list
        redirectWithError(implode('|', $errors), 'login', 'credentials');
    }
    
    $identifier = htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8');
    
    try {
        $stmt = $conn->prepare("SELECT user_id, username, email, password, failed_login_attempts, lockout_until FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($user['lockout_until'] && new DateTime() < new DateTime($user['lockout_until'])) {
                $lockout_time = (new DateTime($user['lockout_until']))->format('g:i A');
                redirectWithError("Account locked due to too many failed attempts. Please try again after $lockout_time.", 'login', 'credentials');
            }

            if (password_verify($password, $user['password'])) {
                $stmt_reset = $conn->prepare("UPDATE users SET failed_login_attempts = 0, lockout_until = NULL WHERE user_id = ?");
                $stmt_reset->bind_param("i", $user['user_id']);
                $stmt_reset->execute();
                $stmt_reset->close();

                $otp = rand(100000, 999999);
                $otp_expiry = time() + (2 * 60);

                $_SESSION['login_user_id_pending'] = $user['user_id'];
                $_SESSION['login_remember_me_pending'] = $remember_me;
                $_SESSION['login_username_pending'] = $user['username'];
                $_SESSION['login_otp'] = $otp;
                $_SESSION['login_otp_expiry'] = $otp_expiry;

                if(sendLoginOTPEmail($user['email'], $otp, $user['username'])) {
                    redirectWithSuccess('We\'ve sent a verification code to your email. Please check your inbox.', 'login', 'otp');
                } else {
                    redirectWithError("We couldn't send a verification code. Please try again.", 'login', 'credentials');
                }

            } else {
                $new_attempts = $user['failed_login_attempts'] + 1;
                
                // Logic for MAX_LOGIN_ATTEMPTS (3) is correct
                if ($new_attempts >= MAX_LOGIN_ATTEMPTS) {
                    $lockout_expiry = (new DateTime())->add(new DateInterval('PT' . LOCKOUT_TIME_MINUTES . 'M'))->format('Y-m-d H:i:s');
                    $stmt_lock = $conn->prepare("UPDATE users SET failed_login_attempts = ?, lockout_until = ? WHERE user_id = ?");
                    $stmt_lock->bind_param("isi", $new_attempts, $lockout_expiry, $user['user_id']);
                    $stmt_lock->execute();
                    $stmt_lock->close();
                    
                    redirectWithError('Invalid credentials. Your account has been locked for ' . LOCKOUT_TIME_MINUTES . ' minutes.', 'login', 'credentials');
                
                } else {
                    $stmt_inc = $conn->prepare("UPDATE users SET failed_login_attempts = ? WHERE user_id = ?");
                    $stmt_inc->bind_param("ii", $new_attempts, $user['user_id']);
                    $stmt_inc->execute();
                    $stmt_inc->close();

                    $attempts_remaining = MAX_LOGIN_ATTEMPTS - $new_attempts;
                    redirectWithError("Invalid credentials. $attempts_remaining attempt(s) remaining.", 'login', 'credentials');
                }
            }
        } else {
            redirectWithError('Invalid credentials', 'login', 'credentials');
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log('Login Step 1 error: ' . $e->getMessage());
        redirectWithError('An error occurred. Please try again.', 'login', 'credentials');
    }
}

/**
 * Handle Login - Step 2: Verify OTP
 */
function handleLoginStep2_VerifyOTP() {
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

    if (empty($otp)) {
        redirectWithError('Please enter the verification code.', 'login', 'otp');
    }
    if (!preg_match('/^[0-9]{6}$/', $otp)) {
        redirectWithError('Verification code must be 6 digits.', 'login', 'otp');
    }
    if (!isset($_SESSION['login_otp']) || !isset($_SESSION['login_otp_expiry']) || !isset($_SESSION['login_user_id_pending'])) {
        redirectWithError('Your session has expired. Please login again.', 'login', 'credentials');
    }
    if (time() > $_SESSION['login_otp_expiry']) {
        unset($_SESSION['login_otp'], $_SESSION['login_otp_expiry'], $_SESSION['login_user_id_pending'], $_SESSION['login_username_pending']);
        redirectWithError('Your verification code has expired. Please login again.', 'login', 'credentials');
    }
    if ($otp != $_SESSION['login_otp']) {
        redirectWithError('Invalid verification code. Please check your email and try again.', 'login', 'otp');
    }

    $_SESSION['user_id'] = $_SESSION['login_user_id_pending'];
    $_SESSION['username'] = $_SESSION['login_username_pending'];

    session_regenerate_id(true); // Prevent session fixation

if (isset($_SESSION['login_remember_me_pending']) && $_SESSION['login_remember_me_pending'] === true) {
    // Call a helper function to create and set the token
    createRememberMeToken($_SESSION['user_id']);
}

unset($_SESSION['login_otp'], $_SESSION['login_otp_expiry'], $_SESSION['login_user_id_pending'], $_SESSION['login_username_pending'], $_SESSION['login_remember_me_pending']);
    // Redirect to index.html after successful login
    redirectToIndexSuccess('Welcome back! Login successful.');
}

/**
 * Handle Registration - Step 1: Email & OTP
 */
function handleRegisterStep1_SendOTP() {
    global $conn, $errors;

    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($email)) {
        $errors[] = 'Email address is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }

    if (!empty($errors)) {
        // Use '|' delimiter for multi-error list
        redirectWithError(implode('|', $errors), 'register', 'email');
    }

    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

    try {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            redirectWithError('This email is already registered. Please login instead.', 'register', 'email');
        }
        $stmt->close();

        $otp = rand(100000, 999999);
        $otp_expiry = time() + (2 * 60);

        $_SESSION['reg_email'] = $email;
        $_SESSION['reg_otp'] = $otp;
        $_SESSION['reg_otp_expiry'] = $otp_expiry;

        if (sendRegistrationOTPEmail($email, $otp)) {
            redirectWithSuccess('Verification code sent! Please check your email inbox.', 'register', 'otp');
        } else {
            redirectWithError("We couldn't send the verification code. Please try again.", 'register', 'email');
        }

    } catch (Exception $e) {
        error_log('Register Step 1 error: ' . $e->getMessage());
        redirectWithError('An error occurred. Please try again.', 'register', 'email');
    }
}

/**
 * Handle Registration - Step 2: Verify OTP
 */
function handleRegisterStep2_VerifyOTP() {
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

    if (empty($otp)) {
        redirectWithError('Please enter the verification code.', 'register', 'otp');
    }
    if (!preg_match('/^[0-9]{6}$/', $otp)) {
        redirectWithError('Verification code must be 6 digits.', 'register', 'otp');
    }
    if (!isset($_SESSION['reg_otp']) || !isset($_SESSION['reg_otp_expiry']) || !isset($_SESSION['reg_email'])) {
        redirectWithError('Your session has expired. Please start registration again.', 'register', 'email');
    }
    if (time() > $_SESSION['reg_otp_expiry']) {
        unset($_SESSION['reg_otp'], $_SESSION['reg_otp_expiry'], $_SESSION['reg_email']);
        redirectWithError('Your verification code has expired. Please request a new one.', 'register', 'email');
    }
    if ($otp != $_SESSION['reg_otp']) {
        redirectWithError('Invalid verification code. Please check your email and try again.', 'register', 'otp');
    }

    unset($_SESSION['reg_otp'], $_SESSION['reg_otp_expiry']);
    $_SESSION['reg_otp_verified'] = true;
    
    // Set a CAPTCHA code for the image to display
    $_SESSION['reg_captcha'] = strtoupper(substr(md5(rand()), 0, 6));

    // Redirect to CAPTCHA step
    redirectWithSuccess('Email verified! Please complete the security check.', 'register', 'captcha');
}

/**
 * Handle Registration - Step 3: Verify CAPTCHA
 */
function handleRegisterStep3_VerifyCAPTCHA() {
    // Check if session is still valid
    if (!isset($_SESSION['reg_email']) || !isset($_SESSION['reg_otp_verified'])) {
        redirectWithError('Your session has expired. Please start registration again.', 'register', 'email');
    }

    // Get reCAPTCHA response
    $recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    
    if (empty($recaptchaResponse)) {
        redirectWithError('Please complete the reCAPTCHA verification.', 'register', 'captcha');
    }

    // Verify reCAPTCHA with Google
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

    if (!$responseData->success) {
        redirectWithError('reCAPTCHA verification failed. Please try again.', 'register', 'captcha');
    }

    // reCAPTCHA verified successfully
    unset($_SESSION['reg_captcha']); // Clean up old session variable
    $_SESSION['reg_captcha_verified'] = true;

    redirectWithSuccess('Security check passed! Almost done—just create your account details.', 'register', 'details');
}


/**
 * Handle Registration - Step 4: Finalize Details
 */
function handleRegisterStep4_Finalize() {
    global $conn, $errors;

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $password_confirm = isset($_POST['password_confirm']) ? trim($_POST['password_confirm']) : '';
    $terms = isset($_POST['terms']) ? $_POST['terms'] : '';
    
    $email = isset($_SESSION['reg_email']) ? $_SESSION['reg_email'] : '';

    // Check for all required session flags (OTP and CAPTCHA)
    if (empty($email) || !isset($_SESSION['reg_otp_verified']) || !isset($_SESSION['reg_captcha_verified'])) {
        redirectWithError('Your session has expired. Please start registration again.', 'register', 'email');
    }
    
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($password)) $errors[] = 'Password is required';
    if (empty($password_confirm)) $errors[] = 'Please confirm your password';
    if ($password !== $password_confirm) $errors[] = 'Passwords do not match';
    if (empty($terms)) $errors[] = 'You must agree to the Terms & Conditions';

    // Ensure PHP validation matches client-side helper functions
    if (!empty($username) && !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors[] = 'Username must be 3-20 characters (letters, numbers, underscore only)';
    }

    if (!empty($password) && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $errors[] = 'Password must contain at least 8 characters with uppercases, lowercases, and numbers';
    }

    if (!empty($errors)) {
        // Use '|' delimiter for multi-error list
        redirectWithError(implode('|', $errors), 'register', 'details');
    }

    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

    try {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            redirectWithError('This username is already taken. Please choose another one.', 'register', 'details');
        }
        $stmt->close();
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'buyer', NOW())");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Send welcome email
            sendWelcomeEmail($email, $username);
            
            unset($_SESSION['reg_email'], $_SESSION['reg_otp_verified'], $_SESSION['reg_captcha_verified']);
            
            redirectWithSuccess('Registration successful! Welcome to Lumora. Please login to continue.', 'login', 'credentials');
        } else {
            $stmt->close();
            redirectWithError('Registration failed. Please try again.', 'register', 'details');
        }
        
    } catch (Exception $e) {
        error_log('Registration final error: ' . $e->getMessage());
        redirectWithError('An error occurred during registration. Please try again.', 'register', 'details');
    }
}

/**
 * Send Registration OTP Email with Enhanced Template
 */
function sendRegistrationOTPEmail($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // WARNING: Replace with your actual email credentials or use environment variables
        $mail->Username   = 'lumora.auth@gmail.com';
        $mail->Password   = 'lftkvhebzmcuqllu'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('lumora.auth@gmail.com', 'Lumora');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - Lumora Registration';
        $mail->Body    = getRegistrationEmailTemplate($otp);
        $mail->AltBody = "Welcome to Lumora! Your verification code is: $otp\n\nThis code will expire in 2 minutes.\n\nIf you didn't request this code, please ignore this email.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send Login OTP Email with Enhanced Template
 */
function sendLoginOTPEmail($email, $otp, $username) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // WARNING: Replace with your actual email credentials or use environment variables
        $mail->Username   = 'lumora.auth@gmail.com';
        $mail->Password   = 'lftkvhebzmcuqllu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('lumora.auth@gmail.com', 'Lumora');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Login Verification Code - Lumora';
        $mail->Body    = getLoginEmailTemplate($otp, $username);
        $mail->AltBody = "Hello $username,\n\nYour login verification code is: $otp\n\nThis code will expire in 2 minutes.\n\nIf you didn't attempt to login, please secure your account immediately.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send Welcome Email
 */
function sendWelcomeEmail($email, $username) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // WARNING: Replace with your actual email credentials or use environment variables
        $mail->Username   = 'lumora.auth@gmail.com';
        $mail->Password   = 'lftkvhebzmcuqllu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('lumora.auth@gmail.com', 'Lumora');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Lumora!';
        $mail->Body    = getWelcomeEmailTemplate($username);
        $mail->AltBody = "Welcome to Lumora, $username!\n\nThank you for joining our community. We're excited to have you here.\n\nStart exploring our exquisite collection of accessories today.\n\nBest regards,\nThe Lumora Team";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Email Templates
 */
function getRegistrationEmailTemplate($otp) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
        <table role="presentation" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 40px 20px;">
                    <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <tr>
                            <td style="background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%); padding: 40px 30px; text-align: center;">
                                <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: 2px;">LUMORA</h1>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="padding: 40px 30px;">
                                <h2 style="margin: 0 0 16px; color: #1f2937; font-size: 24px; font-weight: 600;">Verify Your Email</h2>
                                <p style="margin: 0 0 24px; color: #6b7280; font-size: 15px; line-height: 1.6;">Thank you for choosing Lumora! To complete your registration, please use the verification code below:</p>
                                
                                <table role="presentation" style="width: 100%; margin: 32px 0;">
                                    <tr>
                                        <td style="text-align: center;">
                                            <div style="display: inline-block; background-color: #f0f5f3; border: 2px solid #2d5a4a; border-radius: 12px; padding: 20px 40px;">
                                                <span style="font-size: 36px; font-weight: 700; color: #1e4d3d; letter-spacing: 8px;">' . $otp . '</span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style="margin: 24px 0 0; color: #6b7280; font-size: 14px; line-height: 1.6;">This code will expire in <strong style="color: #1e4d3d;">2 minutes</strong>.</p>
                                <p style="margin: 16px 0 0; color: #9ca3af; font-size: 13px; line-height: 1.6;">If you didn\'t create an account with Lumora, please ignore this email.</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="background-color: #f9fafb; padding: 24px 30px; border-top: 1px solid #e5e7eb;">
                                <p style="margin: 0; color: #6b7280; font-size: 13px; text-align: center; line-height: 1.6;">
                                    © 2025 Lumora. All rights reserved.<br>
                                    Exquisite Accessories for Every Occasion
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}

function getLoginEmailTemplate($otp, $username) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
        <table role="presentation" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 40px 20px;">
                    <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <tr>
                            <td style="background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%); padding: 40px 30px; text-align: center;">
                                <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: 2px;">LUMORA</h1>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="padding: 40px 30px;">
                                <h2 style="margin: 0 0 16px; color: #1f2937; font-size: 24px; font-weight: 600;">Hello, ' . htmlspecialchars($username) . '!</h2>
                                <p style="margin: 0 0 24px; color: #6b7280; font-size: 15px; line-height: 1.6;">We received a login request for your account. Please use the verification code below to complete your login:</p>
                                
                                <table role="presentation" style="width: 100%; margin: 32px 0;">
                                    <tr>
                                        <td style="text-align: center;">
                                            <div style="display: inline-block; background-color: #f0f5f3; border: 2px solid #2d5a4a; border-radius: 12px; padding: 20px 40px;">
                                                <span style="font-size: 36px; font-weight: 700; color: #1e4d3d; letter-spacing: 8px;">' . $otp . '</span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style="margin: 24px 0 0; color: #6b7280; font-size: 14px; line-height: 1.6;">This code will expire in <strong style="color: #1e4d3d;">2 minutes</strong>.</p>
                                <div style="margin: 24px 0; padding: 16px; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px;">
                                    <p style="margin: 0; color: #92400e; font-size: 13px; line-height: 1.6;"><strong>Security Notice:</strong> If you didn\'t attempt to login, please secure your account immediately.</p>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="background-color: #f9fafb; padding: 24px 30px; border-top: 1px solid #e5e7eb;">
                                <p style="margin: 0; color: #6b7280; font-size: 13px; text-align: center; line-height: 1.6;">
                                    © 2025 Lumora. All rights reserved.<br>
                                    Exquisite Accessories for Every Occasion
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}

function getWelcomeEmailTemplate($username) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
        <table role="presentation" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 40px 20px;">
                    <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <tr>
                            <td style="background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%); padding: 40px 30px; text-align: center;">
                                <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: 2px;">LUMORA</h1>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="padding: 40px 30px; text-align: center;">
                                <h2 style="margin: 0 0 16px; color: #1e4d3d; font-size: 28px; font-weight: 600;">Welcome to Lumora!</h2>
                                <p style="margin: 0 0 24px; color: #1f2937; font-size: 18px; font-weight: 500;">Hello, ' . htmlspecialchars($username) . '!</p>
                                <p style="margin: 0 0 32px; color: #6b7280; font-size: 15px; line-height: 1.6;">Thank you for joining our community. We\'re thrilled to have you here and can\'t wait for you to explore our exquisite collection of accessories.</p>
                                
                                <div style="margin: 32px 0; padding: 24px; background-color: #f0f5f3; border-radius: 12px;">
                                    <h3 style="margin: 0 0 16px; color: #1e4d3d; font-size: 18px; font-weight: 600;">What\'s Next?</h3>
                                    <ul style="margin: 0; padding: 0; list-style: none; text-align: left;">
                                        <li style="margin: 0 0 12px; padding-left: 28px; color: #374151; font-size: 14px; line-height: 1.6; position: relative;">
                                            <span style="position: absolute; left: 0; color: #2d5a4a;">✓</span> Browse our curated collections
                                        </li>
                                        <li style="margin: 0 0 12px; padding-left: 28px; color: #374151; font-size: 14px; line-height: 1.6; position: relative;">
                                            <span style="position: absolute; left: 0; color: #2d5a4a;">✓</span> Add items to your wishlist
                                        </li>
                                        <li style="margin: 0; padding-left: 28px; color: #374151; font-size: 14px; line-height: 1.6; position: relative;">
                                            <span style="position: absolute; left: 0; color: #2d5a4a;">✓</span> Enjoy exclusive member benefits
                                        </li>
                                    </ul>
                                </div>
                                
                                <p style="margin: 32px 0 0; color: #6b7280; font-size: 14px; line-height: 1.6;">If you have any questions, our support team is always here to help.</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="background-color: #f9fafb; padding: 24px 30px; border-top: 1px solid #e5e7eb;">
                                <p style="margin: 0 0 8px; color: #1e4d3d; font-size: 14px; text-align: center; font-weight: 600;">Happy Shopping!</p>
                                <p style="margin: 0; color: #6b7280; font-size: 13px; text-align: center; line-height: 1.6;">
                                    © 2025 Lumora. All rights reserved.<br>
                                    Exquisite Accessories for Every Occasion
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}

function redirectToIndexSuccess($message) {
    // Redirect to index page with success message
    $params = [
        'status' => 'success',
        'message' => urlencode($message)
    ];
    $url = '../../frontend/pages/index.html?' . http_build_query($params);
    header('Location: ' . $url);
    exit();
}

function redirectWithError($message, $tab, $step = '') {
    $params = [
        'status' => 'error',
        'message' => urlencode($message),
        'tab' => $tab,
        'step' => $step
    ];
    $url = '../../frontend/pages/login.html?' . http_build_query($params);
    header('Location: ' . $url);
    exit();
}

function redirectWithSuccess($message, $tab, $step = '') {
    $params = [
        'status' => 'success',
        'message' => urlencode($message),
        'tab' => $tab,
        'step' => $step
    ];
    $url = '../../frontend/pages/login.html?' . http_build_query($params);
    header('Location: ' . $url);
    exit();
}

if (isset($conn)) {
    $conn->close();
}
function handleResendLoginOTP() {
    header('Content-Type: application/json');
    
    // Check if user has pending login session
    if (!isset($_SESSION['login_user_id_pending']) || !isset($_SESSION['login_otp'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Session expired. Please login again.'
        ]);
        exit();
    }
    
    // Check cooldown (2 minutes = 120 seconds)
    if (isset($_SESSION['login_otp_resend_time'])) {
        $timeElapsed = time() - $_SESSION['login_otp_resend_time'];
        if ($timeElapsed < 120) {
            $remainingTime = 120 - $timeElapsed;
            echo json_encode([
                'success' => false,
                'message' => "Please wait " . ceil($remainingTime / 60) . " more minute(s) before requesting another code."
            ]);
            exit();
        }
    }
    
    // Get user email
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT email, username FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['login_user_id_pending']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate new OTP
            $otp = random_int(100000, 999999);
            $otp_expiry = time() + (2 * 60); // 2 minutes
            
            // Update session
            $_SESSION['login_otp'] = $otp;
            $_SESSION['login_otp_expiry'] = $otp_expiry;
            $_SESSION['login_otp_resend_time'] = time();
            
            // Send email
            if (sendLoginOTPEmail($user['email'], $otp, $user['username'])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'A new verification code has been sent to your email.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send code. Please try again.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User not found. Please login again.'
            ]);
        }
        $stmt->close();
        
    } catch (Exception $e) {
        error_log('Resend Login OTP error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Please try again.'
        ]);
    }
    exit();
}

/**
 * Handle Resend Register OTP
 */
function handleResendRegisterOTP() {
    header('Content-Type: application/json');
    
    // Check if user has pending registration
    if (!isset($_SESSION['reg_email']) || !isset($_SESSION['reg_otp'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Session expired. Please start registration again.'
        ]);
        exit();
    }
    
    // Check cooldown (2 minutes = 120 seconds)
    if (isset($_SESSION['reg_otp_resend_time'])) {
        $timeElapsed = time() - $_SESSION['reg_otp_resend_time'];
        if ($timeElapsed < 120) {
            $remainingTime = 120 - $timeElapsed;
            echo json_encode([
                'success' => false,
                'message' => "Please wait " . ceil($remainingTime / 60) . " more minute(s) before requesting another code."
            ]);
            exit();
        }
    }
    
    // Generate new OTP
    $otp = random_int(100000, 999999);
    $otp_expiry = time() + (2 * 60); // 2 minutes
    
    // Update session
    $_SESSION['reg_otp'] = $otp;
    $_SESSION['reg_otp_expiry'] = $otp_expiry;
    $_SESSION['reg_otp_resend_time'] = time();
    
    // Send email
    if (sendRegistrationOTPEmail($_SESSION['reg_email'], $otp)) {
        echo json_encode([
            'success' => true,
            'message' => 'A new verification code has been sent to your email.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send code. Please try again.'
        ]);
    }
    exit();
}
/**
 * Handle Forgot Password - Step 1: Send OTP to Email
 */
function handleForgotPasswordStep1_SendOTP() {
    global $conn, $errors;

    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($email)) {
        $errors[] = 'Email address is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }

    if (!empty($errors)) {
        redirectWithError(implode('|', $errors), 'forgot', 'email');
    }

    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

    try {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            redirectWithError('No account found with this email address.', 'forgot', 'email');
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Generate OTP
        $otp = rand(100000, 999999);
        $otp_expiry = time() + (2 * 60); // 2 minutes

        // Store in session
        $_SESSION['forgot_email'] = $email;
        $_SESSION['forgot_user_id'] = $user['user_id'];
        $_SESSION['forgot_username'] = $user['username'];
        $_SESSION['forgot_otp'] = $otp;
        $_SESSION['forgot_otp_expiry'] = $otp_expiry;

        // Send OTP email
        if (sendForgotPasswordOTPEmail($email, $otp, $user['username'])) {
            redirectWithSuccess('A password reset code has been sent to your email.', 'forgot', 'otp');
        } else {
            redirectWithError("We couldn't send the reset code. Please try again.", 'forgot', 'email');
        }

    } catch (Exception $e) {
        error_log('Forgot Password Step 1 error: ' . $e->getMessage());
        redirectWithError('An error occurred. Please try again.', 'forgot', 'email');
    }
}

/**
 * Handle Forgot Password - Step 2: Verify OTP
 */
function handleForgotPasswordStep2_VerifyOTP() {
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

    if (empty($otp)) {
        redirectWithError('Please enter the verification code.', 'forgot', 'otp');
    }
    if (!preg_match('/^[0-9]{6}$/', $otp)) {
        redirectWithError('Verification code must be 6 digits.', 'forgot', 'otp');
    }
    if (!isset($_SESSION['forgot_otp']) || !isset($_SESSION['forgot_otp_expiry']) || !isset($_SESSION['forgot_email'])) {
        redirectWithError('Your session has expired. Please start again.', 'forgot', 'email');
    }
    if (time() > $_SESSION['forgot_otp_expiry']) {
        unset($_SESSION['forgot_otp'], $_SESSION['forgot_otp_expiry'], $_SESSION['forgot_email'], $_SESSION['forgot_user_id'], $_SESSION['forgot_username']);
        redirectWithError('Your verification code has expired. Please request a new one.', 'forgot', 'email');
    }
    if ($otp != $_SESSION['forgot_otp']) {
        redirectWithError('Invalid verification code. Please check your email and try again.', 'forgot', 'otp');
    }

    // OTP verified successfully
    unset($_SESSION['forgot_otp'], $_SESSION['forgot_otp_expiry']);
    $_SESSION['forgot_otp_verified'] = true;

    redirectWithSuccess('Code verified! Now create your new password.', 'forgot', 'reset');
}

/**
 * Handle Forgot Password - Step 3: Reset Password
 */
function handleForgotPasswordStep3_ResetPassword() {
    global $conn, $errors;

    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $password_confirm = isset($_POST['password_confirm']) ? trim($_POST['password_confirm']) : '';

    // Check session validity
    if (!isset($_SESSION['forgot_email']) || !isset($_SESSION['forgot_user_id']) || !isset($_SESSION['forgot_otp_verified'])) {
        redirectWithError('Your session has expired. Please start again.', 'forgot', 'email');
    }

    // Validate password
    if (empty($password)) $errors[] = 'Password is required';
    if (empty($password_confirm)) $errors[] = 'Please confirm your password';
    if ($password !== $password_confirm) $errors[] = 'Passwords do not match';

    if (!empty($password) && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $errors[] = 'Password must contain at least 8 characters with uppercases, lowercases, and numbers';
    }

    if (!empty($errors)) {
        redirectWithError(implode('|', $errors), 'forgot', 'reset');
    }

    try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password in database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $_SESSION['forgot_user_id']);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Send confirmation email
            sendPasswordChangedEmail($_SESSION['forgot_email'], $_SESSION['forgot_username']);
            
            // Clear all forgot password session data
            unset($_SESSION['forgot_email'], $_SESSION['forgot_user_id'], $_SESSION['forgot_username'], $_SESSION['forgot_otp_verified']);
            
            redirectWithSuccess('Password reset successful! You can now login with your new password.', 'login', 'credentials');
        } else {
            $stmt->close();
            redirectWithError('Failed to reset password. Please try again.', 'forgot', 'reset');
        }
        
    } catch (Exception $e) {
        error_log('Password Reset error: ' . $e->getMessage());
        redirectWithError('An error occurred. Please try again.', 'forgot', 'reset');
    }
}

/**
 * Handle Resend Forgot Password OTP
 */
function handleResendForgotPasswordOTP() {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['forgot_email']) || !isset($_SESSION['forgot_user_id']) || !isset($_SESSION['forgot_username'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Session expired. Please start again.'
        ]);
        exit();
    }
    
    // Check cooldown (2 minutes)
    if (isset($_SESSION['forgot_otp_resend_time'])) {
        $timeElapsed = time() - $_SESSION['forgot_otp_resend_time'];
        if ($timeElapsed < 120) {
            $remainingTime = 120 - $timeElapsed;
            echo json_encode([
                'success' => false,
                'message' => "Please wait " . ceil($remainingTime / 60) . " more minute(s) before requesting another code."
            ]);
            exit();
        }
    }
    
    // Generate new OTP
    $otp = rand(100000, 999999);
    $otp_expiry = time() + (2 * 60);
    
    // Update session
    $_SESSION['forgot_otp'] = $otp;
    $_SESSION['forgot_otp_expiry'] = $otp_expiry;
    $_SESSION['forgot_otp_resend_time'] = time();
    
    // Send email
    if (sendForgotPasswordOTPEmail($_SESSION['forgot_email'], $otp, $_SESSION['forgot_username'])) {
        echo json_encode([
            'success' => true,
            'message' => 'A new verification code has been sent to your email.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send code. Please try again.'
        ]);
    }
    exit();
}

/**
 * Send Forgot Password OTP Email
 */
function sendForgotPasswordOTPEmail($email, $otp, $username) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'lumora.auth@gmail.com';
        $mail->Password   = 'lftkvhebzmcuqllu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('lumora.auth@gmail.com', 'Lumora');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code - Lumora';
        $mail->Body    = getForgotPasswordEmailTemplate($otp, $username);
        $mail->AltBody = "Hello $username,\n\nYour password reset code is: $otp\n\nThis code will expire in 2 minutes.\n\nIf you didn't request a password reset, please ignore this email and your password will remain unchanged.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send Password Changed Confirmation Email
 */
function sendPasswordChangedEmail($email, $username) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'lumora.auth@gmail.com';
        $mail->Password   = 'lftkvhebzmcuqllu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('lumora.auth@gmail.com', 'Lumora');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Changed Successfully - Lumora';
        $mail->Body    = getPasswordChangedEmailTemplate($username);
        $mail->AltBody = "Hello $username,\n\nYour password has been successfully changed.\n\nIf you didn't make this change, please contact our support team immediately.\n\nBest regards,\nThe Lumora Team";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Forgot Password Email Template
 */
function getForgotPasswordEmailTemplate($otp, $username) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
        <table role="presentation" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 40px 20px;">
                    <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <tr>
                            <td style="background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%); padding: 40px 30px; text-align: center;">
                                <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: 2px;">LUMORA</h1>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="padding: 40px 30px;">
                                <h2 style="margin: 0 0 16px; color: #1f2937; font-size: 24px; font-weight: 600;">Password Reset Request</h2>
                                <p style="margin: 0 0 24px; color: #6b7280; font-size: 15px; line-height: 1.6;">Hello ' . htmlspecialchars($username) . ',</p>
                                <p style="margin: 0 0 24px; color: #6b7280; font-size: 15px; line-height: 1.6;">We received a request to reset your password. Use the verification code below to proceed:</p>
                                
                                <table role="presentation" style="width: 100%; margin: 32px 0;">
                                    <tr>
                                        <td style="text-align: center;">
                                            <div style="display: inline-block; background-color: #f0f5f3; border: 2px solid #2d5a4a; border-radius: 12px; padding: 20px 40px;">
                                                <span style="font-size: 36px; font-weight: 700; color: #1e4d3d; letter-spacing: 8px;">' . $otp . '</span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style="margin: 24px 0 0; color: #6b7280; font-size: 14px; line-height: 1.6;">This code will expire in <strong style="color: #1e4d3d;">2 minutes</strong>.</p>
                                <div style="margin: 24px 0; padding: 16px; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px;">
                                    <p style="margin: 0; color: #92400e; font-size: 13px; line-height: 1.6;"><strong>Security Notice:</strong> If you didn\'t request a password reset, please ignore this email and your password will remain unchanged.</p>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="background-color: #f9fafb; padding: 24px 30px; border-top: 1px solid #e5e7eb;">
                                <p style="margin: 0; color: #6b7280; font-size: 13px; text-align: center; line-height: 1.6;">
                                    © 2025 Lumora. All rights reserved.<br>
                                    Exquisite Accessories for Every Occasion
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}

/**
 * Password Changed Confirmation Email Template
 */
function getPasswordChangedEmailTemplate($username) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5;">
        <table role="presentation" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 40px 20px;">
                    <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <tr>
                            <td style="background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%); padding: 40px 30px; text-align: center;">
                                <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: 2px;">LUMORA</h1>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="padding: 40px 30px; text-align: center;">
                                <div style="width: 80px; height: 80px; margin: 0 auto 24px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <span style="font-size: 40px; color: white;">✓</span>
                                </div>
                                <h2 style="margin: 0 0 16px; color: #1e4d3d; font-size: 24px; font-weight: 600;">Password Changed Successfully</h2>
                                <p style="margin: 0 0 24px; color: #1f2937; font-size: 16px; font-weight: 500;">Hello, ' . htmlspecialchars($username) . '!</p>
                                <p style="margin: 0 0 32px; color: #6b7280; font-size: 15px; line-height: 1.6;">Your password has been successfully changed. You can now login with your new password.</p>
                                
                                <div style="margin: 32px 0; padding: 16px; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px;">
                                    <p style="margin: 0; color: #92400e; font-size: 13px; line-height: 1.6;"><strong>Security Alert:</strong> If you didn\'t make this change, please contact our support team immediately at support@lumora.com</p>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <td style="background-color: #f9fafb; padding: 24px 30px; border-top: 1px solid #e5e7eb;">
                                <p style="margin: 0; color: #6b7280; font-size: 13px; text-align: center; line-height: 1.6;">
                                    © 2025 Lumora. All rights reserved.<br>
                                    Exquisite Accessories for Every Occasion
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}

/**
 * Creates a new "Remember Me" token, stores it in the DB, and sets the cookie.
 */
function createRememberMeToken($user_id) {
    global $conn;
    
    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $validator);
    $expires_at = (new DateTime())->add(new DateInterval('P30D'))->format('Y-m-d H:i:s'); // 30-day expiry
    
    try {
        // Insert new token into the database
        $stmt = $conn->prepare("INSERT INTO user_remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $selector, $token_hash, $expires_at);
        $stmt->execute();
        $stmt->close();
        
        // Set the cookie
        $cookie_value = $selector . ':' . $validator;
        // Set cookie to be HttpOnly, Secure (if using HTTPS), and SameSite=Lax
        setcookie('remember_me', $cookie_value, [
            'expires' => time() + (86400 * 30), // 30 days
            'path' => '/',
            'domain' => '', // Set your domain if needed
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
    } catch (Exception $e) {
        error_log('Failed to create remember me token: ' . $e->getMessage());
    }
}

/**
 * Validates a "Remember Me" cookie, logs the user in, and regenerates the token.
 */
function validateRememberMeToken() {
    global $conn;
    
    if (empty($_COOKIE['remember_me'])) {
        return false;
    }
    
    list($selector, $validator) = explode(':', $_COOKIE['remember_me'], 2);
    
    if (!$selector || !$validator) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM user_remember_tokens WHERE selector = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $token_data = $result->fetch_assoc();
            $token_hash = hash('sha256', $validator);
            
            if (hash_equals($token_data['token_hash'], $token_hash)) {
                // Token is valid! Log the user in.
                session_regenerate_id(true);
                
                $user_id = $token_data['user_id'];
                
                // Get username to store in session
                $user_stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
                $user_stmt->bind_param("i", $user_id);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();
                $user = $user_result->fetch_assoc();
                
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $user['username'];
                
                $user_stmt->close();
                
                // Best Practice: Issue a new token (sliding window)
                // 1. Delete the old token
                $stmt_del = $conn->prepare("DELETE FROM user_remember_tokens WHERE token_id = ?");
                $stmt_del->bind_param("i", $token_data['token_id']);
                $stmt_del->execute();
                $stmt_del->close();
                
                // 2. Create a new token
                createRememberMeToken($user_id);
                
                return true;
            }
        }
        $stmt->close();
        
        // If token is invalid or expired, clear the cookie
        deleteRememberMeToken($selector, true);
        return false;
        
    } catch (Exception $e) {
        error_log('Failed to validate remember me token: ' . $e->getMessage());
        return false;
    }
}

/**
 * Deletes a "Remember Me" token from the DB and unsets the cookie.
 */
function deleteRememberMeToken($selector, $clear_cookie_only = false) {
    global $conn;
    
    if (!$clear_cookie_only) {
        try {
            $stmt = $conn->prepare("DELETE FROM user_remember_tokens WHERE selector = ?");
            $stmt->bind_param("s", $selector);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log('Failed to delete remember me token: ' . $e->getMessage());
        }
    }
    
    // Unset the cookie
    if (isset($_COOKIE['remember_me'])) {
        unset($_COOKIE['remember_me']);
        setcookie('remember_me', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}

?>