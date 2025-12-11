// js/profile-common.js
// Common functions for profile pages

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = ''; // Restore scrolling
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        closeModal(e.target.id);
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const activeModal = document.querySelector('.modal.active');
        if (activeModal) {
            closeModal(activeModal.id);
        }
    }
});

// File input preview
const fileInputs = document.querySelectorAll('input[type="file"]');
fileInputs.forEach(input => {
    input.addEventListener('change', function() {
        const fileName = this.files[0]?.name || 'No file chosen';
        const fileNameDisplay = this.parentElement.parentElement.querySelector('.file-name');
        if (fileNameDisplay) {
            fileNameDisplay.textContent = fileName;
        }
    });
});

// Phone number formatting
const phoneInput = document.getElementById('phone_number');
if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, ''); // Remove non-digits
        
        if (value.length > 11) {
            value = value.slice(0, 11);
        }
        
        // Format as 0XXX XXX XXXX
        if (value.length > 4 && value.length <= 7) {
            value = value.slice(0, 4) + ' ' + value.slice(4);
        } else if (value.length > 7) {
            value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7);
        }
        
        this.value = value;
        
        // Validate
        const cleanValue = value.replace(/\s/g, '');
        const errorElement = document.getElementById('phoneError');
        
        if (cleanValue.length > 0 && (cleanValue.length !== 11 || !cleanValue.startsWith('09'))) {
            this.classList.add('error');
            if (errorElement) {
                errorElement.style.display = 'block';
            }
        } else {
            this.classList.remove('error');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        }
    });
}

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const requiredInputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// Remove error class on input
document.querySelectorAll('.form-input, .form-select, .form-textarea').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('error');
    });
});

// Confirmation dialogs for delete actions
document.querySelectorAll('form[action*="delete"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
});

// Auto-hide alerts after 5 seconds
const alerts = document.querySelectorAll('.alert');
if (alerts.length > 0) {
    setTimeout(() => {
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                alert.remove();
            }, 500);
        });
    }, 5000);
}

// Image preview for profile picture
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // You can add image preview logic here if needed
            console.log('Image loaded:', e.target.result);
        };
        
        reader.readAsDataURL(input.files[0]);
        
        // Update file name display
        const fileName = input.files[0].name;
        const fileNameDisplay = document.getElementById('fileName');
        if (fileNameDisplay) {
            fileNameDisplay.textContent = fileName;
        }
    }
}

// Export functions for global use
window.openModal = openModal;
window.closeModal = closeModal;
window.validateForm = validateForm;
window.previewImage = previewImage;