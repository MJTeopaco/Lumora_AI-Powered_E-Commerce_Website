<!-- app/views/checkout/index.view.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Lumora</title>
<link rel="stylesheet" href="<?= base_url('/css/checkout.css') ?>"></head>
<body>
    <div class="checkout-container">
        <!-- Header -->
        <div class="checkout-header">
            <div class="checkout-header-content">
                <h1 class="checkout-title">
                    <i class="fas fa-lock"></i>
                    Secure Checkout
                </h1>
                
                <!-- Breadcrumb -->
                <div class="breadcrumb">
                    <a href="/">Home</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="/cart">Cart</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Checkout</span>
                </div>
            </div>
            
            <!-- Progress Indicator -->
            <div class="checkout-progress">
                <div class="progress-step completed">
                    <div class="step-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <span class="step-text">Cart</span>
                </div>
                
                <div class="step-connector completed"></div>
                
                <div class="progress-step active">
                    <div class="step-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <span class="step-text">Checkout</span>
                </div>
                
                <div class="step-connector"></div>
                
                <div class="progress-step">
                    <div class="step-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <span class="step-text">Complete</span>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="checkout-content">
            <!-- Left Column - Forms -->
            <div class="checkout-forms">
                <form id="checkoutForm" action="/checkout/process" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    
                    <!-- Shipping Address Section -->
                    <div class="checkout-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h2 class="section-title">Shipping Address</h2>
                                <p class="section-subtitle">Where should we deliver your order?</p>
                            </div>
                        </div>
                        
                        <?php if (empty($addresses)): ?>
                            <div class="no-address">
                                <i class="fas fa-map-marked-alt"></i>
                                <p>No saved addresses found</p>
                                <button type="button" class="btn-add-address" onclick="alert('Add address modal - to be implemented')">
                                    <i class="fas fa-plus"></i>
                                    Add New Address
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="address-list">
                                <?php foreach ($addresses as $address): ?>
                                    <label class="address-option">
                                        <input 
                                            type="radio" 
                                            name="address_id" 
                                            value="<?= $address['address_id'] ?>"
                                            class="address-radio"
                                            <?= ($defaultAddress && $defaultAddress['address_id'] === $address['address_id']) ? 'checked' : '' ?>
                                            required
                                        >
                                        <div class="address-card">
                                            <div class="radio-indicator"></div>
                                            <div class="address-details">
                                                <div class="address-name">
                                                    <?= htmlspecialchars(ucfirst($address['address_type'])) ?> Address
                                                    <?php if ($address['is_default']): ?>
                                                        <span class="address-badge">Default</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="address-text">
                                                    <?= htmlspecialchars($address['address_line_1']) ?>
                                                    <?php if ($address['address_line_2']): ?>
                                                        , <?= htmlspecialchars($address['address_line_2']) ?>
                                                    <?php endif; ?>
                                                    <br>
                                                    <?= htmlspecialchars($address['barangay']) ?>, 
                                                    <?= htmlspecialchars($address['city']) ?>
                                                    <br>
                                                    <?= htmlspecialchars($address['province']) ?>, 
                                                    <?= htmlspecialchars($address['region']) ?>
                                                    <?php if ($address['postal_code']): ?>
                                                        <?= htmlspecialchars($address['postal_code']) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                                
                                <!-- Add New Address Button -->
                                <button type="button" class="btn-add-address" onclick="alert('Add address modal - to be implemented')">
                                    <i class="fas fa-plus"></i>
                                    Add New Address
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Payment Method Section -->
                    <div class="checkout-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div>
                                <h2 class="section-title">Payment Method</h2>
                                <p class="section-subtitle">Secure payment via PayMongo</p>
                            </div>
                        </div>
                        
                        <div class="payment-methods">
                            <div class="payment-option">
                                <div class="payment-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="payment-info">
                                    <div class="payment-name">PayMongo Secure Payment</div>
                                    <div class="payment-description">
                                        Pay securely with GCash, PayMaya, or Credit/Debit Card
                                    </div>
                                    <div class="payment-logos">
                                        <span class="payment-logo-badge">GCash</span>
                                        <span class="payment-logo-badge">PayMaya</span>
                                        <span class="payment-logo-badge">Visa</span>
                                        <span class="payment-logo-badge">Mastercard</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Right Column - Order Summary -->
            <div class="checkout-summary">
                <div class="summary-header">
                    <h3>Order Summary</h3>
                </div>
                
                <!-- Order Items -->
                <div class="summary-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <div class="summary-item-image">
                                <img 
                                    src="/<?= htmlspecialchars($item['product_picture'] ?: $item['cover_picture']) ?>" 
                                    alt="<?= htmlspecialchars($item['product_name']) ?>"
                                    onerror="this.src='/assets/images/placeholder.jpg'"
                                >
                            </div>
                            <div class="summary-item-details">
                                <div class="summary-item-name">
                                    <?= htmlspecialchars($item['product_name']) ?>
                                </div>
                                <div class="summary-item-variant">
                                    <?= htmlspecialchars($item['variant_name']) ?>
                                    <?php if ($item['color']): ?>
                                        • <?= htmlspecialchars($item['color']) ?>
                                    <?php endif; ?>
                                    <?php if ($item['size']): ?>
                                        • <?= htmlspecialchars($item['size']) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="summary-item-quantity">
                                    Qty: <?= $item['quantity'] ?>
                                </div>
                            </div>
                            <div class="summary-item-price">
                                ₱<?= number_format($item['item_total'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Totals -->
                <div class="summary-totals">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value">₱<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Shipping Fee</span>
                        <span class="summary-value">₱<?= number_format($shippingFee, 2) ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span class="summary-label">Total</span>
                        <span class="summary-value">₱<?= number_format($total, 2) ?></span>
                    </div>
                </div>
                
                <!-- Checkout Button -->
                <button 
                    type="submit" 
                    form="checkoutForm" 
                    class="btn-checkout"
                    <?= empty($addresses) ? 'disabled' : '' ?>
                >
                    <i class="fas fa-lock"></i>
                    Proceed to Payment
                </button>
                
                <div class="secure-badge">
                    <i class="fas fa-shield-check"></i>
                    Secured by PayMongo
                </div>
            </div>
        </div>
    </div>
    
<script src="<?= base_url('/js/checkout.js') ?>"></script></body>
</html>