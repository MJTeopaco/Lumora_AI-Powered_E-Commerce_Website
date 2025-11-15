<?php
// Safety defaults (same pattern as other views)
$isLoggedIn     = $isLoggedIn     ?? false;
$statusMessage  = $statusMessage  ?? null;
$statusType     = $statusType     ?? 'info';
$username       = $username       ?? '';
$products       = $products       ?? [];
$categoryCounts = $categoryCounts ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Sell on Lumora | Become a Creator</title>

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
      <p>Showcase your craft to buyers across the Philippines.</p>
      <a href="collections.php" class="top-link">Browse Marketplace</a>
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
        <li><a href="collections.php">Collections</a></li>
        <li><a href="sell.php" class="active">Sell on Lumora</a></li>
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

  <!-- SELLER HERO / PROMO -->
  <section class="sell-hero">
    <div class="container promo-seller">
      <p class="promo-eyebrow">Become a Lumora Creator</p>

      <h1>Showcase Your Craft. Grow Your Brand.</h1>

      <p class="promo-subtitle">
        Join Lumora’s marketplace of independent Filipino jewelers and artisans.
        We’ll later power this page with your real stats once seller & product data
        are connected to the database.
      </p>

      <div class="promo-actions">
        <!-- Placeholder links – can later point to real signup / dashboard routes -->
        <a href="login.php" class="btn promo-btn">Become a Seller</a>
        <a href="seller-guidelines.php" class="btn btn-outline-light">View Seller Guidelines</a>
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

  <!-- STEP-BY-STEP SECTION -->
  <section class="sell-steps">
    <div class="container">
      <h2 class="section-title">How Selling on Lumora Works</h2>

      <div class="sell-step-grid">
        <div class="sell-step">
          <span class="step-number">1</span>
          <h3>Create Your Seller Account</h3>
          <p>
            Sign up as a Lumora creator and complete your basic seller profile.
            Later, this will be linked to account verification and KYC checks.
          </p>
        </div>

        <div class="sell-step">
          <span class="step-number">2</span>
          <h3>Upload Your Products</h3>
          <p>
            Add product photos, prices, stock, and categories. Once connected
            to the database, your items will automatically appear in the
            marketplace and in relevant collections.
          </p>
        </div>

        <div class="sell-step">
          <span class="step-number">3</span>
          <h3>Start Reaching Buyers</h3>
          <p>
            Customers can browse, add to cart, and place orders.
            Order management, fulfillment status, and payouts
            will later be handled through your seller dashboard.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- SELLER BENEFITS -->
  <section class="sell-benefits">
    <div class="container">
      <h2 class="section-title">Why Sell with Lumora?</h2>

      <div class="sell-benefits-grid">
        <div class="sell-benefit-card">
          <h3>Designed for Artisans</h3>
          <p>
            Lumora focuses on handcrafted, meaningful pieces—
            giving your work a more curated space than generic marketplaces.
          </p>
        </div>

        <div class="sell-benefit-card">
          <h3>Built-In Promotions</h3>
          <p>
            Future promo tools will help you feature bestsellers, launch
            seasonal collections, and run limited-time discounts.
          </p>
        </div>

        <div class="sell-benefit-card">
          <h3>Secure Orders & Payouts</h3>
          <p>
            Orders and payment flows will be backed by PHP + MySQL logic,
            giving buyers and sellers a clear record of every transaction.
          </p>
        </div>

        <div class="sell-benefit-card">
          <h3>Analytics Coming Soon</h3>
          <p>
            As we integrate Python-based analytics, you’ll gain insights
            into buyer behavior, repeat customers, and top-performing pieces.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- SIMPLE FAQ (STATIC FOR NOW) -->
  <section class="sell-faq">
    <div class="container">
      <h2 class="section-title">Seller FAQ</h2>

      <div class="faq-grid">
        <div class="faq-item">
          <h3>Do I need to pay to join Lumora?</h3>
          <p>
            For now, seller onboarding terms are still being finalized.
            This section will later display your actual fee structure
            (commission, payout schedule, and so on) once the business
            rules are locked in.
          </p>
        </div>

        <div class="faq-item">
          <h3>How will I manage my products?</h3>
          <p>
            You’ll eventually have access to a seller dashboard where you can
            add, edit, or archive products. This page is a visual preview
            of where those features will live.
          </p>
        </div>

        <div class="faq-item">
          <h3>Can I sell custom or made-to-order pieces?</h3>
          <p>
            Yes. Once order workflows are in place, you’ll be able to mark
            products as made-to-order and specify lead times and customization
            options for your buyers.
          </p>
        </div>

        <div class="faq-item">
          <h3>Where do I go if I need help?</h3>
          <p>
            A dedicated seller support channel will be added later
            (email or ticketing system). For now, this is a placeholder
            area for contact details and escalation steps.
          </p>
        </div>
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
