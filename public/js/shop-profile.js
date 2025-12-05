/**
 * Shop Profile JavaScript
 * Handles all interactive functionality for the shop profile page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initTabs();
    initImageUploads();
    initForms();
    initCharacterCounter();
});

/**
 * Tab Navigation
 */
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all tabs and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(`${targetTab}-tab`).classList.add('active');
        });
    });
}

/**
 * Image Upload Handlers
 */
function initImageUploads() {
    // Banner upload
    const bannerInput = document.getElementById('bannerInput');
    const editBannerBtn = document.getElementById('editBannerBtn');
    const bannerPreview = document.getElementById('bannerPreview');

    if (editBannerBtn && bannerInput) {
        editBannerBtn.addEventListener('click', () => bannerInput.click());

        bannerInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (!validateImageFile(file, 5)) return;
                uploadImage(file, 'banner');
            }
        });
    }

    // Profile picture upload
    const profileInput = document.getElementById('profileInput');
    const editProfileBtn = document.getElementById('editProfileBtn');
    const profilePreview = document.getElementById('profilePreview');

    if (editProfileBtn && profileInput) {
        editProfileBtn.addEventListener('click', () => profileInput.click());

        profileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (!validateImageFile(file, 3)) return;
                uploadImage(file, 'profile');
            }
        });
    }
}

/**
 * Validate image file
 */
function validateImageFile(file, maxSizeMB) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!allowedTypes.includes(file.type)) {
        showToast('Please select a valid image file (JPEG, PNG, GIF, or WebP)', 'error');
        return false;
    }

    const maxSize = maxSizeMB * 1024 * 1024; // Convert MB to bytes
    if (file.size > maxSize) {
        showToast(`File size must be less than ${maxSizeMB}MB`, 'error');
        return false;
    }

    return true;
}

/**
 * Upload image (banner or profile)
 */
function uploadImage(file, type) {
    const formData = new FormData();
    const endpoint = type === 'banner' ? '/shop/profile/upload-banner' : '/shop/profile/upload-profile';
    const fieldName = type === 'banner' ? 'banner' : 'profile';

    formData.append(fieldName, file);

    showLoading();

    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast(data.message, 'success');
            
            // Update preview
            updateImagePreview(type, data.url);
            
            // Reload page after short delay to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || `Failed to upload ${type}`, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Upload error:', error);
        showToast(`Error uploading ${type}. Please try again.`, 'error');
    });
}

/**
 * Update image preview
 */
function updateImagePreview(type, url) {
    const previewElement = document.getElementById(type === 'banner' ? 'bannerPreview' : 'profilePreview');
    
    if (previewElement) {
        if (type === 'banner') {
            if (previewElement.classList.contains('banner-placeholder')) {
                const img = document.createElement('img');
                img.src = url;
                img.alt = 'Shop Banner';
                img.className = 'banner-image';
                previewElement.parentElement.replaceChild(img, previewElement);
            } else {
                previewElement.src = url;
            }
        } else {
            if (previewElement.classList.contains('avatar-placeholder')) {
                const img = document.createElement('img');
                img.src = url;
                img.alt = 'Shop Profile';
                previewElement.parentElement.replaceChild(img, previewElement);
            } else {
                previewElement.src = url;
            }
        }
    }
}

/**
 * Form Handlers
 */
function initForms() {
    // Basic Information Form
    const basicInfoForm = document.getElementById('basicInfoForm');
    const resetBasicBtn = document.getElementById('resetBasicBtn');

    if (basicInfoForm) {
        const originalBasicData = new FormData(basicInfoForm);

        basicInfoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, '/shop/profile/update-basic-info', 'Basic information updated successfully');
        });

        if (resetBasicBtn) {
            resetBasicBtn.addEventListener('click', function() {
                resetForm(basicInfoForm, originalBasicData);
            });
        }
    }

    // Address Form
    const addressForm = document.getElementById('addressForm');
    const resetAddressBtn = document.getElementById('resetAddressBtn');

    if (addressForm) {
        const originalAddressData = new FormData(addressForm);

        addressForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, '/shop/profile/update-address', 'Address updated successfully');
        });

        if (resetAddressBtn) {
            resetAddressBtn.addEventListener('click', function() {
                resetForm(addressForm, originalAddressData);
            });
        }
    }
}

/**
 * Submit form via AJAX
 */
function submitForm(form, endpoint, successMessage) {
    const formData = new FormData(form);

    // Basic validation
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = 'var(--error)';
            setTimeout(() => {
                field.style.borderColor = '';
            }, 2000);
        }
    });

    if (!isValid) {
        showToast('Please fill in all required fields', 'error');
        return;
    }

    showLoading();

    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast(data.message || successMessage, 'success');
            
            // Update page title if shop name changed
            if (formData.has('shop_name')) {
                document.title = `Shop Profile - ${formData.get('shop_name')}`;
            }
        } else {
            showToast(data.message || 'Failed to update information', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Form submission error:', error);
        showToast('Error submitting form. Please try again.', 'error');
    });
}

/**
 * Reset form to original values
 */
function resetForm(form, originalData) {
    for (let [key, value] of originalData.entries()) {
        const field = form.querySelector(`[name="${key}"]`);
        if (field) {
            field.value = value;
        }
    }
    
    showToast('Form reset to original values', 'success');
}

/**
 * Character Counter
 */
function initCharacterCounter() {
    const descriptionTextarea = document.getElementById('shop_description');
    const charCountSpan = document.getElementById('descCharCount');

    if (descriptionTextarea && charCountSpan) {
        // Set initial count
        charCountSpan.textContent = descriptionTextarea.value.length;

        // Update count on input
        descriptionTextarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            const maxLength = this.getAttribute('maxlength');
            
            charCountSpan.textContent = currentLength;

            // Change color based on length
            if (currentLength >= maxLength * 0.9) {
                charCountSpan.style.color = 'var(--error)';
            } else if (currentLength >= maxLength * 0.7) {
                charCountSpan.style.color = 'var(--warning)';
            } else {
                charCountSpan.style.color = 'var(--primary-gold)';
            }
        });
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    
    if (!toast) return;

    // Set message and type
    toast.textContent = message;
    toast.className = `toast ${type}`;

    // Add icon based on type
    const icon = document.createElement('i');
    icon.className = 'fas';
    
    switch(type) {
        case 'success':
            icon.classList.add('fa-check-circle');
            break;
        case 'error':
            icon.classList.add('fa-exclamation-circle');
            break;
        case 'warning':
            icon.classList.add('fa-exclamation-triangle');
            break;
        default:
            icon.classList.add('fa-info-circle');
    }

    toast.insertBefore(icon, toast.firstChild);

    // Show toast
    toast.classList.add('show');

    // Hide after 4 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.textContent = '';
        }, 300);
    }, 4000);
}

/**
 * Show loading overlay
 */
function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.add('show');
    }
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.remove('show');
    }
}

/**
 * Phone number formatting (Philippine format)
 */
const phoneInput = document.getElementById('contact_phone');
if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        // Limit to 11 digits for Philippine numbers
        if (value.length > 11) {
            value = value.slice(0, 11);
        }

        // Format as 09XX XXX XXXX
        if (value.length > 7) {
            value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7);
        } else if (value.length > 4) {
            value = value.slice(0, 4) + ' ' + value.slice(4);
        }

        e.target.value = value;
    });
}

/**
 * Postal code validation
 */
const postalCodeInput = document.getElementById('postal_code');
if (postalCodeInput) {
    postalCodeInput.addEventListener('input', function(e) {
        // Only allow numbers for Philippine postal codes
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
    });
}

/**
 * Email validation helper
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Real-time email validation
 */
const emailInput = document.getElementById('contact_email');
if (emailInput) {
    emailInput.addEventListener('blur', function() {
        if (this.value && !isValidEmail(this.value)) {
            this.style.borderColor = 'var(--error)';
            showToast('Please enter a valid email address', 'error');
        } else {
            this.style.borderColor = '';
        }
    });
}

/**
 * Prevent form submission on Enter key (except in textareas)
 */
document.querySelectorAll('.profile-form').forEach(form => {
    form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });
});

/**
 * Smooth scroll to top when switching tabs
 */
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});

/**
 * Auto-save indicator (optional enhancement)
 */
let autoSaveTimeout;
function showAutoSaveIndicator() {
    clearTimeout(autoSaveTimeout);
    
    const indicator = document.createElement('div');
    indicator.className = 'autosave-indicator';
    indicator.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Saving...';
    indicator.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--primary-gold);
        color: var(--white);
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        z-index: 9998;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: var(--shadow-md);
    `;
    
    document.body.appendChild(indicator);
    
    autoSaveTimeout = setTimeout(() => {
        indicator.remove();
    }, 2000);
}

/**
 * Handle browser back button
 */
window.addEventListener('popstate', function() {
    // Optionally prompt user if there are unsaved changes
    const forms = document.querySelectorAll('.profile-form');
    let hasChanges = false;
    
    forms.forEach(form => {
        const formData = new FormData(form);
        // Check if form has been modified (implementation depends on your needs)
    });
});

/**
 * Image drag and drop support (enhancement)
 */
function initDragAndDrop() {
    const bannerWrapper = document.querySelector('.banner-image-wrapper');
    const profileAvatar = document.querySelector('.profile-avatar');

    if (bannerWrapper) {
        setupDragAndDrop(bannerWrapper, document.getElementById('bannerInput'));
    }

    if (profileAvatar) {
        setupDragAndDrop(profileAvatar, document.getElementById('profileInput'));
    }
}

function setupDragAndDrop(element, input) {
    element.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.opacity = '0.7';
    });

    element.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.opacity = '1';
    });

    element.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.opacity = '1';
        
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
}

// Initialize drag and drop
initDragAndDrop();

console.log('Shop Profile JS initialized successfully');