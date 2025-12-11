<?php
// app/Views/shop/earnings.view.php
?>
<link rel="stylesheet" href="/css/shop-dashboard.css">
<style>
    /* Custom styles for earnings page matching dashboard theme */
    .earnings-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .earning-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .earning-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }
    .icon-wallet { background: #6c5ce7; }
    .icon-paid { background: #00b894; }
    
    .earning-info h3 { margin: 0; font-size: 28px; color: #2d3436; }
    .earning-info p { margin: 5px 0 0; color: #636e72; font-size: 14px; }
    
    .status-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-pending { background: #ffeaa7; color: #d35400; }
    .status-paid { background: #55efc4; color: #006266; }
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">
                <i class="fas fa-wallet"></i> My Earnings
            </h1>
            <p class="dashboard-subtitle">Track your revenue and payouts</p>
        </div>
    </div>

    <div class="earnings-stats">
        <div class="earning-card">
            <div class="earning-icon icon-wallet">
                <i class="fas fa-coins"></i>
            </div>
            <div class="earning-info">
                <h3>₱<?= number_format($balance, 2) ?></h3>
                <p>Available Balance (Pending)</p>
            </div>
        </div>

        <div class="earning-card">
            <div class="earning-icon icon-paid">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="earning-info">
                <h3>₱<?= number_format($total_paid, 2) ?></h3>
                <p>Total Payouts Received</p>
            </div>
        </div>
    </div>

    <div class="content-card">
        <h3 style="margin-bottom: 20px; font-size: 18px; color: #2d3436;">Transaction History</h3>
        
        <div class="table-container">
            <?php if (empty($history)): ?>
                <div class="empty-state">
                    <i class="fas fa-receipt empty-icon"></i>
                    <h3>No Earnings Yet</h3>
                    <p>When you complete orders, your earnings will appear here.</p>
                </div>
            <?php else: ?>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Item Revenue</th>
                            <th>Platform Fee (5%)</th>
                            <th>Net Payout</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $row): ?>
                        <tr>
                            <td>
                                <strong>#<?= str_pad($row['order_id'], 6, '0', STR_PAD_LEFT) ?></strong>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                <div class="text-muted small"><?= date('h:i A', strtotime($row['created_at'])) ?></div>
                            </td>
                            <td>
                                ₱<?= number_format($row['item_revenue'], 2) ?>
                            </td>
                            <td style="color: #d63031;">
                                - ₱<?= number_format($row['platform_commission'], 2) ?>
                            </td>
                            <td style="color: #00b894; font-weight: bold;">
                                ₱<?= number_format($row['net_payout_amount'], 2) ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= strtolower($row['payout_status']) ?>">
                                    <?= $row['payout_status'] ?>
                                </span>
                                <?php if ($row['payout_status'] === 'PAID' && $row['payout_reference']): ?>
                                    <div class="text-muted small" style="margin-top:2px; font-size:10px;">
                                        Ref: <?= substr($row['payout_reference'], -8) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>