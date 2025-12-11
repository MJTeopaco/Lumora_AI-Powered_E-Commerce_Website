<?php
// views/auth/login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumora</title>
    <link rel="stylesheet" href="<?= base_url('/css/auth.css') ?>">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        /* Floating Support Button (Headset Icon) */
        .support-fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #D4AF37;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 999;
            border: none;
        }

        .support-fab:hover {
            transform: scale(1.1);
            background: #B8942C;
        }

        /* Support Modal */
        .support-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            backdrop-filter: blur(2px);
        }

        .support-modal-overlay.active {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        .support-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            animation: slideUp 0.3s ease;
        }

        .support-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .support-header h3 {
            margin: 0;
            color: #1a1a1a;
            font-size: 18px;
        }

        .close-support {
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>
    <button class="support-fab" id="openSupportBtn" title="Contact Support">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
        </svg>
    </button>

    <div class="support-modal-overlay" id="supportModal">
        <div class="support-card">
            <div class="support-header">
                <h3>Contact Administrator</h3>
                <button class="close-support" id="closeSupportBtn">&times;</button>
            </div>
            <p style="color:#666; font-size:14px; margin-bottom:15px;">
                Account locked or trouble logging in? Send us a message.
            </p>
            <form action="<?= base_url('/support/submit') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="input-group">
                    <label style="display:block; margin-bottom:5px; font-weight:600; font-size:13px; color:#333;">Email or Username</label>
                    <input type="text" name="identifier" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; margin-bottom:15px;">
                </div>

                <div class="input-group">
                    <label style="display:block; margin-bottom:5px; font-weight:600; font-size:13px; color:#333;">Message</label>
                    <textarea name="message" required rows="4" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; margin-bottom:15px; font-family:inherit;" placeholder="I cannot access my account because..."></textarea>
                </div>

                <button type="submit" class="submit-btn">Send Request</button>
            </form>
        </div>
    </div>

    <div class="auth-container">
        <div class="brand-section">
            <div class="logo">LUMORA</div>
            <div class="brand-tagline">Elevate Your Style with Exquisite Accessories</div>
        </div>

        <div class="form-section">
            <div class="tab-buttons">
                <button class="tab-btn <?php echo ($data['tab'] ?? 'login') === 'login' ? 'active' : ''; ?>" data-tab="login">Login</button>
                <button class="tab-btn <?php echo ($data['tab'] ?? '') === 'register' ? 'active' : ''; ?>" data-tab="register">Register</button>
            </div>

            <div id="alert" class="alert <?php echo $data['status'] ?? ''; ?>">
                <?php echo $data['message'] ?? ''; ?>
            </div>

            <div id="login-form" class="form-content <?php echo ($data['tab'] ?? 'login') === 'login' ? 'active' : ''; ?>">
                
                <div id="login-step-credentials" class="login-step <?php echo (($data['tab'] ?? 'login') === 'login' && ($data['step'] ?? 'credentials') === 'credentials') ? 'active' : ''; ?>">
                    <h2>Welcome Back!</h2>
                    <p class="subtitle">Sign in to continue shopping</p>
                    <form id="login-form-element" action="<?= base_url('/auth/login-step-1') ?>" method="POST">
                        <input type="hidden" name="action" value="login_step_1_credentials">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= App\Core\Session::get('csrf_token') ?>">
                        <div class="input-group">
                            <label>Email or Username</label>
                            <input type="text" name="identifier" id="login-identifier" placeholder="Enter your email or username" required>
                        </div>

                        <div class="input-group">
                            <label>Password</label>
                            <div class="password-input">
                                <input type="password" name="password" id="login-password" placeholder="Enter your password" required>
                                <button type="button" class="toggle-password" data-target="login-password">
                                    <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path class="eye-open" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle class="eye-open" cx="12" cy="12" r="3"></circle>
                                        <path class="eye-closed" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line class="eye-closed" x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="checkbox-group" style="margin-bottom: 25px; margin-top: -10px;">
                            <input type="checkbox" name="remember_me" id="remember-me">
                            <label for="remember-me">Remember me</label>
                        </div>

                        <div class="forgot-password">
                            <a href="#" id="forgot-password-link">Forgot Password?</a>
                        </div>
                        <button type="submit" class="submit-btn">Sign In</button>
                    </form>
                </div>
                
                <div id="login-step-otp" class="login-step <?php echo (($data['tab'] ?? 'login') === 'login' && ($data['step'] ?? '') === 'otp') ? 'active' : ''; ?>">
                    <h2>Verify Your Identity</h2>
                    <p class="subtitle">A 6-digit code was sent to your email for security.</p>
                    <form id="login-otp-form-element" action="<?= base_url('/auth/login-step-2') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="input-group">
                            <label>Verification Code (OTP)</label>
                            <input type="text" name="otp" id="login-otp" placeholder="Enter 6-digit OTP" maxlength="6" required>
                        </div>
                        <div class="resend-otp-container">
                            <button type="button" class="resend-otp-btn" id="login-resend-otp">Resend Code</button>
                            <span class="resend-timer" id="login-resend-timer"></span>
                        </div>
                        <button type="submit" class="submit-btn">Verify & Login</button>
                    </form>
                </div>
            </div>

            <div id="forgot-form" class="form-content <?php echo ($data['tab'] ?? '') === 'forgot' ? 'active' : ''; ?>">
                
                <div id="forgot-step-email" class="forgot-step <?php echo (($data['tab'] ?? '') === 'forgot' && ($data['step'] ?? 'email') === 'email') ? 'active' : ''; ?>">
                    <h2>Reset Password</h2>
                    <p class="subtitle">Enter your email address to receive a password reset code</p>
                    <form id="forgot-email-form-element" action="<?= base_url('/auth/forgot-step-1') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="input-group">
                            <label>Email Address</label>
                            <input type="email" name="email" id="forgot-email" placeholder="Enter your registered email" required>
                        </div>
                        <button type="submit" class="submit-btn">Send Reset Code</button>
                    </form>
                    <div class="back-to-login"><a href="#" class="back-to-login-link">← Back to Login</a></div>
                </div>

                <div id="forgot-step-otp" class="forgot-step <?php echo (($data['tab'] ?? '') === 'forgot' && ($data['step'] ?? '') === 'otp') ? 'active' : ''; ?>">
                    <h2>Verify Code</h2>
                    <p class="subtitle">Enter the 6-digit code sent to your email</p>
                    <form id="forgot-otp-form-element" action="<?= base_url('/auth/forgot-step-2') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="input-group">
                            <label>Verification Code (OTP)</label>
                            <input type="text" name="otp" id="forgot-otp" placeholder="Enter 6-digit OTP" maxlength="6" required>
                        </div>
                        <div class="resend-otp-container">
                            <button type="button" class="resend-otp-btn" id="forgot-resend-otp">Resend Code</button>
                            <span class="resend-timer" id="forgot-resend-timer"></span>
                        </div>
                        <button type="submit" class="submit-btn">Verify Code</button>
                    </form>
                    <div class="back-to-login"><a href="#" class="back-to-login-link">← Back to Login</a></div>
                </div>

                <div id="forgot-step-reset" class="forgot-step <?php echo (($data['tab'] ?? '') === 'forgot' && ($data['step'] ?? '') === 'reset') ? 'active' : ''; ?>">
                    <h2>Create New Password</h2>
                    <p class="subtitle">Enter your new password</p>
                    <form id="forgot-reset-form-element" action="<?= base_url('/auth/forgot-step-3') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="input-group">
                            <label>New Password</label>
                            <div class="password-input">
                                <input type="password" name="password" id="forgot-password" placeholder="Enter new password" required>
                                <button type="button" class="toggle-password" data-target="forgot-password">
                                    <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path class="eye-open" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle class="eye-open" cx="12" cy="12" r="3"></circle>
                                        <path class="eye-closed" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line class="eye-closed" x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Confirm New Password</label>
                            <div class="password-input">
                                <input type="password" name="password_confirm" id="forgot-password-confirm" placeholder="Confirm new password" required>
                                <button type="button" class="toggle-password" data-target="forgot-password-confirm">
                                    <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path class="eye-open" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle class="eye-open" cx="12" cy="12" r="3"></circle>
                                        <path class="eye-closed" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line class="eye-closed" x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="submit-btn">Reset Password</button>
                    </form>
                    <div class="back-to-login"><a href="#" class="back-to-login-link">← Back to Login</a></div>
                </div>
            </div>
            
            <div id="register-form" class="form-content <?php echo ($data['tab'] ?? '') === 'register' ? 'active' : ''; ?>">

                <div id="register-step-email" class="register-step <?php echo (($data['tab'] ?? '') === 'register' && ($data['step'] ?? 'email') === 'email') ? 'active' : ''; ?>">
                    <h2>Create Account</h2>
                    <p class="subtitle">Step 1 out of 4: Enter your email to verify</p>
                    <form id="register-email-form-element" action="<?= base_url('/auth/register-step-1') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="input-group">
                            <label>Email</label>
                            <input type="email" name="email" id="register-email" placeholder="Enter your email address" required>
                        </div>
                        <button type="submit" class="submit-btn">Send Verification Code</button>
                    </form>
                </div>

                <div id="register-step-pending" class="register-step <?php echo (($data['tab'] ?? '') === 'register' && ($data['step'] ?? '') === 'pending') ? 'active' : ''; ?>">
                    <h2>Check Your Email</h2>
                    <p class="subtitle">Step 2 out of 4: We've sent an activation link to your email</p>
                    <div class="email-sent-message" style="text-align: center; padding: 40px 20px;">
                        <div style="font-size: 64px; margin-bottom: 20px;">📬</div>
                        <p style="font-size: 18px; font-weight: 500; color: #2d3748; margin-bottom: 10px;">Activation Link Sent!</p>
                        <p style="color: #4a5568; margin-bottom: 20px;">Please check your inbox and click the activation button to continue your registration.</p>
                        <div style="background: #edf2f7; padding: 15px; border-radius: 8px; margin: 20px 0;">
                            <p style="margin: 0; color: #2d3748; font-size: 14px;"><strong>Expires in 3 minutes</strong></p>
                        </div>
                        <p style="color: #718096; font-size: 14px;">Didn't receive the email? Check your spam folder or try again.</p>
                    </div>
                </div>  

                <div id="register-step-captcha" class="register-step <?php echo (($data['tab'] ?? '') === 'register' && ($data['step'] ?? '') === 'captcha') ? 'active' : ''; ?>">
                    <h2>Bot Checker</h2>
                    <p class="subtitle">Step 3 out of 4: Prove you are not a robot</p>
                    <form id="register-captcha-form-element" action="<?= base_url('/auth/register-step-3') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="input-group captcha-group">
                            <div class="g-recaptcha" data-sitekey="6LdpbwwsAAAAAGUr6X_3imAtgnaSBzwyzs20Lpdi"></div>
                        </div>
                        <button type="submit" class="submit-btn">Verify</button>
                    </form>
                </div>
                
                <div id="register-step-details" class="register-step <?php echo (($data['tab'] ?? '') === 'register' && ($data['step'] ?? '') === 'details') ? 'active' : ''; ?>">
                    <h2>Final Step</h2>
                    <p class="subtitle">Step 4 out of 4: Create your username and password</p>
                    <form id="register-form-element" action="<?= base_url('/auth/register-step-4') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="input-group">
                            <label>Username</label>
                            <input type="text" name="username" id="register-username" placeholder="Choose a username" required>
                        </div>

                        <div class="input-group">
                            <label>Password</label>
                            <div class="password-input">
                                <input type="password" name="password" id="register-password" placeholder="Create a password" required>
                                <button type="button" class="toggle-password" data-target="register-password">
                                    <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path class="eye-open" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle class="eye-open" cx="12" cy="12" r="3"></circle>
                                        <path class="eye-closed" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line class="eye-closed" x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="password-strength-container">
                                <div class="strength-bar">
                                    <div class="strength-fill"></div>
                                </div>
                                <div class="strength-text">Password Strength: <span id="strength-label">Very Weak</span></div>
                            </div>
                            
                            <div class="password-requirements">
                                <div class="requirements-grid">
                                    <div class="requirement" data-requirement="length">
                                        <span class="requirement-icon"></span>
                                        <span>At least 8 characters</span>
                                    </div>
                                    <div class="requirement" data-requirement="uppercase">
                                        <span class="requirement-icon"></span>
                                        <span>One uppercase letter</span>
                                    </div>
                                    <div class="requirement" data-requirement="number">
                                        <span class="requirement-icon"></span>
                                        <span>One number</span>
                                    </div>
                                    <div class="requirement" data-requirement="lowercase">
                                        <span class="requirement-icon"></span>
                                        <span>One lowercase letter</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Confirm Password</label>
                            <div class="password-input">
                                <input type="password" name="password_confirm" id="register-password-confirm" placeholder="Confirm your password" required>
                                <button type="button" class="toggle-password" data-target="register-password-confirm">
                                    <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path class="eye-open" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle class="eye-open" cx="12" cy="12" r="3"></circle>
                                        <path class="eye-closed" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line class="eye-closed" x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>

                       <div class="checkbox-group">
                            <input type="checkbox" name="terms" id="terms" required>
                            <label for="terms">I agree to the <a href="#" id="terms-link">Terms & Conditions</a> and <a href="#" id="privacy-link">Privacy Policy</a></label>
                        </div>

                        <button type="submit" class="submit-btn">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/../../products/modal.php'; ?>

    <script src="<?= base_url('/js/auth.js?v=' . time()) ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            let message = urlParams.get('message');
            
            if (message) {
                message = message.replace(/\+/g, ' '); 
            }
            
            if (status && message) {
                setTimeout(() => {
                    showAlert(decodeURIComponent(message), status);
                }, 300);
            }
            
            if (window.history.replaceState) {
                const cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });
    </script>
</body>
</html>