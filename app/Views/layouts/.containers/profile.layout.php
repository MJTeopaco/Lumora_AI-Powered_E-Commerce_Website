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
    <link rel="stylesheet" href="/css/profile.css">
<link rel="stylesheet" href="/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Header -->

    <header class="header">
        <nav class="nav-container">
            <a href="/" class="logo">LUMORA</a>
            <a href="/" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </nav>
    </header>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <strong>✓</strong>
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <strong>✗</strong>
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Profile Container -->
    <div class="profile-container">
        <!-- Sidebar -->
        <aside class="profile-sidebar">
            <?php include $partialsPath . 'profile-sidebar.partial.php'; ?>
        </aside>

        <!-- Main Content -->
        <main class="profile-content">
            <?= $content ?? '' ?>
        </main>
    </div>

    <!-- Footer -->
    <?php include $partialsPath . 'footer.partial.php'; ?>

    <!-- Common Scripts -->
    <script src="/js/profile-common.js"></script>
</body>
</html>