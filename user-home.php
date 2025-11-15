<?php
// Safe defaults (same pattern as other views)
$isLoggedIn     = $isLoggedIn     ?? true;   // this page is for logged-in users
$statusMessage  = $statusMessage  ?? null;
$statusType     = $statusType     ?? 'info';
$username       = $username       ?? 'Lumora Member';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Home | Lumora</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Global + page-specific CSS -->
  <link rel="stylesheet" href="styles.css" />
  <link rel="stylesheet" href="user-home.css" />

  <!-- Page-specific JS -->
  <script src="user-home.js" defer></script>
</head>

<body>

<?php if ($statusMessage): ?>
  <div class="alert alert-<?= htmlspecialchars($statusType) ?>">
    <strong><?= $statusType === 'success' ? '✓' : ($statusType === 'error' ? '✗' : 'ℹ') ?></strong>
    <?= htmlspecialchars($statusMessage) ?>
  </div>
<?php endif; ?>

<!-- ===================== HEADER (logged-in nav) ===================== -->
<header class="site-header">
  <div class="top-bar">
    <div class="container top-bar-inner">
      <p>Welcome back to Lumora — discover new handcrafted pieces today.</p>
      <span class="top-user">Signed in as <?= htmlspecialchars($username) ?></span>
    </div>
  </div>

  <nav class="navbar">
    <div class="container navbar-inner">

      <!-- Logo -->
      <a href="user-home.php" class="logo">Lumora.</a>

      <!-- Mobile Toggle -->
      <button class="nav-toggle" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>

      <!-- Nav Links (user area) -->
      <ul class="nav-links">
        <li><a href="user-home.php" class="active">Home</a></li>
        <li><a href="user-shop.php">Shop</a></li>
        <li><a href="collections.php">Collections</a></li>
      </ul>

      <!-- Right side: search + bag + profile dropdown -->
      <div class="nav-icons">

        <div class="nav-search">
          <input type="text" placeholder="Search jewelry..." />
        </div>

        <!-- Shopping bag -->
        <a href="cart.php" class="icon-btn" aria-label="Shopping Bag">🛍</a>

        <!-- Profile dropdown -->
        <div class="profile-dropdown">
          <button class="icon-btn profile-trigger" aria-haspopup="true" aria-expanded="false">
            👤
          </button>
          <div class="profile-menu" role="menu">
            <a href="buyer-profile.php" role="menuitem">Profile</a>
            <a href="settings.php" role="menuitem">Settings</a>
          </div>
        </div>

      </div>
    </div>
  </nav>
</header>


<!-- ===================== MAIN ===================== -->
<main class="user-home-main">

  <!-- DASHBOARD HERO -->
  <section class="user-hero">
    <div class="container user-hero-inner">
      <div class="user-hero-text">
        <p class="user-hero-eyebrow">Welcome back</p>
        <h1><?= htmlspecialchars($username) ?>, ready to explore something new?</h1>
        <p class="user-hero-subtitle">
          Browse fresh handcrafted arrivals, pick up where you left off, or head
          straight to your cart to complete your order.
        </p>

        <div class="user-hero-actions">
          <a href="user-shop.php" class="btn user-hero-btn-primary">Browse Shop</a>
          <a href="cart.php" class="btn user-hero-btn-ghost">View Cart</a>
        </div>
      </div>

      <div class="user-hero-summary">
        <div class="summary-card">
          <span class="summary-label">Items in cart</span>
          <span class="summary-value">0</span>
        </div>
        <div class="summary-card">
          <span class="summary-label">Orders placed</span>
          <span class="summary-value">—</span>
        </div>
        <div class="summary-card">
          <span class="summary-label">Saved favorites</span>
          <span class="summary-value">—</span>
        </div>
      </div>
    </div>
  </section>

  <!-- QUICK ACTIONS -->
  <section class="user-quick-actions">
    <div class="container">
      <h2 class="section-title-small">Quick actions</h2>

      <div class="quick-grid">
        <a href="user-shop.php" class="quick-card">
          <h3>Continue shopping</h3>
          <p>Explore handcrafted jewelry curated just for you.</p>
        </a>

        <a href="cart.php" class="quick-card">
          <h3>Go to cart</h3>
          <p>Review your selections and get ready to checkout.</p>
        </a>

        <a href="buyer-profile.php" class="quick-card">
          <h3>View profile</h3>
          <p>Update your details, addresses, and preferences.</p>
        </a>

        <a href="settings.php" class="quick-card">
          <h3>Account settings</h3>
          <p>Manage security, notifications, and more.</p>
        </a>
      </div>
    </div>
  </section>

  <!-- RECOMMENDED PRODUCTS (static placeholder for now) -->
  <section class="user-recommended">
    <div class="container">
      <h2 class="section-title-small">Recommended for you</h2>
      <p class="user-recommended-subtitle">
        Once your backend is wired, this area can display personalized picks based on your browsing and purchase history.
      </p>

      <div class="user-product-grid">
        <article class="user-product-card">
          <div class="user-product-thumb"></div>
          <h3>Golden Dawn Bracelet</h3>
          <p class="user-product-price">₱2,450</p>
          <button class="btn user-product-btn">View details</button>
        </article>

        <article class="user-product-card">
          <div class="user-product-thumb"></div>
          <h3>Moonlit Pearl Earrings</h3>
          <p class="user-product-price">₱1,980</p>
          <button class="btn user-product-btn">View details</button>
        </article>

        <article class="user-product-card">
          <div class="user-product-thumb"></div>
          <h3>Solstice Pendant</h3>
          <p class="user-product-price">₱2,150</p>
          <button class="btn user-product-btn">View details</button>
        </article>
      </div>
    </div>
  </section>

</main>

<!-- ===================== FOOTER ===================== -->
<div class="footer-divider"></div>

<footer class="site-footer">
  <div class="container footer-content">
    <div class="footer-nav">
      <h4>Explore</h4>
      <ul>
        <li><a href="user-home.php">Home</a></li>
        <li><a href="user-shop.php">Shop</a></li>
        <li><a href="buyer-profile.php">Profile</a></li>
      </ul>
    </div>

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
