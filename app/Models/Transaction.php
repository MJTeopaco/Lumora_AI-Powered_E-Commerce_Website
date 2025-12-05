<?php
// app/Models/Transaction.php

namespace App\Models;

use App\Core\Database;

class Transaction {
    
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Create a new payment transaction
     */
    public function createTransaction($data) {
        $query = "INSERT INTO payments (
                    order_id,
                    payment_method,
                    payment_gateway,
                    transaction_id,
                    amount_paid,
                    status
                  ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "isssds",
            $data['order_id'],
            $data['payment_method'],
            $data['payment_gateway'],
            $data['transaction_id'],
            $data['amount_paid'],
            $data['status']
        );
        
        $success = $stmt->execute();
        $paymentId = $success ? $this->conn->insert_id : false;
        $stmt->close();
        
        return $paymentId;
    }

    /**
     * Get transaction by ID
     */
    public function getTransactionById($paymentId) {
        $query = "SELECT * FROM payments WHERE payment_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment = $result->fetch_assoc();
        $stmt->close();
        
        return $payment;
    }

    /**
     * Get transaction by PayMongo transaction ID
     */
    public function getTransactionByTransactionId($transactionId) {
        $query = "SELECT * FROM payments WHERE transaction_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $transactionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment = $result->fetch_assoc();
        $stmt->close();
        
        return $payment;
    }

    /**
     * Get all transactions for an order
     */
    public function getOrderTransactions($orderId) {
        $query = "SELECT * FROM payments 
                  WHERE order_id = ? 
                  ORDER BY payment_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $payments = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $payments;
    }

    /**
     * Get successful transaction for order
     */
    public function getSuccessfulTransaction($orderId) {
        $query = "SELECT * FROM payments 
                  WHERE order_id = ? AND status = 'COMPLETED' 
                  ORDER BY payment_date DESC 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment = $result->fetch_assoc();
        $stmt->close();
        
        return $payment;
    }

    /**
     * Update transaction status
     */
    public function updateTransactionStatus($transactionId, $status) {
        $query = "UPDATE payments 
                  SET status = ?,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE transaction_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $status, $transactionId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Update transaction with payment details
     */
    public function updateTransactionDetails($transactionId, $data) {
        $query = "UPDATE payments 
                  SET status = ?,
                      payment_date = CURRENT_TIMESTAMP,
                      updated_at = CURRENT_TIMESTAMP";
        
        $params = [$data['status']];
        $types = "s";
        
        if (isset($data['processed_by'])) {
            $query .= ", processed_by = ?";
            $params[] = $data['processed_by'];
            $types .= "i";
        }
        
        $query .= " WHERE transaction_id = ?";
        $params[] = $transactionId;
        $types .= "s";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Check if order has pending payment
     */
    public function hasPendingPayment($orderId) {
        $query = "SELECT COUNT(*) as count 
                  FROM payments 
                  WHERE order_id = ? AND status = 'PENDING'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

    /**
     * Check if order is paid
     */
    public function isOrderPaid($orderId) {
        $query = "SELECT COUNT(*) as count 
                  FROM payments 
                  WHERE order_id = ? AND status = 'COMPLETED'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats($startDate = null, $endDate = null) {
        $query = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as successful_payments,
                    SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as failed_payments,
                    SUM(CASE WHEN status = 'COMPLETED' THEN amount_paid ELSE 0 END) as total_revenue
                  FROM payments
                  WHERE 1=1";
        
        if ($startDate && $endDate) {
            $query .= " AND DATE(payment_date) BETWEEN ? AND ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ss", $startDate, $endDate);
        } else {
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();
        
        return $stats;
    }

    /**
     * Get failed transactions for retry
     */
    public function getFailedTransactions($limit = 50) {
        $query = "SELECT 
                    p.*,
                    o.user_id,
                    o.total_amount
                  FROM payments p
                  INNER JOIN orders o ON p.order_id = o.order_id
                  WHERE p.status = 'FAILED'
                  ORDER BY p.payment_date DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $transactions;
    }
}