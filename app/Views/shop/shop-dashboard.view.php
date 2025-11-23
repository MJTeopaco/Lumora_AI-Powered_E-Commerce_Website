<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="dashboard-title">
            <i class="fas fa-th-large"></i> Dashboard
        </h1>
        <p class="dashboard-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'Seller') ?>! Here's your shop overview.</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= number_format($stats['total_products'] ?? 0) ?></h3>
                <p class="stat-label">Total Products</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-icon-success">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= number_format($stats['total_orders'] ?? 0) ?></h3>
                <p class="stat-label">Total Orders</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-icon-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= number_format($stats['pending_orders'] ?? 0) ?></h3>
                <p class="stat-label">Pending Orders</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon stat-icon-info">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-value">₱<?= number_format($stats['total_revenue'] ?? 0, 2) ?></h3>
                <p class="stat-label">Total Revenue</p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2 class="section-header">
            <i class="fas fa-bolt"></i> Quick Actions
        </h2>
        <div class="actions-grid">
            <a href="/shop/add-product" class="action-card">
                <i class="fas fa-plus-circle"></i>
                <h3>Add New Product</h3>
                <p>List a new item in your shop</p>
            </a>
            
            <a href="/shop/orders" class="action-card">
                <i class="fas fa-receipt"></i>
                <h3>View Orders</h3>
                <p>Manage customer orders</p>
            </a>
            
            <a href="/shop/products" class="action-card">
                <i class="fas fa-edit"></i>
                <h3>Edit Products</h3>
                <p>Update your product listings</p>
            </a>
            
            <a href="/shop/addresses" class="action-card">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Manage Addresses</h3>
                <p>Update shipping addresses</p>
            </a>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="recent-section">
        <div class="section-header-row">
            <h2 class="section-header">
                <i class="fas fa-history"></i> Recent Orders
            </h2>
            <a href="/shop/orders" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <?php if (!empty($recentOrders)): ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($order['id']) ?></strong></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= htmlspecialchars($order['product_name']) ?></td>
                                <td>₱<?= number_format($order['amount'], 2) ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="/shop/orders/<?= $order['id'] ?>" class="btn-table-action">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox empty-icon"></i>
                <h3>No Recent Orders</h3>
                <p>Your recent orders will appear here once customers start purchasing.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Top Selling Products -->
    <div class="recent-section">
        <div class="section-header-row">
            <h2 class="section-header">
                <i class="fas fa-fire"></i> Top Selling Products
            </h2>
            <a href="/shop/products" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <?php if (!empty($topProducts)): ?>
            <div class="products-list">
                <?php foreach ($topProducts as $product): ?>
                    <div class="product-list-item">
                        <div class="product-list-image">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <div class="placeholder-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-list-info">
                            <h4><?= htmlspecialchars($product['name']) ?></h4>
                            <p class="product-category"><?= htmlspecialchars($product['category']) ?></p>
                        </div>
                        <div class="product-list-stats">
                            <div class="stat-item">
                                <span class="stat-label">Sales:</span>
                                <span class="stat-value"><?= number_format($product['sales']) ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Stock:</span>
                                <span class="stat-value <?= $product['stock'] < 10 ? 'text-warning' : '' ?>">
                                    <?= number_format($product['stock']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="product-list-price">
                            ₱<?= number_format($product['price'], 2) ?>
                        </div>
                        <div class="product-list-actions">
                            <a href="/shop/products/edit/<?= $product['id'] ?>" class="btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open empty-icon"></i>
                <h3>No Products Yet</h3>
                <p>Start adding products to see your top sellers here.</p>
                <a href="/shop/add-product" class="btn btn-primary">Add Your First Product</a>
            </div>
        <?php endif; ?>
    </div>
</div>