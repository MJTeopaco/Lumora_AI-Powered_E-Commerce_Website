<?php
// app/Models/Cart.php

namespace App\Models;

use App\Core\Database;

class Cart {
    
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Get all cart items for a user with product details
     */
    public function getUserCart($userId) {
        // FIX: Added 'p.shop_id' to the SELECT list so checkout knows the seller
        $query = "SELECT 
                    sc.cart_id,
                    sc.user_id,
                    sc.variant_id,
                    sc.quantity,
                    sc.created_at,
                    sc.updated_at,
                    pv.variant_name,
                    pv.price,
                    pv.quantity as max_quantity,
                    pv.sku,
                    pv.color,
                    pv.size,
                    pv.material,
                    pv.product_picture,
                    pv.is_active,
                    p.product_id,
                    p.shop_id,  
                    p.name as product_name,
                    p.cover_picture,
                    p.slug,
                    p.status as product_status
                  FROM shopping_cart sc
                  INNER JOIN product_variants pv ON sc.variant_id = pv.variant_id
                  INNER JOIN products p ON pv.product_id = p.product_id
                  WHERE sc.user_id = ? 
                    AND pv.is_active = 1
                    AND p.status = 'PUBLISHED'
                    AND p.is_deleted = 0
                  ORDER BY sc.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $items;
    }

    /**
     * Get cart count for a user
     */
    public function getCartCount($userId) {
        $query = "SELECT COALESCE(SUM(sc.quantity), 0) as total_count
                  FROM shopping_cart sc
                  INNER JOIN product_variants pv ON sc.variant_id = pv.variant_id
                  INNER JOIN products p ON pv.product_id = p.product_id
                  WHERE sc.user_id = ? 
                    AND pv.is_active = 1
                    AND p.status = 'PUBLISHED'
                    AND p.is_deleted = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['total_count'];
    }

    /**
     * Add item to cart or update quantity if exists
     */
    public function addToCart($userId, $variantId, $quantity = 1) {
        // Check if item already exists in cart
        $checkQuery = "SELECT cart_id, quantity FROM shopping_cart 
                       WHERE user_id = ? AND variant_id = ?";
        
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bind_param("ii", $userId, $variantId);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing = $result->fetch_assoc();
        $stmt->close();
        
        if ($existing) {
            // Update existing cart item
            $newQuantity = $existing['quantity'] + $quantity;
            return $this->updateCartQuantity($userId, $variantId, $newQuantity);
        } else {
            // Insert new cart item
            $insertQuery = "INSERT INTO shopping_cart (user_id, variant_id, quantity) 
                           VALUES (?, ?, ?)";
            
            $stmt = $this->conn->prepare($insertQuery);
            $stmt->bind_param("iii", $userId, $variantId, $quantity);
            $success = $stmt->execute();
            $cartId = $this->conn->insert_id;
            $stmt->close();
            
            if ($success) {
                $this->logCartHistory($userId, 'ADDED', $variantId, $quantity);
            }
            
            return $success ? $cartId : false;
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateCartQuantity($userId, $variantId, $quantity) {
        $query = "UPDATE shopping_cart 
                  SET quantity = ?, 
                      updated_at = CURRENT_TIMESTAMP
                  WHERE user_id = ? AND variant_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $quantity, $userId, $variantId);
        $success = $stmt->execute();
        $stmt->close();
        
        if ($success) {
            $this->logCartHistory($userId, 'UPDATED', $variantId, $quantity);
        }
        
        return $success;
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart($userId, $variantId) {
        $query = "DELETE FROM shopping_cart 
                  WHERE user_id = ? AND variant_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $variantId);
        $success = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        if ($success && $affected > 0) {
            $this->logCartHistory($userId, 'REMOVED', $variantId);
        }
        
        return $success && $affected > 0;
    }

    /**
     * Clear entire cart for a user
     */
    public function clearCart($userId) {
        $query = "DELETE FROM shopping_cart WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $success = $stmt->execute();
        $stmt->close();
        
        if ($success) {
            $this->logCartHistory($userId, 'CLEARED');
        }
        
        return $success;
    }

    /**
     * Get single cart item
     */
    public function getCartItem($userId, $variantId) {
        $query = "SELECT 
                    sc.cart_id,
                    sc.user_id,
                    sc.variant_id,
                    sc.quantity,
                    pv.price,
                    pv.quantity as max_quantity
                  FROM shopping_cart sc
                  INNER JOIN product_variants pv ON sc.variant_id = pv.variant_id
                  WHERE sc.user_id = ? AND sc.variant_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $variantId);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();
        
        return $item;
    }

    /**
     * Check if variant exists in user's cart
     */
    public function itemExistsInCart($userId, $variantId) {
        $query = "SELECT COUNT(*) as count 
                  FROM shopping_cart 
                  WHERE user_id = ? AND variant_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $variantId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

    /**
     * Get cart subtotal
     */
    public function getCartSubtotal($userId) {
        $query = "SELECT COALESCE(SUM(sc.quantity * pv.price), 0) as subtotal
                  FROM shopping_cart sc
                  INNER JOIN product_variants pv ON sc.variant_id = pv.variant_id
                  INNER JOIN products p ON pv.product_id = p.product_id
                  WHERE sc.user_id = ? 
                    AND pv.is_active = 1
                    AND p.status = 'PUBLISHED'
                    AND p.is_deleted = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (float)$row['subtotal'];
    }

    /**
     * Clean up cart - remove items that are no longer available
     */
    public function cleanupCart($userId) {
        $query = "DELETE sc FROM shopping_cart sc
                  LEFT JOIN product_variants pv ON sc.variant_id = pv.variant_id
                  LEFT JOIN products p ON pv.product_id = p.product_id
                  WHERE sc.user_id = ?
                    AND (pv.is_active = 0 
                         OR p.status != 'PUBLISHED' 
                         OR p.is_deleted = 1
                         OR pv.variant_id IS NULL)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Validate cart - check if quantities are available
     * Returns array of items with stock issues
     */
    public function validateCartStock($userId) {
        $query = "SELECT 
                    sc.cart_id,
                    sc.variant_id,
                    sc.quantity as requested_quantity,
                    pv.quantity as available_quantity,
                    p.name as product_name
                  FROM shopping_cart sc
                  INNER JOIN product_variants pv ON sc.variant_id = pv.variant_id
                  INNER JOIN products p ON pv.product_id = p.product_id
                  WHERE sc.user_id = ?
                    AND sc.quantity > pv.quantity";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $issues = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $issues;
    }

    /**
     * Auto-adjust quantities to match available stock
     */
    public function autoAdjustQuantities($userId) {
        $query = "UPDATE shopping_cart sc
                  INNER JOIN product_variants pv ON sc.variant_id = pv.variant_id
                  SET sc.quantity = LEAST(sc.quantity, pv.quantity)
                  WHERE sc.user_id = ? 
                    AND sc.quantity > pv.quantity";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $success = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        return ['success' => $success, 'adjusted_count' => $affected];
    }

    /**
     * Merge guest cart with user cart after login
     * (If you want to support guest carts in future)
     */
    public function mergeGuestCart($userId, $guestCartItems) {
        $merged = 0;
        
        foreach ($guestCartItems as $item) {
            $variantId = $item['variant_id'];
            $quantity = $item['quantity'];
            
            if ($this->addToCart($userId, $variantId, $quantity)) {
                $merged++;
            }
        }
        
        return $merged;
    }

    /**
     * Get cart statistics for user
     */
    public function getCartStats($userId) {
        $query = "SELECT 
                    COUNT(*) as item_count,
                    SUM(sc.quantity) as total_quantity,
                    SUM(sc.quantity * pv.price) as subtotal,
                    MIN(sc.created_at) as oldest_item,
                    MAX(sc.updated_at) as last_updated
                  FROM shopping_cart sc
                  INNER JOIN product_variants pv ON sc.variant_id = pv.variant_id
                  WHERE sc.user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();
        
        return $stats;
    }

    /**
     * Remove old cart items (cleanup cron job)
     * Remove items older than X days
     */
    public function removeOldCartItems($days = 30) {
        $query = "DELETE FROM shopping_cart 
                  WHERE updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $days);
        $success = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        return ['success' => $success, 'removed_count' => $affected];
    }

    /**
     * Log cart action to history table (for analytics)
     */
    private function logCartHistory($userId, $action, $variantId = null, $quantity = null) {
        // Safe check for table
        $checkTable = "SHOW TABLES LIKE 'cart_history'";
        $result = $this->conn->query($checkTable);
        
        if ($result && $result->num_rows > 0) {
            $query = "INSERT INTO cart_history (user_id, action, variant_id, quantity) 
                     VALUES (?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("isii", $userId, $action, $variantId, $quantity);
            $stmt->execute();
            $stmt->close();
        }
    }

    /**
     * Mark cart as checked out (for analytics)
     */
    public function markCartCheckedOut($userId) {
        $this->logCartHistory($userId, 'CHECKED_OUT');
    }

    /**
     * Get abandoned carts (for marketing/analytics)
     * FIXED: Use username instead of non-existent names, added ANY_VALUE
     */
    public function getAbandonedCarts($daysOld = 1, $limit = 100) {
        $query = "SELECT 
                    sc.user_id,
                    ANY_VALUE(u.email) as email,
                    ANY_VALUE(u.username) as username,
                    COUNT(sc.cart_id) as item_count,
                    SUM(sc.quantity * pv.price) as cart_value,
                    MAX(sc.updated_at) as last_updated
                  FROM shopping_cart sc
                  INNER JOIN users u ON sc.user_id = u.user_id
                  INNER JOIN product_variants pv ON sc.variant_id = pv.variant_id
                  WHERE sc.updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                  GROUP BY sc.user_id
                  HAVING item_count > 0
                  ORDER BY cart_value DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $daysOld, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $carts = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $carts;
    }
}