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
                        
                        <button type="button" 
                                class="icon-btn delete" 
                                onclick="confirmDeleteAddress('<?= $address['address_id'] ?>')"
                                title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
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
                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::get('csrf_token') ?>">
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

<div id="deleteAddressModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Delete Address</h2>
            <button class="modal-close" onclick="closeModal('deleteAddressModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this address? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <form id="deleteAddressForm" method="POST" action="/profile/addresses/delete">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::get('csrf_token') ?>">
                <input type="hidden" name="address_id" id="deleteAddressId">
                
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteAddressModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background-color: #DC3545; border-color: #DC3545;">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDeleteAddress(addressId) {
        document.getElementById('deleteAddressId').value = addressId;
        const modal = document.getElementById('deleteAddressModal');
        modal.classList.add('active');
    }

    if (typeof closeModal === 'undefined') {
        window.closeModal = function(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
    }

    window.onclick = function(event) {
        const modal = document.getElementById('deleteAddressModal');
        if (event.target === modal) {
            modal.classList.remove('active');
        }
    }
</script>