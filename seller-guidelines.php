<?php
// Safe defaults (same pattern as other views)
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

  <title>Seller Guidelines | Lumora.</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Shared CSS & JS -->
  <link rel="stylesheet" href="styles.css" />
  <script src="views.js" defer></script>
</head>

<body>

<!-- ===================== READING PROGRESS BAR ===================== -->
<div id="progressBar" class="progress-bar"></div>

<!-- ===================== BACK TO TOP BUTTON ===================== -->
<button id="backToTop" class="back-to-top" aria-label="Back to top">↑</button>

<!-- ===================== ALERT MESSAGES (optional) ===================== -->
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
      <p>Read these guidelines before listing your handcrafted pieces.</p>
      <a href="sell.php" class="top-link">Back to Sell on Lumora</a>
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

  <!-- HERO / INTRO -->
  <section class="guidelines-hero">
    <div class="container">
      <p class="guidelines-eyebrow">Seller Guidelines</p>
      <h1>Standards for Selling on Lumora</h1>
      <p class="guidelines-intro">
        These guidelines outline how to list your products, communicate with buyers,
        and maintain quality on Lumora. Some rules below will later be enforced
        by actual PHP + MySQL validations and admin checks.
      </p>

      <p class="guidelines-updated">Last updated: January 2025</p>

      <div class="guidelines-pill-row">
        <span class="guidelines-pill">Handcrafted &amp; Authentic</span>
        <span class="guidelines-pill">Clear &amp; Honest Listings</span>
        <span class="guidelines-pill">Safe &amp; Respectful Marketplace</span>
      </div>
    </div>
  </section>

  <!-- CONTENT BODY -->
  <section class="guidelines-body">
    <div class="container guidelines-layout">

      <!-- LEFT: TABLE OF CONTENTS -->
      <aside class="guidelines-toc">
        <h2>On this page</h2>
        <ul>
          <li><a href="#eligibility">1. Seller Eligibility</a></li>
          <li><a href="#product-listings">2. Product Listings</a></li>
          <li><a href="#pricing-fees">3. Pricing &amp; Fees</a></li>
          <li><a href="#orders-shipping">4. Orders &amp; Shipping</a></li>
          <li><a href="#returns-refunds">5. Returns &amp; Refunds</a></li>
          <li><a href="#prohibited-items">6. Prohibited Items</a></li>
          <li><a href="#quality-media">7. Quality &amp; Media</a></li>
          <li><a href="#communication">8. Communication &amp; Conduct</a></li>
        </ul>
      </aside>

      <!-- RIGHT: GUIDELINES CONTENT -->
      <div class="guidelines-content">

        <!-- 1. Eligibility -->
        <section id="eligibility" class="guidelines-section">
          <h2>1. Seller Eligibility</h2>
          <p>
            Lumora is a curated marketplace for handcrafted and carefully designed jewelry
            and accessories. To keep the quality consistent, only approved sellers will
            be able to list items for sale.
          </p>
          <ul>
            <li>You must provide accurate legal information during account creation.</li>
            <li>All products listed must be either handcrafted, hand-finished, or original designs.</li>
            <li>Reselling mass-produced items is not allowed, unless explicitly approved by Lumora.</li>
            <li>One person or business may only maintain one seller account, unless approved by admins.</li>
          </ul>
        </section>

        <!-- 2. Product Listings -->
        <section id="product-listings" class="guidelines-section">
          <h2>2. Product Listings</h2>
          <p>
            Product information should be complete, accurate, and not misleading. Later,
            your product forms in PHP will validate many of these details.
          </p>
          <ul>
            <li>Provide a clear and descriptive product name (no excessive emojis or all-caps spam).</li>
            <li>Describe materials honestly (e.g., “18K gold-plated”, “Sterling silver 925”, etc.).</li>
            <li>Indicate if items are made-to-order or ready-to-ship.</li>
            <li>Specify dimensions, weight (if relevant), and any important care instructions.</li>
            <li>Stock quantities must reflect real availability to avoid overselling.</li>
          </ul>
        </section>

        <!-- 3. Pricing & Fees -->
        <section id="pricing-fees" class="guidelines-section">
          <h2>3. Pricing &amp; Fees</h2>
          <p>
            All prices on Lumora are shown in Philippine Peso (PHP). The final fee structure
            (commissions, payout schedule, etc.) will be defined and enforced in your backend
            logic. For now, treat the rules below as the basis for that system.
          </p>
          <ul>
            <li>List prices in PHP, inclusive of any applicable taxes you are responsible for.</li>
            <li>Do not artificially inflate prices only to show large discounts.</li>
            <li>Any additional fees (e.g., customization fees) must be clearly disclosed.</li>
            <li>Future: Platform commissions and payout rules will be displayed to you in your seller dashboard.</li>
          </ul>
        </section>

        <!-- 4. Orders & Shipping -->
        <section id="orders-shipping" class="guidelines-section">
          <h2>4. Orders &amp; Shipping</h2>
          <p>
            Buyers trust that orders placed through Lumora will arrive as described and on time.
            Once order management is fully implemented, these rules will be enforced by system statuses.
          </p>
          <ul>
            <li>Ship orders within the handling time you specify in your product settings.</li>
            <li>Use reliable couriers and always provide tracking details when available.</li>
            <li>Keep buyers updated on any delays or issues with their order.</li>
            <li>Never encourage buyers to move transactions outside the Lumora platform.</li>
          </ul>
        </section>

        <!-- 5. Returns & Refunds -->
        <section id="returns-refunds" class="guidelines-section">
          <h2>5. Returns &amp; Refunds</h2>
          <p>
            Lumora promotes a clear and fair return policy for both buyers and sellers.
            Exact flows will later be handled via your PHP controllers and database.
          </p>
          <ul>
            <li>Respect Lumora’s minimum return window (e.g., 7–30 days, once finalized).</li>
            <li>State clearly which items are non-returnable (e.g., custom engraved pieces).</li>
            <li>Process approved returns and refunds in a timely manner.</li>
            <li>Do not refuse refunds for items that arrive damaged or significantly not as described.</li>
          </ul>
        </section>

        <!-- 6. Prohibited Items -->
        <section id="prohibited-items" class="guidelines-section">
          <h2>6. Prohibited Items <span class="critical-badge">❕</span></h2>
          <p>
            For safety and legal compliance, some products cannot be listed on Lumora.
            This list may expand as your legal/compliance rules evolve.
          </p>
          <ul>
            <li>Counterfeit or trademark-infringing items (e.g., fake branded jewelry).</li>
            <li>Items containing hazardous, toxic, or illegal materials.</li>
            <li>Stolen goods or items obtained through unlawful means.</li>
            <li>Content that promotes hate, violence, or discrimination.</li>
          </ul>
        </section>

        <!-- 7. Quality & Media -->
        <section id="quality-media" class="guidelines-section">
          <h2>7. Quality &amp; Media Standards</h2>
          <p>
            Good photos and accurate descriptions build trust with buyers. In the future,
            image upload validations and recommendations can be powered by Python/ML modules.
          </p>
          <ul>
            <li>Upload clear, well-lit photos that show the product from multiple angles.</li>
            <li>Do not use misleading images (e.g., showing a set when listing only one piece).</li>
            <li>Avoid watermarks that hide the item or make it hard to see details.</li>
            <li>Use respectful and professional language in titles and descriptions.</li>
          </ul>
        </section>

        <!-- 8. Communication & Conduct -->
        <section id="communication" class="guidelines-section">
          <h2>8. Communication &amp; Conduct</h2>
          <p>
            How you communicate reflects on both your brand and Lumora as a marketplace.
            Messaging and notifications will later be logged and managed in your system.
          </p>
          <ul>
            <li>Respond to buyer questions and concerns in a timely and respectful manner.</li>
            <li>Do not harass, threaten, or use abusive language with buyers or staff.</li>
            <li>Never ask buyers to share sensitive information (e.g., passwords, PINs).</li>
            <li>Follow all local laws related to online selling, taxes, and consumer rights.</li>
          </ul>
        </section>

        <!-- LAST SECTION: A SMALL CTA -->
        <section class="guidelines-section guidelines-cta">
          <h2>Ready to start selling?</h2>
          <p>
            Once you’re comfortable with these guidelines, you can proceed to create or
            update your seller account. As your backend logic and database evolve, this
            page will remain the reference for expected behavior on Lumora.
          </p>
          <a href="sell.php" class="btn guidelines-btn">Go to Sell on Lumora</a>
        </section>

      </div><!-- /guidelines-content -->
    </div><!-- /container -->
  </section>

</main>

<!-- ===================== FOOTER ===================== -->
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
        <img src="icon-returns.png" alt="Easy 30 Day Returns">
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
