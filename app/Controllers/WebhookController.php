<?php
// app/Controllers/WebhookController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Notification;
use App\Models\ShopEarnings; // [NEW] Added Import
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
        header('Content-Type: application/json');
        $this->logWebhook("------------------------------------------------");
        $this->logWebhook("Incoming Webhook: " . $_SERVER['REQUEST_METHOD']);

        $rawPayload = @file_get_contents('php://input');

        if (empty($rawPayload)) {
            $this->logWebhook("ERROR: input stream is empty.");
            http_response_code(400);
            echo json_encode(['error' => 'Empty payload']);
            exit;
        }

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

        $payload = json_decode($rawPayload, true);
        $eventType = $payload['data']['attributes']['type'] ?? 'unknown';
        $eventData = $payload['data']['attributes']['data'] ?? [];

        $this->logWebhook("Event Type: " . $eventType);

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

        $this->transactionModel->updateTransactionStatus($checkoutSessionId, 'COMPLETED');
        
        $success = $this->orderModel->updateOrderStatus($orderId, 'PROCESSING');
        
        if ($success) {
            $this->logWebhook("SUCCESS: Database updated for Order #$orderId to PROCESSING");
            $this->orderItemModel->updateAllItemsStatus($orderId, 'PROCESSING');
            
            $this->reduceStockForOrder($orderId);

            // [NEW] Record Earnings
            $this->recordEarnings($orderId);
            
            $this->sendOrderConfirmationEmail($orderId);
            $this->sendOrderNotifications($orderId);
            
        } else {
            $this->logWebhook("ERROR: Failed to update Order #$orderId in database.");
        }
    }

    // [NEW] Helper method to record earnings
    private function recordEarnings($orderId) {
        $order = $this->orderModel->getOrderById($orderId);
        if ($order && $order['shop_id']) {
            $earningsModel = new ShopEarnings();
            $earningsModel->calculateAndRecord(
                $order['order_id'],
                $order['shop_id'],
                $order['total_amount'],
                $order['shipping_fee']
            );
            $this->logWebhook("Earnings recorded for Shop #{$order['shop_id']} Order #$orderId");
        }
    }

    private function handlePaymentFailed($eventData) {
        $meta = $eventData['attributes']['metadata'] ?? [];
        $orderId = $meta['order_id'] ?? null;
        
        if ($orderId) {
            $this->orderModel->updateOrderStatus($orderId, 'CANCELLED');
            $this->orderItemModel->updateAllItemsStatus($orderId, 'CANCELLED');
            $this->logWebhook("Order #$orderId cancelled due to failed payment.");
            
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
    
    private function reduceStockForOrder($orderId) {
        $items = $this->orderItemModel->getOrderItems($orderId);
        $count = 0;
        foreach ($items as $item) {
            $result = $this->productModel->updateStock($item['variant_id'], $item['quantity']);
            if ($result) $count++;
        }
        $this->logWebhook("Stock reduced for $count items in Order #$orderId");
    }
    
    private function sendOrderConfirmationEmail($orderId) {
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) return;
        
        $items = $this->orderItemModel->getOrderItems($orderId);
        $this->emailService->sendOrderConfirmation([
            'order' => $order,
            'orderItems' => $items,
            'customerEmail' => $order['email'],
            'customerName' => $order['full_name'] ?? $order['username']
        ]);
    }
    
    private function sendOrderNotifications($orderId) {
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) return;
        
        $this->notificationModel->createNotification(
            $order['user_id'],
            "Order Placed",
            "Your order #{$orderId} has been successfully placed.",
            "ORDER_PLACED",
            $orderId
        );
        
        $shopModel = new \App\Models\Shop();
        $shop = $shopModel->getShopById($order['shop_id']);
        if ($shop) {
            $this->notificationModel->createNotification(
                $shop['user_id'],
                "New Order Received",
                "You have received a new order #{$orderId}",
                "NEW_ORDER",
                $orderId
            );
        }
    }

    private function logWebhook($msg) {
        $file = __DIR__ . '/../../logs/webhooks.log';
        $date = date('Y-m-d H:i:s');
        if (!is_dir(dirname($file))) mkdir(dirname($file), 0777, true);
        file_put_contents($file, "[$date] $msg" . PHP_EOL, FILE_APPEND);
    }
}