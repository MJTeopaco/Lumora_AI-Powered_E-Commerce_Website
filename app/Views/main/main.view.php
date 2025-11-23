<?php
// app/Views/main/index.view.php
// Main homepage content to be injected into default.layout.php via $content

// Prevent undefined variable warnings
$categoryCounts = $categoryCounts ?? [];
$products = $products ?? [];
$isSeller = $isSeller ?? false;
$isLoggedIn = $isLoggedIn ?? false;
?>

<!-- ===================== HERO CAROUSEL ===================== -->
<section class="hero-carousel">
  <div class="carousel-container">
    <div class="carousel-track">

      <!-- Slide 1 -->
      <div class="carousel-slide is-active">
        <img src="/img/banner-gold-earrings.jpg" alt="Gold Earrings Collection" />
        <div class="hero-overlay"></div>
        <div class="carousel-text align-right">
          <p class="hero-eyebrow">SIGNATURE GOLD PIECES</p>
          <h1>Gold Earrings For Every Story</h1>
          <p class="hero-subtitle">
            Delicately handcrafted pieces to elevate your everyday glow.
          </p>
          <a href="/collections" class="hero-btn">Shop Now</a>
        </div>
      </div>

      <!-- Slide 2 -->
      <div class="carousel-slide">
        <img src="/img/banner-earrings.jpg" alt="Elegant Earring Banner" />
        <div class="hero-overlay"></div>
        <div class="carousel-text align-right">
          <p class="hero-eyebrow">EVERYDAY LUXURY</p>
          <h1>Modern Gold Elegance</h1>
          <p class="hero-subtitle">
            Clean silhouettes that pair with denim days and evening plans.
          </p>
          <a href="/collections" class="hero-btn">Shop Now</a>
        </div>
      </div>

      <!-- Slide 3 -->
      <div class="carousel-slide">
        <img src="/img/banner-hero.jpg" alt="Jewelry Collection Banner" />
        <div class="hero-overlay"></div>
        <div class="carousel-text align-right">
          <p class="hero-eyebrow">CRAFTED BY ARTISANS</p>
          <h1>Timeless Filipino Craftsmanship</h1>
          <p class="hero-subtitle">
            Thoughtfully made by local jewelers, one detail at a time.
          </p>
          <a href="/collections" class="hero-btn">Shop Now</a>
        </div>
      </div>

      <!-- Slide 4 -->
      <div class="carousel-slide">
        <img src="/img/banner-necklace-2.jpg" alt="Necklace Banner" />
        <div class="hero-overlay"></div>
        <div class="carousel-text align-right">
          <p class="hero-eyebrow">SHINE IN YOUR OWN LIGHT</p>
          <h1>Jewelry That Moves With You</h1>
          <p class="hero-subtitle">
            Fine details, a weightless feel, and a glow made to be lived in.
          </p>
          <a href="/collections" class="hero-btn">Shop Now</a>
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

      <a href="/collections?category=bracelets" class="category-item">
        <div class="category-thumb-wrapper">
          <img src="/img/placeholder-bracelets.jpg" alt="Bracelets" class="category-thumb">
        </div>
        <div class="category-text">
          <span class="category-name">Bracelets</span>
          <span class="category-count">
            <?= $categoryCounts['Bracelets'] ?? 0 ?> items
          </span>
        </div>
      </a>

      <a href="/collections?category=urban-staples" class="category-item">
        <div class="category-thumb-wrapper">
          <img src="/img/placeholder-urban.jpg" alt="Urban &amp; Staples" class="category-thumb">
        </div>
        <div class="category-text">
          <span class="category-name">Urban &amp; Staples</span>
          <span class="category-count">
            <?= $categoryCounts['Urban & Staples'] ?? 0 ?> items
          </span>
        </div>
      </a>

      <a href="/collections?category=charms" class="category-item">
        <div class="category-thumb-wrapper">
          <img src="/img/placeholder-charms.jpg" alt="Charms" class="category-thumb">
        </div>
        <div class="category-text">
          <span class="category-name">Charms</span>
          <span class="category-count">
            <?= $categoryCounts['Charms'] ?? 0 ?> items
          </span>
        </div>
      </a>

      <a href="/collections?category=engagement-rings" class="category-item">
        <div class="category-thumb-wrapper">
          <img src="/img/placeholder-rings.jpg" alt="Engagement Rings" class="category-thumb">
        </div>
        <div class="category-text">
          <span class="category-name">Engagement Rings</span>
          <span class="category-count">
            <?= $categoryCounts['Engagement Rings'] ?? 0 ?> items
          </span>
        </div>
      </a>

      <a href="/collections?category=necklaces" class="category-item">
        <div class="category-thumb-wrapper">
          <img src="/img/placeholder-necklaces.jpg" alt="Necklaces" class="category-thumb">
        </div>
        <div class="category-text">
          <span class="category-name">Necklaces</span>
          <span class="category-count">
            <?= $categoryCounts['Necklaces'] ?? 0 ?> items
          </span>
        </div>
      </a>

      <a href="/collections?category=pendants" class="category-item">
        <div class="category-thumb-wrapper">
          <img src="/img/placeholder-pendants.jpg" alt="Pendants" class="category-thumb">
        </div>
        <div class="category-text">
          <span class="category-name">Pendants</span>
          <span class="category-count">
            <?= $categoryCounts['Pendants'] ?? 0 ?> items
          </span>
        </div>
      </a>

    </div>
  </div>
</section>

<!-- ===================== FEATURED PRODUCTS ===================== -->
<section class="featured-products">
  <div class="container featured-grid">

    <div class="feature-card">
      <div class="feature-image">
        <img src="/img/flutter-butterfly.jpg" alt="Flutter & Butterfly Pendant">
      </div>
      <div class="feature-content">
        <h3>Flutter &amp; Butterfly Pendant</h3>
        <a href="/products/flutter-butterfly-pendant" class="feature-btn">Shop Now</a>
      </div>
    </div>

    <div class="feature-card">
      <div class="feature-image">
        <img src="/img/gold-filled-bracelet.jpg" alt="Gold Filled Bracelet">
      </div>
      <div class="feature-content">
        <h3>Best Gold Filled Bracelet</h3>
        <a href="/products/gold-filled-bracelet" class="feature-btn">Shop Now</a>
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
          <a href="/products/<?= htmlspecialchars($product['slug'] ?? $product['product_id']) ?>" 
             class="product-card" 
             data-tag="<?= htmlspecialchars($product['tag'] ?? '') ?>">
            <div class="product-image">
              <img src="<?= htmlspecialchars($product['image']) ?>" 
                   alt="<?= htmlspecialchars($product['name']) ?>"
                   loading="lazy">
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
          </a>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>

  </div>
</section>


<!-- ===================== SELLER PROMO BANNER ===================== -->
<section class="promo-banner promo-seller">
  <img src="/img/banner-silver-earrings.jpg" alt="Sell on Lumora Banner" />

  <div class="promo-text container">
    <p class="promo-eyebrow">BECOME A LUMORA CREATOR</p>

    <h1>Showcase Your Craft. Grow Your Brand.</h1>

    <p class="promo-subtitle">
      Join Lumora's marketplace of independent Filipino jewelers and artisans.
      Reach nationwide customers looking for handcrafted, meaningful pieces.
    </p>

    <div class="promo-actions">
      <?php if (!$isLoggedIn): ?>
        <a href="/login" class="btn promo-btn">
          Become a Seller
        </a>
      <?php else: ?>
        <a href="/seller/register" class="btn promo-btn">
          Start Selling
        </a>
      <?php endif; ?>

      <a href="/seller/guidelines" class="btn btn-outline-light">
        View Seller Guidelines
      </a>

      <?php if ($isSeller): ?>
        <a href="/shop/dashboard" class="promo-dashboard-link">
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