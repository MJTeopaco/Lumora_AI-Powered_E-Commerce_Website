<?php
// Shop-specific header - Different design from main site
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
            
            <button class="shop-icon-btn" onclick="window.location.href='/messages'" title="Messages">
                <i class="fas fa-envelope"></i>
                <?php if (isset($_SESSION['message_count']) && $_SESSION['message_count'] > 0): ?>
                    <span class="shop-badge"><?= $_SESSION['message_count'] ?></span>
                <?php endif; ?>
            </button>
            
            <a href="/" class="shop-view-store-btn">
                <i class="fas fa-external-link-alt"></i>
                <span>View Store</span>
            </a>
            
            <div class="shop-profile-dropdown">
                <button class="shop-profile-trigger" onclick="toggleShopProfileMenu()">
                    <div class="shop-profile-avatar">
                        <?php if ($currentUser && !empty($currentUser['avatar'])): ?>
                            <img src="<?= htmlspecialchars($currentUser['avatar']) ?>" alt="Profile">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-chevron-down shop-dropdown-arrow"></i>
                </button>
                
                <div class="shop-profile-menu" id="shopProfileMenu">
                    <div class="shop-profile-menu-header">
                        <div class="shop-profile-menu-avatar">
                            <?php if ($currentUser && !empty($currentUser['avatar'])): ?>
                                <img src="<?= htmlspecialchars($currentUser['avatar']) ?>" alt="Profile">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="shop-profile-menu-info">
                            <strong><?= htmlspecialchars($currentUser['username'] ?? 'Seller') ?></strong>
                            <span><?= htmlspecialchars($currentUser['email'] ?? '') ?></span>
                        </div>
                    </div>
                    
                    <div class="shop-profile-menu-divider"></div>
                    
                    <a href="/shop/shop-profile" class="shop-profile-menu-item">
                        <i class="fas fa-user-circle"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="/shop/settings" class="shop-profile-menu-item">
                        <i class="fas fa-cog"></i>
                        <span>Shop Settings</span>
                    </a>
                    <a href="/shop/addresses" class="shop-profile-menu-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Addresses</span>
                    </a>
                    
                    <div class="shop-profile-menu-divider"></div>
                    
                    <a href="/help" class="shop-profile-menu-item">
                        <i class="fas fa-question-circle"></i>
                        <span>Help Center</span>
                    </a>
                    
                    <div class="shop-profile-menu-divider"></div>
                    
                    <form action="/logout" method="POST" style="margin: 0;">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" class="shop-profile-menu-item shop-logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function toggleShopProfileMenu() {
    const menu = document.getElementById('shopProfileMenu');
    menu.classList.toggle('show');
    
    // Close menu when clicking outside
    if (menu.classList.contains('show')) {
        setTimeout(() => {
            document.addEventListener('click', closeShopProfileMenu);
        }, 0);
    }
}

function closeShopProfileMenu(e) {
    const menu = document.getElementById('shopProfileMenu');
    const trigger = document.querySelector('.shop-profile-trigger');
    
    if (!menu.contains(e.target) && !trigger.contains(e.target)) {
        menu.classList.remove('show');
        document.removeEventListener('click', closeShopProfileMenu);
    }
}
</script>