<?php
// app/views/shop/products.view.php
?>

<div class="dashboard-container">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">
                <i class="fas fa-box"></i> My Products
            </h1>
            <p class="dashboard-subtitle">Manage your product inventory</p>
        </div>
        <a href="/shop/add-product" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-blue">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['total_products'] ?></div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['published'] ?></div>
                <div class="stat-label">Published</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-yellow">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['draft'] ?></div>
                <div class="stat-label">Drafts</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-red">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['out_of_stock'] ?></div>
                <div class="stat-label">Out of Stock</div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-section">
        <form method="GET" action="/shop/products" class="filters-form">
            <!-- Status Filter -->
            <div class="filter-group">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="all" <?= $currentFilter === 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="published" <?= $currentFilter === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= $currentFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="unpublished" <?= $currentFilter === 'unpublished' ? 'selected' : '' ?>>Unpublished</option>
                    <option value="archived" <?= $currentFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>

            <!-- Search -->
            <div class="search-group">
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Search products..." 
                    value="<?= htmlspecialchars($searchTerm) ?>"
                >
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <?php if ($currentFilter !== 'all' || !empty($searchTerm)): ?>
                <a href="/shop/products" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear Filters
                </a>
            <?php endif; ?>
        </form>

        <!-- Bulk Actions -->
        <form method="POST" action="/shop/products/bulk-action" id="bulkActionForm" class="bulk-actions-form">
            <select name="bulk_action" id="bulkAction" class="filter-select">
                <option value="">Bulk Actions</option>
                <option value="publish">Publish</option>
                <option value="unpublish">Unpublish</option>
                <option value="archive">Archive</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="btn btn-secondary" id="applyBulkAction" disabled>
                Apply
            </button>
        </form>
    </div>

    <!-- Products Table -->
    <div class="table-container">
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No Products Found</h3>
                <p>
                    <?php if (!empty($searchTerm)): ?>
                        No products match your search "<?= htmlspecialchars($searchTerm) ?>"
                    <?php elseif ($currentFilter !== 'all'): ?>
                        No products with status "<?= htmlspecialchars($currentFilter) ?>"
                    <?php else: ?>
                        Start by adding your first product
                    <?php endif; ?>
                </p>
                <a href="/shop/add-product" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>
        <?php else: ?>
            <table class="products-table">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th width="80">Image</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Variants</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <input 
                                    type="checkbox" 
                                    name="product_ids[]" 
                                    value="<?= $product['product_id'] ?>" 
                                    class="product-checkbox"
                                    form="bulkActionForm"
                                >
                            </td>
                            <td>
                                <div class="product-image">
                                    <?php if ($product['cover_picture']): ?>
                                        <img src="/<?= htmlspecialchars($product['cover_picture']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="product-info">
                                    <a href="/shop/products/view/<?= $product['product_id'] ?>" class="product-name">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </a>
                                    <div class="product-description">
                                        <?= htmlspecialchars(substr($product['short_description'], 0, 60)) ?>...
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="category-badge">
                                    <?= htmlspecialchars($product['categories'] ?? 'Uncategorized') ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($product['min_price'] == $product['max_price']): ?>
                                    <span class="price">₱<?= number_format($product['min_price'], 2) ?></span>
                                <?php else: ?>
                                    <span class="price">₱<?= number_format($product['min_price'], 2) ?> - ₱<?= number_format($product['max_price'], 2) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product['total_stock'] == 0): ?>
                                    <span class="stock-badge out-of-stock">Out of Stock</span>
                                <?php elseif ($product['total_stock'] <= 5): ?>
                                    <span class="stock-badge low-stock"><?= $product['total_stock'] ?> left</span>
                                <?php else: ?>
                                    <span class="stock-badge in-stock"><?= $product['total_stock'] ?> in stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="variant-count">
                                    <?= $product['variant_count'] ?> variant<?= $product['variant_count'] != 1 ? 's' : '' ?>
                                </span>
                            </td>
                            <td>
                                <select 
                                    class="status-select status-<?= strtolower($product['status']) ?>"
                                    onchange="updateProductStatus(<?= $product['product_id'] ?>, this.value)"
                                >
                                    <option value="DRAFT" <?= $product['status'] === 'DRAFT' ? 'selected' : '' ?>>Draft</option>
                                    <option value="PUBLISHED" <?= $product['status'] === 'PUBLISHED' ? 'selected' : '' ?>>Published</option>
                                    <option value="UNPUBLISHED" <?= $product['status'] === 'UNPUBLISHED' ? 'selected' : '' ?>>Unpublished</option>
                                    <option value="ARCHIVED" <?= $product['status'] === 'ARCHIVED' ? 'selected' : '' ?>>Archived</option>
                                </select>
                            </td>
                            <td>
                                <span class="date">
                                    <?= date('M d, Y', strtotime($product['created_at'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="/shop/products/view/<?= $product['product_id'] ?>" class="btn-icon" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/shop/products/edit/<?= $product['product_id'] ?>" class="btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button 
                                        onclick="deleteProduct(<?= $product['product_id'] ?>, '<?= htmlspecialchars($product['name']) ?>')" 
                                        class="btn-icon btn-delete" 
                                        title="Delete"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete <strong id="productNameToDelete"></strong>?</p>
        <p class="text-danger">This action cannot be undone.</p>
        <form method="POST" action="/shop/products/delete" id="deleteForm">
            <input type="hidden" name="product_id" id="productIdToDelete">
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete Product</button>
            </div>
        </form>
    </div>
</div>

