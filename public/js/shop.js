
let variantCounter = 0;

// Initialize with one variant
document.addEventListener('DOMContentLoaded', function() {
    addVariant();
});

function addVariant() {
    variantCounter++;
    const container = document.getElementById('variantsContainer');
    
    const variantHtml = `
        <div class="variant-item" id="variant-${variantCounter}">
            <div class="variant-header">
                <h3 class="variant-title">Variant #${variantCounter}</h3>
                <button type="button" class="btn-remove-variant" onclick="removeVariant(${variantCounter})">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>

            <div class="variant-image-upload">
                <label class="form-label">Variant Image</label>
                <div class="variant-file-upload-area" id="variantUploadArea-${variantCounter}">
                    <input 
                        type="file" 
                        id="variant_image_${variantCounter}" 
                        name="variants[${variantCounter}][image]" 
                        accept="image/*"
                        class="variant-file-input"
                    >
                    <div class="file-upload-content">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Click to upload</strong> or drag and drop</p>
                        <span>PNG, JPG, JPEG up to 5MB</span>
                    </div>
                    <div class="variant-file-preview" id="variantPreview-${variantCounter}"></div>
                </div>
            </div>

            <div class="variant-fields-grid">
                <div class="form-group">
                    <label class="form-label">Variant Name</label>
                    <input 
                        type="text" 
                        name="variants[${variantCounter}][name]" 
                        class="form-input" 
                        placeholder="e.g., Black - Large"
                        maxlength="100"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">SKU</label>
                    <input 
                        type="text" 
                        name="variants[${variantCounter}][sku]" 
                        class="form-input" 
                        placeholder="e.g., BAG-001-BLK-L"
                        maxlength="50"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label required">Price (₱)</label>
                    <input 
                        type="number" 
                        name="variants[${variantCounter}][price]" 
                        class="form-input variant-price" 
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label required">Stock Quantity</label>
                    <input 
                        type="number" 
                        name="variants[${variantCounter}][quantity]" 
                        class="form-input variant-quantity" 
                        placeholder="0"
                        min="0"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input 
                        type="text" 
                        name="variants[${variantCounter}][color]" 
                        class="form-input" 
                        placeholder="e.g., Black"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Size</label>
                    <input 
                        type="text" 
                        name="variants[${variantCounter}][size]" 
                        class="form-input" 
                        placeholder="e.g., Large"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Material</label>
                    <input 
                        type="text" 
                        name="variants[${variantCounter}][material]" 
                        class="form-input" 
                        placeholder="e.g., Leather"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="variants[${variantCounter}][status]" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', variantHtml);
    initializeVariantUpload(variantCounter);
}

function removeVariant(variantId) {
    const variantCount = document.querySelectorAll('.variant-item').length;
    
    if (variantCount <= 1) {
        alert('You must have at least one variant');
        return;
    }
    
    if (confirm('Are you sure you want to remove this variant?')) {
        const variantElement = document.getElementById(`variant-${variantId}`);
        variantElement.remove();
        updateVariantNumbers();
    }
}

function updateVariantNumbers() {
    const variants = document.querySelectorAll('.variant-item');
    variants.forEach((variant, index) => {
        const title = variant.querySelector('.variant-title');
        title.textContent = `Variant #${index + 1}`;
    });
}

function initializeVariantUpload(variantId) {
    const fileInput = document.getElementById(`variant_image_${variantId}`);
    const uploadArea = document.getElementById(`variantUploadArea-${variantId}`);
    const preview = document.getElementById(`variantPreview-${variantId}`);
    const uploadContent = uploadArea.querySelector('.file-upload-content');
    
    fileInput.addEventListener('change', function(e) {
        handleVariantFile(e.target.files[0], preview, uploadContent);
    });
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });
    
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.add('drag-over');
        });
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.remove('drag-over');
        });
    });
    
    uploadArea.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        
        const event = new Event('change', { bubbles: true });
        fileInput.dispatchEvent(event);
    });
    
    uploadArea.addEventListener('click', function(e) {
        if (!e.target.closest('.variant-remove-image')) {
            fileInput.click();
        }
    });
}

function handleVariantFile(file, preview, uploadContent) {
    if (!file) return;
    
    if (file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5MB');
        return;
    }
    
    if (!file.type.match('image.*')) {
        alert('Please select an image file');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        uploadContent.style.display = 'none';
        preview.innerHTML = `
            <img src="${e.target.result}" alt="Preview">
            <button type="button" class="variant-remove-image" onclick="removeVariantImage(this)">
                <i class="fas fa-times"></i>
            </button>
        `;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function removeVariantImage(button) {
    const preview = button.parentElement;
    const uploadArea = preview.parentElement;
    const fileInput = uploadArea.querySelector('.variant-file-input');
    const uploadContent = uploadArea.querySelector('.file-upload-content');
    
    fileInput.value = '';
    preview.innerHTML = '';
    preview.style.display = 'none';
    uploadContent.style.display = 'flex';
}

// Cover image upload preview
document.getElementById('cover_picture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('coverPreview');
    const uploadContent = document.querySelector('#coverUploadArea .file-upload-content');
    
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            this.value = '';
            return;
        }
        
        if (!file.type.match('image.*')) {
            alert('Please select an image file');
            this.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            uploadContent.style.display = 'none';
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-image" onclick="removeCoverImage()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Cover image drag and drop
const coverUploadArea = document.getElementById('coverUploadArea');
const coverFileInput = document.getElementById('cover_picture');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    coverUploadArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    coverUploadArea.addEventListener(eventName, () => {
        coverUploadArea.classList.add('drag-over');
    });
});

['dragleave', 'drop'].forEach(eventName => {
    coverUploadArea.addEventListener(eventName, () => {
        coverUploadArea.classList.remove('drag-over');
    });
});

coverUploadArea.addEventListener('drop', function(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    coverFileInput.files = files;
    
    const event = new Event('change', { bubbles: true });
    coverFileInput.dispatchEvent(event);
});

coverUploadArea.addEventListener('click', function(e) {
    if (!e.target.closest('.remove-image')) {
        coverFileInput.click();
    }
});

function removeCoverImage() {
    const fileInput = document.getElementById('cover_picture');
    const preview = document.getElementById('coverPreview');
    const uploadContent = document.querySelector('#coverUploadArea .file-upload-content');
    
    fileInput.value = '';
    preview.innerHTML = '';
    preview.style.display = 'none';
    uploadContent.style.display = 'flex';
}

// Save as draft function
function saveDraft() {
    const statusField = document.getElementById('status');
    statusField.value = 'DRAFT';
    document.getElementById('addProductForm').submit();
}

// FIXED: Form validation
document.getElementById('addProductForm').addEventListener('submit', function(e) {
    let isValid = true;
    const errors = [];
    
    // Check basic required fields
    const productName = document.getElementById('product_name').value.trim();
    const shortDescription = document.getElementById('short_description').value.trim();
    const description = document.getElementById('description').value.trim();
    const categoryId = document.getElementById('category').value;
    const coverPicture = document.getElementById('cover_picture').files.length;
    
    if (!productName) {
        errors.push('Product Name is required');
        isValid = false;
    }
    
    if (!shortDescription) {
        errors.push('Short Description is required');
        isValid = false;
    }
    
    if (!description) {
        errors.push('Full Description is required');
        isValid = false;
    }
    
    if (!categoryId) {
        errors.push('Category is required');
        isValid = false;
    }
    
    if (coverPicture === 0) {
        errors.push('Cover Image is required');
        isValid = false;
    }
    
    // Check variants
    const variants = document.querySelectorAll('.variant-item');
    
    if (variants.length === 0) {
        errors.push('Please add at least one product variant');
        isValid = false;
    } else {
        let hasValidVariant = false;
        
        variants.forEach((variant, index) => {
            const price = variant.querySelector('.variant-price').value;
            const quantity = variant.querySelector('.variant-quantity').value;
            
            if (price && quantity) {
                hasValidVariant = true;
                
                if (parseFloat(price) < 0) {
                    errors.push(`Variant #${index + 1}: Price must be 0 or greater`);
                    isValid = false;
                }
                
                if (parseInt(quantity) < 0) {
                    errors.push(`Variant #${index + 1}: Quantity must be 0 or greater`);
                    isValid = false;
                }
            }
        });
        
        if (!hasValidVariant) {
            errors.push('At least one variant must have price and quantity');
            isValid = false;
        }
    }
    
    if (!isValid) {
        e.preventDefault();
        alert(errors.join('\n'));
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    
    return true;
});
