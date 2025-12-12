<?php
// app/Models/Notification.php - ENHANCED VERSION

namespace App\Models;

use App\Core\Database;

class Notification {
    
    protected $conn;
    
    // Notification types
    const TYPE_ORDER_PLACED = 'order_placed';
    const TYPE_ORDER_CONFIRMED = 'order_confirmed';
    const TYPE_ORDER_PROCESSING = 'order_processing';
    const TYPE_ORDER_SHIPPED = 'order_shipped';
    const TYPE_ORDER_DELIVERED = 'order_delivered';
    const TYPE_ORDER_CANCELLED = 'order_cancelled';
    const TYPE_PAYMENT_FAILED = 'payment_failed';
    const TYPE_PAYMENT_SUCCESS = 'payment_success';
    const TYPE_REFUND_REQUESTED = 'refund_requested';
    const TYPE_REFUND_APPROVED = 'refund_approved';
    const TYPE_REVIEW_NEW = 'review_new';
    const TYPE_REVIEW_RESPONSE = 'review_response';
    const TYPE_LOW_STOCK = 'low_stock';
    const TYPE_SHOP_APPROVED = 'shop_approved';
    const TYPE_SHOP_REJECTED = 'shop_rejected';
    const TYPE_WELCOME = 'welcome';
    const TYPE_PROMOTION = 'promotion';
    const TYPE_SYSTEM = 'system';

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Create a new notification
     */
    public function createNotification($userId, $title, $message, $type, $referenceId = null, $metadata = null) {
        $query = "INSERT INTO notifications 
                  (user_id, title, message, type, reference_id, metadata, is_read, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP)";
        
        $metadataJson = $metadata ? json_encode($metadata) : null;
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isssis", $userId, $title, $message, $type, $referenceId, $metadataJson);
        
        $success = $stmt->execute();
        $notificationId = $success ? $this->conn->insert_id : false;
        $stmt->close();
        
        return $notificationId;
    }

    /**
     * Get user notifications with filtering and pagination
     */
    public function getUserNotifications($userId, $limit = 20, $offset = 0, $filter = 'all') {
        $query = "SELECT * FROM notifications WHERE user_id = ?";
        
        // Apply filter
        if ($filter === 'unread') {
            $query .= " AND is_read = 0";
        } elseif ($filter === 'read') {
            $query .= " AND is_read = 1";
        } elseif ($filter !== 'all') {
            // Filter by type
            $query .= " AND type = ?";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        
        if ($filter !== 'all' && $filter !== 'unread' && $filter !== 'read') {
            $stmt->bind_param("isii", $userId, $filter, $limit, $offset);
        } else {
            $stmt->bind_param("iii", $userId, $limit, $offset);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Parse metadata JSON
        foreach ($notifications as &$notif) {
            if (!empty($notif['metadata'])) {
                $notif['metadata'] = json_decode($notif['metadata'], true);
            }
        }
        
        return $notifications;
    }

    /**
     * Get unread count
     */
    public function getUnreadCount($userId) {
        $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] ?? 0;
    }

    /**
     * Get notification counts by type
     */
    public function getNotificationCounts($userId) {
        // FIXED: Added backticks around `read` because it is a reserved keyword in MySQL
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as `read`,
                    SUM(CASE WHEN type LIKE 'order_%' THEN 1 ELSE 0 END) as orders,
                    SUM(CASE WHEN type LIKE 'review_%' THEN 1 ELSE 0 END) as reviews,
                    SUM(CASE WHEN type = 'promotion' THEN 1 ELSE 0 END) as promotions
                  FROM notifications 
                  WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $counts = $result->fetch_assoc();
        $stmt->close();
        
        return $counts;
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead($notificationId, $userId) {
        $query = "UPDATE notifications 
                  SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                  WHERE notification_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $notificationId, $userId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Mark all as read
     */
    public function markAllAsRead($userId) {
        $query = "UPDATE notifications 
                  SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                  WHERE user_id = ? AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Delete notification
     */
    public function deleteNotification($notificationId, $userId) {
        $query = "DELETE FROM notifications WHERE notification_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $notificationId, $userId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead($userId) {
        $query = "DELETE FROM notifications WHERE user_id = ? AND is_read = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Get notification by ID
     */
    public function getNotificationById($notificationId, $userId) {
        $query = "SELECT * FROM notifications WHERE notification_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $notificationId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $notification = $result->fetch_assoc();
        $stmt->close();
        
        if ($notification && !empty($notification['metadata'])) {
            $notification['metadata'] = json_decode($notification['metadata'], true);
        }
        
        return $notification;
    }

    /**
     * Check if similar notification exists (prevent duplicates)
     */
    public function similarNotificationExists($userId, $type, $referenceId, $withinMinutes = 5) {
        $query = "SELECT notification_id FROM notifications 
                  WHERE user_id = ? 
                  AND type = ? 
                  AND reference_id = ? 
                  AND created_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isii", $userId, $type, $referenceId, $withinMinutes);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }

    // ========================================================================
    // HELPER METHODS FOR SPECIFIC NOTIFICATION TYPES
    // ========================================================================

    /**
     * Create order notification
     */
    public function notifyOrderPlaced($userId, $orderId, $totalAmount) {
        if ($this->similarNotificationExists($userId, self::TYPE_ORDER_PLACED, $orderId)) {
            return false;
        }
        
        return $this->createNotification(
            $userId,
            "Order Placed Successfully",
            "Your order #" . str_pad($orderId, 6, '0', STR_PAD_LEFT) . " has been placed. Total: ₱" . number_format($totalAmount, 2),
            self::TYPE_ORDER_PLACED,
            $orderId,
            ['order_id' => $orderId, 'amount' => $totalAmount]
        );
    }

    /**
     * Notify order status change
     */
    public function notifyOrderStatusChange($userId, $orderId, $newStatus) {
        $statusMessages = [
            'PROCESSING' => 'Your order is being processed',
            'SHIPPED' => 'Your order has been shipped',
            'DELIVERED' => 'Your order has been delivered',
            'CANCELLED' => 'Your order has been cancelled'
        ];
        
        $statusTypes = [
            'PROCESSING' => self::TYPE_ORDER_PROCESSING,
            'SHIPPED' => self::TYPE_ORDER_SHIPPED,
            'DELIVERED' => self::TYPE_ORDER_DELIVERED,
            'CANCELLED' => self::TYPE_ORDER_CANCELLED
        ];
        
        $title = $statusMessages[$newStatus] ?? 'Order Status Updated';
        $type = $statusTypes[$newStatus] ?? self::TYPE_SYSTEM;
        
        return $this->createNotification(
            $userId,
            $title,
            "Order #" . str_pad($orderId, 6, '0', STR_PAD_LEFT) . " - " . $title,
            $type,
            $orderId,
            ['order_id' => $orderId, 'status' => $newStatus]
        );
    }

    /**
     * Notify new review
     */
    public function notifyNewReview($sellerId, $productName, $rating, $reviewId) {
        $stars = str_repeat('⭐', $rating);
        
        return $this->createNotification(
            $sellerId,
            "New Review Received",
            "Someone rated \"{$productName}\" {$stars} ({$rating}/5)",
            self::TYPE_REVIEW_NEW,
            $reviewId,
            ['review_id' => $reviewId, 'product_name' => $productName, 'rating' => $rating]
        );
    }

    /**
     * Notify seller responded to review
     */
    public function notifySellerResponse($customerId, $productName, $shopName, $reviewId) {
        return $this->createNotification(
            $customerId,
            "Seller Responded to Your Review",
            "{$shopName} replied to your review on \"{$productName}\"",
            self::TYPE_REVIEW_RESPONSE,
            $reviewId,
            ['review_id' => $reviewId, 'product_name' => $productName, 'shop_name' => $shopName]
        );
    }

    /**
     * Notify low stock (for sellers)
     */
    public function notifyLowStock($sellerId, $productName, $variantName, $currentStock, $productId) {
        if ($this->similarNotificationExists($sellerId, self::TYPE_LOW_STOCK, $productId, 60)) {
            return false; // Don't spam - only notify once per hour
        }
        
        return $this->createNotification(
            $sellerId,
            "Low Stock Alert",
            "\"{$productName}\" ({$variantName}) is low on stock: {$currentStock} units remaining",
            self::TYPE_LOW_STOCK,
            $productId,
            ['product_id' => $productId, 'variant_name' => $variantName, 'stock' => $currentStock]
        );
    }

    /**
     * Notify shop approval
     */
    public function notifyShopApproval($userId, $shopName) {
        return $this->createNotification(
            $userId,
            "Shop Approved! 🎉",
            "Congratulations! Your shop \"{$shopName}\" has been approved. You can now start selling!",
            self::TYPE_SHOP_APPROVED,
            null,
            ['shop_name' => $shopName]
        );
    }

    /**
     * Notify payment success
     */
    public function notifyPaymentSuccess($userId, $orderId, $amount) {
        return $this->createNotification(
            $userId,
            "Payment Successful",
            "Your payment of ₱" . number_format($amount, 2) . " for order #" . str_pad($orderId, 6, '0', STR_PAD_LEFT) . " was successful",
            self::TYPE_PAYMENT_SUCCESS,
            $orderId,
            ['order_id' => $orderId, 'amount' => $amount]
        );
    }

    /**
     * Notify payment failed
     */
    public function notifyPaymentFailed($userId, $orderId) {
        return $this->createNotification(
            $userId,
            "Payment Failed",
            "Payment for order #" . str_pad($orderId, 6, '0', STR_PAD_LEFT) . " could not be processed. Please try again.",
            self::TYPE_PAYMENT_FAILED,
            $orderId,
            ['order_id' => $orderId]
        );
    }

    /**
     * Send welcome notification
     */
    public function notifyWelcome($userId, $username) {
        return $this->createNotification(
            $userId,
            "Welcome to Lumora! ✨",
            "Hi {$username}! Thank you for joining Lumora. Start exploring our exclusive jewelry collections.",
            self::TYPE_WELCOME,
            null,
            ['username' => $username]
        );
    }

    /**
     * Bulk create notifications (for promotions/announcements)
     */
    public function bulkCreateNotifications($userIds, $title, $message, $type = self::TYPE_PROMOTION, $metadata = null) {
        $query = "INSERT INTO notifications 
                  (user_id, title, message, type, metadata, is_read, created_at) 
                  VALUES (?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP)";
        
        $metadataJson = $metadata ? json_encode($metadata) : null;
        $stmt = $this->conn->prepare($query);
        
        $successCount = 0;
        foreach ($userIds as $userId) {
            $stmt->bind_param("issss", $userId, $title, $message, $type, $metadataJson);
            if ($stmt->execute()) {
                $successCount++;
            }
        }
        
        $stmt->close();
        return $successCount;
    }

    /**
     * Clean old notifications (optional - run via cron)
     */
    public function deleteOldNotifications($daysOld = 90) {
        $query = "DELETE FROM notifications 
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY) 
                  AND is_read = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $daysOld);
        $success = $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        return $affectedRows;
    }
}