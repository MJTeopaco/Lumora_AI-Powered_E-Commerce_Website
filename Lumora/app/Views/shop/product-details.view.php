<?php
// app/Views/shop/product-details.view.php

// Calculate total stock and price range for the overview
$totalStock = 0;
$prices = [];
foreach ($variants as $v) {
    $totalStock += $v['quantity'];
    $prices[] = $v['price'];
}
$minPrice = !empty($prices) ? min($prices) : 0;
$maxPrice = !empty($prices) ? max($prices) : 0;
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-left">
            <div class="breadcrumb-nav">
                <a href="/shop/products" class="text-muted">Products</a>
                <i class="fas fa-chevron-right separator"></i>
                <span class="active">Details</span>
            </div>
            <h1 class="dashboard-title">
                <?= htmlspecialchars($product['name']) ?>
            </h1>
            <div class="product-meta-badges">
                <span class="status-badge status-<?= strtolower($product['status']) ?>">
                    <?= $product['status'] ?>
                </span>
                <span class="category-badge">
                    <i class="fas fa-tag"></i> <?= htmlspecialchars($product['categories'] ?? 'Uncategorized') ?>
                </span>
            </div>
        </div>
        
        <div class="header-actions">
            <a href="/product/<?= $product['slug'] ?>" target="_blank" class="btn btn-outline">
                <i class="fas fa-external-link-alt"></i> View in Store
            </a>
            <a href="/shop/products/edit/<?= $product['product_id'] ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Product
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-gold">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">
                    <?php if ($minPrice == $maxPrice): ?>
                        ₱<?= number_format($minPrice, 2) ?>
                    <?php else: ?>
                        ₱<?= number_format($minPrice, 2) ?> - ₱<?= number_format($maxPrice, 2) ?>
                    <?php endif; ?>
                </div>
                <div class="stat-label">Price Range</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon <?= $totalStock == 0 ? 'bg-red' : ($totalStock < 10 ? 'bg-yellow' : 'bg-black') ?>">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($totalStock) ?></div>
                <div class="stat-label">Total Stock Available</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-gray">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= count($variants) ?></div>
                <div class="stat-label">Variants</div>
            </div>
        </div>
    </div>

    <div class="details-layout">
        <div class="details-main">
            <div class="content-card">
                <h3 class="card-title">Product Images</h3>
                <div class="image-gallery-container">
                    <div class="main-image-wrapper">
                        <?php if (!empty($product['cover_picture'])): ?>
                            <img src="/<?= htmlspecialchars($product['cover_picture']) ?>" alt="Main Image" id="detailMainImage">
                        <?php else: ?>
                            <div class="placeholder-box">No Cover Image</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="thumbnails-grid">
                        <?php if (!empty($product['cover_picture'])): ?>
                            <div class="thumb-item active" onclick="updateMainImage(this, '/<?= htmlspecialchars($product['cover_picture']) ?>')">
                                <img src="/<?= htmlspecialchars($product['cover_picture']) ?>" alt="Cover">
                            </div>
                        <?php endif; ?>

                        <?php foreach ($variants as $variant): ?>
                            <?php if (!empty($variant['product_picture']) && $variant['product_picture'] !== $product['cover_picture']): ?>
                                <div class="thumb-item" onclick="updateMainImage(this, '/<?= htmlspecialchars($variant['product_picture']) ?>')">
                                    <img src="/<?= htmlspecialchars($variant['product_picture']) ?>" alt="Variant">
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <h3 class="card-title">Description</h3>
                <div class="description-content">
                    <?php if (!empty($product['short_description'])): ?>
                        <div class="short-desc">
                            <strong>Short Description:</strong>
                            <p><?= nl2br(htmlspecialchars($product['short_description'])) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="full-desc">
                        <strong>Full Description:</strong>
                        <div class="desc-text">
                            <?= !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : '<em class="text-muted">No full description provided.</em>' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="details-sidebar">
            <div class="content-card">
                <div class="card-header-flex">
                    <h3 class="card-title">Inventory & Variants</h3>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table variants-table">
                        <thead>
                            <tr>
                                <th>Variant</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($variants as $variant): ?>
                                <tr>
                                    <td>
                                        <div class="variant-cell">
                                            <?php if (!empty($variant['product_picture'])): ?>
                                                <img src="/<?= htmlspecialchars($variant['product_picture']) ?>" class="variant-thumb-sm">
                                            <?php else: ?>
                                                <div class="variant-thumb-placeholder"><i class="fas fa-image"></i></div>
                                            <?php endif; ?>
                                            <div class="variant-info">
                                                <span class="variant-name"><?= htmlspecialchars($variant['variant_name'] ?: 'Standard') ?></span>
                                                <small class="text-muted">
                                                    <?= !empty($variant['color']) ? $variant['color'] : '' ?>
                                                    <?= !empty($variant['size']) ? ' · ' . $variant['size'] : '' ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-mono"><?= htmlspecialchars($variant['sku']) ?></td>
                                    <td class="text-gold">₱<?= number_format($variant['price'], 2) ?></td>
                                    <td>
                                        <span class="<?= $variant['quantity'] == 0 ? 'text-danger' : ($variant['quantity'] < 10 ? 'text-warning' : 'text-success') ?>">
                                            <?= $variant['quantity'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($variant['is_active']): ?>
                                            <span class="badge-dot bg-success" title="Active"></span>
                                        <?php else: ?>
                                            <span class="badge-dot bg-gray" title="Inactive"></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-card border-danger mt-4">
                <h3 class="card-title text-danger">Actions</h3>
                <p class="text-muted text-sm mb-3">Be careful, these actions affect your live store.</p>
                
                <button onclick="openDeleteModal(<?= $product['product_id'] ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>')" class="btn btn-danger-outline btn-block w-100">
                    <i class="fas fa-trash-alt"></i> Delete Product
                </button>
            </div>
        </div>
    </div>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete <strong id="productNameToDelete"></strong>?</p>
        <p class="text-danger">This action cannot be undone.</p>
        <form method="POST" action="/shop/products/delete" id="deleteForm">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::get('csrf_token') ?>">
            <input type="hidden" name="product_id" id="productIdToDelete">
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete Product</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Scoped Styles for Detail View matching shop.css */
.breadcrumb-nav {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.breadcrumb-nav .separator {
    font-size: 0.7rem;
    color: #ccc;
}

.breadcrumb-nav a:hover {
    color: #D4AF37;
    text-decoration: underline;
}

.header-left .product-meta-badges {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5e5e5;
}

.header-actions {
    display: flex;
    gap: 1rem;
}

/* Stats Colors */
.bg-gold { background-color: #D4AF37; color: white; }
.bg-black { background-color: #1A1A1A; color: white; }
.bg-yellow { background-color: #B8942C; color: white; }
.bg-red { background-color: #DC3545; color: white; }
.bg-gray { background-color: #6c757d; color: white; }

/* Layout Grid */
.details-layout {
    display: grid;
    grid-template-columns: 2fr 1.2fr;
    gap: 2rem;
}

@media (max-width: 1024px) {
    .details-layout {
        grid-template-columns: 1fr;
    }
}

.content-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
    border: 1px solid transparent;
}

.content-card.border-danger {
    border-color: #ffebeb;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1A1A1A;
    margin-bottom: 1.25rem;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 0.75rem;
}

/* Image Gallery Management Style */
.image-gallery-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.main-image-wrapper {
    width: 100%;
    height: 400px;
    background: #f9f9f9;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 1px solid #e5e5e5;
}

.main-image-wrapper img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.thumbnails-grid {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.thumb-item {
    width: 70px;
    height: 70px;
    border: 2px solid #e5e5e5;
    border-radius: 6px;
    cursor: pointer;
    overflow: hidden;
    opacity: 0.7;
    transition: all 0.2s;
}

.thumb-item:hover, .thumb-item.active {
    border-color: #D4AF37;
    opacity: 1;
}

.thumb-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Description Text */
.desc-text {
    color: #444;
    line-height: 1.6;
    font-size: 0.95rem;
    background: #f9f9f9;
    padding: 1rem;
    border-radius: 8px;
    margin-top: 0.5rem;
}

/* Variants Table Styling */
.variants-table th {
    font-size: 0.8rem;
    text-transform: uppercase;
    color: #888;
    font-weight: 600;
    padding: 0.75rem;
}

.variants-table td {
    vertical-align: middle;
    padding: 0.75rem;
}

.variant-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.variant-thumb-sm {
    width: 40px;
    height: 40px;
    border-radius: 4px;
    object-fit: cover;
    border: 1px solid #eee;
}

.variant-thumb-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 4px;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ccc;
    font-size: 0.8rem;
}

.variant-info {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.variant-name {
    font-weight: 600;
    color: #1A1A1A;
    font-size: 0.9rem;
}

.text-mono {
    font-family: 'Courier New', monospace;
    color: #666;
    font-size: 0.85rem;
}

.text-gold {
    color: #B8942C;
    font-weight: 600;
}

.badge-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.btn-danger-outline {
    border: 1px solid #DC3545;
    color: #DC3545;
    background: transparent;
    padding: 0.75rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-danger-outline:hover {
    background: #DC3545;
    color: white;
}
</style>

<script>
    // Simple Image Switcher
    function updateMainImage(thumb, src) {
        document.getElementById('detailMainImage').src = src;
        document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
    }

    // Modal Functions
    function openDeleteModal(id, name) {
        document.getElementById('productNameToDelete').textContent = name;
        document.getElementById('productIdToDelete').value = id;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modal if clicked outside
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target == modal) {
            closeDeleteModal();
        }
    }
</script>