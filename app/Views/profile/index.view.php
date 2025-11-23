<?php
// app/Views/profile/index.view.php
?>
<div class="content-header">
    <div>
        <h1 class="content-title">Personal Information</h1>
        <p class="content-subtitle">Manage your personal information</p>
    </div>
</div>

<div class="info-section">
    <div class="info-row">
        <div class="info-label">Profile Picture</div>
        <div class="info-value">
            <?php if (!empty($profile['profile_pic'])): ?>
                <img src="/<?= htmlspecialchars($profile['profile_pic']) ?>" alt="Profile" class="profile-pic-preview">
            <?php else: ?>
                <div class="profile-pic-placeholder">
                    <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                </div>
            <?php endif; ?>
            <button class="btn-edit" onclick="openModal('pictureModal')">
                <i class="fas fa-camera"></i>
                Change Photo
            </button>
        </div>
    </div>

    <div class="info-row">
        <div class="info-label">Full Name</div>
        <div class="info-value <?= empty($profile['full_name']) ? 'empty' : '' ?>">
            <span><?= !empty($profile['full_name']) ? htmlspecialchars($profile['full_name']) : 'Not set' ?></span>
            <button class="btn-edit" onclick="openModal('nameModal')">
                <i class="fas fa-edit"></i>
                Edit
            </button>
        </div>
    </div>

    <div class="info-row">
        <div class="info-label">Phone Number</div>
        <div class="info-value <?= empty($profile['phone_number']) ? 'empty' : '' ?>">
            <span>
                <?php 
                    if (!empty($profile['phone_number'])) {
                        echo htmlspecialchars(preg_replace('/^(\d{4})(\d{3})(\d{4})$/', '$1 $2 $3', $profile['phone_number']));
                    } else {
                        echo 'Not set';
                    }
                ?>
            </span>
            <button class="btn-edit" onclick="openModal('phoneModal')">
                <i class="fas fa-edit"></i>
                Edit
            </button>
        </div>
    </div>

    <div class="info-row">
        <div class="info-label">Email</div>
        <div class="info-value">
            <span><?= htmlspecialchars($user['email'] ?? 'email@example.com') ?></span>
            <span class="readonly-badge">Cannot be changed</span>
        </div>
    </div>

    <div class="info-row">
        <div class="info-label">Username</div>
        <div class="info-value">
            <span><?= htmlspecialchars($user['username'] ?? 'User') ?></span>
            <span class="readonly-badge">Cannot be changed</span>
        </div>
    </div>

    <div class="info-row">
        <div class="info-label">Gender</div>
        <div class="info-value <?= empty($profile['gender']) ? 'empty' : '' ?>">
            <span><?= !empty($profile['gender']) ? htmlspecialchars($profile['gender']) : 'Not set' ?></span>
            <button class="btn-edit" onclick="openModal('genderModal')">
                <i class="fas fa-edit"></i>
                Edit
            </button>
        </div>
    </div>

    <div class="info-row">
        <div class="info-label">Birth Date</div>
        <div class="info-value <?= empty($profile['birth_date']) ? 'empty' : '' ?>">
            <span><?= !empty($profile['birth_date']) ? date('F d, Y', strtotime($profile['birth_date'])) : 'Not set' ?></span>
            <button class="btn-edit" onclick="openModal('birthModal')">
                <i class="fas fa-edit"></i>
                Edit
            </button>
        </div>
    </div>
</div>

<!-- Modals -->
<?php include __DIR__ . '/../layouts/partials/profile-modals.partial.php'; ?>

<script src="/js/profile-index.js"></script>