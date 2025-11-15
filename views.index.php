<?php
// Prevent undefined variable warnings
$isLoggedIn = $isLoggedIn ?? false;
$statusMessage = $statusMessage ?? null;
$statusType = $statusType ?? 'info';
$categoryCounts = $categoryCounts ?? [];
$products = $products ?? [];
$username = $username ?? '';
$isSeller = $isSeller ?? false; // future use: mark if the user is already a seller
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Lumora. | Handcrafted Treasures</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Correct asset paths -->
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
      <a href="#" class="top-link">Shop Deals</a>
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
        <li><a href="views.index.php" class="active">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="collections.php">Collections</a></li>
        <li><a href="sell.php">Sell on Lumora</a></li>
      </ul>

      <!-- Icons (PHP controlled) -->
      <div class="nav-icons">

        <!-- Search -->
        <div class="nav-search">
          <input type="text" placeholder="Search for a product..." />
        </div>

        <!-- Dynamic Icons -->
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

<!-- ===================== HERO CAROUSEL ===================== -->
<section class="hero-carousel">
  <div class="carousel-container">
    <div class="carousel-track">

      <!-- Slide 1 -->
      <div class="carousel-slide is-active">
        <img src="banner-gold-earrings.jpg" alt="Gold Earrings Slide 1" />
        <div class="hero-overlay"></div>
        <div class="carousel-text align-right">
          <p class="hero-eyebrow">SIGNATURE GOLD PIECES</p>
          <h1>Gold Earrings For Every Story</h1>
          <p class="hero-subtitle">
            Delicately handcrafted pieces to elevate your everyday glow.
          </p>
          <a href="collections.php" class="hero-btn">Shop Now</a>
        </div>
      </div>

      <!-- Slide 2 -->
      <div class="carousel-slide">
        <img src="banner-earrings.jpg" alt="Elegant Earring Banner" />
        <div class="hero-overlay"></div>
        <div class="carousel-text align-right">
          <p class="hero-eyebrow">EVERYDAY LUXURY</p>
          <h1>Modern Gold Elegance</h1>
          <p class="hero-subtitle">
            Clean silhouettes that pair with denim days and evening plans.
          </p>
          <a href="collections.php" class="hero-btn">Shop Now</a>
        </div>
      </div>

      <!-- Slide 3 -->
      <div class="carousel-slide">
        <img src="banner-hero.jpg" alt="Jewelry Collection Banner" />
        <div class="hero-overlay"></div>
        <div class="carousel-text align-right">
          <p class="hero-eyebrow">CRAFTED BY ARTISANS</p>
          <h1>Timeless Filipino Craftsmanship</h1>
          <p class="hero-subtitle">
            Thoughtfully made by local jewelers, one detail at a time.
          </p>
          <a href="collections.php" class="hero-btn">Shop Now</a>
        </div>
      </div>

      <!-- Slide 4 -->
      <div class="carousel-slide">
        <img src="banner-necklace-2.jpg" alt="Necklace Banner" />
        <div class="hero-overlay"></div>
        <div class="carousel-text align-right">
          <p class="hero-eyebrow">SHINE IN YOUR OWN LIGHT</p>
          <h1>Jewelry That Moves With You</h1>
          <p class="hero-subtitle">
            Fine details, a weightless feel, and a glow made to be lived in.
          </p>
          <a href="collections.php" class="hero-btn">Shop Now</a>
        </div>
      </div>

    </div>

    <!-- Arrows -->
    <button class="carousel-arrow left" aria-label="Previous slide">&#10094;</button>
    <button class="carousel-arrow right" aria-label="Next slide">&#10095;</button>

    <!-- Dots -->
    <div class="carousel-dots">
      <span class="dot active"></span>
      <span class="dot"></span>
      <span class="dot"></span>
      <span class="dot"></span>
    </div>

  </div>
</section>


<!-- ===================== POPULAR CATEGORIES ===================== -->
<section class="popular-categories">
  <div class="container">
    <h2 class="section-title">Popular Categories</h2>

    <div class="category-grid">

      <div class="category-item">
        <div class="category-thumb-wrapper">
          <img src="placeholder-bracelets.jpg" alt="Bracelets" class="category-thumb">
        </div>
        <div class="category-text">
          <span class="category-name">Bracelets</span>
          <span class="category-count">
            <?= $categoryCounts['Bracelets'] ?? 0 ?> items
          </span>
        </div>
      </div>

      <div class="category-item">
        <div class="category-thumb-wrapper">
          <img src="placeholder-urban.jpg" alt="Urban &amp; Staples" class="category-thumb">
        </div>
        <div class="category-text">
          <span class="category-name">Urban &amp; Staples</span>
          <span class="category-count">
            <?= $categoryCounts['Urban & Staples'] ?? 0 ?> items
          </span>
        </div>
      </div>

      <div class="category-item">
        <div class="category-thumb-wrapper">
          <img src="placeholder-charms.jpg" alt="Charms" class="category-thumb">
        </div>
        <div class="category-text">
          <span class="category-name">Charms</span>
          <span class="category-count">
            <?= $categoryCounts['Charms'] ?? 0 ?> items
          </span>
        </div>
      </div>

      <div class="category-item">
        <div class="category-thumb-wrapper">
          <img src="placeholder-rings.jpg" alt="Engagement Rings" class="category-thumb">
        </div>
        <div class="category-text">
          <span class="category-name">Engagement Rings</span>
          <span class="category-count">
            <?= $categoryCounts['Engagement Rings'] ?? 0 ?> items
          </span>
        </div>
      </div>

      <div class="category-item">
        <div class="category-thumb-wrapper">
          <img src="placeholder-necklaces.jpg" alt="Necklaces" class="category-thumb">
        </div>
        <div class="category-text">
          <span class="category-name">Necklaces</span>
          <span class="category-count">
            <?= $categoryCounts['Necklaces'] ?? 0 ?> items
          </span>
        </div>
      </div>

      <div class="category-item">
        <div class="category-thumb-wrapper">
          <img src="placeholder-pendants.jpg" alt="Pendants" class="category-thumb">
        </div>
        <div class="category-text">
          <span class="category-name">Pendants</span>
          <span class="category-count">
            <?= $categoryCounts['Pendants'] ?? 0 ?> items
          </span>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ===================== FEATURED PRODUCTS ===================== -->
<section class="featured-products">
  <div class="container featured-grid">

    <div class="feature-card">
      <div class="feature-image">
        <img src="flutter-butterfly.jpg" alt="Flutter & Butterfly Pendant">
      </div>
      <div class="feature-content">
        <h3>Flutter &amp; Butterfly Pendant</h3>
        <a href="#" class="feature-btn">Shop Now</a>
      </div>
    </div>

    <div class="feature-card">
      <div class="feature-image">
        <img src="gold-filled-bracelet.jpg" alt="Gold Filled Bracelet">
      </div>
      <div class="feature-content">
        <h3>Best Gold Filled Bracelet</h3>
        <a href="#" class="feature-btn">Shop Now</a>
      </div>
    </div>

  </div>
</section>

<!-- ===================== TRENDING PRODUCTS ===================== -->
<section class="trending-products">
  <div class="container">
    <h2 class="section-title">Trending Products</h2>

    <?php if (empty($products)): ?>

      <div class="empty-state trending-empty">
        <h3>No trending products yet</h3>
        <p>New handcrafted pieces will appear here as soon as sellers start adding their creations. Check back soon!</p>
      </div>

    <?php else: ?>

      <div class="product-grid">
        <?php foreach ($products as $product): ?>
          <div class="product-card" data-tag="<?= htmlspecialchars($product['tag'] ?? '') ?>">
            <div class="product-image">
              <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <h4 class="product-name"><?= htmlspecialchars($product['name']) ?></h4>
            <p class="product-price">
              <?php if (!empty($product['old_price'])): ?>
                <span class="current">₱<?= number_format($product['price'], 2) ?></span>
                <del class="old">₱<?= number_format($product['old_price'], 2) ?></del>
              <?php else: ?>
                ₱<?= number_format($product['price'], 2) ?>
              <?php endif; ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>

  </div>
</section>


<!-- ===================== SELLER PROMO BANNER ===================== -->
<section class="promo-banner promo-seller">
  <img src="banner-silver-earrings.jpg" alt="Sell on Lumora Banner" />

  <div class="promo-text container">
    <p class="promo-eyebrow">BECOME A LUMORA CREATOR</p>

    <h1>Showcase Your Craft. Grow Your Brand.</h1>

    <p class="promo-subtitle">
      Join Lumora’s marketplace of independent Filipino jewelers and artisans.
      Reach nationwide customers looking for handcrafted, meaningful pieces.
    </p>

    <div class="promo-actions">
      <?php if (!$isLoggedIn): ?>
        <a href="login.php" class="btn promo-btn">
          Become a Seller
        </a>
      <?php else: ?>
        <a href="sell.php" class="btn promo-btn">
          Start Selling
        </a>
      <?php endif; ?>

      <a href="seller-guidelines.php" class="btn btn-outline-light">
        View Seller Guidelines
      </a>

      <?php if ($isSeller): ?>
        <a href="seller-dashboard.php" class="promo-dashboard-link">
          Go to Seller Dashboard →
        </a>
      <?php endif; ?>
    </div>

    <div class="promo-meta">
      <div class="promo-stat">
        <span class="stat-number">120+</span>
        <span class="stat-label">Active Sellers</span>
      </div>
      <div class="promo-stat">
        <span class="stat-number">850+</span>
        <span class="stat-label">Handcrafted Pieces</span>
      </div>
      <div class="promo-stat">
        <span class="stat-number">3,200+</span>
        <span class="stat-label">Monthly Buyers</span>
      </div>
    </div>
  </div>
</section>

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

  <!-- Single, centered copyright line -->
  <div class="footer-bottom">
    <p>© 2025 Lumora. All rights reserved.</p>
  </div>
</footer>



</body>
</html>
