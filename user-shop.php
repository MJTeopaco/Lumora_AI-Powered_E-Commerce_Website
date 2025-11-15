<?php
$isLoggedIn = $isLoggedIn ?? true;
$username   = $username   ?? 'Demo User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shop | Lumora.</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="user-shop.css" />
  <script src="user-shop.js" defer></script>
</head>
<body>

<header class="site-header">
  <div class="top-bar">
    <div class="container top-bar-inner">
      <p>Find handcrafted jewelry curated just for you.</p>
      <span class="top-user">Signed in as <?= htmlspecialchars($username) ?></span>
    </div>
  </div>

  <nav class="navbar">
    <div class="container navbar-inner">

      <a href="user-home.php" class="logo">Lumora.</a>

      <button class="nav-toggle" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>

      <ul class="nav-links">
        <li><a href="user-home.php">Home</a></li>
        <li><a href="user-shop.php" class="active">Shop</a></li>
        <li><a href="collections.php">Collections</a></li>
      </ul>

      <div class="nav-icons">
        <div class="nav-search">
          <input type="text" placeholder="Search jewelry..." />
        </div>

        <a href="cart.php" class="icon-btn" aria-label="Shopping Bag">🛍</a>

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

<main>
  <section class="page-hero shop-hero">
    <div class="container">
      <h1>Shop</h1>
      <p class="page-hero-subtitle">
        Browse all handcrafted pieces from Lumora sellers.
      </p>
    </div>
  </section>

  <section class="shop-grid-section">
    <div class="container">
      <!-- filters + grid placeholders -->
      <div class="shop-filters">
        <button class="filter-chip is-active">All</button>
        <button class="filter-chip">Rings</button>
        <button class="filter-chip">Necklaces</button>
        <button class="filter-chip">Bracelets</button>
        <button class="filter-chip">Earrings</button>
      </div>

      <div class="product-grid product-grid-shop">
        <!-- repeat cards later with PHP data -->
        <article class="product-card" data-tag="New">
          <div class="product-image">
            <img src="placeholder-ring.jpg" alt="Sample ring">
          </div>
          <h3 class="product-name">Sample Ring</h3>
          <p class="product-price"><span class="current">₱1,200</span></p>
          <button class="view-product-btn user-product-btn">View details</button>
        </article>
      </div>
    </div>
  </section>
</main>

<?php include 'user-footer.php'; // or paste the same footer HTML you used on guidelines ?>

</body>
</html>
