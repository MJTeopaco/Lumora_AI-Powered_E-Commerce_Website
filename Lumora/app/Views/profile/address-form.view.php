<?php
// app/Views/profile/address-form.view.php
?>
<div class="content-header">
    <h1 class="content-title"><?= isset($isEdit) && $isEdit ? 'Edit' : 'Add New' ?> Address</h1>
    <p class="content-subtitle">Please fill in your complete delivery address</p>
</div>

<form method="POST" 
      action="<?= isset($isEdit) && $isEdit ? '/profile/addresses/edit' : '/profile/addresses/add' ?>" 
      id="addressForm">
    
    <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::get('csrf_token') ?>">
    <?php if (isset($isEdit) && $isEdit && !empty($address['address_id'])): ?>
        <input type="hidden" name="address_id" value="<?= htmlspecialchars($address['address_id']) ?>">
    <?php endif; ?>
    
    <div class="form-group">
        <label class="form-label">
            Address Line 1 <span class="required">*</span>
        </label>
        <input type="text" name="address_line_1" class="form-input" 
               placeholder="House No., Building, Street Name"
               value="<?= htmlspecialchars($address['address_line_1'] ?? '') ?>"
               required>
    </div>

    <div class="form-group">
        <label class="form-label">Address Line 2 (Optional)</label>
        <textarea name="address_line_2" class="form-textarea" 
                  placeholder="Unit number, floor, nearby landmark"><?= htmlspecialchars($address['address_line_2'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label class="form-label">
            Region <span class="required">*</span>
        </label>
        <select name="region" id="region" class="form-select" required>
            <option value="">Select Region</option>
            <option value="NCR" <?= (isset($address['region']) && $address['region'] === 'NCR') ? 'selected' : '' ?>>National Capital Region (NCR)</option>
            <option value="Region I" <?= (isset($address['region']) && $address['region'] === 'Region I') ? 'selected' : '' ?>>Region I - Ilocos Region</option>
            <option value="Region II" <?= (isset($address['region']) && $address['region'] === 'Region II') ? 'selected' : '' ?>>Region II - Cagayan Valley</option>
            <option value="Region III" <?= (isset($address['region']) && $address['region'] === 'Region III') ? 'selected' : '' ?>>Region III - Central Luzon</option>
            <option value="Region IV-A" <?= (isset($address['region']) && $address['region'] === 'Region IV-A') ? 'selected' : '' ?>>Region IV-A - CALABARZON</option>
            <option value="Region IV-B" <?= (isset($address['region']) && $address['region'] === 'Region IV-B') ? 'selected' : '' ?>>Region IV-B - MIMAROPA</option>
            <option value="Region V" <?= (isset($address['region']) && $address['region'] === 'Region V') ? 'selected' : '' ?>>Region V - Bicol Region</option>
            <option value="Region VI" <?= (isset($address['region']) && $address['region'] === 'Region VI') ? 'selected' : '' ?>>Region VI - Western Visayas</option>
            <option value="Region VII" <?= (isset($address['region']) && $address['region'] === 'Region VII') ? 'selected' : '' ?>>Region VII - Central Visayas</option>
            <option value="Region VIII" <?= (isset($address['region']) && $address['region'] === 'Region VIII') ? 'selected' : '' ?>>Region VIII - Eastern Visayas</option>
            <option value="Region IX" <?= (isset($address['region']) && $address['region'] === 'Region IX') ? 'selected' : '' ?>>Region IX - Zamboanga Peninsula</option>
            <option value="Region X" <?= (isset($address['region']) && $address['region'] === 'Region X') ? 'selected' : '' ?>>Region X - Northern Mindanao</option>
            <option value="Region XI" <?= (isset($address['region']) && $address['region'] === 'Region XI') ? 'selected' : '' ?>>Region XI - Davao Region</option>
            <option value="Region XII" <?= (isset($address['region']) && $address['region'] === 'Region XII') ? 'selected' : '' ?>>Region XII - SOCCSKSARGEN</option>
            <option value="Region XIII" <?= (isset($address['region']) && $address['region'] === 'Region XIII') ? 'selected' : '' ?>>Region XIII - Caraga</option>
            <option value="CAR" <?= (isset($address['region']) && $address['region'] === 'CAR') ? 'selected' : '' ?>>Cordillera Administrative Region (CAR)</option>
            <option value="BARMM" <?= (isset($address['region']) && $address['region'] === 'BARMM') ? 'selected' : '' ?>>Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)</option>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">
                Province <span class="required">*</span>
            </label>
            <select name="province" id="province" class="form-select" required>
                <option value="">Select Province</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">
                City/Municipality <span class="required">*</span>
            </label>
            <select name="city" id="city" class="form-select" required>
                <option value="">Select City/Municipality</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">
                Barangay <span class="required">*</span>
            </label>
            <select name="barangay" id="barangay" class="form-select" required>
                <option value="">Select Barangay</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Postal Code</label>
            <input type="text" name="postal_code" class="form-input" 
                   placeholder="Enter postal code"
                   value="<?= htmlspecialchars($address['postal_code'] ?? '') ?>"
                   maxlength="4" pattern="[0-9]{4}">
        </div>
    </div>

    <div class="form-group">
        <div class="checkbox-group">
            <input type="checkbox" id="is_default" name="is_default" value="1"
                   <?= (isset($address['is_default']) && $address['is_default'] == 1) ? 'checked' : '' ?>>
            <label for="is_default">Set as default address</label>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i>
            <?= isset($isEdit) && $isEdit ? 'Update' : 'Save' ?> Address
        </button>
        <a href="/profile/addresses" class="btn btn-secondary">
            <i class="fas fa-times"></i>
            Cancel
        </a>
    </div>
</form>

<script>
// Store existing values for edit mode
window.existingAddress = {
    province: '<?= htmlspecialchars($address["province"] ?? "") ?>',
    city: '<?= htmlspecialchars($address["city"] ?? "") ?>',
    barangay: '<?= htmlspecialchars($address["barangay"] ?? "") ?>'
};
</script>

<script src="/js/address-data.js"></script>
<script src="/js/address-form.js"></script>