<?php
// app/Views/stores/show.view.php

// --- CSS FIX: Load stores.css with a cache buster ---
// We try both paths to ensure it loads regardless of server config
?>
<link rel="stylesheet" href="<?= base_url('css/stores.css') ?>?v=<?= time() ?>">
<link rel="stylesheet" href="<?= base_url('public/css/stores.css') ?>?v=<?= time() ?>">
<?php
// ----------------------------------------------------

/**
 * SMART IMAGE RESOLVER HELPER
 * Ensures shop images load correctly by checking DB paths or scanning directories.
 */
$resolveShopImage = function($path, $shopId, $type = 'banner') {
    if (!empty($path)) {
        $cleanPath = ltrim($path, '/');
        // Handle nested public/public/uploads structure if present
        if (strpos($cleanPath, 'uploads/shop/') === 0) {
            return 'public/' . $cleanPath;
        }
        return $cleanPath;
    }

    $dirType = ($type === 'banner') ? 'banners' : 'profiles';
    $prefix  = ($type === 'banner') ? 'banner_' : 'profile_';
    
    // Pattern: public/uploads/shop/[banners|profiles]/[prefix][id]_*
    $searchPattern = 'public/uploads/shop/' . $dirType . '/' . $prefix . $shopId . '_*.*';
    
    $files = glob($searchPattern);
    
    if ($files && !empty($files)) {
        return $files[0]; 
    }

    return null; // No image found
};

// Resolve images for the current shop
$bannerPath = $resolveShopImage($shop['shop_banner'] ?? null, $shop['shop_id'], 'banner');
$profilePath = $resolveShopImage($shop['shop_profile'] ?? null, $shop['shop_id'], 'profile');
?>

<div class="shop-page-wrapper">
    <div class="shop-header-banner">
        <div class="banner-image">
            <?php if ($bannerPath): ?>
                <img src="<?= base_url($bannerPath) ?>" 
                     alt="<?= htmlspecialchars($shop['shop_name'] ?? 'Shop') ?> Banner"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <?php endif; ?>
            
            <div class="banner-placeholder" <?= $bannerPath ? 'style="display: none;"' : '' ?>></div>
        </div>

        <div class="shop-header-content container">
            <div class="shop-profile-section">
                <div class="shop-avatar">
                    <?php if ($profilePath): ?>
                        <img src="<?= base_url($profilePath) ?>" 
                             alt="<?= htmlspecialchars($shop['shop_name'] ?? 'Shop') ?>">
                    <?php else: ?>
                        <div class="avatar-placeholder"><?= strtoupper(substr($shop['shop_name'] ?? 'S', 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="shop-info">
                    <h1 class="shop-name"><?= htmlspecialchars($shop['shop_name'] ?? 'Unknown Shop') ?></h1>
                    <div class="shop-meta">
                        <span class="location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php 
                                $city = $shop['city'] ?? null;
                                $province = $shop['province'] ?? null;
                                $locationParts = array_filter([$city, $province]);
                                
                                echo !empty($locationParts) ? htmlspecialchars(implode(', ', $locationParts)) : 'Location not available';
                            ?>
                        </span>
                        <span class="divider">•</span>
                        <span class="joined-date">Joined <?= date('F Y', strtotime($shop['created_at'] ?? 'now')) ?></span>
                    </div>
                </div>

                <div class="shop-stats-box">
                    <div class="stat">
                        <span class="value"><?= count($products) ?></span>
                        <span class="label">Products</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container shop-content-container">
        <div class="shop-layout">
            <aside class="shop-sidebar">
                <div class="sidebar-block">
                    <h3 class="sidebar-title">About the Shop</h3>
                    <div class="shop-description">
                        <?= nl2br(htmlspecialchars($shop['shop_description'] ?? 'No description provided.')) ?>
                    </div>
                </div>

                <div class="sidebar-block">
                    <h3 class="sidebar-title">Contact</h3>
                    <ul class="contact-list">
                        <?php if (!empty($shop['contact_email'])): ?>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?= htmlspecialchars($shop['contact_email']) ?>">Email Seller</a>
                            </li>
                        <?php endif; ?>
                        <?php if (!empty($shop['contact_phone'])): ?>
                            <li>
                                <i class="fas fa-phone"></i>
                                <?= htmlspecialchars($shop['contact_phone']) ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </aside>

            <main class="shop-main">
                <div class="products-header">
                    <h2>Shop Products</h2>
                    <span class="product-count"><?= count($products) ?> items</span>
                </div>

                <?php if (empty($products)): ?>
                    <div class="empty-state-stores" style="margin-top: 2rem;">
                        <i class="fas fa-box-open" style="font-size: 3rem; color: var(--color-light-gray); margin-bottom: 1rem;"></i>
                        <h3>No products found</h3>
                        <p>This shop hasn't added any products yet.</p>
                    </div>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                            <a href="/products/<?= htmlspecialchars($product['slug'] ?? '') ?>" class="product-card">
                                <div class="product-image">
                                    <?php 
                                    // Handle product image path consistency
                                    $imgSrc = $product['cover_picture'] ?? '';
                                    if (!empty($imgSrc)) {
                                        $imgSrc = ltrim($imgSrc, '/');
                                        // Fix for nested public folders
                                        if (strpos($imgSrc, 'uploads/') === 0 && strpos($imgSrc, 'public/') !== 0) {
                                            $imgSrc = 'public/' . $imgSrc;
                                        }
                                    }
                                    ?>
                                    
                                    <?php if (!empty($imgSrc)): ?>
                                        <img src="<?= base_url($imgSrc) ?>" 
                                             alt="<?= htmlspecialchars($product['name'] ?? 'Product') ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="no-image-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="product-info">
                                    <h3 class="product-name"><?= htmlspecialchars($product['name'] ?? 'Unnamed Product') ?></h3>
                                    
                                    <div class="product-rating">
                                        <?php 
                                            $rating = $product['average_rating'] ?? 0;
                                            $count = $product['review_count'] ?? 0;
                                        ?>
                                        <div class="stars">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if($i <= round($rating)): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="review-count">(<?= $count ?>)</span>
                                    </div>
                                    
                                    <?php if (!empty($product['short_description'])): ?>
                                        <p class="product-description">
                                            <?= htmlspecialchars(substr($product['short_description'], 0, 70)) ?>
                                            <?= strlen($product['short_description']) > 70 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="product-price">
                                        ₱<?= number_format($product['min_price'] ?? 0, 2) ?>
                                        <?php if (isset($product['max_price']) && $product['min_price'] != $product['max_price']): ?>
                                            - ₱<?= number_format($product['max_price'], 2) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>