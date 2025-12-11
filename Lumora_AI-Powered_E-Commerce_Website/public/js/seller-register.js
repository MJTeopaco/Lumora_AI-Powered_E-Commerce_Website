/**
 * Seller Registration Form JavaScript
 * app/public/js/seller-register.js
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('sellerRegistrationForm');
    const phoneInput = document.getElementById('contact_phone');
    const postalCodeInput = document.getElementById('postal_code');

    // Form validation
    if (form) {
        form.addEventListener('submit', function(e) {
            const phone = phoneInput.value;
            const postalCode = postalCodeInput.value;
            
            // Validate phone format
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                showError('Please enter a valid phone number (minimum 10 digits)');
                phoneInput.focus();
                return false;
            }
            
            // Validate postal code
            if (!/^\d{4}$/.test(postalCode)) {
                e.preventDefault();
                showError('Please enter a valid 4-digit postal code');
                postalCodeInput.focus();
                return false;
            }

            // Show loading state
            const submitBtn = form.querySelector('.btn-submit');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        });
    }

    // Auto-format phone number
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Add +63 prefix if user enters 9XXXXXXXX format
            if (value.length === 10 && value.startsWith('9')) {
                value = '63' + value;
            }
            
            // Format with + prefix
            if (value.startsWith('63')) {
                value = '+' + value;
            }
            
            e.target.value = value;
        });

        // Placeholder hint
        phoneInput.addEventListener('focus', function() {
            if (this.value === '') {
                this.placeholder = '+63 912 345 6789';
            }
        });
    }

    // Postal code validation
    if (postalCodeInput) {
        postalCodeInput.addEventListener('input', function(e) {
            // Only allow numbers
            this.value = this.value.replace(/\D/g, '');
            
            // Limit to 4 digits
            if (this.value.length > 4) {
                this.value = this.value.slice(0, 4);
            }
        });
    }

    // Shop name slug preview (optional enhancement)
    const shopNameInput = document.getElementById('shop_name');
    if (shopNameInput) {
        shopNameInput.addEventListener('input', function() {
            const slug = generateSlug(this.value);
            updateSlugPreview(slug);
        });
    }

    // Real-time validation for email
    const emailInput = document.getElementById('contact_email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            validateEmail(this.value);
        });
    }
});

/**
 * Show error message
 */
function showError(message) {
    // Remove existing error if any
    const existingError = document.querySelector('.alert.error');
    if (existingError) {
        existingError.remove();
    }

    // Create new error alert
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert error';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
    `;

    // Insert at top of form content
    const formContent = document.querySelector('.form-content');
    formContent.insertBefore(errorDiv, formContent.firstChild);

    // Scroll to error
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Auto-remove after 5 seconds
    setTimeout(() => {
        errorDiv.style.opacity = '0';
        setTimeout(() => errorDiv.remove(), 300);
    }, 5000);
}

/**
 * Generate slug from shop name
 */
function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

/**
 * Update slug preview (optional feature)
 */
function updateSlugPreview(slug) {
    let preview = document.getElementById('slug-preview');
    
    if (!preview && slug) {
        const shopNameGroup = document.querySelector('#shop_name').closest('.form-group');
        preview = document.createElement('div');
        preview.id = 'slug-preview';
        preview.className = 'helper-text';
        preview.style.color = '#059669';
        shopNameGroup.appendChild(preview);
    }
    
    if (preview) {
        if (slug) {
            preview.innerHTML = `
                <i class="fas fa-link"></i>
                <span>Shop URL: lumora.com/shop/<strong>${slug}</strong></span>
            `;
        } else {
            preview.innerHTML = '';
        }
    }
}

/**
 * Validate email format
 */
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const emailInput = document.getElementById('contact_email');
    
    if (email && !emailRegex.test(email)) {
        emailInput.style.borderColor = '#e74c3c';
        showFieldError(emailInput, 'Please enter a valid email address');
    } else {
        emailInput.style.borderColor = '#10b981';
        removeFieldError(emailInput);
    }
}

/**
 * Show field-specific error
 */
function showFieldError(input, message) {
    removeFieldError(input);
    
    const errorSpan = document.createElement('span');
    errorSpan.className = 'field-error';
    errorSpan.style.color = '#e74c3c';
    errorSpan.style.fontSize = '13px';
    errorSpan.style.marginTop = '5px';
    errorSpan.style.display = 'block';
    errorSpan.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    
    input.parentNode.appendChild(errorSpan);
}

/**
 * Remove field-specific error
 */
function removeFieldError(input) {
    const existingError = input.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}