<?php
// app/Helpers/EmailService.php

namespace App\Helpers;

class EmailService {
    
    private $fromEmail;
    private $fromName;
    private $replyTo;
    
    public function __construct() {
        $this->fromEmail = getenv('MAIL_FROM_ADDRESS') ?: 'noreply@lumora.com';
        $this->fromName = getenv('MAIL_FROM_NAME') ?: 'Lumora';
        $this->replyTo = getenv('MAIL_REPLY_TO') ?: 'support@lumora.com';
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
     * Build HTML email for order confirmation
     */
    private function buildOrderConfirmationHTML($order, $orderItems, $customerName) {
        $orderId = $order['order_id'];
        $orderDate = date('F j, Y', strtotime($order['created_at']));
        $total = number_format($order['total_amount'], 2);
        $shippingFee = number_format($order['shipping_fee'], 2);
        $subtotal = number_format($order['total_amount'] - $order['shipping_fee'], 2);
        
        // Build items list
        $itemsHTML = '';
        foreach ($orderItems as $item) {
            $itemName = htmlspecialchars($item['product_name']);
            $variantName = htmlspecialchars($item['variant_name']);
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
        
        $shippingAddress = "{$order['address_line_1']}, {$order['city']}, {$order['province']}";
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #F9F9F9;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #F9F9F9; padding: 40px 20px;'>
                <tr>
                    <td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                            <tr>
                                <td style='background: linear-gradient(135deg, #D4AF37 0%, #c4961f 100%); padding: 40px 30px; text-align: center;'>
                                    <h1 style='margin: 0; color: #FFFFFF; font-size: 32px;'>LUMORA</h1>
                                    <p style='margin: 10px 0 0 0; color: #FFFFFF; font-size: 16px;'>Luxury Filipino Jewelry</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td style='padding: 40px 30px; text-align: center;'>
                                    <div style='width: 80px; height: 80px; margin: 0 auto 20px; background-color: #4CAF50; border-radius: 50%;'>
                                        <span style='color: #FFFFFF; font-size: 48px; line-height: 80px;'>✓</span>
                                    </div>
                                    <h2 style='margin: 0 0 10px 0; color: #1A1A1A; font-size: 28px;'>Order Confirmed!</h2>
                                    <p style='margin: 0; color: #666; font-size: 16px;'>Thank you, {$customerName}!</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td style='padding: 0 30px 30px 30px;'>
                                    <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #F9F9F9; border-radius: 8px; padding: 20px;'>
                                        <tr>
                                            <td>
                                                <p style='margin: 0 0 10px 0; color: #666;'><strong>Order:</strong> #{$orderId}</p>
                                                <p style='margin: 0 0 10px 0; color: #666;'><strong>Date:</strong> {$orderDate}</p>
                                                <p style='margin: 0; color: #666;'><strong>Shipping:</strong> {$shippingAddress}</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            
                            <tr>
                                <td style='padding: 0 30px 30px 30px;'>
                                    <h3 style='margin: 0 0 20px 0; color: #1A1A1A;'>Order Items</h3>
                                    <table width='100%' cellpadding='0' cellspacing='0' style='border: 1px solid #E0E0E0; border-radius: 8px;'>
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
                                </td>
                            </tr>
                            
                            <tr>
                                <td style='padding: 0 30px 40px 30px;'>
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
                                    </table>
                                </td>
                            </tr>
                            
                            <tr>
                                <td style='background-color: #F9F9F9; padding: 30px; text-align: center;'>
                                    <p style='margin: 0 0 10px 0; color: #666;'>Questions?</p>
                                    <p style='margin: 0; color: #666;'>Contact: {$this->replyTo}</p>
                                    <p style='margin: 20px 0 0 0; color: #999; font-size: 12px;'>© " . date('Y') . " Lumora</p>
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
     * Build plain text for order confirmation
     */
    private function buildOrderConfirmationPlain($order, $orderItems, $customerName) {
        $orderId = $order['order_id'];
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
     * Build HTML for payment failed
     */
    private function buildPaymentFailedHTML($order, $customerName) {
        $orderId = $order['order_id'];
        
        return "
        <!DOCTYPE html>
        <html>
        <body style='font-family: Arial, sans-serif; padding: 40px; background-color: #F9F9F9;'>
            <div style='max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px;'>
                <h1 style='color: #f44336;'>Payment Failed</h1>
                <p>Dear {$customerName},</p>
                <p>We were unable to process your payment for order #{$orderId}.</p>
                <p>Your order has been cancelled. Please place a new order.</p>
                <p>Contact us: {$this->replyTo}</p>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Build plain text for payment failed
     */
    private function buildPaymentFailedPlain($order, $customerName) {
        $orderId = $order['order_id'];
        
        return "
LUMORA - Payment Failed

Dear {$customerName},

Payment for order #{$orderId} could not be processed.

Order cancelled. Please place a new order.

Contact: {$this->replyTo}
        ";
    }
    
    /**
     * Send email using PHP mail()
     */
    private function sendEmail($to, $subject, $htmlBody, $plainBody = null) {
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "From: {$this->fromName} <{$this->fromEmail}>";
        $headers[] = "Reply-To: {$this->replyTo}";
        
        $boundary = md5(time());
        $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
        
        $body = "--{$boundary}\r\n";
        
        if ($plainBody) {
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $body .= $plainBody . "\r\n";
            $body .= "--{$boundary}\r\n";
        }
        
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $htmlBody . "\r\n";
        $body .= "--{$boundary}--";
        
        $success = mail($to, $subject, $body, implode("\r\n", $headers));
        
        if ($success) {
            error_log("Email sent to {$to}: {$subject}");
        } else {
            error_log("Failed to send email to {$to}: {$subject}");
        }
        
        return $success;
    }
}