<?php
// app/Views/main_page/index.view.php
// This is now the content only, to be injected into the layout via $content

// Hero Section
?>
<section class="hero">
    <h1>Exquisite Accessories for Every Occasion</h1>
    <p>Discover timeless elegance with our curated collection</p>
</section>

<main class="container">
    <h2 class="section-title">Featured Products</h2>
    
    <?php if (!$isLoggedIn): ?>
        <div class="alert alert-info">
            <strong>👋 Welcome!</strong> You can browse our products, but you must <a href="/login">login</a> to add items to your cart or place an order.
        </div>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <div class="empty-state">
            <h3>No Products Available</h3>
            <p>Check back soon for our amazing collection!</p>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>