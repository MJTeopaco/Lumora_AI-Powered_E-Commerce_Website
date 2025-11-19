<?php
// Views/layouts/partials/header.partial.php

// Note: We need access to $isLoggedIn, $notificationCount, $cartCount, $username, $userProfile, $isSeller, $statusMessage, $statusType
// In a proper MVC/OOP View System, these variables are typically passed into the View component or made available via $this->data.
// Assuming they are available in the scope where this partial is included/rendered.
$isLoggedIn = $isLoggedIn ?? false;
$username = $username ?? 'Guest';
$userProfile = $userProfile ?? [];
$notificationCount = $notificationCount ?? 0;
$cartCount = $cartCount ?? 0;
$isSeller = $isSeller ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumora - Exquisite Accessories</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /*
        * All CSS from the original index.view.php is included here. 
        * In a production environment, this would ideally be in a separate CSS file.
        */
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

        /* Header / Navigation */
        .header {
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
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

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Icon Buttons */
        .icon-btn {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
        }

        .icon-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .icon-btn img {
            width: 28px;
            height: 28px;
            filter: brightness(0) invert(1);
        }

        .icon-btn .badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background-color: #ef4444;
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        /* Profile Button */
        .profile-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #ffffff;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .profile-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .profile-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #1e4d3d;
            font-size: 14px;
            border: 2px solid #ffffff;
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name {
            font-size: 14px;
            font-weight: 500;
        }

        .btn {
            padding: 0.6rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .btn-primary {
            background-color: #ffffff;
            color: #1e4d3d;
        }

        .btn-primary:hover {
            background-color: #f0f5f3;
            transform: translateY(-2px);
        }

        .btn-add-cart {
            background-color: #2d5a4a;
            color: #ffffff;
            width: 100%;
            padding: 0.8rem;
            margin-top: 0.5rem;
        }

        .btn-add-cart:hover {
            background-color: #1e4d3d;
        }

        .btn-disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .btn-disabled:hover {
            background-color: #9ca3af;
            transform: none;
        }

        /* Alert Messages */
        .alert {
            max-width: 1200px;
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

        .alert-info {
            background-color: #dbeafe;
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }

        .alert a {
            color: inherit;
            text-decoration: underline;
            font-weight: 600;
        }

        /* Hero Section (Keep only styles, content is in index.view.php) */
        .hero {
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            color: #ffffff;
            padding: 4rem 2rem;
            text-align: center;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Products Section (Keep only styles, content is in index.view.php) */
        .container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: #1e4d3d;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }
        
        /* ... (Keep remaining product-related CSS styles here) */
        
        .product-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: linear-gradient(135deg, #f0f5f3 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-category {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .product-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .product-description {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e4d3d;
            margin-bottom: 1rem;
        }

        .product-stock {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .stock-available {
            color: #059669;
        }

        .stock-low {
            color: #f59e0b;
        }

        .stock-out {
            color: #ef4444;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Footer */
        .footer {
            background-color: #1f2937;
            color: #9ca3af;
            padding: 2rem;
            text-align: center;
            margin-top: 4rem;
        }

        .footer p {
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }

            .nav-container {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .profile-name {
                display: none;
            }

            .nav-actions {
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="/" class="logo">LUMORA</a>
            <div class="nav-actions">
                <?php if ($isLoggedIn): ?>
                    <button class="icon-btn" onclick="window.location.href='/notifications'" title="Notifications">
                        <img src="/img/notif-icon.png" alt="Notifications">
                        <?php if (isset($notificationCount) && $notificationCount > 0): ?>
                            <span class="badge"><?= $notificationCount > 99 ? '99+' : $notificationCount ?></span>
                        <?php endif; ?>
                    </button>

                    <button class="icon-btn" onclick="window.location.href='/cart'" title="Shopping Cart">
                        <img src="/img/cart-icon.png" alt="Cart">
                        <?php if (isset($cartCount) && $cartCount > 0): ?>
                            <span class="badge"><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
                        <?php endif; ?>
                    </button>
            
                    
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
                    <a href="/login" class="btn btn-primary">Login / Sign Up</a>
                <?php endif; ?>

                
                <?php if (empty($isSeller) || $isSeller === false): ?>
                    <a href="/seller/register" class="btn btn-primary" style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-user-plus"></i>
                        <span>Become a Seller</span>
                    </a>
                <?php endif; ?>

                <?php if (!empty($isSeller) && $isSeller === true): ?>
                    <a href="/seller/dashboard" class="btn btn-success" style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-store"></i>
                        <span>Seller Dashboard</span>
                    </a>
                <?php endif; ?>


            </div>
        </nav>
    </header>

    <?php if ($statusMessage): ?>
        <div class="alert alert-<?= htmlspecialchars($statusType) ?>">
            <strong><?= $statusType === 'success' ? '✓' : ($statusType === 'error' ? '✗' : 'ℹ') ?></strong>
            <?= htmlspecialchars($statusMessage) ?>
        </div>
    <?php endif; ?>