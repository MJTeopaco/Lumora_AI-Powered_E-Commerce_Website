// Password visibility toggle functionality - MUST BE FIRST
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const targetId = this.getAttribute('data-target');
        const passwordInput = document.getElementById(targetId);
        const eyeIcon = this.querySelector('.eye-icon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.add('toggled');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('toggled');
        }
    });
});

// OTP Resend Functionality with 2-minute cooldown
class OTPResendManager {
    constructor(buttonId, timerId, resendAction) {
        this.button = document.getElementById(buttonId);
        this.timer = document.getElementById(timerId);
        this.resendAction = resendAction;
        this.cooldownSeconds = 120; // 2 minutes
        this.remainingTime = 0;
        this.intervalId = null;
        
        if (this.button) {
            this.button.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleResend();
            });
        }
    }
    
    startCooldown() {
        this.remainingTime = this.cooldownSeconds;
        this.button.disabled = true;
        this.updateTimerDisplay();
        
        this.intervalId = setInterval(() => {
            this.remainingTime--;
            this.updateTimerDisplay();
            
            if (this.remainingTime <= 0) {
                this.stopCooldown();
            }
        }, 1000);
    }
    
    stopCooldown() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        this.button.disabled = false;
        this.timer.textContent = '';
    }
    
    updateTimerDisplay() {
        const minutes = Math.floor(this.remainingTime / 60);
        const seconds = this.remainingTime % 60;
        this.timer.textContent = `(${minutes}:${seconds.toString().padStart(2, '0')})`;
    }
    
    async handleResend() {
        if (this.button.disabled) return;
        
        const originalText = this.button.textContent;
        this.button.disabled = true;
        this.button.textContent = 'Sending...';
        
        try {
            const formData = new FormData();
            formData.append('action', this.resendAction);
            
            const response = await fetch('../../backend/php/auth.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showAlert(result.message, 'success');
                this.button.textContent = originalText;
                this.startCooldown(); // Timer starts ONLY after successful resend
            } else {
                showAlert(result.message, 'error');
                this.button.disabled = false;
                this.button.textContent = originalText;
            }
        } catch (error) {
            console.error('Resend error:', error);
            showAlert('Failed to resend code. Please try again.', 'error');
            this.button.disabled = false;
            this.button.textContent = originalText;
        }
    }
}

// Initialize OTP resend managers
let loginResendManager = null;
let registerResendManager = null;

let forgotResendManager = null;

function initializeResendManagers() {
    const loginOtpStep = document.getElementById('login-step-otp');
    if (loginOtpStep && loginOtpStep.classList.contains('active') && !loginResendManager) {
        loginResendManager = new OTPResendManager('login-resend-otp', 'login-resend-timer', 'resend_login_otp');
    }
    
    const registerOtpStep = document.getElementById('register-step-otp');
    if (registerOtpStep && registerOtpStep.classList.contains('active') && !registerResendManager) {
        registerResendManager = new OTPResendManager('register-resend-otp', 'register-resend-timer', 'resend_register_otp');
    }
    
    const forgotOtpStep = document.getElementById('forgot-step-otp');
    if (forgotOtpStep && forgotOtpStep.classList.contains('active') && !forgotResendManager) {
        forgotResendManager = new OTPResendManager('forgot-resend-otp', 'forgot-resend-timer', 'resend_forgot_password_otp');
    }
}

// Tab switching functionality with improved transitions
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tab = this.getAttribute('data-tab');
        
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Get current and new forms
        const currentForm = document.querySelector('.form-content.active');
        const newForm = document.getElementById(tab + '-form');
        
        // If clicking the same tab, do nothing
        if (currentForm === newForm) return;
        
        // Remove active from all forms immediately
        document.querySelectorAll('.form-content').forEach(form => {
            form.classList.remove('active');
        });
        
        // Activate new form
        newForm.classList.add('active');
        
        // Reset to step 1 for the NEW tab only
        if (tab === 'register') {
            document.querySelectorAll('.register-step').forEach(step => step.classList.remove('active'));
            document.getElementById('register-step-email').classList.add('active');
        } else if (tab === 'login') {
            document.querySelectorAll('.login-step').forEach(step => step.classList.remove('active'));
            document.getElementById('login-step-credentials').classList.add('active');
        }

        // Hide alert
        hideAlert();
    });
});

// Improved alert functions with better animations
function showAlert(message, type) {
    const alert = document.getElementById('alert');
    
    // Hide first if showing
    if (alert.classList.contains('show')) {
        hideAlert();
        setTimeout(() => showAlertImmediate(alert, message, type), 300);
    } else {
        showAlertImmediate(alert, message, type);
    }
}

function showAlertImmediate(alert, message, type) {
    // Check if message contains delimiter for multiple errors
    if (message.includes('|')) {
        let errorHtml = '<ul>';
        message.split('|').forEach(error => {
            errorHtml += `<li>${error}</li>`;
        });
        errorHtml += '</ul>';
        alert.innerHTML = errorHtml;
    } else {
        alert.textContent = message;
    }
    
    alert.className = 'alert ' + type;
    // Trigger reflow
    void alert.offsetWidth;
    alert.classList.add('show');
}

function hideAlert() {
    const alert = document.getElementById('alert');
    alert.classList.remove('show');
}

// Forgot password - Switch to forgot password form
document.getElementById('forgot-password-link')?.addEventListener('click', function(e) {
    e.preventDefault();
    
    // Hide login form
    document.querySelectorAll('.form-content').forEach(form => form.classList.remove('active'));
    
    // Show forgot password form
    document.getElementById('forgot-form').classList.add('active');
    
    // Reset to step 1
    document.querySelectorAll('.forgot-step').forEach(step => step.classList.remove('active'));
    document.getElementById('forgot-step-email').classList.add('active');
    
    // Update tab button (keep login highlighted)
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('.tab-btn[data-tab="login"]').classList.add('active');
    
    hideAlert();
});

// Back to login links
document.querySelectorAll('.back-to-login-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Hide forgot password form
        document.getElementById('forgot-form')?.classList.remove('active');
        
        // Show login form
        document.getElementById('login-form').classList.add('active');
        
        // Reset to login step 1
        document.querySelectorAll('.login-step').forEach(step => step.classList.remove('active'));
        document.getElementById('login-step-credentials').classList.add('active');
        
        hideAlert();
    });
});

// Terms & Conditions Modal with improved animations
const termsModal = document.getElementById('terms-modal');
const termsLink = document.getElementById('terms-link');
const modalCloseBtn = document.querySelector('.modal-close-btn');

termsLink?.addEventListener('click', function(e) {
    e.preventDefault();
    termsModal.classList.add('show');
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
});

function closeModal() {
    termsModal?.classList.remove('show');
    document.body.style.overflow = '';
}

modalCloseBtn?.addEventListener('click', closeModal);

window.addEventListener('click', function(e) {
    if (e.target == termsModal) {
        closeModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && termsModal?.classList.contains('show')) {
        closeModal();
    }
});

// Check for URL parameters (for PRG pattern feedback)
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    let message = urlParams.get('message');
    const tab = urlParams.get('tab');
    const step = urlParams.get('step');

    // Manually replace '+' with spaces to fix URL encoding issue
    if (message) {
        message = message.replace(/\+/g, ' '); 
    }

    // Show alert if message exists (with delay for smooth appearance)
    if (status && message) {
        setTimeout(() => {
            showAlert(decodeURIComponent(message), status);
        }, 300);
    }

    // Show the correct tab and step
if (tab) {
    // Activate tab button
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    const targetTabBtn = document.querySelector(`.tab-btn[data-tab="${tab === 'forgot' ? 'login' : tab}"]`);
    if (targetTabBtn) targetTabBtn.classList.add('active');
    
    // Show form content
    document.querySelectorAll('.form-content').forEach(form => form.classList.remove('active'));
    const targetForm = document.getElementById(tab + '-form');
    if (targetForm) targetForm.classList.add('active');

    // If register tab, show the correct step
    if (tab === 'register' && step) {
        document.querySelectorAll('.register-step').forEach(s => s.classList.remove('active'));
        const targetStep = document.getElementById(`register-step-${step}`);
        if (targetStep) targetStep.classList.add('active');
    }
    
    // If login tab, show the correct step
    if (tab === 'login' && step) {
        document.querySelectorAll('.login-step').forEach(s => s.classList.remove('active'));
        const targetStep = document.getElementById(`login-step-${step}`);
        if (targetStep) targetStep.classList.add('active');
    }
    
    // If forgot tab, show the correct step
    if (tab === 'forgot' && step) {
        document.querySelectorAll('.forgot-step').forEach(s => s.classList.remove('active'));
        const targetStep = document.getElementById(`forgot-step-${step}`);
        if (targetStep) targetStep.classList.add('active');
    }
}

    // Initialize resend managers after URL params are processed
    setTimeout(() => {
        initializeResendManagers();
    }, 500);

    // Clean up URL without page reload
    if (window.history.replaceState) {
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
});

// Helper function for email validation
function isValidEmail(email) {
    const re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return re.test(String(email).toLowerCase());
}

// Helper function for password strength
function isValidPassword(password) {
    // 8+ chars, 1 uppercase, 1 lowercase, 1 number
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
    return re.test(String(password));
}

// Helper function for username
function isValidUsername(username) {
    // 3-20 chars, letters, numbers, underscore
    const re = /^[a-zA-Z0-9_]{3,20}$/;
    return re.test(String(username));
}

// 1. Login - Step 1 (Credentials)
document.getElementById('login-form-element')?.addEventListener('submit', function(e) {
    hideAlert();
    const identifier = document.getElementById('login-identifier').value.trim();
    const password = document.getElementById('login-password').value.trim();

    if (identifier === '' || password === '') {
        e.preventDefault();
        showAlert('Email/Username and Password are required.', 'error');
    }
});

// 2. Login - Step 2 (OTP)
document.getElementById('login-otp-form-element')?.addEventListener('submit', function(e) {
    hideAlert();
    const otp = document.getElementById('login-otp').value.trim();

    if (otp === '') {
        e.preventDefault();
        showAlert('OTP is required.', 'error');
    } else if (!/^[0-9]{6}$/.test(otp)) {
        e.preventDefault();
        showAlert('OTP must be 6 digits.', 'error');
    }
});

// 3. Register - Step 1 (Email)
document.getElementById('register-email-form-element')?.addEventListener('submit', function(e) {
    hideAlert();
    const email = document.getElementById('register-email').value.trim();
    
    if (email === '') {
        e.preventDefault();
        showAlert('Email is required.', 'error');
    } else if (!isValidEmail(email)) {
        e.preventDefault();
        showAlert('Please enter a valid email address.', 'error');
    }
});

// 4. Register - Step 2 (OTP)
document.getElementById('register-otp-form-element')?.addEventListener('submit', function(e) {
    hideAlert();
    const otp = document.getElementById('register-otp').value.trim();

    if (otp === '') {
        e.preventDefault();
        showAlert('OTP is required.', 'error');
    } else if (!/^[0-9]{6}$/.test(otp)) {
        e.preventDefault();
        showAlert('OTP must be 6 digits.', 'error');
    }
});

// 5. Register - Step 3 (Google reCAPTCHA)
document.getElementById('register-captcha-form-element')?.addEventListener('submit', function(e) {
    hideAlert();
    const recaptchaResponse = grecaptcha.getResponse();

    if (recaptchaResponse === '') {
        e.preventDefault();
        showAlert('Please complete the reCAPTCHA verification.', 'error');
    }
});

// 6. Register - Step 4 (Details)
document.getElementById('register-form-element')?.addEventListener('submit', function(e) {
    hideAlert();
    const username = document.getElementById('register-username').value.trim();
    const password = document.getElementById('register-password').value.trim();
    const passwordConfirm = document.getElementById('register-password-confirm').value.trim();
    const terms = document.getElementById('terms').checked;
    
    let errors = [];

    if (username === '') {
        errors.push('Username is required');
    } else if (!isValidUsername(username)) {
        errors.push('Username must be 3-20 characters (letters, numbers, underscore.)');
    }

    if (password === '') {
        errors.push('Password is required');
    } else if (!isValidPassword(password)) {
        errors.push('Password must be atleast 8 characters, with 1 uppercase, 1 lowercase, and 1 number.');
    }

    if (passwordConfirm === '') {
        errors.push('Please confirm your password');
    } else if (password !== passwordConfirm) {
        errors.push('Passwords do not match');
    }
    
    if (!terms) {
        errors.push('You must agree to the Terms & Conditions');
    }

    if (errors.length > 0) {
        e.preventDefault();
        // Use '|' delimiter to show a list
        showAlert(errors.join('|'), 'error');
    }
});

// 7. Forgot Password - Step 1 (Email)
document.getElementById('forgot-email-form-element')?.addEventListener('submit', function(e) {
    hideAlert();
    const email = document.getElementById('forgot-email').value.trim();
    
    if (email === '') {
        e.preventDefault();
        showAlert('Email is required.', 'error');
    } else if (!isValidEmail(email)) {
        e.preventDefault();
        showAlert('Please enter a valid email address.', 'error');
    }
});

// 8. Forgot Password - Step 2 (OTP)
document.getElementById('forgot-otp-form-element')?.addEventListener('submit', function(e) {
    hideAlert();
    const otp = document.getElementById('forgot-otp').value.trim();

    if (otp === '') {
        e.preventDefault();
        showAlert('OTP is required.', 'error');
    } else if (!/^[0-9]{6}$/.test(otp)) {
        e.preventDefault();
        showAlert('OTP must be 6 digits.', 'error');
    }
});

// 9. Forgot Password - Step 3 (Reset Password)
document.getElementById('forgot-reset-form-element')?.addEventListener('submit', function(e) {
    hideAlert();
    const password = document.getElementById('forgot-password').value.trim();
    const passwordConfirm = document.getElementById('forgot-password-confirm').value.trim();
    
    let errors = [];

    if (password === '') {
        errors.push('Password is required');
    } else if (!isValidPassword(password)) {
        errors.push('Password must be at least 8 characters, with 1 uppercase, 1 lowercase, and 1 number.');
    }

    if (passwordConfirm === '') {
        errors.push('Please confirm your password');
    } else if (password !== passwordConfirm) {
        errors.push('Passwords do not match');
    }

    if (errors.length > 0) {
        e.preventDefault();
        showAlert(errors.join('|'), 'error');
    }
});

// Add smooth focus animations to inputs
document.querySelectorAll('input').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'translateY(-2px)';
        this.parentElement.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
    });
    
    input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'translateY(0)';
    });
});