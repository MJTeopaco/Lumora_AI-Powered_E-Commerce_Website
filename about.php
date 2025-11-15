<?php
// Prevent undefined variable warnings (same pattern as homepage)
$isLoggedIn     = $isLoggedIn     ?? false;
$statusMessage  = $statusMessage  ?? null;
$statusType     = $statusType     ?? 'info';
$username       = $username       ?? '';
$categoryCounts = $categoryCounts ?? [];
$products       = $products       ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>About | Lumora. Handcrafted Treasures</title>

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
        <li><a href="about.php" class="active">About</a></li>
        <li><a href="collections.php">Collections</a></li>
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

  <!-- ABOUT HERO -->
  <section class="page-hero about-hero">
    <div class="container">
      <p class="hero-eyebrow">About Lumora</p>
      <h1>Handcrafted Treasures, Curated with Heart.</h1>
      <p class="page-hero-subtitle">
        Lumora is a marketplace where Filipino jewelers and artisans can share
        their craft with buyers across the country. Every piece is curated for
        its story, quality, and the hands that made it.
      </p>
    </div>
  </section>

  <!-- STORY + VALUES -->
  <section class="about-section">
    <div class="container about-layout">
      <div class="about-text">
        <h2>Our Story</h2>
        <p>
          Lumora started with a simple idea: to make it easier for people to
          discover meaningful, handcrafted jewelry from independent Filipino
          creators. Instead of mass-produced designs, we focus on pieces with
          character, intention, and care.
        </p>
        <p>
          We work closely with artisans and small businesses to help them reach
          a wider audience, provide fair opportunities, and build a community
          that celebrates craftsmanship.
        </p>
      </div>

      <aside class="about-aside">
        <h3>What We Value</h3>
        <ul class="about-list">
          <li><strong>Authenticity</strong> – Every piece has a maker and a story behind it.</li>
          <li><strong>Quality</strong> – We encourage high standards in materials and finishing.</li>
          <li><strong>Community</strong> – We want both sellers and buyers to feel at home in Lumora.</li>
          <li><strong>Transparency</strong> – Clear product details, fair pricing, and honest communication.</li>
        </ul>
      </aside>
    </div>
  </section>

  <!-- HIGHLIGHTS -->
  <section class="about-highlights">
    <div class="container about-highlight-grid">
      <div class="about-highlight-card">
        <h4>For Buyers</h4>
        <p>
          Discover unique jewelry that isn’t found in malls. Support local makers
          while finding pieces that match your style and story.
        </p>
      </div>
      <div class="about-highlight-card">
        <h4>For Sellers</h4>
        <p>
          Gain tools to showcase your brand, manage orders, and reach customers
          nationwide — all in one place.
        </p>
      </div>
      <div class="about-highlight-card">
        <h4>For the Community</h4>
        <p>
          We envision a sustainable ecosystem where creativity, culture, and business
          can grow together.
        </p>
      </div>
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
