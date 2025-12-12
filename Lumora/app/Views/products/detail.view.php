<link rel="stylesheet" href="<?= base_url('/css/buyer-product-detail.css') ?>">
<link rel="stylesheet" href="<?= base_url('/css/buyer-product-reviews.css') ?>">

<div class="product-detail-wrapper">
    <div class="product-detail-container">
        <div class="breadcrumb">
            <a href="<?= base_url('/') ?>"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-chevron-right separator"></i>
            <a href="<?= base_url('/collections/index') ?>">Products</a>
            <i class="fas fa-chevron-right separator"></i>
            <span class="current"><?= htmlspecialchars($product['name']) ?></span>
        </div>

        <div class="product-detail-content">
            <div class="product-images-section">
                <div class="main-image-container">
                    <?php if (!empty($product['cover_picture'])): ?>
                        <img src="<?= base_url($product['cover_picture']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             id="mainProductImage"
                             class="main-product-image">
                    <?php else: ?>
                        <div class="no-image-placeholder">
                            <i class="fas fa-image"></i>
                            <p>No image available</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($product['variants'])): ?>
                    <div class="thumbnail-gallery">
                        <?php if (!empty($product['cover_picture'])): ?>
                            <img src="<?= base_url($product['cover_picture']) ?>" 
                                 alt="Main" 
                                 class="thumbnail-image active"
                                 onclick="changeMainImage(this.src)">
                        <?php endif; ?>

                        <?php foreach ($product['variants'] as $variant): ?>
                            <?php if (!empty($variant['product_picture'])): ?>
                                <img src="<?= base_url($variant['product_picture']) ?>" 
                                     alt="<?= htmlspecialchars($variant['variant_name'] ?? 'Variant') ?>" 
                                     class="thumbnail-image"
                                     onclick="changeMainImage(this.src)">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-info-section">
                <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>

                <div class="product-rating">
                    <?php 
                        // FIX: Use dynamic review stats instead of hardcoded values
                        $avgRating = $reviewStats['average_rating'] ?? 0;
                        $totalReviews = $reviewStats['total_reviews'] ?? 0;
                        
                        $fullStars = floor($avgRating);
                        $halfStar = ($avgRating - $fullStars) >= 0.5;
                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                    ?>
                    <div class="stars-container">
                        <?php for($i=0; $i<$fullStars; $i++): ?>
                            <i class="fas fa-star"></i>
                        <?php endfor; ?>
                        
                        <?php if($halfStar): ?>
                            <i class="fas fa-star-half-alt"></i>
                        <?php endif; ?>
                        
                        <?php for($i=0; $i<$emptyStars; $i++): ?>
                            <i class="far fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-text"><?= number_format($avgRating, 1) ?> (<?= number_format($totalReviews) ?> reviews)</span>
                </div>

                <div class="product-price-section">
                    <?php if (!empty($product['variants'])): ?>
                        <?php 
                        $prices = array_column($product['variants'], 'price');
                        $minPrice = min($prices);
                        $maxPrice = max($prices);
                        ?>
                        <?php if ($minPrice == $maxPrice): ?>
                            <span class="product-price">₱<?= number_format($minPrice, 2) ?></span>
                        <?php else: ?>
                            <span class="product-price">₱<?= number_format($minPrice, 2) ?> - ₱<?= number_format($maxPrice, 2) ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="product-description-section">
                    <h3 class="section-title">Description</h3>
                    <p class="product-description-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>

                <?php if (!empty($product['variants'])): ?>
                    <div class="variant-selection-section">
                        <h3 class="section-title">Select Variant</h3>
                        <div class="variant-options-container">
                            <?php foreach ($product['variants'] as $index => $variant): ?>
                                <?php if ($variant['is_active'] == 1 && $variant['quantity'] > 0): ?>
                                    <div class="variant-option-card-wrapper">
                                        <input type="radio" 
                                               name="variant" 
                                               id="variant_<?= $variant['variant_id'] ?>" 
                                               value="<?= $variant['variant_id'] ?>"
                                               data-price="<?= $variant['price'] ?>"
                                               data-stock="<?= $variant['quantity'] ?>"
                                               <?= $index === 0 ? 'checked' : '' ?>
                                               class="variant-radio">
                                        <label for="variant_<?= $variant['variant_id'] ?>" class="variant-option-card">
                                            <div class="variant-header">
                                                <span class="variant-name"><?= htmlspecialchars($variant['variant_name'] ?: 'Standard') ?></span>
                                                <span class="variant-price-tag">₱<?= number_format($variant['price'], 2) ?></span>
                                            </div>
                                            <div class="variant-details">
                                                <?php if (!empty($variant['color'])): ?>
                                                    <span class="variant-attr" title="Color"><i class="fas fa-circle"></i> <?= htmlspecialchars($variant['color']) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($variant['size'])): ?>
                                                    <span class="variant-attr" title="Size"><i class="fas fa-ruler"></i> <?= htmlspecialchars($variant['size']) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($variant['material'])): ?>
                                                    <span class="variant-attr" title="Material"><i class="fas fa-cube"></i> <?= htmlspecialchars($variant['material']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="variant-stock-tag"><?= $variant['quantity'] ?> in stock</div>
                                        </label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="quantity-section">
                    <h3 class="section-title">Quantity</h3>
                    <div class="quantity-wrapper">
                        <div class="quantity-controls">
                            <button type="button" class="qty-button" onclick="decreaseQuantity()">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" id="quantityInput" value="1" min="1" max="99" readonly class="qty-input">
                            <button type="button" class="qty-button" onclick="increaseQuantity()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <span class="stock-availability in-stock" id="stockInfo">In Stock</span>
                    </div>
                </div>

                <div class="action-buttons-section">
                    <button type="button" class="btn-add-to-cart" onclick="addToCart()" id="addToCartBtn">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                    <button type="button" class="btn-buy-now" onclick="buyNow()">
                        <i class="fas fa-bolt"></i> Buy Now
                    </button>
                </div>

                <?php if (!empty($shop)): ?>
                    <div class="shop-info-card">
                        <div class="shop-header">
                            <i class="fas fa-store shop-icon"></i>
                            <span class="shop-label">Sold by</span>
                        </div>
                        <h4 class="shop-name"><?= htmlspecialchars($shop['shop_name']) ?></h4>
                        <?php if (!empty($shop['shop_description'])): ?>
                            <p class="shop-description"><?= htmlspecialchars($shop['shop_description']) ?></p>
                        <?php endif; ?>
                        <a href="<?= base_url('/shop/' . $shop['slug']) ?>" class="btn-visit-shop">
                            Visit Shop <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="csrfToken" value="<?= \App\Core\Session::get('csrf_token') ?>">

<script>
    // Change main image
    function changeMainImage(src) {
        document.getElementById('mainProductImage').src = src;
        document.querySelectorAll('.thumbnail-image').forEach(thumb => thumb.classList.remove('active'));
        event.target.classList.add('active');
    }

    // Quantity logic
    function increaseQuantity() {
        const qtyInput = document.getElementById('quantityInput');
        const maxStock = getSelectedVariantStock();
        if (parseInt(qtyInput.value) < maxStock) qtyInput.value = parseInt(qtyInput.value) + 1;
    }

    function decreaseQuantity() {
        const qtyInput = document.getElementById('quantityInput');
        if (parseInt(qtyInput.value) > 1) qtyInput.value = parseInt(qtyInput.value) - 1;
    }

    function getSelectedVariantStock() {
        const selectedVariant = document.querySelector('input[name="variant"]:checked');
        return selectedVariant ? parseInt(selectedVariant.dataset.stock) : 99;
    }

    // Stock Update Listener
    document.querySelectorAll('input[name="variant"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const stock = parseInt(this.dataset.stock);
            const stockInfo = document.getElementById('stockInfo');
            const qtyInput = document.getElementById('quantityInput');
            
            if (stock === 0) {
                stockInfo.textContent = 'Out of Stock';
                stockInfo.className = 'stock-availability out-of-stock';
            } else if (stock <= 5) {
                stockInfo.textContent = `Only ${stock} left!`;
                stockInfo.className = 'stock-availability low-stock';
            } else {
                stockInfo.textContent = 'In Stock';
                stockInfo.className = 'stock-availability in-stock';
            }

            qtyInput.max = stock;
            if (parseInt(qtyInput.value) > stock) qtyInput.value = stock;
        });
    });

    // Add to Cart Logic
    function addToCart() {
        const selectedVariant = document.querySelector('input[name="variant"]:checked');
        const quantity = document.getElementById('quantityInput').value;
        const csrfToken = document.getElementById('csrfToken').value;

        if (!selectedVariant) {
            showToast('Please select a variant', 'error');
            return;
        }

        const btn = document.getElementById('addToCartBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

        fetch('<?= base_url('/cart/add') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                csrf_token: csrfToken,
                variant_id: selectedVariant.value,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                updateHeaderCartCount(data.cartCount);
                document.getElementById('quantityInput').value = 1;
            } else {
                showToast(data.message, 'error');
                if (data.redirect) window.location.href = data.redirect;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to add to cart.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    function updateHeaderCartCount(count) {
        const badge = document.querySelector('.nav-actions .badge') || document.createElement('span');
        badge.className = 'badge';
        badge.textContent = count > 99 ? '99+' : count;
        
        const cartBtn = document.querySelector('.icon-btn[title="Shopping Cart"]');
        if(cartBtn && !cartBtn.querySelector('.badge')) cartBtn.appendChild(badge);
    }

    function showToast(message, type = 'info') {
        const existingToast = document.querySelector('.toast-notification');
        if (existingToast) existingToast.remove();
        
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `<i class="fas fa-info-circle"></i> <span>${message}</span>`;
        
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function buyNow() {
        const selectedVariant = document.querySelector('input[name="variant"]:checked');
        const quantity = document.getElementById('quantityInput').value;
        const csrfToken = document.getElementById('csrfToken').value;

        // 1. Validation
        if (!selectedVariant) {
            showToast('Please select a variant', 'error');
            return;
        }

        // 2. Visual Feedback (Loading State)
        const btn = document.querySelector('.btn-buy-now');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        // 3. Add to Cart via AJAX
        fetch('<?= base_url('/cart/add') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                csrf_token: csrfToken,
                variant_id: selectedVariant.value,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 4. Success: Redirect to Checkout immediately
                window.location.href = '<?= base_url('/checkout') ?>';
            } else {
                // Error Handling
                showToast(data.message, 'error');
                
                // Handle Login Redirect if not authenticated
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    // Restore button if it was just a stock/validation error
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to process request. Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
</script>
<div class="product-reviews-section" id="reviewsSection">
    <div class="reviews-header">
        <h2 class="reviews-title">
            <i class="fas fa-star"></i>
            Customer Reviews
        </h2>
        </div>

    <div class="review-stats-container" id="reviewStats">
        <div class="average-rating-box">
            <div class="average-rating-number" id="avgRating">0.0</div>
            <div class="rating-stars-display" id="avgStars">
                <i class="far fa-star"></i>
                <i class="far fa-star"></i>
                <i class="far fa-star"></i>
                <i class="far fa-star"></i>
                <i class="far fa-star"></i>
            </div>
            <p class="rating-count-text"><span id="totalReviews">0</span> reviews</p>
            <div class="verified-purchases">
                <i class="fas fa-check-circle"></i>
                <span id="verifiedCount">0</span> verified purchases
            </div>
        </div>

        <div class="rating-breakdown">
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <div class="rating-bar-row">
                    <span class="star-label">
                        <?= $i ?> <i class="fas fa-star"></i>
                    </span>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" id="bar<?= $i ?>Star" style="width: 0%"></div>
                    </div>
                    <span class="rating-count" id="count<?= $i ?>Star">0</span>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="review-controls">
        <div class="review-filter-buttons">
            <button class="filter-btn active" data-filter="all">All Reviews</button>
            <button class="filter-btn" data-filter="5">5 Stars</button>
            <button class="filter-btn" data-filter="4">4 Stars</button>
            <button class="filter-btn" data-filter="3">3 Stars</button>
        </div>
        <select class="review-sort-select" id="reviewSort">
            <option value="newest">Most Recent</option>
            <option value="oldest">Oldest First</option>
            <option value="highest">Highest Rating</option>
            <option value="lowest">Lowest Rating</option>
            <option value="helpful">Most Helpful</option>
        </select>
    </div>

    <div class="reviews-list" id="reviewsList">
        </div>

    <div class="load-more-container" id="loadMoreContainer" style="display: none;">
        <button class="btn-load-more" id="loadMoreBtn">
            <i class="fas fa-chevron-down"></i> Load More Reviews
        </button>
    </div>

    <div class="no-reviews-state" id="noReviewsState" style="display: none;">
        <i class="fas fa-star-half-alt"></i>
        <h3>No Reviews Yet</h3>
        <p>Be the first to share your experience with this product!</p>
        </div>
</div>

<script>
// Review System JavaScript
(function() {
    const productId = <?= $product['id'] ?>;
    let currentPage = 1;
    let currentSort = 'newest';
    let currentFilter = 'all';
    const csrfToken = document.getElementById('csrfToken')?.value || '';

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadReviews();
        setupEventListeners();
    });

    function setupEventListeners() {
        // Sort change
        document.getElementById('reviewSort')?.addEventListener('change', function(e) {
            currentSort = e.target.value;
            currentPage = 1;
            loadReviews();
        });

        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                currentPage = 1;
                loadReviews();
            });
        });

        // Load more
        document.getElementById('loadMoreBtn')?.addEventListener('click', function() {
            currentPage++;
            loadReviews(true);
        });
    }

    function loadReviews(append = false) {
        const url = `<?= base_url('/reviews/get-product-reviews') ?>?product_id=${productId}&page=${currentPage}&sort=${currentSort}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStats(data.stats);
                    
                    if (append) {
                        appendReviews(data.reviews);
                    } else {
                        renderReviews(data.reviews);
                    }

                    // Show/hide load more button
                    const loadMoreContainer = document.getElementById('loadMoreContainer');
                    if (data.has_more) {
                        loadMoreContainer.style.display = 'block';
                    } else {
                        loadMoreContainer.style.display = 'none';
                    }

                    // Show/hide no reviews state
                    const noReviewsState = document.getElementById('noReviewsState');
                    if (data.reviews.length === 0 && currentPage === 1) {
                        noReviewsState.style.display = 'block';
                        document.getElementById('reviewsList').style.display = 'none';
                    } else {
                        noReviewsState.style.display = 'none';
                        document.getElementById('reviewsList').style.display = 'flex';
                    }
                }
            })
            .catch(error => console.error('Error loading reviews:', error));
    }

    function updateStats(stats) {
        const avgRating = parseFloat(stats.average_rating || 0).toFixed(1);
        document.getElementById('avgRating').textContent = avgRating;
        document.getElementById('totalReviews').textContent = stats.total_reviews || 0;
        document.getElementById('verifiedCount').textContent = stats.verified_purchases || 0;

        // Update star display
        updateStarDisplay('avgStars', avgRating);

        // Update rating breakdown
        const total = parseInt(stats.total_reviews) || 1;
        for (let i = 1; i <= 5; i++) {
            const count = parseInt(stats[`${['one', 'two', 'three', 'four', 'five'][i-1]}_star`] || 0);
            const percentage = (count / total) * 100;
            document.getElementById(`bar${i}Star`).style.width = percentage + '%';
            document.getElementById(`count${i}Star`).textContent = count;
        }
    }

    function updateStarDisplay(containerId, rating) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const stars = container.querySelectorAll('i');
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;

        stars.forEach((star, index) => {
            star.className = 'far fa-star'; // Reset
            if (index < fullStars) {
                star.className = 'fas fa-star';
            } else if (index === fullStars && hasHalfStar) {
                star.className = 'fas fa-star-half-alt';
            }
        });
    }

    function renderReviews(reviews) {
        const container = document.getElementById('reviewsList');
        container.innerHTML = reviews.map(review => createReviewCard(review)).join('');
        attachReviewEventListeners();
    }

    function appendReviews(reviews) {
        const container = document.getElementById('reviewsList');
        container.insertAdjacentHTML('beforeend', reviews.map(review => createReviewCard(review)).join(''));
        attachReviewEventListeners();
    }

    function createReviewCard(review) {
        const stars = createStarHTML(review.rating);
        const hasImages = review.images && review.images.length > 0;
        const hasResponse = review.response !== null;
        
        let variantInfo = '';
        if (review.color || review.size || review.material) {
            variantInfo = `<div class="review-variant-info">`;
            if (review.color) variantInfo += `<span><i class="fas fa-circle"></i> Color: ${review.color}</span>`;
            if (review.size) variantInfo += `<span><i class="fas fa-ruler"></i> Size: ${review.size}</span>`;
            if (review.material) variantInfo += `<span><i class="fas fa-cube"></i> Material: ${review.material}</span>`;
            variantInfo += `</div>`;
        }

        let imagesHTML = '';
        if (hasImages) {
            imagesHTML = `<div class="review-images">`;
            review.images.forEach(img => {
                imagesHTML += `<div class="review-image-item" onclick="openImageModal('<?= base_url('/') ?>${img.image_path}')">
                    <img src="<?= base_url('/') ?>${img.image_path}" alt="Review image">
                </div>`;
            });
            imagesHTML += `</div>`;
        }

        let responseHTML = '';
        if (hasResponse) {
            const responseDate = new Date(review.response.created_at).toLocaleDateString('en-US', { 
                year: 'numeric', month: 'short', day: 'numeric' 
            });
            responseHTML = `
                <div class="seller-response">
                    <div class="response-header">
                        <i class="fas fa-store"></i>
                        <strong>${review.response.shop_name}</strong> replied:
                    </div>
                    <p class="response-text">${escapeHtml(review.response.response_text)}</p>
                    <div class="response-date">${responseDate}</div>
                </div>
            `;
        }

        const reviewDate = new Date(review.created_at).toLocaleDateString('en-US', { 
            year: 'numeric', month: 'long', day: 'numeric' 
        });

        return `
            <div class="review-card" data-review-id="${review.review_id}">
                <div class="review-header">
                    <div class="reviewer-info">
                        <div class="reviewer-avatar">
                            ${review.profile_pic ? 
                                `<img src="<?= base_url('/') ?>${review.profile_pic}" alt="${escapeHtml(review.username)}">` : 
                                `<i class="fas fa-user"></i>`
                            }
                        </div>
                        <div class="reviewer-details">
                            <h4>${escapeHtml(review.username)}</h4>
                            ${review.is_verified_purchase == 1 ? 
                                '<span class="verified-purchase-badge"><i class="fas fa-check-circle"></i> Verified Purchase</span>' : 
                                ''
                            }
                            <div class="review-date">${reviewDate}</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        ${stars}
                    </div>
                </div>
                <div class="review-content">
                    ${review.title ? `<h3 class="review-title">${escapeHtml(review.title)}</h3>` : ''}
                    <p class="review-text">${escapeHtml(review.comment)}</p>
                    ${variantInfo}
                    ${imagesHTML}
                </div>
                <div class="review-actions">
                    <button class="action-btn helpful-btn" data-review-id="${review.review_id}">
                        <i class="far fa-thumbs-up"></i>
                        Helpful (${review.helpful_count || 0})
                    </button>
                </div>
                ${responseHTML}
            </div>
        `;
    }

    function createStarHTML(rating) {
        let html = '';
        for (let i = 1; i <= 5; i++) {
            html += i <= rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
        }
        return html;
    }

    function attachReviewEventListeners() {
        document.querySelectorAll('.helpful-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const reviewId = this.dataset.reviewId;
                markHelpful(reviewId, this);
            });
        });
    }

    function markHelpful(reviewId, button) {
        fetch('<?= base_url('/reviews/mark-helpful') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                csrf_token: csrfToken,
                review_id: reviewId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.classList.add('helpful-active');
                const countSpan = button.querySelector('i').nextSibling;
                const currentCount = parseInt(countSpan.textContent.match(/\d+/)[0]);
                countSpan.textContent = ` Helpful (${currentCount + 1})`;
                button.disabled = true;
            } else {
                showToast(data.message, 'info');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `<i class="fas fa-info-circle"></i> <span>${message}</span>`;
        
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Make loadReviews accessible globally if needed
    window.reloadProductReviews = loadReviews;
})();
</script>