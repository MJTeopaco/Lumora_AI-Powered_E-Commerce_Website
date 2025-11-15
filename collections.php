<?php
// Same safety net as other views
$isLoggedIn     = $isLoggedIn     ?? false;
$statusMessage  = $statusMessage  ?? null;
$statusType     = $statusType     ?? 'info';
$username       = $username       ?? '';
$categoryCounts = $categoryCounts ?? [];
$products       = $products       ?? [];

$totalProducts = is_array($products) ? count($products) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Collections | Lumora. Handcrafted Treasures</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Styles & JS -->
  <link rel="stylesheet" href="styles.css" />
  <script src="views.js" defer></script>
</head>

<body>

<!-- ===================== ALERT MESSAGES ===================== -->
<?php if ($statusMessage): ?>
  <div class="alert alert-<?= htmlspecialchars($statusType) ?>">
    <strong><?= $statusType === 'success' ? '✓' : ($statusType === 'error' ? '✗' : 'ℹ') ?></strong>
    <?= htmlspecialchars($statusMessage) ?>
  </div>
<?php endif; ?>

<!-- ===================== HEADER ===================== -->
<header class="site-header">

  <!-- Top Bar -->
  <div class="top-bar">
    <div class="container top-bar-inner">
      <p>Free shipping on your first order!</p>
      <a href="collections.php" class="top-link">Shop Deals</a>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="navbar">
    <div class="container navbar-inner">

      <!-- Logo -->
      <a href="views.index.php" class="logo">Lumora.</a>

      <!-- Mobile Toggle -->
      <button class="nav-toggle" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>

      <!-- Nav Links -->
      <ul class="nav-links">
        <li><a href="views.index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="collections.php" class="active">Collections</a></li>
        <li><a href="sell.php">Sell on Lumora</a></li>
      </ul>

      <!-- Icons (PHP controlled) -->
      <div class="nav-icons">
        <div class="nav-search">
          <input type="text" placeholder="Search for a product..." />
        </div>

        <?php if ($isLoggedIn): ?>
          <span class="user-info">Welcome, <?= htmlspecialchars($username) ?>!</span>
          <a href="cart.php" class="icon-btn" aria-label="Cart">🛒</a>
          <a href="account.php" class="icon-btn" aria-label="Account">👤</a>
        <?php else: ?>
          <a href="login.php" class="icon-btn" aria-label="Sign In">🔑</a>
        <?php endif; ?>
      </div>

    </div>
  </nav>
</header>

<!-- ===================== MAIN CONTENT ===================== -->
<main>

  <!-- COLLECTIONS HERO -->
  <section class="page-hero collections-hero">
    <div class="container">
      <p class="hero-eyebrow">Our Collections</p>
      <h1>Discover Handcrafted Pieces for Every Story.</h1>
      <p class="page-hero-subtitle">
        Browse Lumora’s curated selection of rings, necklaces, bracelets and more.
        These listings will later be powered by real products from sellers in the marketplace.
      </p>
    </div>
  </section>

  <!-- FILTER BAR (placeholder for future dynamic filters) -->
  <section class="collections-filters">
    <div class="container collections-filters-inner">
      <div class="filter-chips">
        <button class="filter-chip is-active">All</button>
        <button class="filter-chip">Rings</button>
        <button class="filter-chip">Necklaces</button>
        <button class="filter-chip">Bracelets</button>
        <button class="filter-chip">Earrings</button>
        <button class="filter-chip">Pendants</button>
      </div>

      <div class="collections-meta">
        <span><?= $totalProducts ?> item<?= $totalProducts === 1 ? '' : 's' ?> listed</span>
        <select class="collections-sort">
          <option value="featured">Sort by: Featured</option>
          <option value="newest">Newest First</option>
          <option value="price_low_high">Price: Low to High</option>
          <option value="price_high_low">Price: High to Low</option>
        </select>
      </div>
    </div>
  </section>

  <!-- COLLECTIONS GRID -->
  <section class="collections-section">
    <div class="container">

      <?php if (empty($products)): ?>

        <div class="empty-state">
          <h3>No products available yet</h3>
          <p>
            Once sellers start adding their creations, you’ll see the full collection here —
            rings, bracelets, earrings and more.
          </p>
        </div>

      <?php else: ?>

        <div class="product-grid product-grid-collections">
          <?php foreach ($products as $product): ?>
            <?php
              $name      = htmlspecialchars($product['name']      ?? 'Untitled Piece');
              $image     = htmlspecialchars($product['image']     ?? 'placeholder-product.jpg');
              $category  = htmlspecialchars($product['category']  ?? 'Uncategorized');
              $price     = $product['price']      ?? 0;
              $oldPrice  = $product['old_price']  ?? null;
              $tag       = htmlspecialchars($product['tag'] ?? '');
            ?>
            <div class="product-card" <?= $tag ? 'data-tag="'. $tag .'"' : '' ?>>
              <div class="product-image">
                <img src="<?= $image ?>" alt="<?= $name ?>">
              </div>

              <div class="product-category"><?= $category ?></div>
              <h4 class="product-name"><?= $name ?></h4>

              <p class="product-price">
                <?php if ($oldPrice): ?>
                  <span class="current">₱<?= number_format($price, 2) ?></span>
                  <del class="old">₱<?= number_format($oldPrice, 2) ?></del>
                <?php else: ?>
                  ₱<?= number_format($price, 2) ?>
                <?php endif; ?>
              </p>

              <!-- Simple “View Details” placeholder, cart logic will come later -->
              <button class="btn-ghost view-product-btn" type="button">
                View Details
              </button>
            </div>
          <?php endforeach; ?>
        </div>

      <?php endif; ?>

    </div>
  </section>

</main>

<!-- ===================== FOOTER ===================== -->
<div class="section-fade"></div>
<div class="footer-divider"></div>

<footer class="site-footer reveal">
  <div class="container footer-content">

    <!-- LEFT COLUMN — Footer Navigation -->
    <div class="footer-nav">
      <h4>Explore</h4>
      <ul>
        <li><a href="about.php">About</a></li>
        <li><a href="collections.php">Collections</a></li>
        <li><a href="sell.php">Sell on Lumora</a></li>
      </ul>
    </div>

    <!-- RIGHT COLUMN — Benefit Items -->
    <div class="footer-benefits">
      <div class="benefit-item">
        <img src="icon-shipping.png" alt="Nationwide Shipping">
        <h4>Nationwide Shipping</h4>
        <p>Free nationwide deliveries on all orders<br>of ₱5,000 and above</p>
      </div>

      <div class="benefit-item">
        <img src="icon-returns.png" alt="Easy Returns">
        <h4>Easy 30 Day Returns</h4>
        <p>Items can be returned or exchanged<br>within 30 days</p>
      </div>

      <div class="benefit-item">
        <img src="icon-moneyback.png" alt="Money Back Guarantee">
        <h4>Money Back Guarantee</h4>
        <p>100% guaranteed refund for damaged<br>or misdescribed items</p>
      </div>
    </div>

  </div>

  <div class="footer-bottom">
    <p>© 2025 Lumora. All rights reserved.</p>
  </div>
</footer>

</body>
</html>
