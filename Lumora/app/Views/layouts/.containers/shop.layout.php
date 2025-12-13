<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Shop Dashboard' ?> - Lumora</title>

    <link rel="stylesheet" href="/css/shop.css">
    <link rel="stylesheet" href="/css/product-management.css">
    <link rel="stylesheet" href="/css/shop-profile.css">
    <link rel="stylesheet" href="/css/notifications.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/shop-header.partial.php'; ?>
    
    <div class="shop-layout">
        <?php include __DIR__ . '/../partials/shop-sidebar.partial.php'; ?>
        
        <main class="shop-main">
            <?= $content ?>
        </main>
    </div>
    
    <script defer src="/js/shop.js"></script>
    <script defer src="/js/product-management.js"></script>
    <script defer src="/js/shop-profile.js"></script>
    <script defer src="/js/add-product.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to create the floating toast
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            toast.innerHTML = `
                <i class="fas ${icon}"></i>
                <span>${message}</span>
            `;

            document.body.appendChild(toast);

            // Animate In
            setTimeout(() => toast.classList.add('show'), 10);

            // Animate Out & Remove after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Check for PHP session messages and trigger the toast
        <?php if (isset($_SESSION['success'])): ?>
            showToast(<?= json_encode($_SESSION['success']) ?>, 'success');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            showToast(<?= json_encode($_SESSION['error']) ?>, 'error');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>