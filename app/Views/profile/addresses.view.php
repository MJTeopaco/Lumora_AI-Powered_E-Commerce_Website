<?php
// app/Views/profile/addresses.view.php
?>
<div class="content-header">
    <div>
        <h1 class="content-title">My Addresses</h1>
        <p class="content-subtitle">Manage your shipping and billing addresses</p>
    </div>
    <a href="/profile/addresses/add" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Add New Address
    </a>
</div>

<?php if (empty($addresses)): ?>
    <!-- Empty State -->
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-map-marked-alt"></i>
        </div>
        <h3>No addresses added yet</h3>
        <p>Add your first address to make checkout faster and easier</p>
        <a href="/profile/addresses/add" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add New Address
        </a>
    </div>
<?php else: ?>
    <!-- Addresses Grid -->
    <div class="addresses-grid">
        <?php foreach ($addresses as $address): ?>
            <div class="address-card <?= $address['is_default'] ? 'default' : '' ?>">
                <div class="address-header">
                    <div class="address-type">
                        <i class="fas fa-<?= $address['address_type'] === 'shipping' ? 'truck' : 'file-invoice-dollar' ?>"></i>
                        <?= ucfirst($address['address_type']) ?>
                        <?php if ($address['is_default']): ?>
                            <span class="default-badge">DEFAULT</span>
                        <?php endif; ?>
                    </div>
                    <div class="address-actions">
                        <button class="icon-btn" 
                                onclick="window.location.href='/profile/addresses/edit/<?= $address['address_id'] ?>'" 
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="/profile/addresses/delete" style="display: inline;" 
                              onsubmit="return confirm('Are you sure you want to delete this address?')">
                            <input type="hidden" name="address_id" value="<?= htmlspecialchars($address['address_id']) ?>">
                            <button type="submit" class="icon-btn delete" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="address-body">
                    <div class="address-line"><?= htmlspecialchars($address['address_line_1']) ?></div>
                    <?php if (!empty($address['address_line_2'])): ?>
                        <div class="address-line"><?= htmlspecialchars($address['address_line_2']) ?></div>
                    <?php endif; ?>
                    <div class="address-line">
                        <?= htmlspecialchars($address['barangay']) ?>, 
                        <?= htmlspecialchars($address['city']) ?>
                    </div>
                    <div class="address-line">
                        <?= htmlspecialchars($address['province']) ?> 
                        <?= htmlspecialchars($address['postal_code']) ?>
                    </div>
                    <div class="address-line"><?= htmlspecialchars($address['region']) ?></div>
                </div>

                <?php if (!$address['is_default']): ?>
                    <div class="address-footer">
                        <form method="POST" action="/profile/addresses/set-default" style="flex: 1;">
                            <input type="hidden" name="address_id" value="<?= htmlspecialchars($address['address_id']) ?>">
                            <button type="submit" class="btn btn-secondary btn-small" style="width: 100%;">
                                <i class="fas fa-check"></i>
                                Set as Default
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>