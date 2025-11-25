<?php
// Views/layouts/partials/header.partial.php
$isLoggedIn = $isLoggedIn ?? false;
$username = $username ?? 'Guest';
$userProfile = $userProfile ?? [];
$notificationCount = $notificationCount ?? 0;
$cartCount = $cartCount ?? 0;
$isSeller = $isSeller ?? false;
?>
<nav class="nav-container">
    <!-- Top Navigation Row -->
    <div class="nav-top">
        <a href="/" class="logo">LUMORA</a>
        
        <!-- Search Bar -->
        <div class="search-container">
            <form action="/search" method="GET">
                <input type="text" name="q" class="search-bar" placeholder="Search for accessories...">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <!-- Right Actions -->
        <div class="nav-actions">
            <?php if ($isLoggedIn): ?>
                <!-- Notifications -->
                <button class="icon-btn" onclick="window.location.href='/notifications'" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <?php if (isset($notificationCount) && $notificationCount > 0): ?>
                        <span class="badge"><?= $notificationCount > 99 ? '99+' : $notificationCount ?></span>
                    <?php endif; ?>
                </button>

                <!-- Shopping Cart -->
                <button class="icon-btn" onclick="window.location.href='/cart'" title="Shopping Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isset($cartCount) && $cartCount > 0): ?>
                        <span class="badge"><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
                    <?php endif; ?>
                </button>
        
                <!-- Profile -->
                <a href="/profile" class="profile-btn">
                    <div class="profile-avatar">
                        <?php if (!empty($userProfile['profile_pic'])): ?>
                            <img src="/<?= htmlspecialchars($userProfile['profile_pic']) ?>" alt="Profile">
                        <?php else: ?>
                            <?= strtoupper(substr($username, 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <span class="profile-name"><?= htmlspecialchars($username) ?></span>
                </a>
            <?php else: ?>
                <a href="/login" class="btn btn-primary">Sign in</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bottom Navigation Row -->
    <div class="nav-bottom">
        <div class="nav-links">
            <a href="/" class="nav-link">Home</a>
            <a href="/collections/index" class="nav-link">Collections</a>
            <a href="/stores" class="nav-link">Stores</a>
            
            <?php if (!empty($isSeller) && $isSeller === true): ?>
                <a href="/shop/dashboard" class="nav-link">
                    <i class="fas fa-store"></i> Shop Dashboard
                </a>
            <?php else: ?>
                <!-- dating /seller/register -->
                <a href="/main/seller-guidelines" class="nav-link">Sell on Lumora</a>
            <?php endif; ?>
        </div>
    </div>
</nav>