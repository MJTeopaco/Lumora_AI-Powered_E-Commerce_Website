<?php
// app/Views/collections/index.view.php
?>

<link rel="stylesheet" href="/css/collections.css">

<section class="hero">
    <h1><?= isset($category) ? htmlspecialchars($category['name']) : 'All Collections' ?></h1>
    <p>Discover handcrafted luxury accessories</p>
</section>

<div class="container">
    
    <div class="collections-toolbar">
        <div class="toolbar-info">
            <h2 class="results-heading">Shop All Products</h2>
            <span class="results-count"><?= $totalProducts ?> Products Available</span>
        </div>
        
        <div class="toolbar-actions">
            <form method="GET" action="/collections/index" class="sort-form">
                <?php if ($currentCategory): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($currentCategory) ?>">
                <?php endif; ?>
                <?php if ($searchTerm): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                <?php endif; ?>
                
                <label for="sortSelect">Sort by:</label>
                <select name="sort" id="sortSelect" class="sort-select" onchange="this.form.submit()">
                    <option value="newest" <?= $currentSort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="popular" <?= $currentSort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                    <option value="price-low" <?= $currentSort === 'price-low' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price-high" <?= $currentSort === 'price-high' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="name" <?= $currentSort === 'name' ? 'selected' : '' ?>>Name: A-Z</option>
                </select>
            </form>

            <button class="btn btn-outline" onclick="toggleFilters()">
                <i class="fas fa-sliders-h"></i> Filters
            </button>
        </div>
    </div>

    <div class="category-pills">
        <a href="/collections/index" 
           class="category-pill <?= empty($currentCategory) ? 'active' : '' ?>">
            All Categories
        </a>
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $cat): ?>
                <a href="/collections/index?category=<?= urlencode($cat['slug']) ?>" 
                   class="category-pill <?= $currentCategory === $cat['slug'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="filters-panel" id="filtersPanel">
        <div class="filters-content">
            <div class="filter-group">
                <label>Search Products</label>
                <form method="GET" action="/collections/index" class="filter-search-form">
                    <?php if ($currentCategory): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($currentCategory) ?>">
                    <?php endif; ?>
                    <input type="text" 
                           name="search" 
                           placeholder="Search..."
                           value="<?= htmlspecialchars($searchTerm ?? '') ?>">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <div class="filter-group">
                <label>Price Range</label>
                <form method="GET" action="/collections/index" class="price-filter">
                    <?php if ($currentCategory): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($currentCategory) ?>">
                    <?php endif; ?>
                    <?php if ($currentSort): ?>
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($currentSort) ?>">
                    <?php endif; ?>
                    
                    <div class="price-inputs">
                        <input type="number" 
                               name="price_min" 
                               placeholder="Min"
                               value="<?= htmlspecialchars($priceMin ?? '') ?>"
                               min="0">
                        <span>to</span>
                        <input type="number" 
                               name="price_max" 
                               placeholder="Max"
                               value="<?= htmlspecialchars($priceMax ?? '') ?>"
                               min="0">
                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </form>
            </div>

            <?php if (!empty($currentCategory) || !empty($searchTerm) || $priceMin || $priceMax): ?>
                <a href="/collections/index" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Clear All Filters
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <div class="empty-state">
            <h3>No Products Found</h3>
            <p>
                <?php if ($searchTerm): ?>
                    No products match "<?= htmlspecialchars($searchTerm) ?>"
                <?php elseif ($currentCategory): ?>
                    This category is currently empty
                <?php else: ?>
                    No products available at the moment
                <?php endif; ?>
            </p>
            <a href="/collections/index" class="btn btn-primary">Browse All Products</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <a href="/products/<?= htmlspecialchars($product['slug']) ?>" class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 loading="lazy">
                        <?php else: ?>
                            🎁
                        <?php endif; ?>
                        
                        <?php if (isset($product['stock']) && $product['stock'] == 0): ?>
                            <span class="stock-badge out-of-stock">Sold Out</span>
                        <?php elseif (isset($product['stock']) && $product['stock'] <= 5): ?>
                            <span class="stock-badge low-stock">Only <?= $product['stock'] ?> Left</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-details">
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        
                        <?php if (!empty($product['short_description'])): ?>
                            <p class="product-description">
                                <?= htmlspecialchars(substr($product['short_description'], 0, 70)) ?>
                                <?= strlen($product['short_description']) > 70 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>

                        <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleFilters() {
        const panel = document.getElementById('filtersPanel');
        panel.classList.toggle('active');
    }

    document.addEventListener('click', function(e) {
        const panel = document.getElementById('filtersPanel');
        const btn = e.target.closest('.btn-outline');
        
        if (panel && !panel.contains(e.target) && !btn) {
            panel.classList.remove('active');
        }
    });
</script>