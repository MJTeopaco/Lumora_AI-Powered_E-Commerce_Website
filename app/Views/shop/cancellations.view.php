<link rel="stylesheet" href="/css/shop-cancellations.css">

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">
                <i class="fas fa-times-circle"></i> Cancellations
            </h1>
            <p class="dashboard-subtitle">Review and manage cancelled orders</p>
        </div>
        
        <div class="cancellation-summary">
            <div class="summary-label">Total Cancelled</div>
            <div class="summary-value"><?= count($cancellations ?? []) ?></div>
        </div>
    </div>

    <div class="cancellations-content">
        <?php if (empty($cancellations)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>No Cancellations</h3>
                <p>Great job! You have no cancelled orders at the moment.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="cancellations-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Date Cancelled</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cancellations as $order): ?>
                            <tr>
                                <td>
                                    <span class="order-id">#<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?></span>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-name"><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></div>
                                        <div class="customer-sub"><?= htmlspecialchars($order['customer_email'] ?? '') ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="item-count">
                                        <?= $order['item_count'] ?? 0 ?> item(s)
                                    </span>
                                </td>
                                <td>
                                    <span class="amount">₱<?= number_format($order['total_amount'], 2) ?></span>
                                </td>
                                <td>
                                    <div class="date-info">
                                        <span class="date"><?= date('M d, Y', strtotime($order['updated_at'] ?? $order['created_at'])) ?></span>
                                        <span class="time"><?= date('g:i A', strtotime($order['updated_at'] ?? $order['created_at'])) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <button 
                                        class="btn-view-details" 
                                        onclick="viewCancellationDetails(<?= $order['order_id'] ?>)"
                                        title="View Order Details"
                                    >
                                        <i class="fas fa-eye"></i> Details
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="cancellationDetailsModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-file-invoice-dollar"></i> Cancellation Details</h3>
            <button class="close-modal" onclick="closeCancellationModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="modalContent">
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading details...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-close" onclick="closeCancellationModal()">Close</button>
        </div>
    </div>
</div>

<script src="/js/shop-cancellations.js"></script>