<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">
                <i class="fas fa-edit"></i> Edit Product
            </h1>
            <p class="dashboard-subtitle">Update your product information</p>
        </div>
        <a href="/shop/products" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <form id="editProductForm" action="/shop/products/update/<?= $product['product_id'] ?>" method="POST" enctype="multipart/form-data" class="product-form">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
        
        <div class="form-section">
            <div class="form-section-header">
                <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                <p>Update the essential details about your product</p>
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
                        value="<?= htmlspecialchars($product['name']) ?>"
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
                        value="<?= htmlspecialchars($product['short_description']) ?>"
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
                    ><?= htmlspecialchars($product['description']) ?></textarea>
                    <span class="form-hint">Include key features, materials, dimensions, and usage instructions</span>
                </div>

                <div class="form-group">
                    <label for="category" class="form-label required">Category</label>
                    <select id="category" name="category_id" class="form-select" required>
                        <option value="">Select a category</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['category_id'] ?>" 
                                    <?= (isset($product['category_id']) && $product['category_id'] == $category['category_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label required">Product Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="PUBLISHED" <?= $product['status'] === 'PUBLISHED' ? 'selected' : '' ?>>Published - Visible in store</option>
                        <option value="UNPUBLISHED" <?= $product['status'] === 'UNPUBLISHED' ? 'selected' : '' ?>>Unpublished - Temporarily hidden</option>
                        <option value="DRAFT" <?= $product['status'] === 'DRAFT' ? 'selected' : '' ?>>Draft - Not visible</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-header">
                <h2><i class="fas fa-image"></i> Product Cover Image</h2>
                <p>Update the main cover image (optional - leave empty to keep current image)</p>
            </div>
            
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="cover_picture" class="form-label">Cover Image</label>
                    
                    <?php if (!empty($product['cover_picture'])): ?>
                        <div class="current-image-preview" style="margin-bottom: 15px;">
                            <p style="font-size: 0.875rem; color: #666; margin-bottom: 10px;"><strong>Current Image:</strong></p>
                            <img src="/<?= htmlspecialchars($product['cover_picture']) ?>" alt="Current cover" style="max-width: 200px; border-radius: 8px; border: 1px solid #e0e0e0;">
                        </div>
                    <?php endif; ?>
                    
                    <div class="file-upload-area" id="coverUploadArea">
                        <input 
                            type="file" 
                            id="cover_picture" 
                            name="cover_picture" 
                            accept="image/*"
                            class="file-input"
                        >
                        <div class="file-upload-content">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p><strong>Click to upload new image</strong> or drag and drop</p>
                            <span>PNG, JPG, JPEG up to 5MB (Leave empty to keep current image)</span>
                        </div>
                        <div class="file-preview" id="coverPreview"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-header">
                <h2><i class="fas fa-boxes"></i> Product Variants</h2>
                <p>Manage different variations of your product</p>
            </div>
            
            <div id="variantsContainer">
                <?php if (!empty($product['variants'])): ?>
                    <?php foreach ($product['variants'] as $index => $variant): ?>
                        <div class="variant-item" id="variant-<?= $index ?>">
                            <input type="hidden" name="variants[<?= $index ?>][variant_id]" value="<?= $variant['variant_id'] ?>">
                            
                            <div class="variant-header">
                                <h3 class="variant-title">Variant #<?= $index + 1 ?></h3>
                                <?php if (count($product['variants']) > 1): ?>
                                    <button type="button" class="btn-remove-variant" onclick="removeEditVariant(<?= $index ?>)">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($variant['product_picture'])): ?>
                                <div class="current-image-preview" style="margin-bottom: 15px;">
                                    <p style="font-size: 0.875rem; color: #666; margin-bottom: 10px;"><strong>Current Variant Image:</strong></p>
                                    <img src="/<?= htmlspecialchars($variant['product_picture']) ?>" alt="Variant image" style="max-width: 150px; border-radius: 8px; border: 1px solid #e0e0e0;">
                                </div>
                            <?php endif; ?>

                            <div class="variant-image-upload">
                                <label class="form-label">Variant Image</label>
                                <div class="variant-file-upload-area" id="variantUploadArea-<?= $index ?>">
                                    <input 
                                        type="file" 
                                        id="variant_image_<?= $index ?>" 
                                        name="variants[<?= $index ?>][product_picture]" 
                                        accept="image/*"
                                        class="variant-file-input"
                                    >
                                    <div class="file-upload-content">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p><strong>Click to upload</strong> or drag and drop</p>
                                        <span>PNG, JPG, JPEG up to 5MB (optional)</span>
                                    </div>
                                    <div class="variant-file-preview" id="variantPreview-<?= $index ?>"></div>
                                </div>
                            </div>
                            
                            <div class="variant-fields-grid">
                                <div class="form-group">
                                    <label class="form-label">Variant Name</label>
                                    <input 
                                        type="text" 
                                        name="variants[<?= $index ?>][name]" 
                                        class="form-input" 
                                        placeholder="e.g., Black - Large"
                                        value="<?= htmlspecialchars($variant['variant_name'] ?? '') ?>"
                                        maxlength="100"
                                    >
                                </div>

                                <div class="form-group">
                                    <label class="form-label">SKU</label>
                                    <input 
                                        type="text" 
                                        name="variants[<?= $index ?>][sku]" 
                                        class="form-input" 
                                        placeholder="Auto-generated"
                                        value="<?= htmlspecialchars($variant['sku'] ?? '') ?>"
                                        readonly
                                    >
                                </div>

                                <div class="form-group">
                                    <label class="form-label required">Price (₱)</label>
                                    <input 
                                        type="number" 
                                        name="variants[<?= $index ?>][price]" 
                                        class="form-input variant-price" 
                                        placeholder="0.00"
                                        step="0.01"
                                        min="0"
                                        value="<?= htmlspecialchars($variant['price'] ?? '') ?>"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label class="form-label required">Stock Quantity</label>
                                    <input 
                                        type="number" 
                                        name="variants[<?= $index ?>][quantity]" 
                                        class="form-input variant-quantity" 
                                        placeholder="0"
                                        min="0"
                                        value="<?= htmlspecialchars($variant['quantity'] ?? '') ?>"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Color</label>
                                    <input 
                                        type="text" 
                                        name="variants[<?= $index ?>][color]" 
                                        class="form-input" 
                                        placeholder="e.g., Black"
                                        value="<?= htmlspecialchars($variant['color'] ?? '') ?>"
                                    >
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Size</label>
                                    <input 
                                        type="text" 
                                        name="variants[<?= $index ?>][size]" 
                                        class="form-input" 
                                        placeholder="e.g., Large"
                                        value="<?= htmlspecialchars($variant['size'] ?? '') ?>"
                                    >
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Material</label>
                                    <input 
                                        type="text" 
                                        name="variants[<?= $index ?>][material]" 
                                        class="form-input" 
                                        placeholder="e.g., Leather"
                                        value="<?= htmlspecialchars($variant['material'] ?? '') ?>"
                                    >
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <select name="variants[<?= $index ?>][status]" class="form-select">
                                        <option value="1" <?= ($variant['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                                        <option value="0" <?= ($variant['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="button" class="btn btn-outline btn-add-variant" onclick="addEditVariant()">
                <i class="fas fa-plus"></i> Add New Variant
            </button>
        </div>

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
                        value="<?= htmlspecialchars($product['tags'] ?? '') ?>"
                    >
                    <span class="form-hint">Add keywords to help customers find your product</span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="/shop/products" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Product
            </button>
        </div>
    </form>
</div>

<script src="/js/shop.js"></script>
<script>
// Counter for new variants (start from existing count)
let editVariantCounter = <?= count($product['variants'] ?? []) ?>;

// Initialize existing variant uploads
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($product['variants'])): ?>
        <?php foreach ($product['variants'] as $index => $variant): ?>
            initializeVariantUpload(<?= $index ?>);
        <?php endforeach; ?>
    <?php endif; ?>
});

// Add new variant function for edit page
function addEditVariant() {
    const container = document.getElementById('variantsContainer');
    
    const variantHtml = `
        <div class="variant-item" id="variant-${editVariantCounter}">
            <input type="hidden" name="variants[${editVariantCounter}][variant_id]" value="">
            
            <div class="variant-header">
                <h3 class="variant-title">Variant #${editVariantCounter + 1} (New)</h3>
                <button type="button" class="btn-remove-variant" onclick="removeEditVariant(${editVariantCounter})">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>

            <div class="variant-image-upload">
                <label class="form-label">Variant Image</label>
                <div class="variant-file-upload-area" id="variantUploadArea-${editVariantCounter}">
                    <input 
                        type="file" 
                        id="variant_image_${editVariantCounter}" 
                        name="variants[${editVariantCounter}][product_picture]" 
                        accept="image/*"
                        class="variant-file-input"
                    >
                    <div class="file-upload-content">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Click to upload</strong> or drag and drop</p>
                        <span>PNG, JPG, JPEG up to 5MB</span>
                    </div>
                    <div class="variant-file-preview" id="variantPreview-${editVariantCounter}"></div>
                </div>
            </div>
            
            <div class="variant-fields-grid">
                <div class="form-group">
                    <label class="form-label">Variant Name</label>
                    <input 
                        type="text" 
                        name="variants[${editVariantCounter}][name]" 
                        class="form-input" 
                        placeholder="e.g., Black - Large"
                        maxlength="100"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">SKU</label>
                    <input 
                        type="text" 
                        name="variants[${editVariantCounter}][sku]" 
                        class="form-input" 
                        placeholder="Auto-generated"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label class="form-label required">Price (₱)</label>
                    <input 
                        type="number" 
                        name="variants[${editVariantCounter}][price]" 
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
                        name="variants[${editVariantCounter}][quantity]" 
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
                        name="variants[${editVariantCounter}][color]" 
                        class="form-input" 
                        placeholder="e.g., Black"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Size</label>
                    <input 
                        type="text" 
                        name="variants[${editVariantCounter}][size]" 
                        class="form-input" 
                        placeholder="e.g., Large"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Material</label>
                    <input 
                        type="text" 
                        name="variants[${editVariantCounter}][material]" 
                        class="form-input" 
                        placeholder="e.g., Leather"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="variants[${editVariantCounter}][status]" class="form-select">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', variantHtml);
    initializeVariantUpload(editVariantCounter);
    editVariantCounter++;
    updateEditVariantNumbers();
}

// Remove variant
function removeEditVariant(index) {
    const variantCount = document.querySelectorAll('.variant-item').length;
    
    if (variantCount <= 1) {
        alert('You must have at least one variant');
        return;
    }
    
    if (confirm('Are you sure you want to remove this variant?')) {
        const variantElement = document.getElementById(`variant-${index}`);
        if (variantElement) {
            variantElement.remove();
            updateEditVariantNumbers();
        }
    }
}

function updateEditVariantNumbers() {
    const variants = document.querySelectorAll('.variant-item');
    variants.forEach((variant, index) => {
        const title = variant.querySelector('.variant-title');
        const isNew = variant.querySelector('input[name*="[variant_id]"]').value === '';
        title.textContent = `Variant #${index + 1}${isNew ? ' (New)' : ''}`;
    });
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

// Form validation
document.getElementById('editProductForm').addEventListener('submit', function(e) {
    const variants = document.querySelectorAll('.variant-item');
    let hasValidVariant = false;
    
    variants.forEach(variant => {
        const price = variant.querySelector('.variant-price').value;
        const quantity = variant.querySelector('.variant-quantity').value;
        
        if (price && quantity) {
            hasValidVariant = true;
        }
    });
    
    if (!hasValidVariant) {
        e.preventDefault();
        alert('At least one variant must have price and quantity');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    
    return true;
});
</script>