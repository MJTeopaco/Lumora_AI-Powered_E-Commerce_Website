<?php
// app/Views/shop/shop-profile.view.php

/**
 * Helper to resolve image paths correctly for the Profile View.
 * Handles:
 * 1. Just filename (legacy) -> prepends public/uploads/shop/...
 * 2. Relative path (uploads/...) -> prepends public/
 * 3. Full path with public/ -> leaves as is
 */
$resolveProfileImage = function($path, $type) {
    if (empty($path)) return null;
    
    // Clean path
    $cleanPath = ltrim($path, '/');
    
    // Case A: Already has 'public/' prefix (e.g. from Auto-Discovery)
    if (strpos($cleanPath, 'public/') === 0) {
        return base_url($cleanPath);
    }
    
    // Case B: Has 'uploads/' path (New Standard from Controller)
    if (strpos($cleanPath, 'uploads/') === 0) {
        return base_url('public/' . $cleanPath);
    }
    
    // Case C: Just a filename (Legacy DB records)
    $folder = ($type === 'banner') ? 'banners' : 'profiles';
    return base_url("public/uploads/shop/{$folder}/" . $cleanPath);
};

// Resolve URLs for this specific page load
$bannerUrl = $resolveProfileImage($shop_banner ?? null, 'banner');
$profileUrl = $resolveProfileImage($shop_profile ?? null, 'profile');
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="banner-section">
            <div class="banner-image-wrapper">
                <?php if (!empty($bannerUrl)): ?>
                    <img src="<?= htmlspecialchars($bannerUrl) ?>" 
                         alt="Shop Banner" 
                         class="banner-image" 
                         id="bannerPreview">
                <?php else: ?>
                    <div class="banner-placeholder" id="bannerPreview">
                        <i class="fas fa-image"></i>
                        <p>Add a banner to your shop</p>
                    </div>
                <?php endif; ?>
                <button class="banner-edit-btn" id="editBannerBtn">
                    <i class="fas fa-camera"></i>
                    <span>Change Banner</span>
                </button>
                <input type="file" id="bannerInput" accept="image/*" style="display: none;">
            </div>
        </div>

        <div class="profile-info-section">
            <div class="profile-avatar-wrapper">
                <div class="profile-avatar">
                    <?php if (!empty($profileUrl)): ?>
                        <img src="<?= htmlspecialchars($profileUrl) ?>" 
                             alt="Shop Profile" 
                             id="profilePreview">
                    <?php else: ?>
                        <div class="avatar-placeholder" id="profilePreview">
                            <i class="fas fa-store"></i>
                        </div>
                    <?php endif; ?>
                    <button class="avatar-edit-btn" id="editProfileBtn">
                        <i class="fas fa-camera"></i>
                    </button>
                    <input type="file" id="profileInput" accept="image/*" style="display: none;">
                </div>
            </div>

            <div class="profile-details">
                <h1 class="shop-name"><?= htmlspecialchars($shop_name ?? 'Shop Name') ?></h1>
                <p class="shop-slug">@<?= htmlspecialchars($slug ?? 'shop-slug') ?></p>
                <div class="shop-meta">
                    <span class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        Joined <?= date('F Y', strtotime($created_at ?? 'now')) ?>
                    </span>
                </div>
            </div>

            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value"><?= $stats['total_products'] ?? 0 ?></span>
                        <span class="stat-label">Products</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orders">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value"><?= $stats['total_orders'] ?? 0 ?></span>
                        <span class="stat-label">Orders</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">₱<?= number_format($stats['total_revenue'] ?? 0, 2) ?></span>
                        <span class="stat-label">Revenue</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-content">
        <div class="tabs-container">
            <button class="tab-btn active" data-tab="basic">
                <i class="fas fa-info-circle"></i>
                Basic Information
            </button>
            <button class="tab-btn" data-tab="address">
                <i class="fas fa-map-marker-alt"></i>
                Address
            </button>
        </div>

        <div class="tab-content-container">
            <div class="tab-content active" id="basic-tab">
                <form id="basicInfoForm" class="profile-form">
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-store"></i>
                            Shop Information
                        </h2>
                        
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="shop_name">
                                    Shop Name <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="shop_name" 
                                    name="shop_name" 
                                    value="<?= htmlspecialchars($shop_name ?? '') ?>" 
                                    required
                                    maxlength="100">
                                <span class="form-hint">This is your public shop name visible to customers</span>
                            </div>

                            <div class="form-group">
                                <label for="contact_email">
                                    Contact Email <span class="required">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    id="contact_email" 
                                    name="contact_email" 
                                    value="<?= htmlspecialchars($contact_email ?? '') ?>" 
                                    required
                                    maxlength="100">
                            </div>

                            <div class="form-group">
                                <label for="contact_phone">
                                    Contact Phone
                                </label>
                                <input 
                                    type="tel" 
                                    id="contact_phone" 
                                    name="contact_phone" 
                                    value="<?= htmlspecialchars($contact_phone ?? '') ?>"
                                    maxlength="20"
                                    placeholder="09XX XXX XXXX">
                            </div>

                            <div class="form-group full-width">
                                <label for="shop_description">
                                    Shop Description
                                </label>
                                <textarea 
                                    id="shop_description" 
                                    name="shop_description" 
                                    rows="5"
                                    maxlength="1000"
                                    placeholder="Tell customers about your shop..."><?= htmlspecialchars($shop_description ?? '') ?></textarea>
                                <span class="form-hint char-count">
                                    <span id="descCharCount">0</span>/1000 characters
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-wallet"></i>
                            Billing Information
                        </h2>
                        <p class="section-description">
                            Set up your payout account to receive payments from completed orders. This information is kept secure and private.
                        </p>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="payout_provider">
                                    Payment Provider <span class="required">*</span>
                                </label>
                                <select 
                                    id="payout_provider" 
                                    name="payout_provider" 
                                    required>
                                    <option value="">Select Provider</option>
                                    <option value="GCash" <?= ($payout_provider ?? '') === 'GCash' ? 'selected' : '' ?>>GCash</option>
                                    <option value="Maya" <?= ($payout_provider ?? '') === 'Maya' ? 'selected' : '' ?>>Maya</option>
                                </select>
                                <span class="form-hint">Choose your preferred payout method</span>
                            </div>

                            <div class="form-group">
                                <label for="payout_account_name">
                                    Account Name <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="payout_account_name" 
                                    name="payout_account_name" 
                                    value="<?= htmlspecialchars($payout_account_name ?? '') ?>"
                                    required
                                    maxlength="255"
                                    placeholder="Full name as registered">
                                <span class="form-hint">Name must match your account</span>
                            </div>

                            <div class="form-group full-width">
                                <label for="payout_account_number">
                                    Account Number <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="payout_account_number" 
                                    name="payout_account_number" 
                                    value="<?= htmlspecialchars($payout_account_number ?? '') ?>"
                                    required
                                    maxlength="50"
                                    placeholder="09XX XXX XXXX">
                                <span class="form-hint">
                                    <i class="fas fa-shield-alt"></i>
                                    Your account information is encrypted and secure
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($payout_provider)): ?>
                            <div class="billing-status-card">
                                <div class="status-icon verified">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="status-content">
                                    <h4>Billing Information Set</h4>
                                    <p>Your <?= htmlspecialchars($payout_provider) ?> account is configured and ready to receive payouts.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="billing-status-card warning">
                                <div class="status-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="status-content">
                                    <h4>Billing Information Required</h4>
                                    <p>Please set up your payout account to receive payments from completed orders.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" id="resetBasicBtn">
                            <i class="fas fa-undo"></i>
                            Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <div class="tab-content" id="address-tab">
                <form id="addressForm" class="profile-form">
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Shop Address
                        </h2>
                        
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="address_line1">
                                    Address Line 1 <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="address_line1" 
                                    name="address_line1" 
                                    value="<?= htmlspecialchars($address_line_1 ?? '') ?>"
                                    required
                                    placeholder="Street address, building number">
                            </div>

                            <div class="form-group full-width">
                                <label for="address_line2">
                                    Address Line 2
                                </label>
                                <input 
                                    type="text" 
                                    id="address_line2" 
                                    name="address_line2" 
                                    value="<?= htmlspecialchars($address_line_2 ?? '') ?>"
                                    placeholder="Apartment, suite, unit, floor, etc.">
                            </div>

                            <div class="form-group">
                                <label for="barangay">
                                    Barangay
                                </label>
                                <input 
                                    type="text" 
                                    id="barangay" 
                                    name="barangay" 
                                    value="<?= htmlspecialchars($barangay ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="city">
                                    City <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="city" 
                                    name="city" 
                                    value="<?= htmlspecialchars($city ?? '') ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="province">
                                    Province
                                </label>
                                <input 
                                    type="text" 
                                    id="province" 
                                    name="province" 
                                    value="<?= htmlspecialchars($province ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="region">
                                    Region
                                </label>
                                <input 
                                    type="text" 
                                    id="region" 
                                    name="region" 
                                    value="<?= htmlspecialchars($region ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="postal_code">
                                    Postal Code
                                </label>
                                <input 
                                    type="text" 
                                    id="postal_code" 
                                    name="postal_code" 
                                    value="<?= htmlspecialchars($postal_code ?? '') ?>"
                                    maxlength="10">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" id="resetAddressBtn">
                                <i class="fas fa-undo"></i>
                                Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Address
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<div id="loadingOverlay" class="loading-overlay">
    <div class="spinner"></div>
</div>