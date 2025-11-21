<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="dashboard-title">
            <i class="fas fa-plus-circle"></i> Add New Product
        </h1>
        <p class="dashboard-subtitle">Fill in the details to create a new product listing</p>
    </div>

    <form id="addProductForm" action="/shop/products/store" method="POST" enctype="multipart/form-data" class="product-form">
        
        <!-- Basic Information Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                <p>Enter the essential details about your product</p>
            </div>
            
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="product_name" class="form-label required">Product Name</label>
                    <input 
                        type="text" 
                        id="product_name" 
                        name="product_name" 
                        class="form-input" 
                        placeholder="e.g., Premium Leather Backpack"
                        required
                        maxlength="200"
                    >
                    <span class="form-hint">Maximum 200 characters</span>
                </div>

                <div class="form-group full-width">
                    <label for="short_description" class="form-label required">Short Description</label>
                    <input 
                        type="text" 
                        id="short_description" 
                        name="short_description" 
                        class="form-input" 
                        placeholder="Brief description that appears in product listings"
                        required
                        maxlength="255"
                    >
                    <span class="form-hint">Maximum 255 characters - This appears in search results</span>
                </div>

                <div class="form-group full-width">
                    <label for="description" class="form-label required">Full Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-textarea" 
                        rows="6"
                        placeholder="Provide detailed information about your product..."
                        required
                    ></textarea>
                    <span class="form-hint">Include key features, materials, dimensions, and usage instructions</span>
                </div>

                <div class="form-group">
                    <label for="category" class="form-label required">Category</label>
                    <select id="category" name="category_id" class="form-select" required>
                        <option value="">Select a category</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['category_id'] ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label required">Product Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="DRAFT">Draft - Not visible to customers</option>
                        <option value="PUBLISHED">Published - Visible in store</option>
                        <option value="UNPUBLISHED">Unpublished - Temporarily hidden</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Product Cover Image Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h2><i class="fas fa-image"></i> Product Cover Image</h2>
                <p>Upload the main cover image for your product</p>
            </div>
            
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="cover_picture" class="form-label required">Cover Image</label>
                    <div class="file-upload-area" id="coverUploadArea">
                        <input 
                            type="file" 
                            id="cover_picture" 
                            name="cover_picture" 
                            accept="image/*"
                            class="file-input"
                            required
                        >
                        <div class="file-upload-content">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p><strong>Click to upload</strong> or drag and drop</p>
                            <span>PNG, JPG, JPEG up to 5MB</span>
                        </div>
                        <div class="file-preview" id="coverPreview"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Variants Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h2><i class="fas fa-boxes"></i> Product Variants</h2>
                <p>Add different variations of your product (e.g., sizes, colors, styles)</p>
            </div>
            
            <div id="variantsContainer">
                <!-- Variant items will be added here dynamically -->
            </div>

            <button type="button" class="btn btn-outline btn-add-variant" onclick="addVariant()">
                <i class="fas fa-plus"></i> Add Variant
            </button>
        </div>

        <!-- Additional Information Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h2><i class="fas fa-tags"></i> Additional Information</h2>
                <p>Optional details to help customers find your product</p>
            </div>
            
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="tags" class="form-label">Tags</label>
                    <input 
                        type="text" 
                        id="tags" 
                        name="tags" 
                        class="form-input" 
                        placeholder="leather, backpack, travel, premium (separated by commas)"
                    >
                    <span class="form-hint">Add keywords to help customers find your product</span>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="button" class="btn btn-outline" onclick="saveDraft()">
                <i class="fas fa-save"></i> Save as Draft
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check"></i> Create Product
            </button>
        </div>
    </form>
</div>

<style>
.variant-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
}

.variant-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #dee2e6;
}

.variant-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
}

.btn-remove-variant {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.3s;
}

.btn-remove-variant:hover {
    background: #c82333;
}

.variant-image-upload {
    margin-bottom: 15px;
}

.variant-file-upload-area {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: white;
    position: relative;
}

.variant-file-upload-area:hover,
.variant-file-upload-area.drag-over {
    border-color: #007bff;
    background: #f0f8ff;
}

.variant-file-input {
    display: none;
}

.variant-file-preview {
    display: none;
    position: relative;
}

.variant-file-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 5px;
}

.variant-remove-image {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-size: 14px;
}

.btn-add-variant {
    width: 100%;
    margin-top: 10px;
}

.variant-fields-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

@media (max-width: 768px) {
    .variant-fields-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
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
                        class="form-input" 
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
                        class="form-input" 
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
    
    // File input change event
    fileInput.addEventListener('change', function(e) {
        handleVariantFile(e.target.files[0], preview, uploadContent);
    });
    
    // Drag and drop events
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
    
    // Validate file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5MB');
        return;
    }
    
    // Validate file type
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
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            this.value = '';
            return;
        }
        
        // Validate file type
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

// Form validation
document.getElementById('addProductForm').addEventListener('submit', function(e) {
    const variants = document.querySelectorAll('.variant-item');
    
    if (variants.length === 0) {
        e.preventDefault();
        alert('Please add at least one product variant');
        return false;
    }
    
    let isValid = true;
    variants.forEach((variant, index) => {
        const price = variant.querySelector('input[name*="[price]"]').value;
        const quantity = variant.querySelector('input[name*="[quantity]"]').value;
        
        if (!price || parseFloat(price) < 0) {
            isValid = false;
            alert(`Variant #${index + 1}: Please enter a valid price`);
        }
        
        if (!quantity || parseInt(quantity) < 0) {
            isValid = false;
            alert(`Variant #${index + 1}: Please enter a valid quantity`);
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
});
</script>