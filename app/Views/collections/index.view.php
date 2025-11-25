<!-- app/views/collections/index.view.php -->

<div class="collections-container">
    <!-- Header -->
    <div class="collections-header">
        <h1 class="collections-title">Collections</h1>
        <p class="collections-subtitle">Discover unique accessories curated just for you</p>
    </div>

    <!-- Category Filter -->
    <div class="category-filter">
        <button class="category-btn active" data-category="all">All</button>
        <?php foreach ($categories as $category): ?>
            <button class="category-btn" data-category="<?= $category['category_id'] ?>">
                <?= htmlspecialchars($category['name']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Products Grid -->
    <div id="productsContainer">
        <?php if (!empty($products)): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" onclick="viewProduct('<?= htmlspecialchars($product['slug']) ?>')">
                        <div class="product-image-container">
                            <?php if ($product['cover_picture']): ?>
                                <img src="/<?= htmlspecialchars($product['cover_picture']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="product-image"
                                     onerror="this.src='/assets/images/placeholder-product.jpg'">
                            <?php else: ?>
                                <img src="/assets/images/placeholder-product.jpg" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="product-image">
                            <?php endif; ?>
                            <div class="product-badge">New</div>
                        </div>
                        <div class="product-info">
                            <div class="product-shop">
                                <?= htmlspecialchars($product['shop_name']) ?>
                            </div>
                            <h3 class="product-name">
                                <?= htmlspecialchars($product['name']) ?>
                            </h3>
                            <?php if ($product['short_description']): ?>
                                <p class="product-description">
                                    <?= htmlspecialchars($product['short_description']) ?>
                                </p>
                            <?php endif; ?>
                            <div class="product-footer">
                                <div class="product-price">
                                    ₱<?= number_format($product['price'], 2) ?>
                                </div>
                                <button class="view-btn">View</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h3 class="empty-title">No Products Found</h3>
                <p class="empty-message">Check back later for new arrivals</p>
            </div>
        <?php endif; ?>
    </div>
</div>