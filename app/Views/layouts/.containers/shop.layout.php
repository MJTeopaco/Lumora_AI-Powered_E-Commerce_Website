<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Shop Dashboard' ?> - Lumora</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/shop.css">
    <link rel="stylesheet" href="/css/product-management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/shop-header.partial.php'; ?>
    
    <div class="shop-layout">
        <?php include __DIR__ . '/../partials/shop-sidebar.partial.php'; ?>
        
        <main class="shop-main">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?= $content ?>
        </main>
    </div>
    
   
    
    <script defer src="/js/shop.js"></script>
    <script defer src="/js/product-management.js"></script>
</body>
</html>