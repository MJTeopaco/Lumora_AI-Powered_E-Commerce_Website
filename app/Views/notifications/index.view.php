<?php
// app/Views/notifications/index.view.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Notifications - Lumora' ?></title>
    
    <link rel="stylesheet" href="<?= base_url('/css/main.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('/css/notifications.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .notifications-page-container {
            max-width: 1200px;
            width: 95%;
            margin: 40px auto !important; /* Forces centering */
            padding: 0 15px;
            min-height: 60vh;
            display: block; /* Ensures it takes up space */
        }

        .notification-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Ensure header doesn't overlap */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* Fix for buttons */
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-outline {
            border: 1px solid #D4AF37;
            color: #D4AF37;
            background: transparent;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-outline:hover {
            background: #D4AF37;
            color: white;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/partials/header.partial.php'; ?>

    <div class="notifications-page-container">
        <div class="content-header">
            <div class="header-text">
                <h1 class="content-title">
                    <i class="fas fa-bell"></i>
                    Notifications
                </h1>
                <p class="content-subtitle">Stay updated with your latest activities</p>
            </div>
            <div class="header-actions">
                <button id="markAllReadBtn" class="btn btn-outline" <?= ($counts['unread'] ?? 0) == 0 ? 'disabled' : '' ?>>
                    <i class="fas fa-check-double"></i> Mark all as read
                </button>
                <button id="deleteAllReadBtn" class="btn btn-outline-danger" style="border: 1px solid #dc3545; color: #dc3545; background: transparent; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-trash-alt"></i> Clear read
                </button>
            </div>
        </div>

        <div class="notification-stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(212, 175, 55, 0.1);">
                    <i class="fas fa-bell" style="color: #D4AF37;"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $counts['total'] ?? 0 ?></h3>
                    <p>Total</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(33, 150, 243, 0.1);">
                    <i class="fas fa-envelope" style="color: #2196F3;"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $counts['unread'] ?? 0 ?></h3>
                    <p>Unread</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(76, 175, 80, 0.1);">
                    <i class="fas fa-shopping-bag" style="color: #4CAF50;"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $counts['orders'] ?? 0 ?></h3>
                    <p>Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(255, 152, 0, 0.1);">
                    <i class="fas fa-star" style="color: #FF9800;"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $counts['reviews'] ?? 0 ?></h3>
                    <p>Reviews</p>
                </div>
            </div>
        </div>

        <div class="notifications-container">
            <div class="notification-tabs">
                <button class="tab-btn <?= ($currentFilter ?? 'all') === 'all' ? 'active' : '' ?>" data-filter="all">
                    <i class="fas fa-th-list"></i> All
                    <span class="tab-count"><?= $counts['total'] ?? 0 ?></span>
                </button>
                <button class="tab-btn <?= ($currentFilter ?? 'all') === 'unread' ? 'active' : '' ?>" data-filter="unread">
                    <i class="fas fa-envelope"></i> Unread
                    <span class="tab-count"><?= $counts['unread'] ?? 0 ?></span>
                </button>
                <button class="tab-btn <?= ($currentFilter ?? 'all') === 'order_placed' ? 'active' : '' ?>" data-filter="order_placed">
                    <i class="fas fa-shopping-bag"></i> Orders
                    <span class="tab-count"><?= $counts['orders'] ?? 0 ?></span>
                </button>
                <button class="tab-btn <?= ($currentFilter ?? 'all') === 'review_new' ? 'active' : '' ?>" data-filter="review_new">
                    <i class="fas fa-star"></i> Reviews
                    <span class="tab-count"><?= $counts['reviews'] ?? 0 ?></span>
                </button>
            </div>

            <div class="notifications-list" id="notificationsList">
                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="far fa-bell-slash"></i>
                        </div>
                        <h3>No notifications yet</h3>
                        <p>We'll let you know when something important happens.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <?php
                            // Determine icon and color based on type
                            $iconMap = [
                                'order_placed' => ['icon' => 'fa-shopping-bag', 'bg' => 'bg-primary'],
                                'order_confirmed' => ['icon' => 'fa-check-circle', 'bg' => 'bg-success'],
                                'order_processing' => ['icon' => 'fa-cog', 'bg' => 'bg-info'],
                                'order_shipped' => ['icon' => 'fa-shipping-fast', 'bg' => 'bg-warning'],
                                'order_delivered' => ['icon' => 'fa-box-open', 'bg' => 'bg-success'],
                                'order_cancelled' => ['icon' => 'fa-times-circle', 'bg' => 'bg-danger'],
                                'payment_success' => ['icon' => 'fa-check-circle', 'bg' => 'bg-success'],
                                'payment_failed' => ['icon' => 'fa-exclamation-triangle', 'bg' => 'bg-danger'],
                                'review_new' => ['icon' => 'fa-star', 'bg' => 'bg-gold'],
                                'review_response' => ['icon' => 'fa-reply', 'bg' => 'bg-info'],
                                'low_stock' => ['icon' => 'fa-exclamation-circle', 'bg' => 'bg-warning'],
                                'shop_approved' => ['icon' => 'fa-store', 'bg' => 'bg-success'],
                                'welcome' => ['icon' => 'fa-hand-sparkles', 'bg' => 'bg-gold'],
                                'promotion' => ['icon' => 'fa-tag', 'bg' => 'bg-danger'],
                                'system' => ['icon' => 'fa-info-circle', 'bg' => 'bg-info']
                            ];
                            
                            $iconData = $iconMap[$notif['type']] ?? ['icon' => 'fa-bell', 'bg' => 'bg-primary'];
                        ?>
                        <div class="notification-item <?= $notif['is_read'] ? 'read' : 'unread' ?>" 
                             data-id="<?= $notif['notification_id'] ?>"
                             data-read="<?= $notif['is_read'] ?>"
                             data-type="<?= $notif['type'] ?>">
                            
                            <div class="notif-icon-wrapper">
                                <div class="notif-icon <?= $iconData['bg'] ?>">
                                    <i class="fas <?= $iconData['icon'] ?>"></i>
                                </div>
                            </div>

                            <div class="notif-content">
                                <div class="notif-header">
                                    <h4 class="notif-title"><?= htmlspecialchars($notif['title']) ?></h4>
                                    <div class="notif-meta">
                                        <span class="notif-time" data-time="<?= $notif['created_at'] ?>">
                                            <?= timeAgo($notif['created_at']) ?>
                                        </span>
                                    </div>
                                </div>
                                <p class="notif-message"><?= htmlspecialchars($notif['message']) ?></p>
                                
                                <?php if ($notif['reference_id']): ?>
                                    <div class="notif-actions">
                                        <?php if (strpos($notif['type'], 'order') !== false): ?>
                                            <a href="<?= base_url('/profile/orders/details?order_id=' . $notif['reference_id']) ?>" class="notif-action-btn">
                                                <i class="fas fa-eye"></i> View Order
                                            </a>
                                        <?php elseif (strpos($notif['type'], 'review') !== false): ?>
                                            <a href="<?= base_url('/shop/reviews') ?>" class="notif-action-btn">
                                                <i class="fas fa-reply"></i> View Review
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="notif-controls">
                                <?php if (!$notif['is_read']): ?>
                                    <div class="notif-status">
                                        <span class="status-dot"></span>
                                    </div>
                                <?php endif; ?>
                                <button class="notif-delete-btn" onclick="deleteNotification(<?= $notif['notification_id'] ?>)" title="Delete">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../layouts/partials/footer.partial.php'; ?>

    <input type="hidden" id="csrfToken" value="<?= \App\Core\Session::get('csrf_token') ?>">

    <script src="<?= base_url('/js/notifications.js') ?>?v=<?= time() ?>"></script>
    
    <script>
    // Time ago function
    function timeAgo(datetime) {
        const timestamp = new Date(datetime).getTime();
        const now = new Date().getTime();
        const diff = Math.floor((now - timestamp) / 1000);
        
        if (diff < 60) return 'Just now';
        if (diff < 3600) {
            const mins = Math.floor(diff / 60);
            return mins + ' minute' + (mins > 1 ? 's' : '') + ' ago';
        }
        if (diff < 86400) {
            const hours = Math.floor(diff / 3600);
            return hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
        }
        if (diff < 604800) {
            const days = Math.floor(diff / 86400);
            return days + ' day' + (days > 1 ? 's' : '') + ' ago';
        }
        
        const date = new Date(datetime);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    // Update all time displays
    function updateTimestamps() {
        document.querySelectorAll('.notif-time').forEach(el => {
            const time = el.getAttribute('data-time');
            if (time) {
                el.textContent = timeAgo(time);
            }
        });
    }

    // Update timestamps every minute
    updateTimestamps();
    setInterval(updateTimestamps, 60000);

    // Filter tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            window.location.href = '<?= base_url('/notifications') ?>?filter=' + filter;
        });
    });
    </script>
</body>
</html>

<?php
// Helper function for time ago (PHP version for initial render)
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    }
    if ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    }
    if ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
    
    return date('M j, Y', $timestamp);
}
?>