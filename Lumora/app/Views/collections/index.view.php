<?php
// app/Views/collections/index.view.php
?>

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
                <?php if (isset($_GET['page'])): ?>
                    <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page']) ?>">
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
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <a href="/products/<?= htmlspecialchars($product['slug']) ?>" class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="no-image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($product['stock']) && $product['stock'] == 0): ?>
                            <span class="stock-badge out-of-stock">Sold Out</span>
                        <?php elseif (isset($product['stock']) && $product['stock'] <= 5): ?>
                            <span class="stock-badge low-stock">Only <?= $product['stock'] ?> Left</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        
                        <div class="product-rating">
                            <?php 
                                $rating = $product['average_rating'] ?? 0;
                                $count = $product['review_count'] ?? 0;
                            ?>
                            <div class="stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php if($i <= round($rating)): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <span class="review-count">(<?= $count ?>)</span>
                        </div>
                        
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

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php
                // Build query string for pagination links
                $queryParams = [];
                if ($currentCategory) $queryParams['category'] = $currentCategory;
                if ($currentSort !== 'newest') $queryParams['sort'] = $currentSort;
                if ($searchTerm) $queryParams['search'] = $searchTerm;
                if ($priceMin) $queryParams['price_min'] = $priceMin;
                if ($priceMax) $queryParams['price_max'] = $priceMax;
                
                $baseQuery = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
                ?>

                <!-- Previous Button -->
                <?php if ($currentPage > 1): ?>
                    <a href="/collections/index?page=<?= $currentPage - 1 ?><?= $baseQuery ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php else: ?>
                    <span class="pagination-btn disabled">
                        <i class="fas fa-chevron-left"></i> Previous
                    </span>
                <?php endif; ?>

                <!-- Page Numbers -->
                <div class="pagination-numbers">
                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    // Always show first page
                    if ($startPage > 1): ?>
                        <a href="/collections/index?page=1<?= $baseQuery ?>" 
                           class="pagination-number">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="pagination-number active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="/collections/index?page=<?= $i ?><?= $baseQuery ?>" 
                               class="pagination-number"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Always show last page -->
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                        <a href="/collections/index?page=<?= $totalPages ?><?= $baseQuery ?>" 
                           class="pagination-number"><?= $totalPages ?></a>
                    <?php endif; ?>
                </div>

                <!-- Next Button -->
                <?php if ($currentPage < $totalPages): ?>
                    <a href="/collections/index?page=<?= $currentPage + 1 ?><?= $baseQuery ?>" 
                       class="pagination-btn">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="pagination-btn disabled">
                        Next <i class="fas fa-chevron-right"></i>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Pagination Info -->
            <div class="pagination-info">
                Showing <?= (($currentPage - 1) * $itemsPerPage) + 1 ?> to 
                <?= min($currentPage * $itemsPerPage, $totalProducts) ?> of 
                <?= $totalProducts ?> products
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>