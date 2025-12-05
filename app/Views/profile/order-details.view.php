<?php
// app/Views/profile/order-details.view.php

// 1. Safe Status Handling
$status = $order['order_status'] ?? 'PENDING';
$statusClass = strtolower(str_replace('_', '-', $status));
$statusDisplay = ucwords(str_replace('_', ' ', $status));

// 2. Safe Address Handling
$addressParts = [
    $order['address_line_1'] ?? null,
    $order['address_line_2'] ?? null,
    $order['barangay'] ?? null,
    $order['city'] ?? null,
    $order['province'] ?? null,
    $order['region'] ?? null,
    $order['postal_code'] ?? null
];
$formattedAddress = implode(', ', array_filter($addressParts));
if (empty($formattedAddress)) {
    $formattedAddress = 'No address details available';
}

$username = $order['username'] ?? 'Valued Customer';
?>

<link rel="stylesheet" href="/css/profile-orders.css">
<style>
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #666;
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 1rem;
        transition: color 0.3s ease;
    }
    .back-link:hover {
        color: #D4AF37;
    }
    .page-card {
        background: #FFFFFF;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .header-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }
    /* Timeline Styles */
    .status-timeline {
        display: flex;
        justify-content: space-between;
        margin: 2rem 0;
        position: relative;
    }
    .status-timeline::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 0;
        right: 0;
        height: 2px;
        background: #e5e5e5;
        z-index: 1;
    }
    .timeline-step {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        background: #fff;
        padding: 0 1rem;
    }
    .step-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #e5e5e5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #e5e5e5;
        transition: all 0.3s ease;
    }
    .timeline-step.active .step-dot {
        border-color: #D4AF37;
        background: #D4AF37;
        color: #fff;
    }
    .timeline-step.completed .step-dot {
        border-color: #D4AF37;
        background: #fff;
        color: #D4AF37;
    }
    .step-label {
        font-size: 12px;
        font-weight: 600;
        color: #999;
        text-transform: uppercase;
    }
    .timeline-step.active .step-label,
    .timeline-step.completed .step-label {
        color: #1A1A1A;
    }
    
    /* Review Button Style */
    .btn-review-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        background-color: #D4AF37;
        color: white;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 500;
        transition: background-color 0.2s;
        margin-top: 8px;
    }
    .btn-review-item:hover {
        background-color: #c49f30;
    }

    /* Reviewed Badge Styles */
    .badge-reviewed {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        background-color: #e9ecef;
        color: #28a745;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: default;
        margin-top: 8px;
    }
    .btn-view-product {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        background-color: #f8f9fa;
        color: #333;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.85rem;
        transition: all 0.2s;
        margin-top: 8px;
        margin-left: 5px;
    }
    .btn-view-product:hover {
        background-color: #e2e6ea;
        color: #000;
    }
</style>

<div class="content-header">
    <div>
        <a href="/profile/orders" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to My Orders
        </a>
        <h1 class="content-title">Order Details</h1>
        <p class="content-subtitle">
            Order #<?= str_pad($order['order_id'] ?? 0, 6, '0', STR_PAD_LEFT) ?>
            <span class="order-status status-<?= htmlspecialchars($statusClass) ?>" style="margin-left: 10px; font-size: 0.8rem; vertical-align: middle;">
                <?= htmlspecialchars($statusDisplay) ?>
            </span>
        </p>
    </div>
</div>

<div class="page-card">
    <?php 
    $steps = ['PENDING_PAYMENT' => 'Placed', 'PROCESSING' => 'Processing', 'SHIPPED' => 'Shipped', 'DELIVERED' => 'Delivered'];
    $currentStatusFound = false;
    $currentStatus = $order['order_status'] ?? '';
    $isCancelled = $currentStatus === 'CANCELLED' || $currentStatus === 'REFUND_REQUESTED';
    ?>
    
    <?php if (!$isCancelled): ?>
    <div class="status-timeline">
        <?php foreach ($steps as $key => $label): ?>
            <?php 
                $isActive = $currentStatus === $key;
                $isCompleted = !$currentStatusFound && !$isActive;
                if ($isActive) $currentStatusFound = true;
                
                $stepClass = $isActive ? 'active' : ($isCompleted ? 'completed' : '');
            ?>
            <div class="timeline-step <?= $stepClass ?>">
                <div class="step-dot">
                    <i class="fas fa-check"></i>
                </div>
                <div class="step-label"><?= $label ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php elseif ($currentStatus === 'REFUND_REQUESTED'): ?>
        <div class="alert alert-warning" style="background: #FFF3CD; color: #856404; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
            <i class="fas fa-exclamation-circle" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Refund Requested</strong>
                <p style="margin: 0; font-size: 0.9rem;">You have requested a cancellation and refund. This is pending seller approval.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger" style="background: #F8D7DA; color: #721C24; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
            <i class="fas fa-times-circle" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Order Cancelled</strong>
                <p style="margin: 0; font-size: 0.9rem;">This order has been cancelled and will not be processed.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="order-details-container">
        
        <div class="detail-section">
            <h4><i class="fas fa-info-circle"></i> Order Information</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Order ID</span>
                    <span class="detail-value">#<?= str_pad($order['order_id'] ?? 0, 6, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Order Date</span>
                    <span class="detail-value"><?= date('F j, Y, g:i a', strtotime($order['created_at'] ?? 'now')) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment Status</span>
                    <span class="detail-value">
                        <?php if (($order['order_status'] ?? '') === 'PENDING_PAYMENT'): ?>
                            <span style="color: #856404;"><i class="fas fa-clock"></i> Unpaid</span>
                        <?php else: ?>
                            <span style="color: #155724;"><i class="fas fa-check-circle"></i> Paid</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Last Updated</span>
                    <span class="detail-value"><?= date('F j, Y', strtotime($order['updated_at'] ?? 'now')) ?></span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <h4><i class="fas fa-map-marker-alt"></i> Shipping Details</h4>
            <div class="address-display">
                <p class="recipient-name" style="font-weight: bold; margin-bottom: 0.5rem;">
                    <?= htmlspecialchars($username) ?>
                </p>
                <p class="recipient-address" style="color: #555;">
                    <?= htmlspecialchars($formattedAddress) ?>
                </p>
            </div>
        </div>

        <div class="detail-section">
            <h4><i class="fas fa-box"></i> Order Items (<?= count($orderItems ?? []) ?>)</h4>
            <div class="order-items-list">
                <?php if (!empty($orderItems)): ?>
                    <?php foreach ($orderItems as $item): ?>
                        <div class="order-item-detail">
                            <div class="item-image">
                                <?php if (!empty($item['cover_picture'])): ?>
                                    <img src="/<?= htmlspecialchars($item['cover_picture']) ?>" alt="<?= htmlspecialchars($item['product_name'] ?? 'Product') ?>">
                                <?php else: ?>
                                    <div class="no-image"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="item-info">
                                <div class="item-name"><?= htmlspecialchars($item['product_name'] ?? 'Unknown Product') ?></div>
                                
                                <?php if (!empty($item['shop_name'])): ?>
                                    <div class="item-shop"><i class="fas fa-store"></i> <?= htmlspecialchars($item['shop_name']) ?></div>
                                <?php endif; ?>

                                <?php 
                                $variants = [];
                                if (!empty($item['color'])) $variants[] = "Color: " . $item['color'];
                                if (!empty($item['size'])) $variants[] = "Size: " . $item['size'];
                                if (!empty($item['material'])) $variants[] = "Material: " . $item['material'];
                                ?>
                                
                                <?php if (!empty($variants)): ?>
                                    <div class="item-variant"><?= implode(' • ', array_map('htmlspecialchars', $variants)) ?></div>
                                <?php endif; ?>

                                <?php if (!empty($item['personalized_notes'])): ?>
                                    <div class="item-notes"><strong>Note:</strong> <?= htmlspecialchars($item['personalized_notes']) ?></div>
                                <?php endif; ?>

                                <div class="item-pricing">
                                    <span class="item-quantity">Qty: <?= $item['quantity'] ?? 0 ?></span>
                                    <span class="item-price">₱<?= number_format($item['price_at_purchase'] ?? 0, 2) ?> each</span>
                                    <span class="item-total">₱<?= number_format($item['total_price'] ?? 0, 2) ?></span>
                                </div>

                                <?php if (($order['order_status'] ?? '') === 'DELIVERED'): ?>
                                    <div class="item-actions">
                                        <?php if (!empty($item['has_reviewed'])): ?>
                                            <span class="badge-reviewed">
                                                <i class="fas fa-check-circle"></i> Reviewed
                                            </span>
                                            <?php if (!empty($item['product_slug'])): ?>
                                                <a href="/products/<?= htmlspecialchars($item['product_slug']) ?>" class="btn-view-product">
                                                    View Product
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="/reviews/create?product_id=<?= $item['product_id'] ?>" class="btn-review-item">
                                                <i class="fas fa-star"></i> Write a Review
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No items found.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="detail-section">
            <h4><i class="fas fa-receipt"></i> Payment Summary</h4>
            <div class="summary-grid">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₱<?= number_format(($order['total_amount'] ?? 0) - ($order['shipping_fee'] ?? 0), 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping Fee</span>
                    <span>₱<?= number_format($order['shipping_fee'] ?? 0, 2) ?></span>
                </div>
                <div class="summary-row total">
                    <span><strong>Total Amount</strong></span>
                    <span><strong>₱<?= number_format($order['total_amount'] ?? 0, 2) ?></strong></span>
                </div>
            </div>
        </div>

        <div class="header-actions" style="justify-content: flex-end;">
            <?php 
            $cancellable = in_array($status, ['PENDING_PAYMENT', 'PROCESSING', 'PAID', 'READY_TO_SHIP']);
            ?>
            
            <?php if ($cancellable): ?>
                <form method="POST" action="/profile/orders/cancel" onsubmit="return confirm('Are you sure you want to cancel this order? This cannot be undone.');">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?? '' ?>">
                    <button type="submit" class="btn-cancel-order">
                        <i class="fas fa-times-circle"></i> Cancel Order
                    </button>
                </form>
            <?php endif; ?>
            
            <?php if ($status === 'PENDING_PAYMENT'): ?>
                <a href="/checkout" class="btn-primary" style="text-decoration: none;">
                    <i class="fas fa-credit-card"></i> Pay Now (Go to Checkout)
                </a>
            <?php endif; ?>
        </div>

    </div>
</div>