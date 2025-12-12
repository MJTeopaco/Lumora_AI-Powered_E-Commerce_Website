<?php
$currentUser = $_SESSION['user'] ?? null;
$shopData = $shop ?? null;
?>
<header class="shop-header">
    <div class="shop-header-container">
        <div class="shop-header-left">
            <a href="/" class="shop-logo-link">
                <i class="fas fa-arrow-left"></i>
                <span class="shop-logo-text">LUMORA</span>
            </a>
            
            <?php if ($shopData): ?>
                <div class="shop-info-badge">
                    <i class="fas fa-store"></i>
                    <span class="shop-name-badge"><?= htmlspecialchars($shopData['shop_name']) ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <nav class="shop-header-nav">
            <a href="/shop/dashboard" class="shop-nav-item <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="/shop/products" class="shop-nav-item <?= ($currentPage ?? '') === 'products' ? 'active' : '' ?>">
                <i class="fas fa-box-open"></i>
                <span>Products</span>
            </a>
            <a href="/shop/orders" class="shop-nav-item <?= ($currentPage ?? '') === 'orders' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Orders</span>
            </a>
        </nav>
        
        <div class="shop-header-right">
            <button class="shop-icon-btn" onclick="window.location.href='/notifications'" title="Notifications">
                <i class="fas fa-bell"></i>
                <?php if (isset($_SESSION['notification_count']) && $_SESSION['notification_count'] > 0): ?>
                    <span class="shop-badge"><?= $_SESSION['notification_count'] ?></span>
                <?php endif; ?>
            </button>
            
            <a href="/shop/shop-profile" class="shop-view-store-btn">
                <i class="fas fa-user-circle"></i>
                <span>Shop Profile</span>
            </a>
            
            </form>
        </div>
    </div>
</header>