<link rel="stylesheet" href="<?= base_url('/css/buyer-product-detail.css') ?>">

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
                    <div class="stars-container">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <span class="rating-text">4.5 (120 reviews)</span>
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

        // FIX: Use PHP base_url for correct path
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
        // FIX: Use PHP base_url
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