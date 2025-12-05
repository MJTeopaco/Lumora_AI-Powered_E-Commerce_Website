<?php
// app/Views/admin/sellers.view.php
?>

<input type="hidden" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<div class="content-card">
    <h2>
        <span><i class="fas fa-clock"></i> Pending Seller Applications</span>
        <span class="badge badge-warning"><?= count($pending_sellers) ?></span>
    </h2>

    <?php if (empty($pending_sellers)): ?>
        <div class="empty-state">
            <i class="fas fa-check-circle"></i>
            <p>No pending applications. All sellers have been reviewed!</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Shop Name</th>
                        <th>Owner</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Applied Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_sellers as $seller): ?>
                    <tr>
                        <td>
                            <div class="shop-info">
                                <strong><?= htmlspecialchars($seller['shop_name']) ?></strong>
                                <small class="text-muted"><?= htmlspecialchars($seller['slug']) ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="user-info-cell">
                                <strong><?= htmlspecialchars($seller['username']) ?></strong>
                                <small><?= htmlspecialchars($seller['email']) ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($seller['contact_email']) ?></div>
                                <div><i class="fas fa-phone"></i> <?= htmlspecialchars($seller['contact_phone']) ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="location-info">
                                <?= htmlspecialchars($seller['city'] ?? 'N/A') ?>, 
                                <?= htmlspecialchars($seller['region'] ?? 'N/A') ?>
                            </div>
                        </td>
                        <td>
                            <span class="date-badge">
                                <?= date('M d, Y', strtotime($seller['applied_at'])) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-info btn-sm" 
                                    onclick='viewSellerDetails(<?= json_encode($seller) ?>)'>
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn btn-success btn-sm" 
                                    onclick="approveSeller(<?= $seller['user_id'] ?>, '<?= htmlspecialchars($seller['shop_name']) ?>')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn btn-danger btn-sm" 
                                    onclick="rejectSeller(<?= $seller['user_id'] ?>, '<?= htmlspecialchars($seller['shop_name']) ?>')">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="content-card" style="margin-top: 30px;">
    <h2>
        <span><i class="fas fa-check-circle"></i> Approved Sellers</span>
        <span class="badge badge-success"><?= count($approved_sellers) ?></span>
    </h2>

    <?php if (empty($approved_sellers)): ?>
        <div class="empty-state">
            <i class="fas fa-store"></i>
            <p>No approved sellers yet. Start approving applications!</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Shop Name</th>
                        <th>Owner</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Approved Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approved_sellers as $seller): ?>
                    <tr>
                        <td>
                            <div class="shop-info">
                                <strong><?= htmlspecialchars($seller['shop_name']) ?></strong>
                                <small class="text-muted"><?= htmlspecialchars($seller['slug']) ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="user-info-cell">
                                <strong><?= htmlspecialchars($seller['username']) ?></strong>
                                <small><?= htmlspecialchars($seller['email']) ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($seller['contact_email']) ?></div>
                                <div><i class="fas fa-phone"></i> <?= htmlspecialchars($seller['contact_phone']) ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="location-info">
                                <?= htmlspecialchars($seller['city'] ?? 'N/A') ?>, 
                                <?= htmlspecialchars($seller['region'] ?? 'N/A') ?>
                            </div>
                        </td>
                        <td>
                            <span class="date-badge">
                                <?= date('M d, Y', strtotime($seller['approved_at'])) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-info btn-sm" 
                                    onclick='viewSellerDetails(<?= json_encode($seller) ?>)'>
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn btn-warning btn-sm" 
                                    onclick="suspendSeller(<?= $seller['user_id'] ?>, '<?= htmlspecialchars($seller['shop_name']) ?>')">
                                    <i class="fas fa-ban"></i> Suspend
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div id="sellerDetailsModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3><i class="fas fa-store"></i> Seller Details</h3>
            <button class="close-modal" onclick="closeDetailsModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="details-grid">
                <div class="details-section">
                    <h4><i class="fas fa-store-alt"></i> Shop Information</h4>
                    <div class="details-content">
                        <div class="detail-item">
                            <label>Shop Name:</label>
                            <span id="detail_shop_name"></span>
                        </div>
                        <div class="detail-item">
                            <label>Shop Slug:</label>
                            <span id="detail_slug" class="code-text"></span>
                        </div>
                        <div class="detail-item">
                            <label>Description:</label>
                            <p id="detail_description"></p>
                        </div>
                    </div>
                </div>

                <div class="details-section">
                    <h4><i class="fas fa-user"></i> Owner Information</h4>
                    <div class="details-content">
                        <div class="detail-item">
                            <label>Username:</label>
                            <span id="detail_username"></span>
                        </div>
                        <div class="detail-item">
                            <label>Email:</label>
                            <span id="detail_email"></span>
                        </div>
                        <div class="detail-item">
                            <label>User ID:</label>
                            <span id="detail_user_id"></span>
                        </div>
                    </div>
                </div>

                <div class="details-section">
                    <h4><i class="fas fa-address-card"></i> Contact Information</h4>
                    <div class="details-content">
                        <div class="detail-item">
                            <label>Contact Email:</label>
                            <span id="detail_contact_email"></span>
                        </div>
                        <div class="detail-item">
                            <label>Contact Phone:</label>
                            <span id="detail_contact_phone"></span>
                        </div>
                    </div>
                </div>

                <div class="details-section">
                    <h4><i class="fas fa-map-marker-alt"></i> Shop Address</h4>
                    <div class="details-content">
                        <div class="detail-item">
                            <label>Street Address:</label>
                            <span id="detail_address"></span>
                        </div>
                        <div class="detail-item">
                            <label>Barangay:</label>
                            <span id="detail_barangay"></span>
                        </div>
                        <div class="detail-item">
                            <label>City:</label>
                            <span id="detail_city"></span>
                        </div>
                        <div class="detail-item">
                            <label>Province:</label>
                            <span id="detail_province"></span>
                        </div>
                        <div class="detail-item">
                            <label>Region:</label>
                            <span id="detail_region"></span>
                        </div>
                        <div class="detail-item">
                            <label>Postal Code:</label>
                            <span id="detail_postal_code"></span>
                        </div>
                    </div>
                </div>

                <div class="details-section full-width">
                    <h4><i class="fas fa-clock"></i> Timeline</h4>
                    <div class="details-content">
                        <div class="detail-item">
                            <label>Applied Date:</label>
                            <span id="detail_applied_date"></span>
                        </div>
                        <div class="detail-item" id="approved_date_section" style="display: none;">
                            <label>Approved Date:</label>
                            <span id="detail_approved_date"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeDetailsModal()">Close</button>
            <div id="modal_action_buttons"></div>
        </div>
    </div>
</div>

<script src="/js/admin-sellers.js"></script>