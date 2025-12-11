<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">
                <i class="fas fa-shopping-cart"></i> Manage Orders
            </h1>
            <p class="dashboard-subtitle">Track and fulfill customer orders</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-blue">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['total_orders'] ?? 0) ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-yellow">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['processing'] ?? 0) ?></div>
                <div class="stat-label">Processing</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-purple">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['ready_to_ship'] ?? 0) ?></div>
                <div class="stat-label">Ready to Ship</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-green">
                <i class="fas fa-truck"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['shipped'] ?? 0) ?></div>
                <div class="stat-label">Shipped</div>
            </div>
        </div>
    </div>

    <div class="filters-section">
        <form method="GET" action="/shop/orders" class="filters-form">
            <div class="filter-group">
                <?php $filter = $currentFilter ?? 'all'; ?>
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Orders</option>
                    <option value="processing" <?= $filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="ready_to_ship" <?= $filter === 'ready_to_ship' ? 'selected' : '' ?>>Ready to Ship</option>
                    <option value="shipped" <?= $filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="delivered" <?= $filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= $filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="refund_requested" <?= $filter === 'refund_requested' ? 'selected' : '' ?>>Refund Requested</option>
                </select>
            </div>

            <div class="search-group">
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Search by Order ID or Customer..." 
                    value="<?= htmlspecialchars($searchTerm ?? '') ?>"
                >
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <?php if (($currentFilter ?? 'all') !== 'all' || !empty($searchTerm)): ?>
                <a href="/shop/orders" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear Filters
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox empty-icon"></i>
                <h3>No Orders Found</h3>
                <p>
                    <?php if (!empty($searchTerm)): ?>
                        No orders match your search "<?= htmlspecialchars($searchTerm) ?>"
                    <?php elseif (($currentFilter ?? 'all') !== 'all'): ?>
                        No orders with status "<?= htmlspecialchars($currentFilter) ?>"
                    <?php else: ?>
                        You haven't received any orders yet
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <strong class="order-id">#<?= str_pad($order['order_id'] ?? 0, 6, '0', STR_PAD_LEFT) ?></strong>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-name">
                                        <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?>
                                    </div>
                                    <div class="customer-email">
                                        <?= htmlspecialchars($order['customer_email'] ?? '') ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="item-count">
                                    <?= number_format($order['item_count'] ?? 0) ?> item(s) 
                                    <span class="text-muted">(<?= number_format($order['total_items'] ?? 0) ?> qty)</span>
                                </span>
                            </td>
                            <td>
                                <div class="order-amount">
                                    <strong>₱<?= number_format($order['total_amount'] ?? 0, 2) ?></strong>
                                    <?php if (($order['shipping_fee'] ?? 0) > 0): ?>
                                        <div class="text-muted small">+ ₱<?= number_format($order['shipping_fee'], 2) ?> shipping</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $status = $order['order_status'] ?? 'PENDING';
                                $statusClass = strtolower(str_replace('_', '-', $status));
                                $statusDisplay = ucwords(str_replace('_', ' ', $status));

                                // --- ADDED: Custom Logic for Refund Requested ---
                                if ($status === 'REFUND_REQUESTED') {
                                    $statusDisplay = "Refund Needed";
                                    $statusClass = "warning"; // Will use your existing orange/yellow warning style
                                }
                                ?>
                                <span class="order-status status-<?= $statusClass ?>">
                                    <?= $statusDisplay ?>
                                </span>
                            </td>
                            <td>
                                <span class="date">
                                    <?= date('M d, Y', strtotime($order['created_at'] ?? 'now')) ?>
                                </span>
                                <div class="text-muted small">
                                    <?= date('g:i A', strtotime($order['created_at'] ?? 'now')) ?>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button 
                                        onclick="viewOrderDetails(<?= $order['order_id'] ?? 0 ?>)" 
                                        class="btn-icon" 
                                        title="View Details"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <?php if (($order['order_status'] ?? '') === 'PROCESSING'): ?>
                                        <button 
                                            onclick="updateOrderStatus(<?= $order['order_id'] ?>, 'SHIPPED')" 
                                            class="btn-icon btn-primary" 
                                            title="Mark as Shipped"
                                        >
                                            <i class="fas fa-truck"></i>
                                        </button>
                                    <?php elseif (($order['order_status'] ?? '') === 'READY_TO_SHIP'): ?>
                                        <button 
                                            onclick="updateOrderStatus(<?= $order['order_id'] ?>, 'SHIPPED')" 
                                            class="btn-icon btn-primary" 
                                            title="Mark as Shipped"
                                        >
                                            <i class="fas fa-truck"></i>
                                        </button>
                                    <?php elseif (($order['order_status'] ?? '') === 'SHIPPED'): ?>
                                        <button 
                                            onclick="updateOrderStatus(<?= $order['order_id'] ?>, 'DELIVERED')" 
                                            class="btn-icon btn-success" 
                                            title="Mark as Delivered"
                                        >
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    
                                    <?php elseif (($order['order_status'] ?? '') === 'REFUND_REQUESTED'): ?>
                                        <button 
                                            onclick="updateOrderStatus(<?= $order['order_id'] ?>, 'CANCELLED')" 
                                            class="btn-icon btn-danger" 
                                            title="Approve Refund"
                                            style="background-color: #dc3545; color: white;"
                                        >
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <button 
                                            onclick="updateOrderStatus(<?= $order['order_id'] ?>, 'PROCESSING')" 
                                            class="btn-icon btn-secondary" 
                                            title="Reject Refund"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div id="orderDetailsModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3><i class="fas fa-file-invoice"></i> Order Details</h3>
            <button class="modal-close" onclick="closeOrderDetailsModal()">&times;</button>
        </div>
        <div class="modal-body" id="orderDetailsContent">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i> Loading order details...
            </div>
        </div>
    </div>
</div>

<div id="statusUpdateModal" class="modal">
    <div class="modal-content">
        <h3>Update Order Status</h3>
        <p id="statusUpdateMessage"></p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeStatusUpdateModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="confirmStatusUpdate()">Confirm</button>
        </div>
    </div>
</div>

<input type="hidden" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
<script src="/js/shop-orders.js"></script>