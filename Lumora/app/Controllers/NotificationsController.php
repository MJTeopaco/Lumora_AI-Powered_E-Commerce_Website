<?php
// app/Controllers/NotificationsController.php - ENHANCED VERSION

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserProfile;

class NotificationsController extends Controller {

    protected $notificationModel;
    protected $userModel;
    protected $userProfileModel;

    public function __construct() {
        $this->notificationModel = new Notification();
        $this->userModel = new User();
        $this->userProfileModel = new UserProfile();
    }

    /**
     * Display notifications page
     */
    public function index() {
        if (!Session::has('user_id')) {
            $this->redirect('/login');
        }

        $userId = Session::get('user_id');
        $filter = $_GET['filter'] ?? 'all';
        
        // Get notifications
        $notifications = $this->notificationModel->getUserNotifications($userId, 50, 0, $filter);
        
        // Get counts
        $counts = $this->notificationModel->getNotificationCounts($userId);
        
        // User profile data for header
        $userProfile = $this->userProfileModel->getByUserId($userId);
        $username = Session::get('username');
        
        // Check seller status
        $isSeller = $this->userModel->checkRole($userId);
        
        $data = [
            'pageTitle' => 'Notifications - Lumora',
            'notifications' => $notifications,
            'counts' => $counts,
            'currentFilter' => $filter,
            'isLoggedIn' => true,
            'username' => $username,
            'userProfile' => $userProfile,
            'isSeller' => $isSeller,
            'notificationCount' => $counts['unread'],
            'cartCount' => 0 // Will be populated by header partial
        ];

        $this->view('notifications/index', $data);
    }

    /**
     * Get latest notifications (AJAX for header dropdown)
     */
    public function getLatest() {
        header('Content-Type: application/json');
        
        if (!$this->isAjax()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = Session::get('user_id');
        $notifications = $this->notificationModel->getUserNotifications($userId, 10, 0, 'all');
        $unreadCount = $this->notificationModel->getUnreadCount($userId);

        // Format timestamps for better display
        foreach ($notifications as &$notif) {
            $notif['time_ago'] = $this->timeAgo($notif['created_at']);
        }

        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
        exit;
    }

    /**
     * Mark single notification as read (AJAX)
     */
    public function markAsRead() {
        header('Content-Type: application/json');
        
        if (!$this->isAjax() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $notificationId = $_POST['notification_id'] ?? null;

        if (!$notificationId) {
            echo json_encode(['success' => false, 'message' => 'Notification ID required']);
            exit;
        }

        if ($this->notificationModel->markAsRead($notificationId, $userId)) {
            $unreadCount = $this->notificationModel->getUnreadCount($userId);
            echo json_encode([
                'success' => true, 
                'message' => 'Notification marked as read',
                'unreadCount' => $unreadCount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update notification']);
        }
        exit;
    }

    /**
     * Mark all as read (AJAX)
     */
    public function markAllAsRead() {
        header('Content-Type: application/json');
        
        if (!$this->isAjax() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');

        if ($this->notificationModel->markAllAsRead($userId)) {
            echo json_encode([
                'success' => true, 
                'message' => 'All notifications marked as read',
                'unreadCount' => 0
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update notifications']);
        }
        exit;
    }

    /**
     * Delete notification (AJAX)
     */
    public function deleteNotification() {
        header('Content-Type: application/json');
        
        if (!$this->isAjax() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // CSRF check
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }

        $userId = Session::get('user_id');
        $notificationId = $_POST['notification_id'] ?? null;

        if (!$notificationId) {
            echo json_encode(['success' => false, 'message' => 'Notification ID required']);
            exit;
        }

        if ($this->notificationModel->deleteNotification($notificationId, $userId)) {
            $unreadCount = $this->notificationModel->getUnreadCount($userId);
            echo json_encode([
                'success' => true, 
                'message' => 'Notification deleted',
                'unreadCount' => $unreadCount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete notification']);
        }
        exit;
    }

    /**
     * Delete all read notifications (AJAX)
     */
    public function deleteAllRead() {
        header('Content-Type: application/json');
        
        if (!$this->isAjax() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // CSRF check
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }

        $userId = Session::get('user_id');

        $deletedCount = $this->notificationModel->deleteAllRead($userId);
        
        if ($deletedCount !== false) {
            echo json_encode([
                'success' => true, 
                'message' => "{$deletedCount} notification(s) deleted",
                'deletedCount' => $deletedCount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete notifications']);
        }
        exit;
    }

    /**
     * Get notification counts (AJAX for real-time updates)
     */
    public function getCounts() {
        header('Content-Type: application/json');
        
        if (!$this->isAjax()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        if (!Session::has('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = Session::get('user_id');
        $counts = $this->notificationModel->getNotificationCounts($userId);

        echo json_encode([
            'success' => true,
            'counts' => $counts
        ]);
        exit;
    }

    /**
     * Helper: Check if request is AJAX
     */
    private function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Helper: Validate CSRF token
     */
    private function validateCsrfToken($token) {
        return Session::has('csrf_token') && hash_equals(Session::get('csrf_token'), $token);
    }

    /**
     * Helper: Convert timestamp to "time ago" format
     */
    private function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $timestamp);
        }
    }
}