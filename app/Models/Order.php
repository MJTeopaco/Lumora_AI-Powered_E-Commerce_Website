<?php
// app/Models/Order.php

namespace App\Models;

use App\Core\Database;

class Order {
    
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Create a new order
     * Returns order_id on success, false on failure
     */
    public function createOrder($data) {
        $query = "INSERT INTO orders (
                    user_id,
                    shop_id,
                    shipping_address_id,
                    order_status,
                    total_amount,
                    shipping_fee
                  ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "iiisdd",
            $data['user_id'],
            $data['shop_id'],
            $data['shipping_address_id'],
            $data['order_status'],
            $data['total_amount'],
            $data['shipping_fee']
        );
        
        $success = $stmt->execute();
        $orderId = $success ? $this->conn->insert_id : false;
        $stmt->close();
        
        return $orderId;
    }

    /**
     * Get order by ID with full details
     */
    public function getOrderById($orderId, $userId = null) {
        // FIXED: Changed up.first_name, up.last_name to up.full_name
        $query = "SELECT 
                    o.*,
                    a.address_line_1,
                    a.address_line_2,
                    a.barangay,
                    a.city,
                    a.province,
                    a.region,
                    a.postal_code,
                    u.email,
                    u.username,
                    up.full_name, 
                    up.phone_number
                  FROM orders o
                  LEFT JOIN addresses a ON o.shipping_address_id = a.address_id
                  LEFT JOIN users u ON o.user_id = u.user_id
                  LEFT JOIN user_profiles up ON o.user_id = up.user_id
                  WHERE o.order_id = ?";
        
        if ($userId !== null) {
            $query .= " AND o.user_id = ?";
        }
        
        $query .= " AND o.is_deleted = 0";
        
        $stmt = $this->conn->prepare($query);
        
        if ($userId !== null) {
            $stmt->bind_param("ii", $orderId, $userId);
        } else {
            $stmt->bind_param("i", $orderId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        
        return $order;
    }

    /**
     * Get user's orders with pagination
     */
    public function getUserOrders($userId, $limit = 10, $offset = 0, $status = null) {
        $query = "SELECT 
                    o.*,
                    COUNT(oi.order_item_id) as item_count
                  FROM orders o
                  LEFT JOIN order_items oi ON o.order_id = oi.order_id
                  WHERE o.user_id = ? AND o.is_deleted = 0";
        
        if ($status !== null) {
            $query .= " AND o.order_status = ?";
        }
        
        $query .= " GROUP BY o.order_id
                    ORDER BY o.created_at DESC
                    LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        
        if ($status !== null) {
            $stmt->bind_param("isii", $userId, $status, $limit, $offset);
        } else {
            $stmt->bind_param("iii", $userId, $limit, $offset);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $orders;
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($orderId, $status, $userId = null) {
        $query = "UPDATE orders 
                  SET order_status = ?,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE order_id = ?";
        
        if ($userId !== null) {
            $query .= " AND user_id = ?";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($userId !== null) {
            $stmt->bind_param("sii", $status, $orderId, $userId);
        } else {
            $stmt->bind_param("si", $status, $orderId);
        }
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Cancel order
     */
    public function cancelOrder($orderId, $userId) {
        $allowedStatuses = ['PENDING_PAYMENT', 'PROCESSING'];
        
        $order = $this->getOrderById($orderId, $userId);
        
        if (!$order || !in_array($order['order_status'], $allowedStatuses)) {
            return false;
        }
        
        return $this->updateOrderStatus($orderId, 'CANCELLED', $userId);
    }

    /**
     * Get order statistics for user
     */
    public function getUserOrderStats($userId) {
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN order_status = 'DELIVERED' THEN 1 ELSE 0 END) as completed_orders,
                    SUM(CASE WHEN order_status = 'PENDING_PAYMENT' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(total_amount) as total_spent
                  FROM orders
                  WHERE user_id = ? AND is_deleted = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();
        
        return $stats;
    }

    /**
     * Get connection (for transactions)
     */
    public function getConnection() {
        return $this->conn;
    }
}