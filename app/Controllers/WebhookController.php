<?php
// app/Controllers/WebhookController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Notification;
use App\Models\User;
use App\Helpers\PayMongoService;
use App\Helpers\EmailService;

class WebhookController extends Controller {
    
    private $orderModel;
    private $orderItemModel;
    private $transactionModel;
    private $productModel;
    private $notificationModel;
    private $paymongoService;
    private $emailService;
    
    public function __construct() {
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->transactionModel = new Transaction();
        $this->productModel = new Product();
        $this->notificationModel = new Notification();
        $this->paymongoService = new PayMongoService();
        $this->emailService = new EmailService();
    }
    
    public function paymongo() {
        // 1. Prepare Response
        header('Content-Type: application/json');
        $this->logWebhook("------------------------------------------------");
        $this->logWebhook("Incoming Webhook: " . $_SERVER['REQUEST_METHOD']);

        // 2. Get Raw Payload
        $rawPayload = @file_get_contents('php://input');

        if (empty($rawPayload)) {
            $this->logWebhook("ERROR: input stream is empty.");
            http_response_code(400);
            echo json_encode(['error' => 'Empty payload']);
            exit;
        }

        // 3. Verify Signature
        $signature = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? null;
        if (!$signature) {
            $this->logWebhook("ERROR: No signature header.");
            http_response_code(401); 
            exit;
        }

        $isValid = $this->paymongoService->verifyWebhookSignature($rawPayload, $signature);

        if (!$isValid) {
            $this->logWebhook("ERROR: Signature verification failed.");
            http_response_code(401);
            echo json_encode(['error' => 'Invalid signature']);
            exit;
        }
        
        $this->logWebhook("SUCCESS: Signature verified.");

        // 4. Decode JSON
        $payload = json_decode($rawPayload, true);
        $eventType = $payload['data']['attributes']['type'] ?? 'unknown';
        $eventData = $payload['data']['attributes']['data'] ?? [];

        $this->logWebhook("Event Type: " . $eventType);

        // 5. Handle Event
        if ($eventType === 'checkout_session.payment.paid') {
            $this->handlePaymentPaid($eventData);
        } elseif ($eventType === 'payment.failed') {
            $this->handlePaymentFailed($eventData);
        } else {
            $this->logWebhook("Ignored event: $eventType");
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok']);
    }
    
    private function handlePaymentPaid($eventData) {
        $checkoutSessionId = $eventData['id'] ?? null;
        $this->logWebhook("Processing Checkout Session: $checkoutSessionId");

        $session = $this->paymongoService->getCheckoutSession($checkoutSessionId);
        $metadata = $session['data']['attributes']['metadata'] ?? [];
        $orderId = $metadata['order_id'] ?? null;

        if (!$orderId) {
            $this->logWebhook("ERROR: Order ID missing in metadata.");
            return;
        }

        $this->logWebhook("Found Order ID: $orderId");

        // Update Transaction
        $this->transactionModel->updateTransactionStatus($checkoutSessionId, 'COMPLETED');
        
        // Update Order Status
        $success = $this->orderModel->updateOrderStatus($orderId, 'PROCESSING');
        
        if ($success) {
            $this->logWebhook("SUCCESS: Database updated for Order #$orderId to PROCESSING");
            $this->orderItemModel->updateAllItemsStatus($orderId, 'PROCESSING');
            
            // 1. Reduce Stock
            $this->reduceStockForOrder($orderId);
            
            // 2. Send Confirmation Email
            $this->sendOrderConfirmationEmail($orderId);
            
            // 3. Send Notifications
            $this->sendOrderNotifications($orderId);
            
        } else {
            $this->logWebhook("ERROR: Failed to update Order #$orderId in database.");
        }
    }

    private function handlePaymentFailed($eventData) {
        $meta = $eventData['attributes']['metadata'] ?? [];
        $orderId = $meta['order_id'] ?? null;
        
        if ($orderId) {
            $this->orderModel->updateOrderStatus($orderId, 'CANCELLED');
            $this->orderItemModel->updateAllItemsStatus($orderId, 'CANCELLED');
            $this->logWebhook("Order #$orderId cancelled due to failed payment.");
            
            // Optional: Send failure email
            $order = $this->orderModel->getOrderById($orderId);
            if ($order) {
                $this->emailService->sendPaymentFailed([
                    'order' => $order,
                    'customerEmail' => $order['email'],
                    'customerName' => $order['full_name'] ?? $order['username']
                ]);
            }
        }
    }
    
    /**
     * Reduce stock for all items in order
     */
    private function reduceStockForOrder($orderId) {
        $items = $this->orderItemModel->getOrderItems($orderId);
        $count = 0;
        
        foreach ($items as $item) {
            $variantId = $item['variant_id'];
            $quantity = $item['quantity'];
            
            $result = $this->productModel->updateStock($variantId, $quantity);
            
            if ($result) {
                $count++;
            } else {
                $this->logWebhook("WARNING: Failed to reduce stock for Item ID {$item['order_item_id']}");
            }
        }
        
        $this->logWebhook("Stock reduced for $count items in Order #$orderId");
    }
    
    /**
     * Send email confirmation
     */
    private function sendOrderConfirmationEmail($orderId) {
        $order = $this->orderModel->getOrderById($orderId);
        
        if (!$order) {
            $this->logWebhook("ERROR: Could not fetch order #$orderId for email.");
            return;
        }
        
        $items = $this->orderItemModel->getOrderItems($orderId);
        
        $emailData = [
            'order' => $order,
            'orderItems' => $items,
            'customerEmail' => $order['email'],
            'customerName' => $order['full_name'] ?? $order['username']
        ];
        
        $sent = $this->emailService->sendOrderConfirmation($emailData);
        
        if ($sent) {
            $this->logWebhook("Email confirmation sent to {$order['email']}");
        } else {
            $this->logWebhook("Failed to send email to {$order['email']}");
        }
    }
    
    /**
     * Send in-app notifications
     */
    private function sendOrderNotifications($orderId) {
        $order = $this->orderModel->getOrderById($orderId);
        
        if (!$order) return;
        
        // Notify Customer
        $this->notificationModel->createNotification(
            $order['user_id'],
            "Order Placed",
            "Your order #{$orderId} has been successfully placed and is now processing.",
            "ORDER_PLACED",
            $orderId
        );
        
        // Notify Seller (Shop Owner)
        $shopModel = new \App\Models\Shop();
        $shop = $shopModel->getShopById($order['shop_id']);
        
        if ($shop) {
            $this->notificationModel->createNotification(
                $shop['user_id'],
                "New Order Received",
                "You have received a new order #{$orderId} worth ₱" . number_format($order['total_amount'], 2),
                "NEW_ORDER",
                $orderId
            );
            $this->logWebhook("Notification sent to seller (User ID: {$shop['user_id']})");
        }
    }

    private function logWebhook($msg) {
        $file = __DIR__ . '/../../logs/webhooks.log';
        $date = date('Y-m-d H:i:s');
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, "[$date] $msg" . PHP_EOL, FILE_APPEND);
    }
}