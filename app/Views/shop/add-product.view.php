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
                        <option value="PUBLISHED">Published - Visible in store</option>
                        <!--
                        <option value="DRAFT">Draft - Not visible to customers</option>
                        -->
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
            <!--
            <button type="button" class="btn btn-outline" onclick="saveDraft()">
                <i class="fas fa-save"></i> Save as Draft
            </button>
            -->
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check"></i> Create Product
            </button>
        </div>
    </form>
</div>



