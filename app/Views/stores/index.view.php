<?php
// app/Views/stores/index.view.php
// Fixed: Automatically finds shop images even if Database is NULL by scanning the directory
// Handles nested 'public/public/uploads' structure.

$shops = $shops ?? [];
$featuredSellers = $featuredSellers ?? [];
$regions = $regions ?? [];
$specialties = $specialties ?? [];
$currentRegion = $currentRegion ?? null;
$currentSpecialty = $currentSpecialty ?? null;
$searchTerm = $searchTerm ?? '';
$totalShops = $totalShops ?? 0;

/**
 * Smart Helper to resolve image paths.
 * 1. If path exists in DB, clean and format it.
 * 2. If path is EMPTY in DB, try to find the file on disk using Shop ID.
 * 3. Handles the nested 'public/public/uploads' folder structure.
 */
$resolveShopImage = function($path, $shopId, $type = 'banner') {
    // 1. If we have a path from DB, just fix the URL format
    if (!empty($path)) {
        $cleanPath = ltrim($path, '/');
        // Fix: If it's a standard upload path, verify if it needs 'public/' prefix
        // (For your specific structure: public/public/uploads/...)
        if (strpos($cleanPath, 'uploads/shop/') === 0) {
            return 'public/' . $cleanPath;
        }
        return $cleanPath;
    }

    // 2. If DB is NULL, attempt Auto-Discovery
    // Look inside the nested public folder: public/public/uploads/shop/[banners|profiles]
    $dirType = ($type === 'banner') ? 'banners' : 'profiles';
    $prefix  = ($type === 'banner') ? 'banner_' : 'profile_';
    
    // Pattern to search: public/uploads/shop/banners/banner_{id}_*
    // Note: Relative to public/index.php execution context
    $searchPattern = 'public/uploads/shop/' . $dirType . '/' . $prefix . $shopId . '_*.*';
    
    $files = glob($searchPattern);
    
    if ($files && !empty($files)) {
        // Return the first match found, ensuring it has the 'public/' prefix for the URL
        return $files[0]; 
    }

    return null; // No image found
};
?>

<section class="stores-hero">
    <div class="stores-hero-content">
        <p class="stores-eyebrow">LUMORA ARTISAN COMMUNITY</p>
        <h1>Meet the Makers</h1>
        <p class="stores-subtitle">Connect directly with independent Filipino jewelers and artisans crafting exceptional pieces</p>
        
        <div class="stores-search-wrapper">
            <form method="GET" action="<?= base_url('/stores') ?>" class="stores-search-form">
                <input type="text" 
                       name="search" 
                       class="stores-search-input" 
                       placeholder="Search for shops, artisans, or locations..."
                       value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="stores-search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
    </div>
</section>

<?php if (!empty($featuredSellers)): ?>
<section class="featured-sellers">
    <div class="container">
        <h2 class="section-title-stores">Spotlight Artisans</h2>
        <p class="section-subtitle">Top-rated makers in our community</p>
        
        <div class="featured-sellers-grid">
            <?php foreach ($featuredSellers as $seller): 
                // Resolve images (from DB or Auto-Discovery)
                $bannerPath = $resolveShopImage($seller['shop_banner'] ?? null, $seller['shop_id'], 'banner');
                $profilePath = $resolveShopImage($seller['shop_profile'] ?? null, $seller['shop_id'], 'profile');
            ?>
                <div class="featured-seller-card">
                    <div class="featured-seller-banner">
                        <?php if ($bannerPath): ?>
                            <img src="<?= base_url($bannerPath) ?>" 
                                 alt="<?= htmlspecialchars($seller['shop_name']) ?> Banner"
                                 class="banner-img"
                                 style="object-fit: cover;"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <?php endif; ?>
                        
                        <div class="featured-seller-banner-placeholder" <?= $bannerPath ? 'style="display: none;"' : '' ?>></div>
                        
                        <div class="featured-seller-profile-pic">
                            <?php if ($profilePath): ?>
                                <img src="<?= base_url($profilePath) ?>" 
                                     alt="<?= htmlspecialchars($seller['shop_name']) ?>"
                                     class="profile-img">
                            <?php elseif (!empty($seller['profile_pic'])): ?>
                                <img src="<?= base_url(ltrim($seller['profile_pic'], '/')) ?>" 
                                     alt="<?= htmlspecialchars($seller['shop_name']) ?>"
                                     class="profile-img">
                            <?php else: ?>
                                <div class="featured-profile-placeholder">
                                    <?= strtoupper(substr($seller['shop_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="featured-seller-content">
                        <h3 class="featured-seller-name">
                            <a href="<?= base_url('/stores/' . htmlspecialchars($seller['slug'])) ?>">
                                <?= htmlspecialchars($seller['shop_name']) ?>
                            </a>
                        </h3>
                        
                        <p class="featured-seller-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php 
                            $location = [];
                            if (!empty($seller['city'])) $location[] = $seller['city'];
                            if (!empty($seller['province'])) $location[] = $seller['province'];
                            echo htmlspecialchars(implode(', ', $location) ?: 'Philippines');
                            ?>
                        </p>
                        
                        <blockquote class="featured-seller-quote">
                            <?php 
                            $description = $seller['shop_description'] ?? 'Crafting beautiful jewelry with passion and tradition.';
                            $truncated = substr($description, 0, 120);
                            echo '"' . htmlspecialchars($truncated);
                            if (strlen($description) > 120) echo '...';
                            echo '"';
                            ?>
                        </blockquote>
                        
                        <?php if (!empty($seller['specialties'])): ?>
                            <div class="featured-seller-specialties">
                                <?php 
                                $specs = explode(', ', $seller['specialties']);
                                $displaySpecs = array_slice($specs, 0, 3);
                                foreach ($displaySpecs as $spec): 
                                ?>
                                    <span class="specialty-tag"><?= htmlspecialchars($spec) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="featured-seller-stats">
                            <div class="stat-item">
                                <span class="stat-number"><?= $seller['product_count'] ?? 0 ?></span>
                                <span class="stat-label">Products</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?= $seller['order_count'] ?? 0 ?></span>
                                <span class="stat-label">Sales</span>
                            </div>
                        </div>
                        
                        <a href="<?= base_url('/stores/' . htmlspecialchars($seller['slug'])) ?>" class="btn-visit-shop">
                            Visit Shop <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="stores-directory">
    <div class="container-stores">
        <div class="stores-layout">
            <aside class="stores-sidebar">
                <div class="filter-section">
                    <h3 class="filter-title">Filter by Region</h3>
                    <form method="GET" action="<?= base_url('/stores') ?>" class="filter-form">
                        <?php if ($currentSpecialty): ?>
                            <input type="hidden" name="specialty" value="<?= htmlspecialchars($currentSpecialty) ?>">
                        <?php endif; ?>
                        <?php if ($searchTerm): ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                        <?php endif; ?>
                        
                        <label class="filter-option">
                            <input type="radio" name="region" value="all" 
                                   <?= empty($currentRegion) || $currentRegion === 'all' ? 'checked' : '' ?>
                                   onchange="this.form.submit()">
                            <span>All Regions</span>
                        </label>
                        
                        <?php if (!empty($regions)): ?>
                            <?php foreach ($regions as $region): ?>
                                <label class="filter-option">
                                    <input type="radio" name="region" value="<?= htmlspecialchars($region) ?>" 
                                           <?= $currentRegion === $region ? 'checked' : '' ?>
                                           onchange="this.form.submit()">
                                    <span><?= htmlspecialchars($region) ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="filter-section">
                    <h3 class="filter-title">Filter by Specialty</h3>
                    <form method="GET" action="<?= base_url('/stores') ?>" class="filter-form">
                        <?php if ($currentRegion): ?>
                            <input type="hidden" name="region" value="<?= htmlspecialchars($currentRegion) ?>">
                        <?php endif; ?>
                        <?php if ($searchTerm): ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                        <?php endif; ?>
                        
                        <label class="filter-option">
                            <input type="radio" name="specialty" value="all" 
                                   <?= empty($currentSpecialty) || $currentSpecialty === 'all' ? 'checked' : '' ?>
                                   onchange="this.form.submit()">
                            <span>All Specialties</span>
                        </label>
                        
                        <?php if (!empty($specialties)): ?>
                            <?php foreach ($specialties as $specialty): ?>
                                <label class="filter-option">
                                    <input type="radio" name="specialty" value="<?= htmlspecialchars($specialty['slug']) ?>" 
                                           <?= $currentSpecialty === $specialty['slug'] ? 'checked' : '' ?>
                                           onchange="this.form.submit()">
                                    <span><?= htmlspecialchars($specialty['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </form>
                </div>
                
                <?php if ($currentRegion || $currentSpecialty || $searchTerm): ?>
                    <a href="<?= base_url('/stores') ?>" class="btn-clear-filters">
                        <i class="fas fa-times"></i> Clear All Filters
                    </a>
                <?php endif; ?>
            </aside>
            
            <main class="stores-main">
                <div class="stores-toolbar">
                    <h2 class="stores-results-heading">All Artisan Shops</h2>
                    <span class="stores-results-count"><?= $totalShops ?> <?= $totalShops === 1 ? 'Shop' : 'Shops' ?></span>
                </div>
                
                <?php if (empty($shops)): ?>
                    <div class="empty-state-stores">
                        <i class="fas fa-store-slash" style="font-size: 4rem; color: var(--color-light-gray);"></i>
                        <h3>No Shops Found</h3>
                        <p>
                            <?php if ($searchTerm): ?>
                                No shops match "<?= htmlspecialchars($searchTerm) ?>"
                            <?php elseif ($currentRegion || $currentSpecialty): ?>
                                No shops match your filter criteria
                            <?php else: ?>
                                No shops available at the moment
                            <?php endif; ?>
                        </p>
                        <a href="<?= base_url('/stores') ?>" class="btn btn-primary">View All Shops</a>
                    </div>
                <?php else: ?>
                    <div class="stores-grid">
                        <?php foreach ($shops as $shop): 
                            // Resolve images (from DB or Auto-Discovery)
                            $bannerPath = $resolveShopImage($shop['shop_banner'] ?? null, $shop['shop_id'], 'banner');
                            $profilePath = $resolveShopImage($shop['shop_profile'] ?? null, $shop['shop_id'], 'profile');
                        ?>
                            <div class="store-card">
                                <div class="store-card-banner">
                                    <?php if ($bannerPath): ?>
                                        <img src="<?= base_url($bannerPath) ?>" 
                                             alt="<?= htmlspecialchars($shop['shop_name']) ?> Banner"
                                             class="banner-img"
                                             style="object-fit: cover;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <?php endif; ?>
                                    
                                    <div class="store-banner-placeholder" <?= $bannerPath ? 'style="display: none;"' : '' ?>></div>
                                    
                                    <div class="store-card-profile">
                                        <?php if ($profilePath): ?>
                                            <img src="<?= base_url($profilePath) ?>" 
                                                 alt="<?= htmlspecialchars($shop['shop_name']) ?>"
                                                 class="profile-img">
                                        <?php elseif (!empty($shop['profile_pic'])): ?>
                                            <img src="<?= base_url(ltrim($shop['profile_pic'], '/')) ?>" 
                                                 alt="<?= htmlspecialchars($shop['shop_name']) ?>"
                                                 class="profile-img">
                                        <?php else: ?>
                                            <div class="store-profile-placeholder">
                                                <?= strtoupper(substr($shop['shop_name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="store-card-content">
                                    <h3 class="store-card-name">
                                        <a href="<?= base_url('/stores/' . htmlspecialchars($shop['slug'])) ?>">
                                            <?= htmlspecialchars($shop['shop_name']) ?>
                                        </a>
                                    </h3>
                                    
                                    <p class="store-card-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php 
                                        $location = [];
                                        if (!empty($shop['city'])) $location[] = $shop['city'];
                                        if (!empty($shop['province'])) $location[] = $shop['province'];
                                        echo htmlspecialchars(implode(', ', $location) ?: 'Philippines');
                                        ?>
                                    </p>
                                    
                                    <p class="store-card-description">
                                        <?php 
                                        $desc = $shop['shop_description'] ?? 'Quality handcrafted jewelry';
                                        $truncDesc = substr($desc, 0, 100);
                                        echo htmlspecialchars($truncDesc);
                                        if (strlen($desc) > 100) echo '...';
                                        ?>
                                    </p>
                                    
                                    <?php if (!empty($shop['product_previews'])): ?>
                                        <div class="store-product-previews">
                                            <?php foreach ($shop['product_previews'] as $preview): ?>
                                                <a href="<?= base_url('/products/' . htmlspecialchars($preview['slug'])) ?>" class="preview-thumb">
                                                    <?php if (!empty($preview['cover_picture'])): 
                                                        $prodImg = $preview['cover_picture'];
                                                        if (strpos($prodImg, 'uploads/shop/') === 0) $prodImg = 'public/' . $prodImg;
                                                    ?>
                                                        <img src="<?= base_url($prodImg) ?>" 
                                                             alt="<?= htmlspecialchars($preview['name']) ?>"
                                                             class="preview-img">
                                                    <?php else: ?>
                                                        <div class="preview-placeholder">
                                                            <i class="fas fa-image"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </a>
                                            <?php endforeach; ?>
                                            
                                            <?php if ($shop['total_products'] > 3): ?>
                                                <a href="<?= base_url('/stores/' . htmlspecialchars($shop['slug'])) ?>" class="preview-more">
                                                    +<?= $shop['total_products'] - 3 ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <a href="<?= base_url('/stores/' . htmlspecialchars($shop['slug'])) ?>" class="btn-view-shop">
                                        View Shop <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</section>

<section class="cta-become-seller">
    <div class="container">
        <div class="cta-content">
            <h2>Are You an Artisan?</h2>
            <p>Join Lumora's thriving community of Filipino jewelry makers. Share your craft with customers nationwide.</p>
            <div class="cta-buttons">
                <a href="<?= base_url('/main/seller-guidelines') ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-store"></i> Open Your Shop Today
                </a>
                <a href="<?= base_url('/main/seller-guidelines') ?>" class="btn btn-outline btn-lg">
                    Learn More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* Fallback styles for broken images */
.banner-img, .profile-img, .preview-img {
    display: block;
}

.banner-img[src=""], 
.banner-img:not([src]),
.profile-img[src=""],
.profile-img:not([src]),
.preview-img[src=""],
.preview-img:not([src]) {
    display: none;
}
</style>