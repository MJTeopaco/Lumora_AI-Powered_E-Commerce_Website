<?php
// app/Views/admin/payouts.view.php
?>

<input type="hidden" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<!-- Summary Stats -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-info">
            <h3>Pending Payouts</h3>
            <p><?= count($pendingPayouts) ?></p>
        </div>
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
    </div>

    <div class="stat-card green">
        <div class="stat-info">
            <h3>Total Amount Pending</h3>
            <p>₱<?= number_format(array_sum(array_column($pendingPayouts, 'total_payout')), 2) ?></p>
        </div>
        <div class="stat-icon">
            <i class="fas fa-coins"></i>
        </div>
    </div>

    <div class="stat-card orange">
        <div class="stat-info">
            <h3>Orders to Process</h3>
            <p><?= array_sum(array_column($pendingPayouts, 'order_count')) ?></p>
        </div>
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
    </div>
</div>

<!-- Pending Payouts Table -->
<div class="content-card">
    <h2>
        <span><i class="fas fa-wallet"></i> Pending Seller Payouts</span>
        <span class="badge badge-warning"><?= count($pendingPayouts) ?> Pending</span>
    </h2>

    <?php if (empty($pendingPayouts)): ?>
        <div class="empty-state">
            <i class="fas fa-check-circle"></i>
            <p>No pending payouts! All sellers have been paid.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Shop Name</th>
                        <th>Seller</th>
                        <th>Payout Details</th>
                        <th>Orders</th>
                        <th>Total Amount</th>
                        <th>Oldest Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingPayouts as $payout): ?>
                    <tr>
                        <td>
                            <div class="shop-info">
                                <strong><?= htmlspecialchars($payout['shop_name']) ?></strong>
                                <small class="text-muted">Shop ID: <?= $payout['shop_id'] ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="user-info-cell">
                                <strong><?= htmlspecialchars($payout['seller_name']) ?></strong>
                            </div>
                        </td>
                        <td>
                            <div class="payout-details">
                                <?php if (!empty($payout['payout_provider']) && !empty($payout['payout_account_number'])): ?>
                                    <div class="payout-method">
                                        <span class="badge badge-success">
                                            <i class="fas fa-mobile-alt"></i>
                                            <?= htmlspecialchars($payout['payout_provider']) ?>
                                        </span>
                                    </div>
                                    <div class="payout-account">
                                        <i class="fas fa-user"></i>
                                        <strong><?= htmlspecialchars($payout['payout_account_name'] ?? 'Not Set') ?></strong>
                                    </div>
                                    <div class="payout-number">
                                        <i class="fas fa-phone"></i>
                                        <code><?= htmlspecialchars($payout['payout_account_number']) ?></code>
                                    </div>
                                <?php else: ?>
                                    <span class="badge danger">
                                        <i class="fas fa-exclamation-triangle"></i> No Billing Info
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="order-count">
                                <i class="fas fa-shopping-bag"></i>
                                <?= $payout['order_count'] ?> order(s)
                            </span>
                        </td>
                        <td>
                            <div class="amount-display">
                                <strong style="color: #D4AF37; font-size: 16px;">
                                    ₱<?= number_format($payout['total_payout'], 2) ?>
                                </strong>
                            </div>
                        </td>
                        <td>
                            <span class="date-badge">
                                <?= date('M d, Y', strtotime($payout['oldest_order'])) ?>
                            </span>
                            <small class="text-muted" style="display: block; margin-top: 3px;">
                                <?= floor((time() - strtotime($payout['oldest_order'])) / 86400) ?> days ago
                            </small>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-info btn-sm" 
                                    onclick='viewPayoutDetails(<?= json_encode($payout) ?>)'>
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <?php if (!empty($payout['payout_provider'])): ?>
                                    <button class="btn btn-success btn-sm" 
                                        onclick="markAsPaid(<?= $payout['shop_id'] ?>, '<?= htmlspecialchars($payout['shop_name']) ?>', <?= $payout['total_payout'] ?>)">
                                        <i class="fas fa-check"></i> Mark Paid
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-danger btn-sm" disabled title="Seller needs to set up billing information">
                                        <i class="fas fa-ban"></i> No Billing Info
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Instructions Card -->
<div class="content-card" style="margin-top: 30px; background: #fffcf5; border-left: 4px solid #D4AF37;">
    <h2 style="display: flex; align-items: center; gap: 10px; justify-content: flex-start !important;">
        <i class="fas fa-info-circle"></i>
        <span>How to Process Payouts</span>
    </h2>
    <ol style="margin-left: 20px; line-height: 1.8;">
        <li>Review the pending payouts table above</li>
        <li>Verify the seller's GCash/Maya account details</li>
        <li>Transfer the amount via GCash/Maya using the phone number provided</li>
        <li>Click <strong>"Mark Paid"</strong> to record the payout in the system</li>
        <li>The system will generate a unique reference ID for record keeping</li>
    </ol>
    <p style="margin-top: 15px; padding: 10px; background: white; border-radius: 6px; display: flex; align-items: flex-start; gap: 8px;">
        <i class="fas fa-shield-alt" style="color: #28a745; margin-top: 2px; flex-shrink: 0;"></i>
        <span>
            <strong>Note:</strong> Sellers without billing information cannot receive payouts. 
            Ask them to update their shop profile with GCash or Maya details.
        </span>
    </p>
</div>

<!-- Payout Details Modal -->
<div id="payoutDetailsModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3><i class="fas fa-wallet"></i> Payout Details</h3>
            <button class="close-modal" onclick="closePayoutModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="details-grid">
                <div class="details-section">
                    <h4><i class="fas fa-store"></i> Shop Information</h4>
                    <div class="details-content">
                        <div class="detail-item">
                            <label>Shop Name:</label>
                            <span id="modal_shop_name"></span>
                        </div>
                        <div class="detail-item">
                            <label>Shop ID:</label>
                            <span id="modal_shop_id"></span>
                        </div>
                        <div class="detail-item">
                            <label>Seller Name:</label>
                            <span id="modal_seller_name"></span>
                        </div>
                    </div>
                </div>

                <div class="details-section">
                    <h4><i class="fas fa-mobile-alt"></i> Payout Account</h4>
                    <div class="details-content">
                        <div class="detail-item">
                            <label>Payment Provider:</label>
                            <span id="modal_payout_provider"></span>
                        </div>
                        <div class="detail-item">
                            <label>Account Name:</label>
                            <span id="modal_payout_account_name"></span>
                        </div>
                        <div class="detail-item">
                            <label>Account Number:</label>
                            <span id="modal_payout_account_number" class="code-text"></span>
                        </div>
                    </div>
                </div>

                <div class="details-section full-width">
                    <h4><i class="fas fa-calculator"></i> Payout Summary</h4>
                    <div class="details-content">
                        <div class="detail-item">
                            <label>Number of Orders:</label>
                            <span id="modal_order_count"></span>
                        </div>
                        <div class="detail-item">
                            <label>Total Payout Amount:</label>
                            <span id="modal_total_amount" style="color: #D4AF37; font-size: 18px; font-weight: bold;"></span>
                        </div>
                        <div class="detail-item">
                            <label>Oldest Pending Order:</label>
                            <span id="modal_oldest_order"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closePayoutModal()">Close</button>
            <div id="modal_action_button"></div>
        </div>
    </div>
</div>

<style>
/* Additional Payout-specific Styles */
.payout-details {
    font-size: 13px;
}

.payout-method {
    margin-bottom: 8px;
}

.payout-account,
.payout-number {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 5px;
}

.payout-account i,
.payout-number i {
    width: 14px;
    color: #7f8c8d;
}

.payout-number code {
    background: #f8f9fa;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #1A1A1A;
    font-weight: 600;
}

.order-count {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
}

.order-count i {
    color: #D4AF37;
}

.amount-display {
    text-align: right;
}

.badge.danger {
    background: #f8d7da;
    color: #721c24;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
}
</style>

<script>
// View Payout Details
function viewPayoutDetails(payout) {
    document.getElementById('modal_shop_name').textContent = payout.shop_name;
    document.getElementById('modal_shop_id').textContent = payout.shop_id;
    document.getElementById('modal_seller_name').textContent = payout.seller_name;
    
    if (payout.payout_provider) {
        document.getElementById('modal_payout_provider').innerHTML = 
            '<span class="badge badge-success"><i class="fas fa-mobile-alt"></i> ' + payout.payout_provider + '</span>';
        document.getElementById('modal_payout_account_name').textContent = payout.payout_account_name || 'Not Set';
        document.getElementById('modal_payout_account_number').textContent = payout.payout_account_number;
    } else {
        document.getElementById('modal_payout_provider').innerHTML = 
            '<span class="badge danger"><i class="fas fa-exclamation-triangle"></i> Not Configured</span>';
        document.getElementById('modal_payout_account_name').textContent = '-';
        document.getElementById('modal_payout_account_number').textContent = '-';
    }
    
    document.getElementById('modal_order_count').textContent = payout.order_count + ' order(s)';
    document.getElementById('modal_total_amount').textContent = '₱' + parseFloat(payout.total_payout).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    const oldestDate = new Date(payout.oldest_order);
    const daysAgo = Math.floor((Date.now() - oldestDate.getTime()) / (1000 * 60 * 60 * 24));
    document.getElementById('modal_oldest_order').textContent = 
        oldestDate.toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'}) + 
        ' (' + daysAgo + ' days ago)';
    
    // Add action button
    const actionButton = document.getElementById('modal_action_button');
    if (payout.payout_provider) {
        actionButton.innerHTML = `
            <button class="btn btn-success" onclick="markAsPaid(${payout.shop_id}, '${payout.shop_name}', ${payout.total_payout})">
                <i class="fas fa-check"></i> Mark as Paid
            </button>
        `;
    } else {
        actionButton.innerHTML = '';
    }
    
    document.getElementById('payoutDetailsModal').classList.add('active');
}

// Close Modal
function closePayoutModal() {
    document.getElementById('payoutDetailsModal').classList.remove('active');
}

// Mark as Paid
function markAsPaid(shopId, shopName, amount) {
    const confirmed = confirm(
        `Confirm Payout\n\n` +
        `Shop: ${shopName}\n` +
        `Amount: ₱${parseFloat(amount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}\n\n` +
        `Have you transferred this amount via GCash/Maya?\n\n` +
        `Click OK to mark as PAID.`
    );
    
    if (!confirmed) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/payouts/process';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = document.getElementById('csrf_token').value;
    
    const shopInput = document.createElement('input');
    shopInput.type = 'hidden';
    shopInput.name = 'shop_id';
    shopInput.value = shopId;
    
    form.appendChild(csrfInput);
    form.appendChild(shopInput);
    document.body.appendChild(form);
    form.submit();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('payoutDetailsModal');
    if (event.target === modal) {
        closePayoutModal();
    }
}
</script>