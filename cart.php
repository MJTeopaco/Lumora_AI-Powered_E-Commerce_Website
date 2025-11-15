<?php
$isLoggedIn = $isLoggedIn ?? true;
$username   = $username   ?? 'Demo User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shopping Cart | Lumora.</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="cart.css" />
  <script src="cart.js" defer></script>
</head>
<body>

<header class="site-header">
  <div class="top-bar">
    <div class="container top-bar-inner">
      <p>Review your items before checkout.</p>
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
        <li><a href="user-shop.php">Shop</a></li>
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
  <section class="cart-page">
    <div class="container">
      <h1>Shopping Bag</h1>
      <!-- cart table + summary placeholders -->
    </div>
  </section>
</main>

<?php include 'user-footer.php'; ?>

</body>
</html>
