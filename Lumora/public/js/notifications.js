// public/js/notifications.js - ENHANCED VERSION
// Lumora Notifications System

(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        pollInterval: 30000, // Poll every 30 seconds for new notifications
        baseUrl: window.BASE_URL || '',
        csrfToken: document.getElementById('csrfToken')?.value || ''
    };

    // State
    let isPolling = false;
    let pollTimer = null;

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        initializeEventListeners();
        startPolling();
    });

    /**
     * Initialize all event listeners
     */
    function initializeEventListeners() {
        // Mark all as read button
        const markAllBtn = document.getElementById('markAllReadBtn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', markAllAsRead);
        }

        // Delete all read button
        const deleteAllBtn = document.getElementById('deleteAllReadBtn');
        if (deleteAllBtn) {
            deleteAllBtn.addEventListener('click', deleteAllRead);
        }

        // Individual notification clicks (mark as read)
        document.querySelectorAll('.notification-item.unread').forEach(item => {
            item.addEventListener('click', function(e) {
                // Don't trigger if clicking delete button or action button
                if (e.target.closest('.notif-delete-btn') || e.target.closest('.notif-action-btn')) {
                    return;
                }
                
                const notificationId = this.getAttribute('data-id');
                markAsRead(notificationId, this);
            });
        });
    }

    /**
     * Mark all notifications as read
     */
    function markAllAsRead() {
        if (!confirm('Mark all notifications as read?')) {
            return;
        }

        const button = document.getElementById('markAllReadBtn');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Marking...';

        fetch(`${CONFIG.baseUrl}/notifications/mark-all-read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                csrf_token: CONFIG.csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    item.classList.add('read');
                    const statusDot = item.querySelector('.status-dot');
                    if (statusDot) {
                        statusDot.parentElement.remove();
                    }
                });

                // Update counts
                updateNotificationCounts(0);
                
                showToast(data.message || 'All notifications marked as read', 'success');
                
                // Disable button
                button.disabled = true;
            } else {
                showToast(data.message || 'Failed to mark notifications as read', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }

    /**
     * Mark single notification as read
     */
    function markAsRead(notificationId, element) {
        if (!element.classList.contains('unread')) {
            return; // Already read
        }

        fetch(`${CONFIG.baseUrl}/notifications/mark-read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                csrf_token: CONFIG.csrfToken,
                notification_id: notificationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.classList.remove('unread');
                element.classList.add('read');
                
                const statusDot = element.querySelector('.status-dot');
                if (statusDot) {
                    statusDot.parentElement.remove();
                }

                updateNotificationCounts(data.unreadCount);
            }
        })
        .catch(error => {
            console.error('Error marking as read:', error);
        });
    }

    /**
     * Delete single notification
     */
    window.deleteNotification = function(notificationId) {
        if (!confirm('Delete this notification?')) {
            return;
        }

        const notifElement = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
        
        fetch(`${CONFIG.baseUrl}/notifications/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                csrf_token: CONFIG.csrfToken,
                notification_id: notificationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Animate out
                notifElement.style.opacity = '0';
                notifElement.style.transform = 'translateX(100%)';
                
                setTimeout(() => {
                    notifElement.remove();
                    
                    // Check if list is empty
                    const list = document.getElementById('notificationsList');
                    if (list && list.querySelectorAll('.notification-item').length === 0) {
                        showEmptyState();
                    }
                }, 300);

                updateNotificationCounts(data.unreadCount);
                showToast(data.message || 'Notification deleted', 'success');
            } else {
                showToast(data.message || 'Failed to delete notification', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    };

    /**
     * Delete all read notifications
     */
    function deleteAllRead() {
        if (!confirm('Delete all read notifications? This cannot be undone.')) {
            return;
        }

        const button = document.getElementById('deleteAllReadBtn');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

        fetch(`${CONFIG.baseUrl}/notifications/delete-all-read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                csrf_token: CONFIG.csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove all read notifications from UI
                document.querySelectorAll('.notification-item.read').forEach(item => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(100%)';
                    setTimeout(() => item.remove(), 300);
                });

                showToast(data.message || 'Read notifications deleted', 'success');

                // Check if empty
                setTimeout(() => {
                    const list = document.getElementById('notificationsList');
                    if (list && list.querySelectorAll('.notification-item').length === 0) {
                        showEmptyState();
                    }
                }, 400);
            } else {
                showToast(data.message || 'Failed to delete notifications', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }

    /**
     * Start polling for new notifications
     */
    function startPolling() {
        if (isPolling) return;
        
        isPolling = true;
        pollTimer = setInterval(checkForNewNotifications, CONFIG.pollInterval);
    }

    /**
     * Stop polling
     */
    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
        isPolling = false;
    }

    /**
     * Check for new notifications (AJAX)
     */
    function checkForNewNotifications() {
        fetch(`${CONFIG.baseUrl}/notifications/get-counts`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.counts) {
                const currentUnread = parseInt(document.querySelector('.stat-card:nth-child(2) h3')?.textContent || 0);
                const newUnread = data.counts.unread;

                if (newUnread > currentUnread) {
                    // Show notification that new notifications arrived
                    showToast(`You have ${newUnread - currentUnread} new notification(s)`, 'info');
                    
                    // Update counts in stats
                    updateStatsFromData(data.counts);
                }
            }
        })
        .catch(error => {
            console.error('Polling error:', error);
        });
    }

    /**
     * Update notification counts in UI
     */
    function updateNotificationCounts(unreadCount) {
        // Update header badge
        const headerBadge = document.querySelector('.nav-actions .icon-btn .badge');
        if (headerBadge) {
            if (unreadCount > 0) {
                headerBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                headerBadge.style.display = '';
            } else {
                headerBadge.style.display = 'none';
            }
        }

        // Update stats cards
        const statCards = document.querySelectorAll('.stat-card h3');
        if (statCards[1]) {
            statCards[1].textContent = unreadCount;
        }

        // Update tab counts
        const unreadTab = document.querySelector('.tab-btn[data-filter="unread"] .tab-count');
        if (unreadTab) {
            unreadTab.textContent = unreadCount;
        }
    }

    /**
     * Update all stats from server data
     */
    function updateStatsFromData(counts) {
        const statCards = document.querySelectorAll('.stat-card h3');
        if (statCards[0]) statCards[0].textContent = counts.total || 0;
        if (statCards[1]) statCards[1].textContent = counts.unread || 0;
        if (statCards[2]) statCards[2].textContent = counts.orders || 0;
        if (statCards[3]) statCards[3].textContent = counts.reviews || 0;

        // Update tab counts
        const allTab = document.querySelector('.tab-btn[data-filter="all"] .tab-count');
        const unreadTab = document.querySelector('.tab-btn[data-filter="unread"] .tab-count');
        const ordersTab = document.querySelector('.tab-btn[data-filter="order_placed"] .tab-count');
        const reviewsTab = document.querySelector('.tab-btn[data-filter="review_new"] .tab-count');

        if (allTab) allTab.textContent = counts.total || 0;
        if (unreadTab) unreadTab.textContent = counts.unread || 0;
        if (ordersTab) ordersTab.textContent = counts.orders || 0;
        if (reviewsTab) reviewsTab.textContent = counts.reviews || 0;

        // Update header badge
        updateNotificationCounts(counts.unread || 0);
    }

    /**
     * Show empty state
     */
    function showEmptyState() {
        const list = document.getElementById('notificationsList');
        if (!list) return;

        list.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="far fa-bell-slash"></i>
                </div>
                <h3>No notifications yet</h3>
                <p>We'll let you know when something important happens.</p>
            </div>
        `;
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'info') {
        // Remove existing toast
        const existingToast = document.querySelector('.toast-notification');
        if (existingToast) {
            existingToast.remove();
        }

        // Create new toast
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        
        const icon = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        }[type] || 'fa-info-circle';

        toast.innerHTML = `
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(toast);

        // Animate in
        setTimeout(() => toast.classList.add('show'), 10);

        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    /**
     * Stop polling when page is hidden (performance)
     */
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
        }
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        stopPolling();
    });

})();