<?php
// app/Views/layouts/profile/addresses.view.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Addresses - Lumora</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            color: #1f2937;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            text-decoration: none;
        }

        .back-link {
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 14px;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .back-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Alert Messages */
        .alert {
            max-width: 1400px;
            margin: 1.5rem auto;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background-color: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .alert-error {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        /* Main Container */
        .profile-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            align-items: start;
        }

        /* Sidebar */
        .profile-sidebar {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: sticky;
            top: 100px;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        .sidebar-avatar-container {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
        }

        .sidebar-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 600;
            color: #ffffff;
            border: 3px solid #e5e7eb;
            overflow: hidden;
        }

        .sidebar-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidebar-username {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .sidebar-email {
            font-size: 13px;
            color: #6b7280;
        }

        .sidebar-menu {
            padding: 0.5rem 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1.5rem;
            color: #4b5563;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            cursor: pointer;
        }

        .menu-item:hover {
            background-color: #f9fafb;
            color: #1e4d3d;
        }

        .menu-item.active {
            background-color: #f0f5f3;
            color: #1e4d3d;
            border-left-color: #2d5a4a;
            font-weight: 600;
        }

        .menu-item i {
            width: 20px;
            font-size: 18px;
        }

        .menu-item span {
            font-size: 14px;
        }

        .menu-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 0.5rem 0;
        }

        .menu-item.logout {
            color: #ef4444;
        }

        .menu-item.logout:hover {
            background-color: #fef2f2;
            color: #dc2626;
        }

        /* Main Content Area */
        .profile-content {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            min-height: 500px;
        }

        .content-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-title {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
        }

        .content-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #2d5a4a;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #1e4d3d;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #e5e7eb;
        }

        .btn-danger {
            background-color: #ef4444;
            color: #ffffff;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #9ca3af;
            margin-bottom: 1.5rem;
        }

        /* Address Cards */
        .addresses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .address-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            position: relative;
            transition: all 0.3s ease;
        }

        .address-card:hover {
            border-color: #2d5a4a;
            box-shadow: 0 4px 12px rgba(45, 90, 74, 0.1);
        }

        .address-card.default {
            border-color: #2d5a4a;
            background-color: #f0f5f3;
        }

        .address-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .address-type {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 14px;
            font-weight: 600;
            color: #2d5a4a;
        }

        .default-badge {
            background-color: #2d5a4a;
            color: #ffffff;
            font-size: 11px;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
        }

        .address-actions {
            display: flex;
            gap: 0.5rem;
        }

        .icon-btn {
            background: none;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 6px;
            transition: background-color 0.2s ease;
            color: #6b7280;
        }

        .icon-btn:hover {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        .icon-btn.delete:hover {
            background-color: #fef2f2;
            color: #ef4444;
        }

        .address-body {
            margin-bottom: 1rem;
        }

        .address-line {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 0.25rem;
        }

        .address-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .address-footer {
            display: flex;
            gap: 0.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 13px;
        }

        footer {
            background-color: #1f2937;
            color: #9ca3af;
            padding: 2rem;
            text-align: center;
            margin-top: 4rem;
        }

        @media (max-width: 1024px) {
            .profile-container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .profile-sidebar {
                position: relative;
                top: 0;
            }

            .addresses-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 0 1rem;
            }

            .sidebar-header {
                padding: 1.5rem 1rem;
            }

            .profile-content {
                padding: 1.5rem;
            }

            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav-container">
            <a href="/" class="logo">LUMORA</a>
            <a href="/" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </nav>
    </header>

    <!-- Alert Messages -->
    <?php if (isset($statusMessage) && $statusMessage): ?>
        <div class="alert alert-<?= htmlspecialchars($statusType) ?>">
            <strong><?= $statusType === 'success' ? '✓' : '✗' ?></strong>
            <?= htmlspecialchars($statusMessage) ?>
        </div>
    <?php endif; ?>

    <!-- Main Container -->
    <div class="profile-container">
        <!-- Sidebar -->
        <aside class="profile-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-avatar-container">
                    <div class="sidebar-avatar">
                        <?php if (!empty($profile['profile_pic'])): ?>
                            <img src="/<?= htmlspecialchars($profile['profile_pic']) ?>" alt="Profile Picture">
                        <?php else: ?>
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="sidebar-username"><?= htmlspecialchars($user['username']) ?></div>
                <div class="sidebar-email"><?= htmlspecialchars($user['email']) ?></div>
            </div>

            <nav class="sidebar-menu">
                <a href="/profile" class="menu-item">
                    <i class="fas fa-user"></i>
                    <span>Personal Information</span>
                </a>
                <a href="/profile/addresses" class="menu-item active">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>My Addresses</span>
                </a>
                <a href="/profile/orders" class="menu-item">
                    <i class="fas fa-shopping-bag"></i>
                    <span>My Orders</span>
                </a>
                <a href="/profile/settings" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Account Settings</span>
                </a>
                
                <div class="menu-divider"></div>
                
                <form method="POST" action="/logout" style="margin: 0;">
                    <button type="submit" class="menu-item logout" style="background: none; border: none; width: 100%; text-align: left;">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="profile-content">
            <div class="content-header">
                <div>
                    <h1 class="content-title">My Addresses</h1>
                    <p class="content-subtitle">Manage your shipping and billing addresses</p>
                </div>
                <a href="/profile/addresses/add" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add New Address
                </a>
            </div>

            <?php if (empty($addresses)): ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h3>No addresses added yet</h3>
                    <p>Add your first address to make checkout faster and easier</p>
                    <a href="/profile/addresses/add" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add New Address
                    </a>
                </div>
            <?php else: ?>
                <!-- Addresses Grid -->
                <div class="addresses-grid">
                    <?php foreach ($addresses as $address): ?>
                        <div class="address-card <?= $address['is_default'] ? 'default' : '' ?>">
                            <div class="address-header">
                                <div class="address-type">
                                    <i class="fas fa-<?= $address['address_type'] === 'shipping' ? 'truck' : 'file-invoice-dollar' ?>"></i>
                                    <?= ucfirst($address['address_type']) ?>
                                    <?php if ($address['is_default']): ?>
                                        <span class="default-badge">DEFAULT</span>
                                    <?php endif; ?>
                                </div>
                                <div class="address-actions">
                                    <button class="icon-btn" onclick="window.location.href='/profile/addresses/edit/<?= $address['address_id'] ?>'" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="/profile/addresses/delete/<?= $address['address_id'] ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this address?')">
                                        <button type="submit" class="icon-btn delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="address-body">
                                <div class="address-line"><?= htmlspecialchars($address['address_line_1']) ?></div>
                                <?php if (!empty($address['address_line_2'])): ?>
                                    <div class="address-line"><?= htmlspecialchars($address['address_line_2']) ?></div>
                                <?php endif; ?>
                                <div class="address-line">
                                    <?= htmlspecialchars($address['barangay']) ?>, 
                                    <?= htmlspecialchars($address['city']) ?>
                                </div>
                                <div class="address-line">
                                    <?= htmlspecialchars($address['province']) ?> 
                                    <?= htmlspecialchars($address['postal_code']) ?>
                                </div>
                                <div class="address-line"><?= htmlspecialchars($address['region']) ?></div>
                            </div>

                            <?php if (!$address['is_default']): ?>
                                <div class="address-footer">
                                    <form method="POST" action="/profile/addresses/set-default/<?= $address['address_id'] ?>" style="flex: 1;">
                                        <button type="submit" class="btn btn-secondary btn-small" style="width: 100%;">
                                            <i class="fas fa-check"></i>
                                            Set as Default
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Footer -->
    <footer>
        <p><strong>LUMORA</strong> - Exquisite Accessories for Every Occasion</p>
        <p>© 2025 Lumora. All rights reserved.</p>
    </footer>
</body>
</html>