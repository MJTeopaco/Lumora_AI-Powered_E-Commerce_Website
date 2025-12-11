<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="content-card" style="margin-bottom: 30px;">
    <h2>
        <span><i class="fas fa-filter"></i> Date Range</span>
        <form method="GET" style="display: flex; gap: 10px; align-items: center; font-weight: normal;">
            <div style="display: flex; align-items: center; gap: 5px;">
                <label style="font-size: 12px; margin: 0; font-weight: 600;">Start:</label>
                <input type="date" name="start_date" value="<?= $dateRange['start'] ?>" style="padding: 6px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div style="display: flex; align-items: center; gap: 5px;">
                <label style="font-size: 12px; margin: 0; font-weight: 600;">End:</label>
                <input type="date" name="end_date" value="<?= $dateRange['end'] ?>" style="padding: 6px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Update</button>
        </form>
    </h2>
</div>

<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-info">
            <h3>Total Revenue</h3>
            <p>₱<?= number_format($overview['total_revenue'] ?? 0, 2) ?></p>
        </div>
        <div class="stat-icon">
            <i class="fas fa-coins"></i>
        </div>
    </div>

    <div class="stat-card blue">
        <div class="stat-info">
            <h3>Total Orders</h3>
            <p><?= number_format($overview['total_orders'] ?? 0) ?></p>
        </div>
        <div class="stat-icon">
            <i class="fas fa-shopping-bag"></i>
        </div>
    </div>

    <div class="stat-card orange">
        <div class="stat-info">
            <h3>Avg. Order Value</h3>
            <p>₱<?= number_format($overview['average_order_value'] ?? 0, 2) ?></p>
        </div>
        <div class="stat-icon">
            <i class="fas fa-chart-pie"></i>
        </div>
    </div>
</div>

<div class="content-grid">
    <div class="content-card">
        <h2><i class="fas fa-chart-area"></i> Revenue Trend</h2>
        <div style="position: relative; height: 450px; width: 100%;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <div class="content-card">
        <h2><i class="fas fa-crown"></i> Top Products</h2>
        <?php if(empty($topProducts)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <p>No sales data available.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align: right;">Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topProducts as $product): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                            <br><small style="color: #95a5a6;"><?= htmlspecialchars($product['shop_name']) ?></small>
                        </td>
                        <td style="text-align: right;">
                            <span class="badge success">₱<?= number_format($product['revenue_generated'], 2) ?></span>
                            <br><small><?= $product['total_sold'] ?> sold</small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data from PHP
    const salesData = <?= json_encode($dailySales) ?>;
    
    // Check if chart element exists
    const chartCanvas = document.getElementById('salesChart');
    if (chartCanvas && salesData) {
        const ctx = chartCanvas.getContext('2d');
        const labels = salesData.map(item => item.date);
        const data = salesData.map(item => item.revenue);

        // Chart Theme Colors
        const goldColor = '#D4AF37'; 
        const goldBg = 'rgba(212, 175, 55, 0.1)';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: data,
                    borderColor: goldColor,
                    backgroundColor: goldBg,
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: goldColor,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Allows chart to fill the 450px height
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱ ' + parseFloat(context.parsed.y).toLocaleString('en-PH', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#f0f0f0' },
                        ticks: {
                            callback: function(value) { return '₱' + value; },
                            color: '#7f8c8d'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#7f8c8d' }
                    }
                }
            }
        });
    }
});
</script>