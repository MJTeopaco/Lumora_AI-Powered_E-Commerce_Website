<?php 
// app/Views/layouts/.containers/default.layout.php
$partialsPath = __DIR__ . '/../partials/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Lumora - Exquisite Accessories' ?></title>
    <link rel="stylesheet" href="/css/new-main.css">
    <link rel="stylesheet" href="/css/home.css">
    <link rel="stylesheet" href="/css/guidelines.css">
    <link rel="stylesheet" href="/css/collections.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header class="header">
        <?php include $partialsPath . 'header.partial.php'; ?>
    </header>

    <?php if (isset($statusMessage) && $statusMessage): ?>
        <div class="alert alert-<?= htmlspecialchars($statusType ?? 'info') ?>">
            <strong><?= $statusType === 'success' ? '✓' : ($statusType === 'error' ? '✗' : 'ℹ') ?></strong>
            <?= htmlspecialchars($statusMessage) ?>
        </div>
    <?php endif; ?>

    <?= $content ?? '' ?> 

    <footer class="footer">
        <?php include $partialsPath . 'footer.partial.php'; ?>
    </footer>
</body>

<!-- Link to JavaScript -->
<script src="/js/home.js" defer></script>
<script src="/js/guidelines.js" defer></script>
<script src="/js/collections.js" defer></script>

</html>