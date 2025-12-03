<?php
// app/Views/layouts/main_page/index.view.php
include __DIR__ . '/includes/success.popup.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumora - Exquisite Accessories</title>
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

        /* Header / Navigation */
        .header {
            /* REPLACED GREEN GRADIENT */
            background: linear-gradient(135deg, #YOUR_PRIMARY_COLOR 0%, #YOUR_SECONDARY_COLOR 100%);
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
            color: #YOUR_PRIMARY_COLOR; /* REPLACED TEXT COLOR */
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
            color: #YOUR_PRIMARY_COLOR; /* REPLACED TEXT COLOR */
        }

        .btn-primary:hover {
            background-color: #f0f5f3;
            transform: translateY(-2px);
        }

        .btn-add-cart {
            background-color: #YOUR_SECONDARY_COLOR; /* REPLACED BUTTON BG */
            color: #ffffff;
            width: 100%;
            padding: 0.8rem;
            margin-top: 0.5rem;
        }

        .btn-add-cart:hover {
            background-color: #YOUR_PRIMARY_COLOR; /* REPLACED HOVER BG */
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

        /* Hero Section */
        .hero {
            /* REPLACED GREEN GRADIENT */
            background: linear-gradient(135deg, #YOUR_PRIMARY_COLOR 0%, #YOUR_SECONDARY_COLOR 100%);
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

        /* Products Section */
        .container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: #YOUR_PRIMARY_COLOR; /* REPLACED TITLE COLOR */
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }

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
            color: #YOUR_PRIMARY_COLOR; /* REPLACED PRICE COLOR */
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
    <!-- Header -->
    <header class="header">
        <nav class="nav-container">
            <a href="/" class="logo">LUMORA</a>
            <div class="nav-actions">
                <?php if ($isLoggedIn): ?>
                    <!-- Notification Icon -->
                    <button class="icon-btn" onclick="window.location.href='/notifications'" title="Notifications">
<img src="<?= base_url('/img/notif-icon.png') ?>" alt="Notifications">                        <?php if (isset($notificationCount) && $notificationCount > 0): ?>
                            <span class="badge"><?= $notificationCount > 99 ? '99+' : $notificationCount ?></span>
                        <?php endif; ?>
                    </button>

                    <!-- Cart Icon -->
                    <button class="icon-btn" onclick="window.location.href='/cart'" title="Shopping Cart">
                        <img src="/img/cart-icon.png" alt="Cart">
                        <?php if (isset($cartCount) && $cartCount > 0): ?>
                            <span class="badge"><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
                        <?php endif; ?>
                    </button>
            
                    
                    <!-- Profile Link -->
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

                
                <!-- Become a Seller (if not a seller) -->
                <?php if (empty($isSeller) || $isSeller === false): ?>
                    <a href="/seller/register" class="btn btn-primary" style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-user-plus"></i>
                        <span>Become a Seller</span>
                    </a>
                <?php endif; ?>

                <!-- Seller Dashboard (if seller) -->
                <?php if (!empty($isSeller) && $isSeller === true): ?>
                    <a href="/seller/dashboard" class="btn btn-success" style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-store"></i>
                        <span>Seller Dashboard</span>
                    </a>
                <?php endif; ?>


            </div>
        </nav>
    </header>

    <!-- Alert Messages -->
    <?php if ($statusMessage): ?>
        <div class="alert alert-<?= htmlspecialchars($statusType) ?>">
            <strong><?= $statusType === 'success' ? '✓' : ($statusType === 'error' ? '✗' : 'ℹ') ?></strong>
            <?= htmlspecialchars($statusMessage) ?>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Exquisite Accessories for Every Occasion</h1>
        <p>Discover timeless elegance with our curated collection</p>
    </section>

    <!-- Products Section -->
    <main class="container">
        <h2 class="section-title">Featured Products</h2>
        
        <?php if (!$isLoggedIn): ?>
            <div class="alert alert-info">
                <strong>👋 Welcome!</strong> You can browse our products, but you must <a href="/login">login</a> to add items to your cart or place an order.
            </div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <div class="empty-state">
                <h3>No Products Available</h3>
                <p>Check back soon for our amazing collection!</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                🎁
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?= htmlspecialchars($product['category']) ?></div>
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            
                            <?php if (!empty($product['description'])): ?>
                                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                            <?php endif; ?>
                            
                            <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                            
                            <div class="product-stock <?= $product['stock'] > 10 ? 'stock-available' : ($product['stock'] > 0 ? 'stock-low' : 'stock-out') ?>">
                                <?php if ($product['stock'] > 10): ?>
                                    ✓ In Stock
                                <?php elseif ($product['stock'] > 0): ?>
                                    ⚠ Only <?= $product['stock'] ?> left!
                                <?php else: ?>
                                    ✗ Out of Stock
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($isLoggedIn): ?>
                                <?php if ($product['stock'] > 0): ?>
                                    <form method="POST" action="/cart/add">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-add-cart">Add to Cart</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-add-cart btn-disabled" disabled>Out of Stock</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-add-cart btn-disabled" disabled title="Please login to add items to cart">
                                    Login to Purchase
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p><strong>LUMORA</strong> - Exquisite Accessories for Every Occasion</p>
        <p>© 2025 Lumora. All rights reserved.</p>
    </footer>
</body>
</html>