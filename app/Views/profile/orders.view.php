<?php
// app/Views/profile/orders.view.php
?>
<link rel="stylesheet" href="/css/profile-orders.css">

<div class="content-header">
    <div>
        <h1 class="content-title">My Orders</h1>
        <p class="content-subtitle">Track and manage your order history</p>
    </div>
</div>

<div class="order-stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-shopping-bag"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $stats['total_orders'] ?? 0 ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $stats['completed_orders'] ?? 0 ?></div>
            <div class="stat-label">Completed</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $stats['pending_orders'] ?? 0 ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-peso-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">₱<?= number_format($stats['total_spent'] ?? 0, 2) ?></div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>
</div>

<div class="orders-filters">
    <form method="GET" action="/profile/orders" class="filter-form">
        <div class="filter-tabs">
            <a href="/profile/orders?status=all" 
               class="filter-tab <?= $statusFilter === 'all' ? 'active' : '' ?>">
                All Orders (<?= $statusCounts['all'] ?>)
            </a>
            <a href="/profile/orders?status=PENDING_PAYMENT" 
               class="filter-tab <?= $statusFilter === 'PENDING_PAYMENT' ? 'active' : '' ?>">
                To Pay (<?= $statusCounts['pending_payment'] ?>)
            </a>
            <a href="/profile/orders?status=PROCESSING" 
               class="filter-tab <?= $statusFilter === 'PROCESSING' ? 'active' : '' ?>">
                Processing (<?= $statusCounts['processing'] ?>)
            </a>
            <a href="/profile/orders?status=SHIPPED" 
               class="filter-tab <?= $statusFilter === 'SHIPPED' ? 'active' : '' ?>">
                Shipped (<?= $statusCounts['shipped'] ?>)
            </a>
            <a href="/profile/orders?status=DELIVERED" 
               class="filter-tab <?= $statusFilter === 'DELIVERED' ? 'active' : '' ?>">
                Delivered (<?= $statusCounts['delivered'] ?>)
            </a>
            <a href="/profile/orders?status=CANCELLED" 
               class="filter-tab <?= $statusFilter === 'CANCELLED' ? 'active' : '' ?>">
                Cancelled (<?= $statusCounts['cancelled'] ?>)
            </a>
        </div>

        <div class="search-filter">
            <input 
                type="text" 
                name="search" 
                placeholder="Search by Order ID..." 
                value="<?= htmlspecialchars($searchTerm) ?>"
                class="search-input-filter"
            >
            <button type="submit" class="btn-search-filter">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
</div>

<div class="orders-section">
    <?php if (empty($orders)): ?>
        <div class="empty-orders">
            <i class="fas fa-shopping-bag"></i>
            <h3>No Orders Found</h3>
            <p>
                <?php if (!empty($searchTerm)): ?>
                    No orders match your search "<?= htmlspecialchars($searchTerm) ?>"
                <?php elseif ($statusFilter !== 'all'): ?>
                    You don't have any orders with status "<?= htmlspecialchars(str_replace('_', ' ', $statusFilter)) ?>"
                <?php else: ?>
                    You haven't placed any orders yet
                <?php endif; ?>
            </p>
            <a href="/" class="btn-primary">
                <i class="fas fa-shopping-bag"></i>
                Start Shopping
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div class="order-info-left">
                        <div class="order-id">
                            Order #<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?>
                        </div>
                        <div class="order-date">
                            <i class="fas fa-calendar"></i>
                            <?= date('F d, Y', strtotime($order['created_at'])) ?>
                        </div>
                    </div>
                    <div class="order-info-right">
                        <?php 
                            $statusClass = strtolower(str_replace('_', '-', $order['order_status']));
                            $statusDisplay = ucwords(str_replace('_', ' ', $order['order_status']));
                        ?>
                        <span class="order-status status-<?= $statusClass ?>">
                            <?= $statusDisplay ?>
                        </span>
                    </div>
                </div>

                <div class="order-body">
                    <div class="order-items-preview">
                        <div class="items-count">
                            <i class="fas fa-box"></i>
                            <?= $order['item_count'] ?> item(s)
                        </div>
                    </div>

                    <div class="order-amount">
                        <div class="amount-label">Total Amount</div>
                        <div class="amount-value">₱<?= number_format($order['total_amount'], 2) ?></div>
                    </div>
                </div>

                <div class="order-footer">
                    <a href="/profile/orders/details?order_id=<?= $order['order_id'] ?>" class="btn-view-order" style="text-decoration: none;">
                        <i class="fas fa-eye"></i>
                        View Details
                    </a>

                    <?php if ($order['order_status'] === 'PENDING_PAYMENT' || $order['order_status'] === 'PROCESSING'): ?>
                        <button 
                            class="btn-cancel-order" 
                            onclick="confirmCancelOrder(<?= $order['order_id'] ?>)"
                        >
                            <i class="fas fa-times-circle"></i>
                            Cancel Order
                        </button>
                    <?php endif; ?>

                    <?php if ($order['order_status'] === 'DELIVERED'): ?>
                        <a href="/profile/orders/details?order_id=<?= $order['order_id'] ?>" class="btn-secondary-order" style="text-decoration: none;">
                            <i class="fas fa-star"></i>
                            Rate Items
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="cancelOrderModal" class="modal-overlay">
    <div class="modal-content-small">
        <div class="modal-header-custom">
            <h3><i class="fas fa-exclamation-triangle"></i> Cancel Order</h3>
            <button class="modal-close-btn" onclick="closeCancelModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body-custom">
            <p class="cancel-message">Are you sure you want to cancel this order?</p>
            <p class="cancel-note">This action cannot be undone.</p>
            
            <form method="POST" action="/profile/orders/cancel" id="cancelOrderForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="order_id" id="cancelOrderId">
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary-modal" onclick="closeCancelModal()">
                        No, Keep Order
                    </button>
                    <button type="submit" class="btn-danger-modal">
                        Yes, Cancel Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<input type="hidden" id="csrfToken" value="<?= $_SESSION['csrf_token'] ?>">
<script src="/js/profile-orders.js"></script>