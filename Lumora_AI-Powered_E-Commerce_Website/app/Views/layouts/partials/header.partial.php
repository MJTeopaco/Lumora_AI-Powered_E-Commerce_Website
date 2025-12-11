<?php
// Views/layouts/partials/header.partial.php
$isLoggedIn = $isLoggedIn ?? false;
$username = $username ?? 'Guest';
$userProfile = $userProfile ?? [];
$notificationCount = $notificationCount ?? 0;
$cartCount = $cartCount ?? 0;
$isSeller = $isSeller ?? false;

// Get cart count from database if logged in and not already set
if ($isLoggedIn && $cartCount === 0) {
    try {
        $cartModel = new \App\Models\Cart();
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $cartCount = $cartModel->getCartCount($userId);
        }
    } catch (Exception $e) {
        error_log("Failed to get cart count: " . $e->getMessage());
    }
}

// Get notification count from database if logged in and not already set
if ($isLoggedIn && $notificationCount === 0) {
    try {
        $notifModel = new \App\Models\Notification();
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $notificationCount = $notifModel->getUnreadCount($userId);
        }
    } catch (Exception $e) {
        error_log("Failed to get notification count: " . $e->getMessage());
    }
}
?>
<script>
    const BASE_URL = "<?= rtrim(base_url(), '/') ?>";
</script>

<nav class="nav-container">
    <div class="nav-top">
        <a href="<?= base_url('/') ?>" class="logo">LUMORA</a>
        
        <div class="search-container">
            <form action="<?= base_url('/collections/smart-search') ?>" method="POST">
                <input type="text" name="q" class="search-bar" placeholder="Search for accessories...">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="nav-actions">
            <?php if ($isLoggedIn): ?>
                <button class="icon-btn" onclick="window.location.href='<?= base_url('/notifications') ?>'" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <?php if (isset($notificationCount) && $notificationCount > 0): ?>
                        <span class="badge"><?= $notificationCount > 99 ? '99+' : $notificationCount ?></span>
                    <?php endif; ?>
                </button>

                <button class="icon-btn" onclick="window.location.href='<?= base_url('/cart') ?>'" title="Shopping Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="badge"><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
                    <?php endif; ?>
                </button>
        
                <a href="<?= base_url('/profile') ?>" class="profile-btn">
                    <div class="profile-avatar">
                        <?php if (!empty($userProfile['profile_pic'])): ?>
                            <img src="<?= base_url($userProfile['profile_pic']) ?>" alt="Profile">
                        <?php else: ?>
                            <?= strtoupper(substr($username, 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <span class="profile-name"><?= htmlspecialchars($username) ?></span>
                </a>
            <?php else: ?>
                <a href="<?= base_url('/login') ?>" class="btn btn-primary">Sign in</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="nav-bottom">
        <div class="nav-links">
            <a href="<?= base_url('/') ?>" class="nav-link">Home</a>
            <a href="<?= base_url('/collections/index') ?>" class="nav-link">Collections</a>
            <a href="<?= base_url('/stores') ?>" class="nav-link">Stores</a>
            
            <?php if (!empty($isSeller) && $isSeller === true): ?>
                <a href="<?= base_url('/shop/dashboard') ?>" class="nav-link">
                    <i class="fas fa-store"></i> Shop Dashboard
                </a>
            <?php else: ?>
                <a href="<?= base_url('/main/seller-guidelines') ?>" class="nav-link">Sell on Lumora</a>
            <?php endif; ?>
        </div>
    </div>
</nav>