<?php
// app/Controllers/WebhookController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Helpers\PayMongoService;
use App\Helpers\EmailService;

class WebhookController extends Controller {
    
    private $orderModel;
    private $orderItemModel;
    private $transactionModel;
    private $paymongoService;
    private $emailService;
    
    public function __construct() {
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->transactionModel = new Transaction();
        $this->paymongoService = new PayMongoService();
        $this->emailService = new EmailService();
    }
    
    public function paymongo() {
        // 1. Prepare Response
        header('Content-Type: application/json');
        $this->logWebhook("------------------------------------------------");
        $this->logWebhook("Incoming Webhook: " . $_SERVER['REQUEST_METHOD']);

        // 2. Get Raw Payload (CRITICAL: Must be exact string)
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

        // Validate using the robust service method
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
        // Extract IDs
        $checkoutSessionId = $eventData['id'] ?? null;
        $this->logWebhook("Processing Checkout Session: $checkoutSessionId");

        // Fetch full session for metadata (Order ID is inside metadata)
        $session = $this->paymongoService->getCheckoutSession($checkoutSessionId);
        $metadata = $session['data']['attributes']['metadata'] ?? [];
        $orderId = $metadata['order_id'] ?? null;

        if (!$orderId) {
            $this->logWebhook("ERROR: Order ID missing in metadata.");
            return;
        }

        $this->logWebhook("Found Order ID: $orderId");

        // Update Database
        $this->transactionModel->updateTransactionStatus($checkoutSessionId, 'COMPLETED');
        
        // The Critical Step: Update Order Status
        $success = $this->orderModel->updateOrderStatus($orderId, 'PROCESSING');
        
        if ($success) {
            $this->logWebhook("SUCCESS: Database updated for Order #$orderId to PROCESSING");
            $this->orderItemModel->updateAllItemsStatus($orderId, 'PROCESSING');
            
            // Optional: Reduce Stock & Email
            // $this->reduceStockForOrder($orderId);
            // $this->sendOrderConfirmationEmail($orderId);
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
        }
    }

    private function logWebhook($msg) {
        $file = __DIR__ . '/../../logs/webhooks.log';
        $date = date('Y-m-d H:i:s');
        // Ensure logs directory exists
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, "[$date] $msg" . PHP_EOL, FILE_APPEND);
    }
}