<?php
// app/Views/stores/show.view.php

/**
 * ROBUST IMAGE RESOLVER (Show Page)
 * 1. Checks Database Path
 * 2. Fallback: Auto-Discovery (Scans disk if DB is empty)
 * 3. Handles 'public/public' nested folder issues
 */
$resolveShopImage = function($path, $shopId, $type = 'banner') {
    // === STRATEGY 1: Database Path (Fastest) ===
    if (!empty($path)) {
        $cleanPath = ltrim($path, '/');
        
        // Case A: Already has 'public/' prefix
        if (strpos($cleanPath, 'public/') === 0) {
            return base_url($cleanPath);
        }
        
        // Case B: Has 'uploads/' path (Standard) -> Add 'public/' prefix
        if (strpos($cleanPath, 'uploads/') === 0) {
            return base_url('public/' . $cleanPath);
        }
        
        // Case C: Legacy Filename
        $folder = ($type === 'banner') ? 'banners' : 'profiles';
        return base_url("public/uploads/shop/{$folder}/" . $cleanPath);
    }

    // === STRATEGY 2: Auto-Discovery (Fallback for NULL DB) ===
    // This finds files physically existing on the server
    
    $dirType = ($type === 'banner') ? 'banners' : 'profiles';
    $prefix  = ($type === 'banner') ? 'banner_' : 'profile_';
    
    // We define the physical path to the 'public' folder on the server
    // dirname(__DIR__, 3) gets us to the project root (Lumora/)
    $projectRoot = dirname(__DIR__, 3); 
    
    // We check TWO possible locations because of the folder structure issues
    
    // Location 1: Standard (Lumora/public/uploads/...)
    $stdPattern = $projectRoot . '/public/uploads/shop/' . $dirType . '/' . $prefix . $shopId . '_*.*';
    
    // Location 2: Nested (Lumora/public/public/uploads/...) - This matches your index.view.php logic
    $nestedPattern = $projectRoot . '/public/public/uploads/shop/' . $dirType . '/' . $prefix . $shopId . '_*.*';

    // Try Standard first
    $files = glob($stdPattern);
    
    // If not found, try Nested
    if (!$files || empty($files)) {
        $files = glob($nestedPattern);
        $isNested = true;
    } else {
        $isNested = false;
    }

    if ($files && !empty($files)) {
        $foundFile = $files[0];
        $filename = basename($foundFile);
        
        // Construct the URL based on where we found it
        if ($isNested) {
            // Found in public/public/uploads... URL needs 'public/public/...'
            return base_url("public/public/uploads/shop/{$dirType}/{$filename}");
        } else {
            // Found in public/uploads... URL needs 'public/uploads/...'
            return base_url("public/uploads/shop/{$dirType}/{$filename}");
        }
    }

    return null; // No image found anywhere
};

// Resolve images to Full URLs
$bannerUrl = $resolveShopImage($shop['shop_banner'] ?? null, $shop['shop_id'], 'banner');
$profileUrl = $resolveShopImage($shop['shop_profile'] ?? null, $shop['shop_id'], 'profile');
?>

<div class="shop-page-wrapper">
    <div class="shop-header-banner">
        <div class="banner-image">
            <?php if ($bannerUrl): ?>
                <img src="<?= $bannerUrl ?>" 
                     alt="<?= htmlspecialchars($shop['shop_name'] ?? 'Shop') ?> Banner"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <?php endif; ?>
            
            <div class="banner-placeholder" <?= $bannerUrl ? 'style="display: none;"' : '' ?>>
                <i class="fas fa-image"></i>
            </div>
        </div>

        <div class="shop-header-content container">
            <div class="shop-profile-section">
                <div class="shop-avatar">
                    <?php if ($profileUrl): ?>
                        <img src="<?= $profileUrl ?>" 
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
                        <i class="fas fa-box-open" style="font-size: 3rem; color: #999; margin-bottom: 1rem;"></i>
                        <h3>No products found</h3>
                        <p>This shop hasn't added any products yet.</p>
                    </div>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                            <a href="<?= base_url('/products/' . htmlspecialchars($product['slug'] ?? '')) ?>" class="product-card">
                                <div class="product-image">
                                    <?php if (!empty($product['cover_picture'])): ?>
                                        <img src="<?= base_url($product['cover_picture']) ?>" 
                                             alt="<?= htmlspecialchars($product['name'] ?? 'Product') ?>"
                                             loading="lazy"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="no-image-placeholder" style="display: none;">
                                            <i class="fas fa-image"></i>
                                        </div>
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