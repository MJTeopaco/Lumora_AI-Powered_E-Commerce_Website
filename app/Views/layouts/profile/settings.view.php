<?php
// app/Views/layouts/profile/settings.view.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Lumora</title>
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
            min-height: 500px;
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

        .settings-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .settings-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
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

        .form-label .required {
            color: #ef4444;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #2d5a4a;
            box-shadow: 0 0 0 3px rgba(45, 90, 74, 0.1);
        }

        .form-input.error {
            border-color: #ef4444;
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

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: #2d5a4a;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #1e4d3d;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: #ef4444;
            color: #ffffff;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .danger-zone {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 1.5rem;
        }

        .danger-zone-title {
            font-size: 16px;
            font-weight: 600;
            color: #991b1b;
            margin-bottom: 0.5rem;
        }

        .danger-zone-description {
            font-size: 14px;
            color: #7f1d1d;
            margin-bottom: 1rem;
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

    <?php if (isset($statusMessage) && $statusMessage): ?>
        <div class="alert alert-<?= htmlspecialchars($statusType) ?>">
            <strong><?= $statusType === 'success' ? '✓' : '✗' ?></strong>
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
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="sidebar-username"><?= htmlspecialchars($user['username']) ?></div>
                <div class="sidebar-email"><?= htmlspecialchars($user['email']) ?></div>
            </div>

            <nav class="sidebar-menu">
                <a href="/profile" class="menu-item">
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
                <a href="/profile/settings" class="menu-item active">
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
                <h1 class="content-title">Account Settings</h1>
                <p class="content-subtitle">Manage your account security and preferences</p>
            </div>

            <!-- Change Password Section -->
            <div class="settings-section">
                <h2 class="section-title">Change Password</h2>
                <form method="POST" action="/profile/change-password" id="passwordForm">
                    <div class="form-group">
                        <label class="form-label">
                            Current Password <span class="required">*</span>
                        </label>
                        <input 
                            type="password" 
                            name="current_password" 
                            class="form-input" 
                            required
                            placeholder="Enter your current password"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            New Password <span class="required">*</span>
                        </label>
                        <input 
                            type="password" 
                            name="new_password" 
                            id="new_password"
                            class="form-input" 
                            required
                            placeholder="Enter your new password"
                            minlength="8"
                        >
                        <p class="form-help">Password must be at least 8 characters long</p>
                        <p class="form-error" id="passwordError">Password must be at least 8 characters long</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Confirm New Password <span class="required">*</span>
                        </label>
                        <input 
                            type="password" 
                            name="confirm_password" 
                            id="confirm_password"
                            class="form-input" 
                            required
                            placeholder="Confirm your new password"
                        >
                        <p class="form-error" id="confirmError">Passwords do not match</p>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-lock"></i>
                        Update Password
                    </button>
                </form>
            </div>

            <!-- Account Information -->
            <div class="settings-section">
                <h2 class="section-title">Account Information</h2>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        class="form-input" 
                        value="<?= htmlspecialchars($user['email']) ?>"
                        disabled
                    >
                    <p class="form-help">Email cannot be changed. Contact support if you need assistance.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input 
                        type="text" 
                        class="form-input" 
                        value="<?= htmlspecialchars($user['username']) ?>"
                        disabled
                    >
                    <p class="form-help">Username cannot be changed. Contact support if you need assistance.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Member Since</label>
                    <input 
                        type="text" 
                        class="form-input" 
                        value="<?= date('F j, Y', strtotime($user['created_at'])) ?>"
                        disabled
                    >
                </div>
            </div>
                    </div>


    <script>
        const passwordForm = document.getElementById('passwordForm');
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordError = document.getElementById('passwordError');
        const confirmError = document.getElementById('confirmError');

        function validatePassword() {
            const password = newPassword.value;
            
            if (password.length < 8) {
                newPassword.classList.add('error');
                passwordError.style.display = 'block';
                return false;
            } else {
                newPassword.classList.remove('error');
                passwordError.style.display = 'none';
                return true;
            }
        }

        function validateConfirmPassword() {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.classList.add('error');
                confirmError.style.display = 'block';
                return false;
            } else {
                confirmPassword.classList.remove('error');
                confirmError.style.display = 'none';
                return true;
            }
        }

        newPassword.addEventListener('blur', validatePassword);
        confirmPassword.addEventListener('blur', validateConfirmPassword);

        passwordForm.addEventListener('submit', function(e) {
            const isPasswordValid = validatePassword();
            const isConfirmValid = validateConfirmPassword();
            
            if (!isPasswordValid || !isConfirmValid) {
                e.preventDefault();
            }
        });
    </script>
     <footer>
        <p><strong>LUMORA</strong> - Exquisite Accessories for Every Occasion</p>
        <p>© 2025 Lumora. All rights reserved.</p>
    </footer>

</body>

</html>