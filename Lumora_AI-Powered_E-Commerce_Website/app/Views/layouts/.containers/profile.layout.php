<?php
// app/Views/layouts/.containers/profile.layout.php
$partialsPath = __DIR__ . '/../partials/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'My Profile - Lumora' ?></title>
    
    <link rel="stylesheet" href="<?= base_url('/css/user-profile.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/css/profile-orders.css') ?>">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Simple Profile Header - White background to match main page */
        .profile-simple-header {
            background: #ffffff;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .profile-simple-header-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .profile-simple-logo {
            color: #000;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .profile-simple-logo:hover {
            transform: scale(1.05);
        }

        .profile-back-btn {
            color: #1A1A1A;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 14px;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            background: #f5f5f5;
            transition: all 0.3s ease;
        }

        .profile-back-btn:hover {
            background: #e5e5e5;
            transform: translateX(-3px);
        }

        /* Footer Styles */
        .profile-page-footer {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            color: #ffffff;
            padding: 3rem 2rem 1.5rem;
            margin-top: 4rem;
        }

        .profile-footer-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .profile-footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .profile-footer-section h4 {
            color: #D4AF37;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 1rem;
            letter-spacing: 1px;
        }

        .profile-footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .profile-footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .profile-footer-section ul li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .profile-footer-section ul li a:hover {
            color: #D4AF37;
        }

        .profile-footer-section p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            line-height: 1.6;
        }

        .profile-footer-social {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .profile-footer-social a {
            color: rgba(255, 255, 255, 0.8);
            font-size: 20px;
            transition: color 0.3s;
        }

        .profile-footer-social a:hover {
            color: #D4AF37;
        }

        .profile-footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1.5rem;
            text-align: center;
        }

        .profile-footer-bottom p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            margin: 0;
        }

        .profile-footer-logo {
            color: #D4AF37;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 1rem;
            display: block;
        }
    </style>
    
    <script>
        const BASE_URL = "<?= rtrim(base_url(), '/') ?>";
    </script>
</head>
<body>

    <!-- Simple Profile Header - White background with gold logo -->
    <header class="profile-simple-header">
        <div class="profile-simple-header-container">
            <a href="<?= base_url('/') ?>" class="profile-simple-logo">LUMORA</a>
            <a href="<?= base_url('/') ?>" class="profile-back-btn">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </header>

    <!-- Alert Messages -->
    <?php if (isset($statusMessage) && $statusMessage): ?>
        <div class="container" style="margin-top: 20px;">
            <div class="alert alert-<?= htmlspecialchars($statusType ?? 'success') ?>">
                <strong><?= $statusType === 'success' ? '✓' : ($statusType === 'error' ? '✗' : 'ℹ') ?></strong>
                <?= htmlspecialchars($statusMessage) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Profile Container -->
    <div class="profile-container">
        <!-- Sidebar -->
        <aside class="profile-sidebar">
            <?php 
            if (file_exists($partialsPath . 'profile-sidebar.partial.php')) {
                include $partialsPath . 'profile-sidebar.partial.php';
            }
            ?>
        </aside>

        <!-- Main Content -->
        <main class="profile-content">
            <?= $content ?? '' ?>
        </main>
    </div>

    <!-- Footer matching main page style -->
    <footer class="profile-page-footer">
        <div class="profile-footer-container">
            <div class="profile-footer-grid">
                <!-- About Section -->
                <div class="profile-footer-section">
                    <span class="profile-footer-logo">LUMORA</span>
                    <p>Elevate Your Style with Exquisite Accessories</p>
                    <div class="profile-footer-social">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="profile-footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?= base_url('/') ?>">Home</a></li>
                        <li><a href="<?= base_url('/shop') ?>">Shop</a></li>
                        <li><a href="<?= base_url('/about') ?>">About Us</a></li>
                        <li><a href="<?= base_url('/contact') ?>">Contact</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div class="profile-footer-section">
                    <h4>Customer Service</h4>
                    <ul>
                        <li><a href="<?= base_url('/profile') ?>">My Account</a></li>
                        <li><a href="<?= base_url('/profile/orders') ?>">Order Tracking</a></li>
                        <li><a href="<?= base_url('/help') ?>">Help & FAQs</a></li>
                        <li><a href="<?= base_url('/returns') ?>">Returns</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="profile-footer-section">
                    <h4>Contact Us</h4>
                    <ul>
                        <li><a href="mailto:support@lumora.com"><i class="fas fa-envelope"></i> support@lumora.com</a></li>
                        <li><a href="tel:+1234567890"><i class="fas fa-phone"></i> +1 (234) 567-890</a></li>
                        <li><i class="fas fa-map-marker-alt"></i> Metro Manila, Philippines</li>
                    </ul>
                </div>
            </div>

            <div class="profile-footer-bottom">
                <p>&copy; <?= date('Y') ?> Lumora. All rights reserved. | <a href="<?= base_url('/privacy') ?>" style="color: rgba(255,255,255,0.6);">Privacy Policy</a> | <a href="<?= base_url('/terms') ?>" style="color: rgba(255,255,255,0.6);">Terms & Conditions</a></p>
            </div>
        </div>
    </footer>

    <!-- Common Scripts -->
    <script src="<?= base_url('/js/profile-common.js') ?>"></script>
</body>
</html>