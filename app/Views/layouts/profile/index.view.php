<?php
// app/Views/profile/index.view.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Lumora</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            color: #1f2937;
        }

        .header {
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            text-decoration: none;
        }

        .back-link {
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 14px;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .back-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .alert {
            max-width: 1400px;
            margin: 1.5rem auto;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background-color: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .alert-error {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .profile-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            align-items: start;
        }

        .profile-sidebar {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: sticky;
            top: 100px;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        .sidebar-avatar-container {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
        }

        .sidebar-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 600;
            color: #ffffff;
            border: 3px solid #e5e7eb;
            overflow: hidden;
        }

        .sidebar-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidebar-username {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .sidebar-email {
            font-size: 13px;
            color: #6b7280;
        }

        .sidebar-menu {
            padding: 0.5rem 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1.5rem;
            color: #4b5563;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            cursor: pointer;
        }

        .menu-item:hover {
            background-color: #f9fafb;
            color: #1e4d3d;
        }

        .menu-item.active {
            background-color: #f0f5f3;
            color: #1e4d3d;
            border-left-color: #2d5a4a;
            font-weight: 600;
        }

        .menu-item i {
            width: 20px;
            font-size: 18px;
        }

        .menu-item span {
            font-size: 14px;
        }

        .menu-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 0.5rem 0;
        }

        .menu-item.logout {
            color: #ef4444;
        }

        .menu-item.logout:hover {
            background-color: #fef2f2;
            color: #dc2626;
        }

        .profile-content {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 2rem;
        }

        .content-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .content-title {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
        }

        .content-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .info-section {
            margin-bottom: 2rem;
        }

        .info-row {
            display: flex;
            align-items: center;
            padding: 1.25rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            width: 200px;
            font-size: 14px;
            color: #6b7280;
            flex-shrink: 0;
        }

        .info-value {
            flex: 1;
            font-size: 14px;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .info-value.empty {
            color: #9ca3af;
        }

        .profile-pic-preview {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }

        .profile-pic-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 600;
            color: #ffffff;
            border: 2px solid #e5e7eb;
        }

        .btn-edit {
            padding: 0.5rem 1.25rem;
            border: 1px solid #d1d5db;
            background-color: #ffffff;
            color: #374151;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-edit:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
        }

        .readonly-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: #f3f4f6;
            color: #6b7280;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #ffffff;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #6b7280;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .modal-close:hover {
            background-color: #f3f4f6;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: #ffffff;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #2d5a4a;
            box-shadow: 0 0 0 3px rgba(45, 90, 74, 0.1);
        }

        .form-help {
            font-size: 12px;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .form-error {
            font-size: 12px;
            color: #ef4444;
            margin-top: 0.25rem;
            display: none;
        }

        .form-input.error {
            border-color: #ef4444;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #2d5a4a;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #1e4d3d;
        }

        .btn-secondary {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #e5e7eb;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-button {
            background-color: #f3f4f6;
            color: #374151;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .file-input-button:hover {
            background-color: #e5e7eb;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-name {
            margin-top: 0.5rem;
            font-size: 12px;
            color: #6b7280;
        }

        footer {
            background-color: #1f2937;
            color: #9ca3af;
            padding: 2rem;
            text-align: center;
            margin-top: 4rem;
        }

        @media (max-width: 1024px) {
            .profile-container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .profile-sidebar {
                position: relative;
                top: 0;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .info-label {
                width: 100%;
            }

            .info-value {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 0 1rem;
            }

            .sidebar-header {
                padding: 1.5rem 1rem;
            }

            .profile-content {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="/" class="logo">LUMORA</a>
            <a href="/" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </nav>
    </header>

    <?php if (!empty($statusMessage)): ?>
        <div class="alert alert-<?= htmlspecialchars($statusType ?? 'error') ?>">
            <strong><?= ($statusType ?? 'error') === 'success' ? '✓' : '✗' ?></strong>
            <?= htmlspecialchars($statusMessage) ?>
        </div>
    <?php endif; ?>

    <div class="profile-container">
        <aside class="profile-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-avatar-container">
                    <div class="sidebar-avatar">
                        <?php if (!empty($profile['profile_pic'])): ?>
                            <img src="/<?= htmlspecialchars($profile['profile_pic']) ?>" alt="Profile Picture">
                        <?php else: ?>
                            <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="sidebar-username"><?= htmlspecialchars($user['username'] ?? 'User') ?></div>
                <div class="sidebar-email"><?= htmlspecialchars($user['email'] ?? 'email@example.com') ?></div>
            </div>

            <nav class="sidebar-menu">
                <a href="/profile" class="menu-item active">
                    <i class="fas fa-user"></i>
                    <span>Personal Information</span>
                </a>
                <a href="/profile/addresses" class="menu-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>My Addresses</span>
                </a>
                <a href="/profile/orders" class="menu-item">
                    <i class="fas fa-shopping-bag"></i>
                    <span>My Orders</span>
                </a>
                <a href="/profile/settings" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Account Settings</span>
                </a>
                
                <div class="menu-divider"></div>
                
                <form method="POST" action="/logout" style="margin: 0;">
                    <button type="submit" class="menu-item logout" style="background: none; border: none; width: 100%; text-align: left;">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </aside>

        <main class="profile-content">
            <div class="content-header">
                <h1 class="content-title">Personal Information</h1>
                <p class="content-subtitle">Manage your personal information</p>
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
                                // Format phone number inline: 09XX XXX XXXX
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
        </main>
    </div>

    <div id="pictureModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Change Profile Picture</h2>
                <button class="modal-close" onclick="closeModal('pictureModal')">&times;</button>
            </div>
            <form method="POST" action="/profile/update" enctype="multipart/form-data">
                <input type="hidden" name="update_field" value="picture">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Select Photo</label>
                        <div class="file-input-wrapper">
                            <label for="profile_pic" class="file-input-button">
                                <i class="fas fa-upload"></i>
                                <span>Choose Photo</span>
                            </label>
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/*" onchange="previewImage(this)">
                        </div>
                        <p class="file-name" id="fileName">No file chosen</p>
                        <p class="form-help">JPG, PNG, GIF or WebP. Max 5MB. Recommended: 400x400px</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('pictureModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="nameModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Full Name</h2>
                <button class="modal-close" onclick="closeModal('nameModal')">&times;</button>
            </div>
            <form method="POST" action="/profile/update">
                <input type="hidden" name="update_field" value="name">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-input" value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>" placeholder="Enter your full name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('nameModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="phoneModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Phone Number</h2>
                <button class="modal-close" onclick="closeModal('phoneModal')">&times;</button>
            </div>
            <form method="POST" action="/profile/update" id="phoneForm">
                <input type="hidden" name="update_field" value="phone">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" id="phone_number" name="phone_number" class="form-input" value="<?= htmlspecialchars($profile['phone_number'] ?? '') ?>" placeholder="0912 345 6789" maxlength="13">
                        <p class="form-error" id="phoneError">Please enter a valid mobile number</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('phoneModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="genderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Gender</h2>
                <button class="modal-close" onclick="closeModal('genderModal')">&times;</button>
            </div>
            <form method="POST" action="/profile/update">
                <input type="hidden" name="update_field" value="gender">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select Gender</option>
                            <option value="Male" <?= ($profile['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($profile['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($profile['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                            <option value="Prefer not to say" <?= ($profile['gender'] ?? '') === 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('genderModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="birthModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Birth Date</h2>
                <button class="modal-close" onclick="closeModal('birthModal')">&times;</button>
            </div>
            <form method="POST" action="/profile/update">
                <input type="hidden" name="update_field" value="birthdate">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Birth Date</label>
                        <input type="date" name="birth_date" class="form-input" value="<?= htmlspecialchars($profile['birth_date'] ?? '') ?>" max="<?= date('Y-m-d', strtotime('-13 years')) ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('birthModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p><strong>LUMORA</strong> - Exquisite Accessories for Every Occasion</p>
        <p>© 2025 Lumora. All rights reserved.</p>
    </footer>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal(this.id);
                }
            });
        });

        // Phone number validation
        const phoneInput = document.getElementById('phone_number');
        const phoneError = document.getElementById('phoneError');
        const phoneForm = document.getElementById('phoneForm');

        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length === 1 && value === '9') {
                    value = '0' + value;
                    e.target.value = value;
                } else {
                    e.target.value = value;
                }
                
                if (value.length > 11) {
                    e.target.value = value.slice(0, 11);
                }
            });

            phoneInput.addEventListener('blur', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value === '') {
                    return;
                }
                
                if (value.length === 10 && value.startsWith('9')) {
                    value = '0' + value;
                }
                
                if (value.length === 11 && value.startsWith('09')) {
                    e.target.value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7, 11);
                }
                
                validatePhone();
            });

            phoneInput.addEventListener('focus', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                e.target.value = value;
            });
        }

        function validatePhone() {
            if (!phoneInput) return true;
            
            const value = phoneInput.value.replace(/\D/g, '');
            
            if (value === '') {
                phoneInput.classList.remove('error');
                phoneError.style.display = 'none';
                return true;
            }
            
            if (value.length === 11 && value.startsWith('09')) {
                phoneInput.classList.remove('error');
                phoneError.style.display = 'none';
                return true;
            } else {
                phoneInput.classList.add('error');
                phoneError.style.display = 'block';
                return false;
            }
        }

        if (phoneForm) {
            phoneForm.addEventListener('submit', function(e) {
                // Remove spaces before submitting
                let value = phoneInput.value.replace(/\D/g, '');
                
                // Validate first
                if (value && (value.length !== 11 || !value.startsWith('09'))) {
                    e.preventDefault();
                    phoneInput.focus();
                    phoneInput.classList.add('error');
                    phoneError.style.display = 'block';
                    return false;
                }
                
                // Set the clean value without spaces for submission
                phoneInput.value = value;
            });
        }
        
        window.addEventListener('DOMContentLoaded', function() {
            if (phoneInput && phoneInput.value) {
                let value = phoneInput.value.replace(/\D/g, '');
                
                if (value.length === 10 && value.startsWith('9')) {
                    value = '0' + value;
                }
                
                if (value.length === 11 && value.startsWith('09')) {
                    phoneInput.value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7, 11);
                }
            }
        });

        function previewImage(input) {
            const fileName = document.getElementById('fileName');
            
            if (input.files && input.files[0]) {
                fileName.textContent = input.files[0].name;
            } else {
                fileName.textContent = 'No file chosen';
            }
        }
    </script>
</body>
</html>