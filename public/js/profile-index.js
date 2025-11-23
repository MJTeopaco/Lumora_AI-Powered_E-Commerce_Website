/**
 * Profile Index JavaScript
 * app/public/js/profile-index.js
 * Handles personal information page functionality
 */

// Phone Number Validation
const phoneInput = document.getElementById('phone_number');
const phoneError = document.getElementById('phoneError');
const phoneForm = document.getElementById('phoneForm');

if (phoneInput) {
    // Format input as user types
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        // Auto-add 0 prefix if starting with 9
        if (value.length === 1 && value === '9') {
            value = '0' + value;
        }
        
        e.target.value = value;
        
        // Limit to 11 digits
        if (value.length > 11) {
            e.target.value = value.slice(0, 11);
        }
    });

    // Format on blur (add spaces)
    phoneInput.addEventListener('blur', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value === '') return;
        
        // Convert 10-digit starting with 9 to 11-digit
        if (value.length === 10 && value.startsWith('9')) {
            value = '0' + value;
        }
        
        // Format as 0XXX XXX XXXX
        if (value.length === 11 && value.startsWith('09')) {
            e.target.value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7, 11);
        }
        
        validatePhone();
    });

    // Remove spaces on focus
    phoneInput.addEventListener('focus', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
    });
}

function validatePhone() {
    if (!phoneInput) return true;
    
    const value = phoneInput.value.replace(/\D/g, '');
    
    if (value === '') {
        phoneInput.classList.remove('error');
        phoneError.style.display = 'none';
        return true;
    }
    
    if (value.length === 11 && value.startsWith('09')) {
        phoneInput.classList.remove('error');
        phoneError.style.display = 'none';
        return true;
    } else {
        phoneInput.classList.add('error');
        phoneError.style.display = 'block';
        return false;
    }
}

// Form Submission
if (phoneForm) {
    phoneForm.addEventListener('submit', function(e) {
        let value = phoneInput.value.replace(/\D/g, '');
        
        // Validate before submit
        if (value && (value.length !== 11 || !value.startsWith('09'))) {
            e.preventDefault();
            phoneInput.focus();
            phoneInput.classList.add('error');
            phoneError.style.display = 'block';
            return false;
        }
        
        // Set clean value without spaces for submission
        phoneInput.value = value;
    });
}

// Initialize phone format on page load
window.addEventListener('DOMContentLoaded', function() {
    if (phoneInput && phoneInput.value) {
        let value = phoneInput.value.replace(/\D/g, '');
        
        if (value.length === 10 && value.startsWith('9')) {
            value = '0' + value;
        }
        
        if (value.length === 11 && value.startsWith('09')) {
            phoneInput.value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7, 11);
        }
    }
});

// Profile Picture Preview
function previewImage(input) {
    const fileName = document.getElementById('fileName');
    
    if (input.files && input.files[0]) {
        fileName.textContent = input.files[0].name;
        
        // Optional: Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            // You can add preview image here if needed
            console.log('Image loaded');
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        fileName.textContent = 'No file chosen';
    }
}