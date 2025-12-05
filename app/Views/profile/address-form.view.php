<?php
// app/Views/profile/address-form.view.php
?>
<div class="content-header">
    <h1 class="content-title"><?= isset($isEdit) && $isEdit ? 'Edit' : 'Add New' ?> Address</h1>
    <p class="content-subtitle">Please fill in your complete delivery address</p>
</div>

<form method="POST" 
      action="<?= isset($isEdit) && $isEdit ? '/profile/addresses/edit/' . htmlspecialchars($address['address_id'] ?? '') : '/profile/addresses/add' ?>" 
      id="addressForm">
    
    <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::get('csrf_token') ?>">
    
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
            <?php if (isset($regions) && is_array($regions)): ?>
                <?php foreach ($regions as $key => $name): ?>
                    <option value="<?= htmlspecialchars($key) ?>" 
                            <?= (isset($address['region']) && $address['region'] === $key) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($name) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
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
    window.existingAddress = {
        province: '<?= htmlspecialchars($address["province"] ?? "") ?>',
        city: '<?= htmlspecialchars($address["city"] ?? "") ?>',
        barangay: '<?= htmlspecialchars($address["barangay"] ?? "") ?>'
    };
</script>

<script src="/js/address-data.js"></script>
<script src="/js/address-form.js"></script>