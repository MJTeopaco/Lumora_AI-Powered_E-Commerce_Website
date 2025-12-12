<?php
// app/Views/seller/register.view.php
// Main layout handles HTML/CSS/JS inclusions
?>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-store"></i> Become a Seller on Lumora</h1>
        <p>Join our marketplace and start selling your products today</p>
    </div>

    <div class="form-content">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>

        <div class="info-banner">
            <i class="fas fa-info-circle"></i>
            <div>
                <p><strong>Getting Started:</strong> Complete this registration form to create your seller account.</p>
            </div>
        </div>

        <form action="/seller/register" method="POST" id="sellerRegistrationForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-store-alt"></i>
                    <span>Shop Information</span>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="shop_name">Shop Name <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-shop"></i>
                            <input type="text" id="shop_name" name="shop_name" placeholder="Enter your shop name" required>
                        </div>
                        <div class="helper-text">
                            <i class="fas fa-lightbulb"></i>
                            <span>Choose a unique and memorable name for your shop</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="shop_description">Shop Description</label>
                        <textarea id="shop_description" name="shop_description" placeholder="Describe what your shop offers..."></textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-address-card"></i>
                    <span>Contact Information</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_email">Contact Email <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="contact_email" name="contact_email" placeholder="shop@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact_phone">Contact Phone <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="contact_phone" name="contact_phone" placeholder="+63 912 345 6789" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Shop Address</span>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="address_line_1">Address Line 1 <span class="required">*</span></label>
                        <input type="text" id="address_line_1" name="address_line_1" placeholder="Street address" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="address_line_2">Address Line 2</label>
                        <input type="text" id="address_line_2" name="address_line_2" placeholder="Additional address info">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="barangay">Barangay <span class="required">*</span></label>
                        <input type="text" id="barangay" name="barangay" placeholder="Enter barangay" required>
                    </div>

                    <div class="form-group">
                        <label for="city">City/Municipality <span class="required">*</span></label>
                        <input type="text" id="city" name="city" placeholder="Enter city" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="province">Province <span class="required">*</span></label>
                        <input type="text" id="province" name="province" placeholder="Enter province" required>
                    </div>

                    <div class="form-group">
                        <label for="region">Region <span class="required">*</span></label>
                        <select id="region" name="region" required>
                            <option value="">Select region</option>
                            <?php foreach ($regions as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="postal_code">Postal Code <span class="required">*</span></label>
                        <input type="text" id="postal_code" name="postal_code" placeholder="e.g., 1600" pattern="[0-9]{4}" maxlength="4" required>
                    </div>
                </div>
            </div>

            <div class="terms-checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    I agree to the <a href="#" id="terms-link">Terms and Conditions</a> and <a href="#" id="seller-policy-link">Seller Policy</a>.
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Submit Application
            </button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/partials/seller-terms-modals.partial.php'; ?>