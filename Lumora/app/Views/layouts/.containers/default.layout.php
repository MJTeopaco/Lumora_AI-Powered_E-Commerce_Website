<?php 
// app/Views/layouts/containers/default.layout.php
$partialsPath = __DIR__ . '/../partials/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Lumora - Exquisite Accessories' ?></title>
    
    <link rel="stylesheet" href="<?= base_url('/css/new-main.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/css/home.css') ?>"> 
    
    <link rel="stylesheet" href="<?= base_url('/css/guidelines.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/css/collections.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/css/product-detail.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/css/buyer-product-detail.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/css/buyer-product-reviews.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/css/cart.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/css/checkout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/css/notifications.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/css/stores.css') ?>">
    
    <link rel="stylesheet" href="<?= base_url('/css/seller-register.css') ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script>
        const BASE_URL = "<?= rtrim(base_url(), '/') ?>";
    </script>
</head>
<body>
    <header class="header">
        <?php 
        if (file_exists($partialsPath . 'header.partial.php')) {
            include $partialsPath . 'header.partial.php';
        }
        ?>
    </header>

    <?php if (isset($statusMessage) && $statusMessage): ?>
        <div class="container" style="margin-top: 20px;">
            <div class="alert alert-<?= htmlspecialchars($statusType ?? 'info') ?>">
                <strong><?= $statusType === 'success' ? '✓' : ($statusType === 'error' ? '✗' : 'ℹ') ?></strong>
                <?= htmlspecialchars($statusMessage) ?>
            </div>
        </div>
    <?php endif; ?>

    <main>
        <?= $content ?? '' ?>
    </main>

    <footer class="footer">
        <?php 
        if (file_exists($partialsPath . 'footer.partial.php')) {
            include $partialsPath . 'footer.partial.php';
        }
        ?>
    </footer>

    <script src="<?= base_url('/js/home.js') ?>" defer></script>
    <script src="<?= base_url('/js/guidelines.js') ?>" defer></script>
    <script src="<?= base_url('/js/collections.js') ?>" defer></script>
    <script src="<?= base_url('/js/product-detail-user.js') ?>" defer></script>
    <script src="<?= base_url('/js/cart.js') ?>" defer></script>
    <script src="<?= base_url('/js/notifications.js') ?>" defer></script>
    <script src="<?= base_url('/js/stores.js') ?>" defer></script>
    
    <script src="<?= base_url('/js/seller-register.js') ?>" defer></script>
</body>
</html>