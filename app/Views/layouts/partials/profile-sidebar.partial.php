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

<style>
    /* Force Profile Sidebar Header Color - Black & Gold Theme */
    .sidebar-header {
        background: #FFFFFF !important; /* White background for cleanliness */
        border-bottom: 1px solid #e5e5e5 !important;
    }

    .sidebar-username {
        color: #1A1A1A !important; /* Black text */
    }
    
    .sidebar-email {
        color: #666666 !important; /* Grey text */
    }

    /* Sidebar Avatar - Gold background */
    .sidebar-avatar {
        background: #D4AF37 !important; 
        border: 3px solid #f5f5f5 !important;
    }

    /* Force Main Navigation Header Color (if it appears on profile pages) */
    .header, .nav-top {
        background: #FFFFFF !important;
    }

    /* Active Menu Item Highlight */
    .sidebar-menu .menu-item.active {
        color: #D4AF37 !important; /* Gold Text */
        border-left: 4px solid #D4AF37 !important; /* Gold Border */
        background-color: #fffcf5 !important; /* Very light gold tint bg */
    }
    
    .sidebar-menu .menu-item:hover {
        background-color: #f9f9f9 !important;
        color: #1A1A1A !important;
    }
    
    .sidebar-menu .menu-item.active i {
        color: #D4AF37 !important;
    }

    /* Page Headings */
    .content-title {
        color: #1A1A1A !important;
    }

    /* Primary Buttons (Save, Add Address) - Gold */
    .btn-primary {
        background-color: #D4AF37 !important;
        border-color: #D4AF37 !important;
        color: #ffffff !important;
    }
    
    .btn-primary:hover {
        background-color: #B8942C !important; /* Darker gold on hover */
    }

    /* Edit Buttons (Small outline buttons) */
    .btn-edit, .icon-btn {
        color: #666 !important;
        border-color: #e0e0e0 !important;
        background: transparent !important;
    }
    
    .btn-edit:hover, .icon-btn:hover {
        border-color: #D4AF37 !important;
        color: #D4AF37 !important;
        background: #fffcf5 !important;
    }

    /* Profile Picture Placeholder */
    .profile-pic-placeholder {
        background-color: #D4AF37 !important;
        color: #ffffff !important;
    }

    /* Address Cards (Icons) */
    .address-type i {
        color: #D4AF37 !important;
    }
    
    /* Default Badge */
    .default-badge {
        background-color: #D4AF37 !important;
    }
    
    /* Logout Button Area - Fixed Footer Style */
    .sidebar-footer {
        padding-top: 0.5rem;
        margin-top: 0.5rem;
        border-top: 1px solid #e5e5e5;
    }

    .menu-item.logout {
        color: #DC3545 !important;
        width: 100%;
        text-align: left;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.875rem 1.5rem;
        border: none;
        background: transparent;
        font-family: inherit;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .menu-item.logout:hover {
        background-color: #fff5f5 !important;
        color: #c82333 !important;
    }

    .menu-item.logout i {
        width: 20px;
        font-size: 18px;
        text-align: center;
    }
</style>

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