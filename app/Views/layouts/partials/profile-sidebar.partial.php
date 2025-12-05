<?php
// app/Views/layouts/partials/profile-sidebar.partial.php

// Get current path for active state
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$activeTab = $activeTab ?? '';

// Auto-detect active tab if not set
if (empty($activeTab)) {
    if (strpos($currentPath, '/profile/addresses') !== false) {
        $activeTab = 'addresses';
    } elseif (strpos($currentPath, '/profile/orders') !== false) {
        $activeTab = 'orders';
    } elseif (strpos($currentPath, '/profile/settings') !== false) {
        $activeTab = 'settings';
    } else {
        $activeTab = 'info';
    }
}
?>



<div class="sidebar-header">
    <div class="sidebar-avatar-container">
        <div class="sidebar-avatar">
            <?php if (!empty($profile['profile_pic'])): ?>
                <img src="/<?= htmlspecialchars($profile['profile_pic']) ?>" alt="Profile Picture">
            <?php else: ?>
                <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="sidebar-username"><?= htmlspecialchars($user['username'] ?? 'User') ?></div>
    <div class="sidebar-email"><?= htmlspecialchars($user['email'] ?? 'email@example.com') ?></div>
</div>

<nav class="sidebar-menu">
    <a href="/profile" class="menu-item <?= $activeTab === 'info' ? 'active' : '' ?>">
        <i class="fas fa-user"></i>
        <span>Personal Information</span>
    </a>
    <a href="/profile/addresses" class="menu-item <?= $activeTab === 'addresses' ? 'active' : '' ?>">
        <i class="fas fa-map-marker-alt"></i>
        <span>My Addresses</span>
    </a>
    <a href="/profile/orders" class="menu-item <?= $activeTab === 'orders' ? 'active' : '' ?>">
        <i class="fas fa-shopping-bag"></i>
        <span>My Orders</span>
    </a>
    <a href="/profile/settings" class="menu-item <?= $activeTab === 'settings' ? 'active' : '' ?>">
        <i class="fas fa-cog"></i>
        <span>Account Settings</span>
    </a>
    
    <div class="sidebar-footer">
        <form method="POST" action="/logout" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <button type="submit" class="menu-item logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</nav>