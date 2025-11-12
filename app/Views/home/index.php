<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Lumora - Exquisite Accessories' ?></title>
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

        .user-info {
            color: #ffffff;
            font-size: 14px;
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
        }

        .btn-primary {
            background-color: #ffffff;
            color: #1e4d3d;
        }

        .btn-primary:hover {
            background-color: #f0f5f3;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: transparent;
            color: #ffffff;
            border: 2px solid #ffffff;
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
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

        /* Hero Section */
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
            color: #1e4d3d;
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
            background: linear-gradient(135deg, #f0f5f3 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #9ca3af;
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

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e4d3d;
            margin-bottom: 1rem;
        }

        .login-notice {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            margin-top: 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            color: #92400e;
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
                flex-direction: column;
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
            <div class="nav-actions">
                <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                    <span class="user-info">Welcome, <?= htmlspecialchars($username) ?>!</span>
                    <a href="/cart" class="btn btn-secondary">Cart</a>
                    <a href="/logout" class="btn btn-primary">Logout</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-primary">Login / Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Alert Messages -->
    <?php if ($statusMessage): ?>
        <div class="alert alert-<?= $statusType ?>">
            <strong><?= $statusType === 'success' ? '✓' : '✗' ?></strong>
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
            <div class="alert alert-error">
                <strong>Note:</strong> You can browse our products, but you must <a href="/login" style="color: #991b1b; text-decoration: underline; font-weight: 600;">login</a> to add items to your cart or place an order.
            </div>
        <?php endif; ?>

        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        🎁
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?= htmlspecialchars($product['category']) ?></div>
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                        
                        <?php if ($isLoggedIn): ?>
                            <form method="POST" action="/add-to-cart">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn btn-add-cart">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-add-cart btn-disabled" disabled title="Please login to add items to cart">
                                Login to Purchase
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p><strong>LUMORA</strong> - Exquisite Accessories for Every Occasion</p>
        <p>© 2025 Lumora. All rights reserved.</p>
    </footer>
</body>
</html>