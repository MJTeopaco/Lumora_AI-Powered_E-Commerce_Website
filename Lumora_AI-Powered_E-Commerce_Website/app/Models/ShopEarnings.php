<?php
// app/Models/ShopEarnings.php

namespace App\Models;

use App\Core\Database;

class ShopEarnings {
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Record earnings when an order is paid
     */
    public function calculateAndRecord($orderId, $shopId, $totalAmount, $shippingFee) {
        $commissionRate = 0.05; // 5% Platform Fee
        
        // Revenue from items (Total - Shipping)
        $itemRevenue = $totalAmount - $shippingFee;
        
        // Calculate Commission
        $commission = $itemRevenue * $commissionRate;
        
        // Net Payout (Item Revenue - Commission + Shipping)
        // Seller gets the shipping fee to cover their courier costs
        $netPayout = ($itemRevenue - $commission) + $shippingFee;

        $stmt = $this->conn->prepare("
            INSERT INTO shop_earnings 
            (order_id, shop_id, item_revenue, platform_commission, net_payout_amount, payout_status) 
            VALUES (?, ?, ?, ?, ?, 'PENDING')
        ");
        
        $stmt->bind_param("iiddd", $orderId, $shopId, $itemRevenue, $commission, $netPayout);
        return $stmt->execute();
    }

    /**
     * Get earnings history for a specific shop
     */
    public function getShopEarnings($shopId) {
        $stmt = $this->conn->prepare("
            SELECT e.*, o.order_status, o.created_at as order_date 
            FROM shop_earnings e
            JOIN orders o ON e.order_id = o.order_id
            WHERE e.shop_id = ?
            ORDER BY e.created_at DESC
        ");
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get total pending balance for a shop
     */
    public function getPendingBalance($shopId) {
        $stmt = $this->conn->prepare("
            SELECT SUM(net_payout_amount) as total 
            FROM shop_earnings 
            WHERE shop_id = ? AND payout_status = 'PENDING'
        ");
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0.00;
    }
    
    /**
     * Get total paid out amount for a shop
     */
    public function getTotalPaid($shopId) {
        $stmt = $this->conn->prepare("
            SELECT SUM(net_payout_amount) as total 
            FROM shop_earnings 
            WHERE shop_id = ? AND payout_status = 'PAID'
        ");
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0.00;
    }
}