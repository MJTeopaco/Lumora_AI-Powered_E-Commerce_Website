<?php
// app/Views/cart/index.view.php
?>

<div class="cart-container">
    <div class="cart-header">
        <div class="cart-header-content">
            <h1 class="cart-title">
                <i class="fas fa-shopping-cart"></i> Your Shopping Cart
            </h1>
            <div class="breadcrumb">
                <a href="/"><i class="fas fa-home"></i> Home</a>
                <i class="fas fa-chevron-right"></i>
                <span>Shopping Cart</span>
            </div>
        </div>
        <?php if (!empty($cartItems)): ?>
            <div class="cart-progress">
                <div class="progress-step active">
                    <div class="step-icon"><i class="fas fa-shopping-cart"></i></div>
                    <span class="step-label">Shopping Cart</span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step">
                    <div class="step-icon"><i class="fas fa-credit-card"></i></div>
                    <span class="step-label">Checkout</span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step">
                    <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                    <span class="step-label">Complete</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2>Your Cart is Empty</h2>
            <p>Discover our exclusive collection of luxury items crafted just for you.</p>
            <div class="empty-cart-actions">
                <a href="/collections/index" class="btn btn-primary btn-lg">
                    <i class="fas fa-gem"></i> Explore Collections
                </a>
                <a href="/" class="btn btn-outline btn-lg">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
            <div class="empty-cart-suggestions">
                <p class="suggestions-title">Popular Categories</p>
                <div class="category-chips">
                    <a href="/collections/index?category=jewelry" class="category-chip">
                        <i class="fas fa-gem"></i> Jewelry
                    </a>
                    <a href="/collections/index?category=watches" class="category-chip">
                        <i class="fas fa-clock"></i> Watches
                    </a>
                    <a href="/collections/index?category=accessories" class="category-chip">
                        <i class="fas fa-briefcase"></i> Accessories
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="cart-content">
            <div class="cart-items-section">
                <div class="cart-items-header">
                    <div class="header-left">
                        <h2>Cart Items</h2>
                        <span class="item-count"><?= $cartCount ?> <?= $cartCount === 1 ? 'item' : 'items' ?></span>
                    </div>
                    <form method="POST" action="/cart/clear" id="clearCartForm">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::get('csrf_token') ?>">
                        <button type="button" class="btn-clear-cart" onclick="confirmClearCart()">
                            <i class="fas fa-trash-alt"></i> Clear All
                        </button>
                    </form>
                </div>

                <div class="cart-items-list">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item <?= !$item['is_available'] ? 'item-unavailable' : '' ?>" data-variant-id="<?= $item['variant_id'] ?>">
                            <div class="cart-item-image">
                                <a href="/products/<?= htmlspecialchars($item['slug']) ?>">
                                    <?php if (!empty($item['product_picture'])): ?>
                                        <img src="/<?= htmlspecialchars($item['product_picture']) ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>"
                                             loading="lazy">
                                    <?php elseif (!empty($item['cover_picture'])): ?>
                                        <img src="/<?= htmlspecialchars($item['cover_picture']) ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!$item['is_available']): ?>
                                        <div class="unavailable-overlay">
                                            <span>Out of Stock</span>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <div class="cart-item-details">
                                <div class="item-header">
                                    <h3 class="cart-item-name">
                                        <a href="/products/<?= htmlspecialchars($item['slug']) ?>">
                                            <?= htmlspecialchars($item['product_name']) ?>
                                        </a>
                                    </h3>
                                    <button type="button" 
                                            class="btn-remove-mobile" 
                                            onclick="removeItem(<?= $item['variant_id'] ?>, '<?= htmlspecialchars($item['product_name'], ENT_QUOTES) ?>')"
                                            title="Remove item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                
                                <?php if (!empty($item['variant_name']) || !empty($item['color']) || !empty($item['size']) || !empty($item['material'])): ?>
                                    <div class="cart-item-variant">
                                        <?php if (!empty($item['variant_name'])): ?>
                                            <span class="variant-tag"><?= htmlspecialchars($item['variant_name']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['color'])): ?>
                                            <span class="variant-attr">
                                                <i class="fas fa-palette"></i> <?= htmlspecialchars($item['color']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['size'])): ?>
                                            <span class="variant-attr">
                                                <i class="fas fa-ruler-combined"></i> <?= htmlspecialchars($item['size']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['material'])): ?>
                                            <span class="variant-attr">
                                                <i class="fas fa-cube"></i> <?= htmlspecialchars($item['material']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="cart-item-meta">
                                    <div class="cart-item-price">
                                        <span class="price-label">Unit Price:</span>
                                        <span class="price-value">₱<?= number_format($item['price'], 2) ?></span>
                                    </div>

                                    <div class="cart-item-quantity">
                                        <label>Quantity:</label>
                                        <div class="quantity-controls">
                                            <button type="button" 
                                                    class="qty-btn qty-decrease" 
                                                    onclick="updateQuantity(<?= $item['variant_id'] ?>, -1)"
                                                    <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>
                                                    aria-label="Decrease quantity">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" 
                                                   class="qty-input" 
                                                   value="<?= $item['quantity'] ?>" 
                                                   min="1" 
                                                   max="<?= $item['max_quantity'] ?>"
                                                   data-variant-id="<?= $item['variant_id'] ?>"
                                                   onchange="updateQuantityDirect(<?= $item['variant_id'] ?>, this.value)"
                                                   <?= !$item['is_available'] ? 'disabled' : '' ?>
                                                   aria-label="Quantity">
                                            <button type="button" 
                                                    class="qty-btn qty-increase" 
                                                    onclick="updateQuantity(<?= $item['variant_id'] ?>, 1)"
                                                    <?= $item['quantity'] >= $item['max_quantity'] || !$item['is_available'] ? 'disabled' : '' ?>
                                                    aria-label="Increase quantity">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <span class="stock-info">
                                            <i class="fas fa-box"></i> <?= $item['max_quantity'] ?> available
                                        </span>
                                    </div>

                                    <div class="cart-item-total">
                                        <span class="total-label">Subtotal:</span>
                                        <span class="total-value" data-variant-id="<?= $item['variant_id'] ?>">
                                            ₱<?= number_format($item['item_total'], 2) ?>
                                        </span>
                                    </div>
                                </div>

                                <?php if ($item['stock_changed']): ?>
                                    <div class="stock-alert stock-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span>Only <?= $item['max_quantity'] ?> available. Quantity adjusted automatically.</span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!$item['is_available']): ?>
                                    <div class="stock-alert stock-error">
                                        <i class="fas fa-times-circle"></i>
                                        <span>This item is currently out of stock and cannot be purchased.</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="cart-item-actions">
                                <button type="button" 
                                        class="btn-remove" 
                                        onclick="removeItem(<?= $item['variant_id'] ?>, '<?= htmlspecialchars($item['product_name'], ENT_QUOTES) ?>')"
                                        title="Remove from cart">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>Remove</span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="cart-summary-section">
                <div class="cart-summary">
                    <h2 class="summary-title">
                        <i class="fas fa-receipt"></i> Order Summary
                    </h2>
                    
                    <div class="summary-details">
                        <div class="summary-row">
                            <span class="row-label">
                                <i class="fas fa-shopping-bag"></i> Subtotal (<span class="item-count"><?= $cartCount ?> <?= $cartCount === 1 ? 'item' : 'items' ?></span>)
                            </span>
                            <span class="row-value" id="subtotalAmount">₱<?= number_format($subtotal, 2) ?></span>
                        </div>

                        <div class="summary-row">
                            <span class="row-label">
                                <i class="fas fa-shipping-fast"></i> Shipping Fee
                            </span>
                            <span class="row-value" id="shippingFee">
                                <?= $shippingFee == 0 ? 'Free' : '₱' . number_format($shippingFee, 2) ?>
                            </span>
                        </div>

                        <div id="freeShippingContainer" style="display: <?= $subtotal >= 5000 ? 'block' : 'none' ?>;">
                            <div class="free-shipping-notice">
                                <i class="fas fa-check-circle"></i>
                                <span>You qualify for free shipping!</span>
                            </div>
                        </div>

                        <div id="shippingProgressContainer" style="display: <?= $subtotal < 5000 ? 'block' : 'none' ?>;">
                            <div class="shipping-progress">
                                <div class="progress-text">
                                    <i class="fas fa-truck"></i>
                                    <span>Add <span id="remainingAmount">₱<?= number_format(max(0, 5000 - $subtotal), 2) ?></span> more for free shipping</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= min(($subtotal / 5000) * 100, 100) ?>%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="summary-row summary-total">
                            <span class="row-label">Total Amount</span>
                            <span class="row-value" id="totalAmount">₱<?= number_format($total, 2) ?></span>
                        </div>

                        <p class="tax-notice">
                            <i class="fas fa-info-circle"></i> Tax included where applicable
                        </p>
                    </div>

                    <button type="button" class="btn btn-checkout" onclick="proceedToCheckout()">
                        Checkout
                    </button>

                    <a href="/collections/index" class="btn btn-continue-shopping">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>

                    <div class="payment-methods">
                        <p class="methods-title">We Accept</p>
                        <div class="methods-icons">
                            <i class="fab fa-cc-visa" title="Visa"></i>
                            <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                            <i class="fab fa-cc-paypal" title="PayPal"></i>
                            <i class="fas fa-credit-card" title="Credit Card"></i>
                        </div>
                    </div>
                </div>

                <div class="trust-badges">
                    <div class="trust-badge">
                        <i class="fas fa-shield-alt"></i>
                        <div class="badge-content">
                            <strong>Secure Payment</strong>
                            <span>SSL encrypted checkout</span>
                        </div>
                    </div>
                    <div class="trust-badge">
                        <i class="fas fa-shipping-fast"></i>
                        <div class="badge-content">
                            <strong>Fast Delivery</strong>
                            <span>Express shipping available</span>
                        </div>
                    </div>
                    <div class="trust-badge">
                        <i class="fas fa-undo-alt"></i>
                        <div class="badge-content">
                            <strong>Easy Returns</strong>
                            <span>30-day return policy</span>
                        </div>
                    </div>
                    <div class="trust-badge">
                        <i class="fas fa-headset"></i>
                        <div class="badge-content">
                            <strong>24/7 Support</strong>
                            <span>Always here to help</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<input type="hidden" id="csrfToken" value="<?= \App\Core\Session::get('csrf_token') ?>">

