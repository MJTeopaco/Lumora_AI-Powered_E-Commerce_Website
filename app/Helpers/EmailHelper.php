<?php
// app/Helpers/EmailHelper.php

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
        $mail->Password   = 'lftkvhebzmcuqllu'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom('lumora.auth@gmail.com', 'Lumora');
        return $mail;
    }

    public static function sendRegistrationOTPEmail($email, $otp) {
        try {
            $mail = self::configureMailer();
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email - Lumora Registration';
            $mail->Body    = self::getRegistrationEmailTemplate($otp);
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
            $mail->Body    = self::getLoginEmailTemplate($otp, $username);
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
            $mail->Body    = self::getWelcomeEmailTemplate($username);
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
            $mail->Body    = self::getForgotPasswordEmailTemplate($otp, $username);
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
            $mail->Body    = self::getPasswordChangedEmailTemplate($username);
            $mail->AltBody = "Hello $username,\nYour password has been successfully changed.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    // --- EMAIL TEMPLATES (Copied from your auth.php) ---
    // (All get...EmailTemplate functions are placed here)

    private static function getRegistrationEmailTemplate($otp) {
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

private static function getLoginEmailTemplate($otp, $username) {
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

private static function getWelcomeEmailTemplate($username) {
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

private static function getForgotPasswordEmailTemplate($otp, $username) {
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

private static function getPasswordChangedEmailTemplate($username) {
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
}