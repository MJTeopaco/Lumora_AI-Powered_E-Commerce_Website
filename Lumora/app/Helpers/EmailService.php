<?php
// app/Helpers/EmailService.php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    
    private $fromEmail;
    private $fromName;
    private $replyTo;
    private $mail;
    
    public function __construct() {
        $this->fromEmail = 'lumora.auth@gmail.com';
        $this->fromName = 'Lumora';
        $this->replyTo = getenv('MAIL_REPLY_TO') ?: 'support@lumora.com';
    }
    
    /**
     * Initialize PHPMailer with common settings
     */
    private function initMailer() {
        $this->mail = new PHPMailer(true);
        
        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'lumora.auth@gmail.com';
        $this->mail->Password   = getenv('MAIL_PASSWORD') ?: 'authlumora';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port       = 465;
        $this->mail->CharSet    = 'UTF-8';
        
        // Default sender
        $this->mail->setFrom($this->fromEmail, $this->fromName);
        $this->mail->addReplyTo($this->replyTo);
    }
    
    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation($data) {
        $order = $data['order'];
        $orderItems = $data['orderItems'];
        $customerEmail = $data['customerEmail'];
        $customerName = $data['customerName'];
        
        $subject = "Order Confirmation - Order #{$order['order_id']}";
        
        $htmlBody = $this->buildOrderConfirmationHTML($order, $orderItems, $customerName);
        $plainBody = $this->buildOrderConfirmationPlain($order, $orderItems, $customerName);
        
        return $this->sendEmail($customerEmail, $subject, $htmlBody, $plainBody);
    }

    /**
     * Send Order Status Update Email
     */
    public function sendOrderStatusUpdate($data) {
        $order = $data['order'];
        $status = $data['status'];
        $customerEmail = $data['customerEmail'];
        $customerName = $data['customerName'];

        $readableStatus = ucwords(str_replace('_', ' ', strtolower($status)));
        $subject = "Order Update: Order #{$order['order_id']} is {$readableStatus}";

        $htmlBody = $this->buildStatusUpdateHTML($order, $status, $customerName);
        $plainBody = $this->buildStatusUpdatePlain($order, $status, $customerName);

        return $this->sendEmail($customerEmail, $subject, $htmlBody, $plainBody);
    }
    
    /**
     * Send payment failed email
     */
    public function sendPaymentFailed($data) {
        $order = $data['order'];
        $customerEmail = $data['customerEmail'];
        $customerName = $data['customerName'];
        
        $subject = "Payment Failed - Order #{$order['order_id']}";
        
        $htmlBody = $this->buildPaymentFailedHTML($order, $customerName);
        $plainBody = $this->buildPaymentFailedPlain($order, $customerName);
        
        return $this->sendEmail($customerEmail, $subject, $htmlBody, $plainBody);
    }

    /**
     * NEW: Send review request email (after order delivered)
     */
    public function sendReviewRequest($data) {
        $order = $data['order'];
        $orderItems = $data['orderItems'];
        $customerEmail = $data['customerEmail'];
        $customerName = $data['customerName'];
        
        $subject = "Share Your Experience - Review Your Recent Purchase";
        
        $htmlBody = $this->buildReviewRequestHTML($order, $orderItems, $customerName);
        $plainBody = $this->buildReviewRequestPlain($order, $orderItems, $customerName);
        
        return $this->sendEmail($customerEmail, $subject, $htmlBody, $plainBody);
    }

    /**
     * NEW: Notify seller of new review
     */
    public function sendNewReviewNotification($data) {
        $review = $data['review'];
        $product = $data['product'];
        $sellerEmail = $data['sellerEmail'];
        $sellerName = $data['sellerName'];
        $customerName = $data['customerName'];
        
        $subject = "New Review on {$product['name']}";
        
        $htmlBody = $this->buildNewReviewNotificationHTML($review, $product, $sellerName, $customerName);
        $plainBody = $this->buildNewReviewNotificationPlain($review, $product, $sellerName, $customerName);
        
        return $this->sendEmail($sellerEmail, $subject, $htmlBody, $plainBody);
    }

    /**
     * NEW: Notify customer of seller response
     */
    public function sendSellerResponseNotification($data) {
        $review = $data['review'];
        $response = $data['response'];
        $product = $data['product'];
        $customerEmail = $data['customerEmail'];
        $customerName = $data['customerName'];
        $shopName = $data['shopName'];
        
        $subject = "{$shopName} Responded to Your Review";
        
        $htmlBody = $this->buildSellerResponseHTML($review, $response, $product, $customerName, $shopName);
        $plainBody = $this->buildSellerResponsePlain($review, $response, $product, $customerName, $shopName);
        
        return $this->sendEmail($customerEmail, $subject, $htmlBody, $plainBody);
    }

    /**
     * NEW: Send order cancellation confirmation
     */
    public function sendOrderCancellation($data) {
        $order = $data['order'];
        $customerEmail = $data['customerEmail'];
        $customerName = $data['customerName'];
        $reason = $data['reason'] ?? 'Customer request';
        
        $subject = "Order Cancelled - Order #{$order['order_id']}";
        
        $htmlBody = $this->buildOrderCancellationHTML($order, $customerName, $reason);
        $plainBody = $this->buildOrderCancellationPlain($order, $customerName, $reason);
        
        return $this->sendEmail($customerEmail, $subject, $htmlBody, $plainBody);
    }

    /**
     * NEW: Send low stock alert to seller
     */
    public function sendLowStockAlert($data) {
        $product = $data['product'];
        $variant = $data['variant'];
        $sellerEmail = $data['sellerEmail'];
        $sellerName = $data['sellerName'];
        $currentStock = $data['currentStock'];
        
        $subject = "Low Stock Alert - {$product['name']}";
        
        $htmlBody = $this->buildLowStockAlertHTML($product, $variant, $sellerName, $currentStock);
        $plainBody = $this->buildLowStockAlertPlain($product, $variant, $sellerName, $currentStock);
        
        return $this->sendEmail($sellerEmail, $subject, $htmlBody, $plainBody);
    }

    /**
     * NEW: Send welcome email to new users
     */
    public function sendWelcomeEmail($data) {
        $customerEmail = $data['customerEmail'];
        $customerName = $data['customerName'];
        
        $subject = "Welcome to Lumora - Your Journey to Luxury Begins";
        
        $htmlBody = $this->buildWelcomeEmailHTML($customerName);
        $plainBody = $this->buildWelcomeEmailPlain($customerName);
        
        return $this->sendEmail($customerEmail, $subject, $htmlBody, $plainBody);
    }
    
    /**
     * Build HTML email for order confirmation
     */
    private function buildOrderConfirmationHTML($order, $orderItems, $customerName) {
        $orderId = str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);
        $orderDate = date('F j, Y', strtotime($order['created_at']));
        $total = number_format($order['total_amount'], 2);
        $shippingFee = number_format($order['shipping_fee'], 2);
        $subtotal = number_format($order['total_amount'] - $order['shipping_fee'], 2);
        
        // Build items list
        $itemsHTML = '';
        foreach ($orderItems as $item) {
            $itemName = htmlspecialchars($item['product_name']);
            $variantName = htmlspecialchars($item['variant_name'] ?? 'Standard');
            $quantity = $item['quantity'];
            $price = number_format($item['price_at_purchase'], 2);
            $itemTotal = number_format($item['total_price'], 2);
            
            $itemsHTML .= "
            <tr>
                <td style='padding: 15px; border-bottom: 1px solid #E0E0E0;'>
                    <strong>{$itemName}</strong><br>
                    <span style='color: #666; font-size: 14px;'>{$variantName}</span>
                </td>
                <td style='padding: 15px; border-bottom: 1px solid #E0E0E0; text-align: center;'>{$quantity}</td>
                <td style='padding: 15px; border-bottom: 1px solid #E0E0E0; text-align: right;'>₱{$price}</td>
                <td style='padding: 15px; border-bottom: 1px solid #E0E0E0; text-align: right;'><strong>₱{$itemTotal}</strong></td>
            </tr>";
        }
        
        $shippingAddress = htmlspecialchars("{$order['address_line_1']}, {$order['city']}, {$order['province']}");
        
        return $this->getEmailTemplate([
            'header_text' => 'Order Confirmed!',
            'header_icon' => '✓',
            'header_icon_bg' => '#4CAF50',
            'title' => 'Thank you, ' . htmlspecialchars($customerName) . '!',
            'subtitle' => 'Your order has been confirmed and is being processed.',
            'body' => "
                <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #F9F9F9; border-radius: 8px; padding: 20px; margin-bottom: 20px;'>
                    <tr>
                        <td>
                            <p style='margin: 0 0 10px 0; color: #666;'><strong>Order:</strong> #{$orderId}</p>
                            <p style='margin: 0 0 10px 0; color: #666;'><strong>Date:</strong> {$orderDate}</p>
                            <p style='margin: 0; color: #666;'><strong>Shipping:</strong> {$shippingAddress}</p>
                        </td>
                    </tr>
                </table>
                
                <h3 style='margin: 0 0 20px 0; color: #1A1A1A;'>Order Items</h3>
                <table width='100%' cellpadding='0' cellspacing='0' style='border: 1px solid #E0E0E0; border-radius: 8px; margin-bottom: 20px;'>
                    <thead>
                        <tr style='background-color: #F9F9F9;'>
                            <th style='padding: 15px; text-align: left;'>Item</th>
                            <th style='padding: 15px; text-align: center;'>Qty</th>
                            <th style='padding: 15px; text-align: right;'>Price</th>
                            <th style='padding: 15px; text-align: right;'>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemsHTML}
                    </tbody>
                </table>
                
                <table width='100%'>
                    <tr>
                        <td style='padding: 10px 0; text-align: right;'>Subtotal:</td>
                        <td style='padding: 10px 0; text-align: right; width: 120px;'><strong>₱{$subtotal}</strong></td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; text-align: right;'>Shipping:</td>
                        <td style='padding: 10px 0; text-align: right;'><strong>₱{$shippingFee}</strong></td>
                    </tr>
                    <tr style='border-top: 2px solid #E0E0E0;'>
                        <td style='padding: 15px 0; font-size: 18px; text-align: right;'><strong>Total:</strong></td>
                        <td style='padding: 15px 0; color: #D4AF37; font-size: 20px; text-align: right;'><strong>₱{$total}</strong></td>
                    </tr>
                </table>"
        ]);
    }
    
    /**
     * Build plain text for order confirmation
     */
    private function buildOrderConfirmationPlain($order, $orderItems, $customerName) {
        $orderId = str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);
        $orderDate = date('F j, Y', strtotime($order['created_at']));
        $total = number_format($order['total_amount'], 2);
        
        $itemsList = '';
        foreach ($orderItems as $item) {
            $itemsList .= "{$item['product_name']} - {$item['variant_name']} (x{$item['quantity']}) - ₱" . number_format($item['total_price'], 2) . "\n";
        }
        
        return "
LUMORA - Order Confirmation

Dear {$customerName},

Thank you for your order!

Order: #{$orderId}
Date: {$orderDate}
Total: ₱{$total}

ITEMS:
{$itemsList}

Contact: {$this->replyTo}

© " . date('Y') . " Lumora
        ";
    }

    /**
     * Build HTML for Status Update
     */
    private function buildStatusUpdateHTML($order, $status, $customerName) {
        $readableStatus = ucwords(str_replace('_', ' ', strtolower($status)));
        $orderId = str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);
        
        $statusColors = [
            'PROCESSING' => '#2196F3',
            'SHIPPED' => '#FF9800',
            'DELIVERED' => '#4CAF50',
            'CANCELLED' => '#f44336'
        ];
        
        $statusColor = $statusColors[$status] ?? '#D4AF37';
        
        return $this->getEmailTemplate([
            'header_text' => 'Order Update',
            'title' => 'Your Order Status Changed',
            'body' => "
                <p>Dear " . htmlspecialchars($customerName) . ",</p>
                <p>The status of your order <strong>#{$orderId}</strong> has been updated to:</p>
                <div style='text-align: center; padding: 30px; background: #fdfdfd; border-radius: 8px; margin: 20px 0;'>
                    <h2 style='color: {$statusColor}; margin: 0; font-size: 24px;'>{$readableStatus}</h2>
                </div>
                <p>You can view your order details by logging into your account.</p>
                <p style='margin-top: 30px;'>Thank you for shopping with Lumora!</p>"
        ]);
    }

    /**
     * Build Plain Text for Status Update
     */
    private function buildStatusUpdatePlain($order, $status, $customerName) {
        $readableStatus = ucwords(str_replace('_', ' ', strtolower($status)));
        $orderId = str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);
        return "Dear {$customerName},\n\nYour order #{$orderId} is now {$readableStatus}.\n\nThank you,\nLumora";
    }
    
    /**
     * Build HTML for payment failed
     */
    private function buildPaymentFailedHTML($order, $customerName) {
        $orderId = str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);
        
        return $this->getEmailTemplate([
            'header_text' => 'Payment Failed',
            'header_icon' => '✕',
            'header_icon_bg' => '#f44336',
            'title' => 'Payment Could Not Be Processed',
            'body' => "
                <p>Dear " . htmlspecialchars($customerName) . ",</p>
                <p>We were unable to process your payment for order <strong>#{$orderId}</strong>.</p>
                <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                    <p style='margin: 0; color: #856404;'>
                        <strong>What happened?</strong><br>
                        Your payment could not be completed. This may be due to insufficient funds, incorrect card details, or bank restrictions.
                    </p>
                </div>
                <p>Your order has been cancelled. Please place a new order or contact us for assistance.</p>
                <p>If you need help, please reach out to: {$this->replyTo}</p>"
        ]);
    }
    
    /**
     * Build plain text for payment failed
     */
    private function buildPaymentFailedPlain($order, $customerName) {
        $orderId = str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);
        
        return "
LUMORA - Payment Failed

Dear {$customerName},

Payment for order #{$orderId} could not be processed.

Order cancelled. Please place a new order.

Contact: {$this->replyTo}
        ";
    }

    /**
     * NEW: Build review request HTML
     */
    private function buildReviewRequestHTML($order, $orderItems, $customerName) {
        $itemsHTML = '';
        foreach ($orderItems as $item) {
            $productName = htmlspecialchars($item['product_name']);
            $productSlug = $item['product_slug'] ?? '';
            $reviewLink = "https://lumora.com/reviews/create?product_id={$item['product_id']}";
            
            $itemsHTML .= "
            <div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 15px;'>
                <h3 style='margin: 0 0 10px 0; color: #1A1A1A;'>{$productName}</h3>
                <a href='{$reviewLink}' style='display: inline-block; padding: 10px 20px; background: #D4AF37; color: #1A1A1A; text-decoration: none; border-radius: 6px; font-weight: 600;'>
                    ★ Write a Review
                </a>
            </div>";
        }
        
        return $this->getEmailTemplate([
            'header_text' => 'How Was Your Experience?',
            'header_icon' => '★',
            'header_icon_bg' => '#D4AF37',
            'title' => 'Share Your Thoughts',
            'subtitle' => 'Your feedback helps us and other customers!',
            'body' => "
                <p>Dear " . htmlspecialchars($customerName) . ",</p>
                <p>We hope you're enjoying your recent purchase! We'd love to hear about your experience.</p>
                <div style='margin: 30px 0;'>
                    {$itemsHTML}
                </div>
                <p style='color: #666; font-size: 14px;'>Your honest review helps other shoppers make informed decisions and helps us improve our products and service.</p>"
        ]);
    }

    /**
     * NEW: Build review request plain text
     */
    private function buildReviewRequestPlain($order, $orderItems, $customerName) {
        $itemsList = '';
        foreach ($orderItems as $item) {
            $itemsList .= "- {$item['product_name']}\n";
        }
        
        return "
LUMORA - Review Your Purchase

Dear {$customerName},

We hope you're enjoying your recent purchase! Please take a moment to share your experience:

{$itemsList}

Visit your profile to leave a review.

Thank you,
Lumora
        ";
    }

    /**
     * NEW: Build new review notification HTML
     */
    private function buildNewReviewNotificationHTML($review, $product, $sellerName, $customerName) {
        $rating = $review['rating'];
        $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
        $comment = htmlspecialchars($review['comment']);
        $productName = htmlspecialchars($product['name']);
        
        return $this->getEmailTemplate([
            'header_text' => 'New Review Received',
            'header_icon' => '★',
            'header_icon_bg' => '#D4AF37',
            'title' => 'A customer reviewed your product!',
            'body' => "
                <p>Dear " . htmlspecialchars($sellerName) . ",</p>
                <p>Great news! <strong>" . htmlspecialchars($customerName) . "</strong> left a review for:</p>
                <div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='margin: 0 0 10px 0; color: #1A1A1A;'>{$productName}</h3>
                    <div style='color: #D4AF37; font-size: 24px; margin: 10px 0;'>{$stars}</div>
                    <p style='margin: 10px 0 0 0; color: #555; font-style: italic;'>\"{$comment}\"</p>
                </div>
                <p>Respond to this review to show your customer appreciation and build trust!</p>
                <a href='https://lumora.com/shop/reviews' style='display: inline-block; padding: 12px 30px; background: #D4AF37; color: #1A1A1A; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 10px;'>
                    View & Respond
                </a>"
        ]);
    }

    /**
     * NEW: Build new review notification plain text
     */
    private function buildNewReviewNotificationPlain($review, $product, $sellerName, $customerName) {
        $rating = $review['rating'];
        $stars = str_repeat('*', $rating) . str_repeat('-', 5 - $rating);
        
        return "
LUMORA - New Review

Dear {$sellerName},

{$customerName} reviewed {$product['name']}:

Rating: {$stars} ({$rating}/5)
\"{$review['comment']}\"

Respond at: https://lumora.com/shop/reviews

Lumora
        ";
    }

    /**
     * NEW: Build seller response notification HTML
     */
    private function buildSellerResponseHTML($review, $response, $product, $customerName, $shopName) {
        $productName = htmlspecialchars($product['name']);
        $shopNameEsc = htmlspecialchars($shopName);
        $responseText = htmlspecialchars($response['response_text']);
        
        return $this->getEmailTemplate([
            'header_text' => 'Seller Responded',
            'title' => "{$shopNameEsc} responded to your review!",
            'body' => "
                <p>Dear " . htmlspecialchars($customerName) . ",</p>
                <p>The seller has responded to your review on <strong>{$productName}</strong>:</p>
                <div style='background: #f0f8ff; border-left: 4px solid #D4AF37; padding: 20px; border-radius: 4px; margin: 20px 0;'>
                    <p style='margin: 0 0 10px 0; font-weight: 600; color: #1A1A1A;'>{$shopNameEsc} replied:</p>
                    <p style='margin: 0; color: #555;'>\"{$responseText}\"</p>
                </div>
                <a href='https://lumora.com/products/{$product['slug']}#reviews' style='display: inline-block; padding: 12px 30px; background: #D4AF37; color: #1A1A1A; text-decoration: none; border-radius: 6px; font-weight: 600;'>
                    View Full Conversation
                </a>"
        ]);
    }

    /**
     * NEW: Build seller response plain text
     */
    private function buildSellerResponsePlain($review, $response, $product, $customerName, $shopName) {
        return "
LUMORA - Seller Response

Dear {$customerName},

{$shopName} responded to your review on {$product['name']}:

\"{$response['response_text']}\"

View at: https://lumora.com/products/{$product['slug']}

Lumora
        ";
    }

    /**
     * NEW: Build order cancellation HTML
     */
    private function buildOrderCancellationHTML($order, $customerName, $reason) {
        $orderId = str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);
        
        return $this->getEmailTemplate([
            'header_text' => 'Order Cancelled',
            'header_icon' => 'ℹ',
            'header_icon_bg' => '#FF9800',
            'title' => 'Order Cancellation Confirmed',
            'body' => "
                <p>Dear " . htmlspecialchars($customerName) . ",</p>
                <p>Your order <strong>#{$orderId}</strong> has been cancelled.</p>
                <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                    <p style='margin: 0; color: #856404;'>
                        <strong>Reason:</strong> " . htmlspecialchars($reason) . "
                    </p>
                </div>
                <p>If payment was already processed, you will receive a refund within 5-7 business days.</p>
                <p>If you have any questions, please contact us at: {$this->replyTo}</p>"
        ]);
    }

    /**
     * NEW: Build order cancellation plain text
     */
    private function buildOrderCancellationPlain($order, $customerName, $reason) {
        $orderId = str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);
        
        return "
LUMORA - Order Cancelled

Dear {$customerName},

Order #{$orderId} has been cancelled.

Reason: {$reason}

Refund (if applicable): 5-7 business days

Questions? {$this->replyTo}

Lumora
        ";
    }

    /**
     * NEW: Build low stock alert HTML
     */
    private function buildLowStockAlertHTML($product, $variant, $sellerName, $currentStock) {
        $productName = htmlspecialchars($product['name']);
        $variantName = htmlspecialchars($variant['variant_name'] ?? 'Standard');
        
        return $this->getEmailTemplate([
            'header_text' => 'Low Stock Alert',
            'header_icon' => '⚠',
            'header_icon_bg' => '#FF9800',
            'title' => 'Stock Running Low!',
            'body' => "
                <p>Dear " . htmlspecialchars($sellerName) . ",</p>
                <p>This is an automated alert about low stock levels:</p>
                <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; border-radius: 4px; margin: 20px 0;'>
                    <h3 style='margin: 0 0 10px 0; color: #856404;'>{$productName}</h3>
                    <p style='margin: 0 0 10px 0; color: #856404;'><strong>Variant:</strong> {$variantName}</p>
                    <p style='margin: 0; font-size: 24px; color: #d32f2f;'><strong>{$currentStock}</strong> units remaining</p>
                </div>
                <p>Consider restocking soon to avoid running out!</p>
                <a href='https://lumora.com/shop/products' style='display: inline-block; padding: 12px 30px; background: #D4AF37; color: #1A1A1A; text-decoration: none; border-radius: 6px; font-weight: 600;'>
                    Manage Inventory
                </a>"
        ]);
    }

    /**
     * NEW: Build low stock alert plain text
     */
    private function buildLowStockAlertPlain($product, $variant, $sellerName, $currentStock) {
        return "
LUMORA - Low Stock Alert

Dear {$sellerName},

LOW STOCK WARNING

Product: {$product['name']}
Variant: {$variant['variant_name']}
Current Stock: {$currentStock} units

Please restock soon.

Manage: https://lumora.com/shop/products

Lumora
        ";
    }

    /**
     * NEW: Build welcome email HTML
     */
    private function buildWelcomeEmailHTML($customerName) {
        return $this->getEmailTemplate([
            'header_text' => 'Welcome to Lumora!',
            'header_icon' => '✨',
            'header_icon_bg' => '#D4AF37',
            'title' => 'Welcome, ' . htmlspecialchars($customerName) . '!',
            'subtitle' => 'Your journey to luxury begins here',
            'body' => "
                <p>Dear " . htmlspecialchars($customerName) . ",</p>
                <p>Thank you for joining Lumora, your destination for authentic Filipino luxury jewelry and accessories!</p>
                <div style='margin: 30px 0;'>
                    <div style='text-align: center; padding: 30px; background: linear-gradient(135deg, #f9f9f9 0%, #fff 100%); border-radius: 8px;'>
                        <h3 style='margin: 0 0 20px 0; color: #1A1A1A;'>What's Next?</h3>
                        <div style='display: inline-block; text-align: left;'>
                            <p style='margin: 10px 0;'>✓ Browse our exclusive collections</p>
                            <p style='margin: 10px 0;'>✓ Add items to your wishlist</p>
                            <p style='margin: 10px 0;'>✓ Complete your profile</p>
                            <p style='margin: 10px 0;'>✓ Enjoy secure checkout</p>
                        </div>
                    </div>
                </div>
                <p style='text-align: center;'>
                    <a href='https://lumora.com/collections' style='display: inline-block; padding: 15px 40px; background: #D4AF37; color: #1A1A1A; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px; margin-top: 10px;'>
                        Start Shopping
                    </a>
                </p>
                <p style='margin-top: 30px; color: #666; font-size: 14px; text-align: center;'>
                    Need help? We're here for you at {$this->replyTo}
                </p>"
        ]);
    }

    /**
     * NEW: Build welcome email plain text
     */
    private function buildWelcomeEmailPlain($customerName) {
        return "
LUMORA - Welcome!

Dear {$customerName},

Welcome to Lumora - your destination for authentic Filipino luxury jewelry and accessories!

What's Next?
- Browse exclusive collections
- Add items to wishlist
- Complete your profile
- Enjoy secure checkout

Start shopping: https://lumora.com/collections

Need help? {$this->replyTo}

Welcome aboard!
Lumora Team
        ";
    }

    /**
     * NEW: Master email template with Lumora branding
     */
    private function getEmailTemplate($params) {
        $headerText = $params['header_text'] ?? 'LUMORA';
        $headerIcon = $params['header_icon'] ?? '';
        $headerIconBg = $params['header_icon_bg'] ?? '#D4AF37';
        $title = $params['title'] ?? '';
        $subtitle = $params['subtitle'] ?? '';
        $body = $params['body'] ?? '';
        
        $iconHTML = '';
        if ($headerIcon) {
            $iconHTML = "
            <div style='width: 80px; height: 80px; margin: 0 auto 20px; background-color: {$headerIconBg}; border-radius: 50%; display: flex; align-items: center; justify-content: center;'>
                <span style='color: #FFFFFF; font-size: 48px; line-height: 80px;'>{$headerIcon}</span>
            </div>";
        }
        
        $subtitleHTML = $subtitle ? "<p style='margin: 10px 0 0 0; color: #666; font-size: 16px;'>" . htmlspecialchars($subtitle) . "</p>" : '';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #F9F9F9;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #F9F9F9; padding: 40px 20px;'>
                <tr>
                    <td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                            <!-- Header -->
                            <tr>
                                <td style='background: linear-gradient(135deg, #D4AF37 0%, #c4961f 100%); padding: 40px 30px; text-align: center;'>
                                    <h1 style='margin: 0; color: #FFFFFF; font-size: 32px; letter-spacing: 2px;'>LUMORA</h1>
                                    <p style='margin: 10px 0 0 0; color: #FFFFFF; font-size: 14px; opacity: 0.9;'>Luxury Filipino Jewelry</p>
                                </td>
                            </tr>
                            
                            <!-- Icon & Title -->
                            <tr>
                                <td style='padding: 40px 30px; text-align: center;'>
                                    {$iconHTML}
                                    <h2 style='margin: 0 0 10px 0; color: #1A1A1A; font-size: 28px;'>{$title}</h2>
                                    {$subtitleHTML}
                                </td>
                            </tr>
                            
                            <!-- Body Content -->
                            <tr>
                                <td style='padding: 0 30px 40px 30px;'>
                                    {$body}
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style='background-color: #F9F9F9; padding: 30px; text-align: center; border-top: 1px solid #E0E0E0;'>
                                    <p style='margin: 0 0 10px 0; color: #666; font-size: 14px;'>Questions? Contact us:</p>
                                    <p style='margin: 0 0 20px 0; color: #D4AF37; font-weight: 600;'>{$this->replyTo}</p>
                                    <div style='margin: 20px 0; padding-top: 20px; border-top: 1px solid #E0E0E0;'>
                                        <p style='margin: 0 0 10px 0; color: #999; font-size: 12px;'>
                                            © " . date('Y') . " Lumora. All rights reserved.
                                        </p>
                                        <p style='margin: 0; color: #999; font-size: 11px;'>
                                            Authentic Filipino Luxury Jewelry & Accessories
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }
    
    /**
     * Send email using PHPMailer
     */
    private function sendEmail($to, $subject, $htmlBody, $plainBody = null) {
        try {
            $this->initMailer();
            
            // Recipients
            $this->mail->addAddress($to);
            
            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $htmlBody;
            
            if ($plainBody) {
                $this->mail->AltBody = $plainBody;
            }

            $this->mail->send();
            error_log("Email sent successfully to {$to} - Subject: {$subject}");
            return true;

        } catch (Exception $e) {
            error_log("Email Service Error: Failed to send to {$to}. Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * NEW: Batch send emails (for newsletters, announcements)
     */
    public function sendBatchEmails($recipients, $subject, $htmlBody, $plainBody = null) {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($recipients as $recipient) {
            $email = $recipient['email'];
            $name = $recipient['name'] ?? 'Valued Customer';
            
            // Personalize body with customer name
            $personalizedHTML = str_replace('{{name}}', htmlspecialchars($name), $htmlBody);
            $personalizedPlain = $plainBody ? str_replace('{{name}}', $name, $plainBody) : null;
            
            if ($this->sendEmail($email, $subject, $personalizedHTML, $personalizedPlain)) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = $email;
            }
            
            // Add small delay to avoid rate limiting
            usleep(100000); // 0.1 second delay
        }
        
        return $results;
    }
}