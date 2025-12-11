<?php
// app/Views/profile/settings.view.php
?>
<div class="content-header">
    <div>
        <h1 class="content-title">Account Settings</h1>
        <p class="content-subtitle">Manage your account security</p>
    </div>
</div>

<div class="settings-section">
    <div class="settings-card">
        <div class="settings-card-header">
            <div class="settings-icon">
                <i class="fas fa-lock"></i>
            </div>
            <div>
                <h3 class="settings-card-title">Change Password</h3>
                <p class="settings-card-subtitle">Update your password to keep your account secure</p>
            </div>
        </div>

        <form method="POST" action="<?= base_url('/profile/change-password') ?>" class="settings-form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="form-group">
                <label class="form-label">Current Password <span class="required">*</span></label>
                <div class="password-input-wrapper">
                    <input type="password" name="current_password" id="current_password" 
                           class="form-input" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">New Password <span class="required">*</span></label>
                <div class="password-input-wrapper">
                    <input type="password" name="new_password" id="new_password" 
                           class="form-input" required minlength="8">
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                
                <!-- Password strength indicator -->
                <div class="password-strength-container">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strength-fill"></div>
                    </div>
                    <div class="strength-text">Password Strength: <span id="strength-label">Very Weak</span></div>
                </div>
                
                <!-- Password requirements (like register) -->
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

            <div class="form-group">
                <label class="form-label">Confirm New Password <span class="required">*</span></label>
                <div class="password-input-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" 
                           class="form-input" required minlength="8">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <p class="form-error" id="password-match-error">Passwords do not match</p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.settings-section {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.settings-card {
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.settings-card-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #f0f0f0;
}

.settings-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    background: linear-gradient(135deg, #D4AF37 0%, #C9A02C 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.settings-card-title {
    font-size: 18px;
    font-weight: 600;
    color: #1A1A1A;
    margin-bottom: 0.25rem;
}

.settings-card-subtitle {
    font-size: 14px;
    color: #666;
}

.settings-form {
    max-width: 600px;
}

.password-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input-wrapper .form-input {
    padding-right: 3rem;
}

.password-toggle {
    position: absolute;
    right: 0.75rem;
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: #D4AF37;
}

/* Password Strength Indicator (from register) */
.password-strength-container {
    margin: 8px 0 6px 0;
}

.strength-bar {
    height: 6px;
    background: #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 6px;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.strength-fill {
    height: 100%;
    width: 0%;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    background: #e5e7eb;
    border-radius: 10px;
}

.strength-text {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
}

#strength-label {
    font-weight: 600;
    transition: color 0.3s;
}

/* Password Requirements (from register) */
.password-requirements {
    margin: 10px 0 5px 0;
    padding: 10px;
    background: #f9fafb;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.requirements-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 12px;
}

.requirement {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6b7280;
    font-size: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 2px 0;
}

.requirement.met {
    color: #10b981;
}

.requirement-icon {
    font-size: 18px;
    font-weight: bold;
    min-width: 20px;
    transition: all 0.3s;
    display: inline-block;
}

.requirement-icon::before {
    content: '☐';
    display: inline-block;
    transition: all 0.3s;
}

.requirement.met .requirement-icon {
    color: #10b981;
    transform: scale(1.1);
}

.requirement.met .requirement-icon::before {
    content: '☑';
    animation: checkPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes checkPop {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Strength level colors */
.strength-very-weak {
    background: #ef4444 !important;
}

.strength-weak {
    background: #f59e0b !important;
}

.strength-fair {
    background: #eab308 !important;
}

.strength-good {
    background: #84cc16 !important;
}

.strength-strong {
    background: #10b981 !important;
}

@media (max-width: 768px) {
    .settings-form {
        max-width: 100%;
    }
    
    .requirements-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.parentElement.querySelector('.password-toggle i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker (from register)
const newPasswordInput = document.getElementById('new_password');
const strengthIndicator = document.querySelector('.password-strength-container');
const strengthFill = document.getElementById('strength-fill');
const strengthLabel = document.getElementById('strength-label');

newPasswordInput.addEventListener('input', function() {
    const password = this.value;
    
    if (password.length === 0) {
        strengthIndicator.style.display = 'none';
        // Reset all requirements
        document.querySelectorAll('.requirement').forEach(req => {
            req.classList.remove('met');
        });
        return;
    }
    
    strengthIndicator.style.display = 'block';
    
    // Define requirements
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password)
    };
    
    // Update requirement checkboxes
    Object.keys(requirements).forEach(req => {
        const element = document.querySelector(`[data-requirement="${req}"]`);
        if (element) {
            if (requirements[req]) {
                element.classList.add('met');
            } else {
                element.classList.remove('met');
            }
        }
    });
    
    // Calculate strength (0-4)
    const metCount = Object.values(requirements).filter(Boolean).length;
    
    // Define strength levels
    const strengthLevels = {
        0: { width: '0%', className: 'strength-very-weak', label: 'Very Weak', color: '#6b7280' },
        1: { width: '25%', className: 'strength-very-weak', label: 'Very Weak', color: '#ef4444' },
        2: { width: '50%', className: 'strength-weak', label: 'Weak', color: '#f59e0b' },
        3: { width: '75%', className: 'strength-good', label: 'Good', color: '#84cc16' },
        4: { width: '100%', className: 'strength-strong', label: 'Strong', color: '#10b981' }
    };
    
    const currentStrength = strengthLevels[metCount];
    
    // Remove all strength classes
    strengthFill.className = 'strength-fill';
    
    // Apply new strength
    strengthFill.style.width = currentStrength.width;
    strengthFill.classList.add(currentStrength.className);
    strengthLabel.textContent = currentStrength.label;
    strengthLabel.style.color = currentStrength.color;
});

// Password match validation
const confirmPasswordInput = document.getElementById('confirm_password');
const matchError = document.getElementById('password-match-error');

confirmPasswordInput.addEventListener('input', function() {
    if (this.value !== newPasswordInput.value && this.value.length > 0) {
        matchError.style.display = 'block';
        this.classList.add('error');
    } else {
        matchError.style.display = 'none';
        this.classList.remove('error');
    }
});

// Form validation before submit
document.querySelector('.settings-form').addEventListener('submit', function(e) {
    if (newPasswordInput.value !== confirmPasswordInput.value) {
        e.preventDefault();
        matchError.style.display = 'block';
        confirmPasswordInput.classList.add('error');
        confirmPasswordInput.focus();
    }
});
</script>