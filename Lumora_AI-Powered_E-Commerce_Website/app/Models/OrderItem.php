<?php
// app/Models/OrderItem.php

namespace App\Models;

use App\Core\Database;

class OrderItem {
    
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Add item to order
     */
    public function addOrderItem($data) {
        $query = "INSERT INTO order_items (
                    order_id,
                    variant_id,
                    quantity,
                    price_at_purchase,
                    total_price,
                    personalized_notes,
                    status
                  ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "iiiddss",
            $data['order_id'],
            $data['variant_id'],
            $data['quantity'],
            $data['price_at_purchase'],
            $data['total_price'],
            $data['personalized_notes'],
            $data['status']
        );
        
        $success = $stmt->execute();
        $itemId = $success ? $this->conn->insert_id : false;
        $stmt->close();
        
        return $itemId;
    }

    /**
     * Bulk add items to order from cart
     */
    public function addOrderItemsFromCart($orderId, $cartItems) {
        $addedCount = 0;
        
        foreach ($cartItems as $item) {
            $itemData = [
                'order_id' => $orderId,
                'variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'price_at_purchase' => $item['price'],
                'total_price' => $item['price'] * $item['quantity'],
                'personalized_notes' => null,
                'status' => 'PENDING'
            ];
            
            if ($this->addOrderItem($itemData)) {
                $addedCount++;
            }
        }
        
        return $addedCount;
    }

    /**
     * Get all items for an order with product details
     */
    public function getOrderItems($orderId) {
        $query = "SELECT 
                    oi.*,
                    pv.variant_name,
                    pv.color,
                    pv.size,
                    pv.material,
                    pv.product_picture,
                    pv.sku,
                    p.product_id,
                    p.name as product_name,
                    p.cover_picture,
                    p.slug
                  FROM order_items oi
                  INNER JOIN product_variants pv ON oi.variant_id = pv.variant_id
                  INNER JOIN products p ON pv.product_id = p.product_id
                  WHERE oi.order_id = ?
                  ORDER BY oi.order_item_id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $items;
    }

    /**
     * Get single order item
     */
    public function getOrderItem($orderItemId) {
        $query = "SELECT 
                    oi.*,
                    pv.variant_name,
                    pv.color,
                    pv.size,
                    pv.material,
                    pv.product_picture,
                    p.product_id,
                    p.name as product_name,
                    p.cover_picture,
                    p.slug
                  FROM order_items oi
                  INNER JOIN product_variants pv ON oi.variant_id = pv.variant_id
                  INNER JOIN products p ON pv.product_id = p.product_id
                  WHERE oi.order_item_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderItemId);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();
        
        return $item;
    }

    /**
     * Update order item status
     */
    public function updateItemStatus($orderItemId, $status) {
        $query = "UPDATE order_items 
                  SET status = ?
                  WHERE order_item_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $status, $orderItemId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Update all items status for an order
     */
    public function updateAllItemsStatus($orderId, $status) {
        $query = "UPDATE order_items 
                  SET status = ?
                  WHERE order_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $status, $orderId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Add review to order item
     */
    public function addReview($orderItemId, $reviewId) {
        $query = "UPDATE order_items 
                  SET review_id = ?
                  WHERE order_item_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $reviewId, $orderItemId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Check if item can be reviewed
     */
    public function canReviewItem($orderItemId, $userId) {
        $query = "SELECT oi.order_item_id 
                  FROM order_items oi
                  INNER JOIN orders o ON oi.order_id = o.order_id
                  WHERE oi.order_item_id = ?
                    AND o.user_id = ?
                    AND oi.status = 'COMPLETED'
                    AND oi.review_id IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $orderItemId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $canReview = $result->num_rows > 0;
        $stmt->close();
        
        return $canReview;
    }

    /**
     * Get order subtotal (sum of all items)
     */
    public function getOrderSubtotal($orderId) {
        $query = "SELECT COALESCE(SUM(total_price), 0) as subtotal
                  FROM order_items
                  WHERE order_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (float)$row['subtotal'];
    }

    /**
     * Count items in order
     */
    public function countOrderItems($orderId) {
        $query = "SELECT COUNT(*) as count
                  FROM order_items
                  WHERE order_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['count'];
    }
}