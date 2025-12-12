<?php
// app/Helpers/PayMongoService.php

namespace App\Helpers;

class PayMongoService {
    
    private $secretKey;
    private $publicKey;
    private $webhookSecret;
    private $apiUrl = 'https://api.paymongo.com/v1';
    
    public function __construct() {
        // Use your specific keys here
        $this->secretKey = getenv('PAYMONGO_SECRET_KEY') ?: 'sk_test_5h8zTbpc4pAXPGErPpjAvdsc';
        $this->publicKey = getenv('PAYMONGO_PUBLIC_KEY') ?: 'pk_test_vJ9KXg9VSyShjBpaJyYSH3Z4';
        $this->webhookSecret = trim('whsk_xjMc9YzZNTK2dNVX5dR156bf'); 
    }

    public function createCheckoutSession($data) {
        $endpoint = '/checkout_sessions';
        $payload = [
            'data' => [
                'attributes' => [
                    'send_email_receipt' => true,
                    'show_description' => true,
                    'show_line_items' => true,
                    'description' => $data['description'] ?? 'Lumora Order',
                    'line_items' => $data['line_items'],
                    'payment_method_types' => $data['payment_method_types'] ?? ['gcash', 'paymaya', 'card'],
                    'success_url' => $data['success_url'],
                    'cancel_url' => $data['cancel_url'],
                    'metadata' => $data['metadata'] ?? []
                ]
            ]
        ];
        return $this->makeRequest('POST', $endpoint, $payload);
    }

    public function getCheckoutSession($checkoutSessionId) {
        return $this->makeRequest('GET', "/checkout_sessions/{$checkoutSessionId}");
    }

    public function getPaymentIdFromSession($checkoutSessionId) {
        $session = $this->getCheckoutSession($checkoutSessionId);
        $paymentIntentId = $session['data']['attributes']['payment_intent']['id'] ?? null;

        if (!$paymentIntentId) return null;

        $paymentIntent = $this->makeRequest('GET', "/payment_intents/{$paymentIntentId}");
        $payments = $paymentIntent['data']['attributes']['payments'] ?? [];
        
        if (empty($payments)) return null;

        return end($payments)['id'];
    }

    public function createRefund($paymentId, $amount, $reason = 'requested_by_customer') {
        $endpoint = '/refunds';
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => $this->formatAmountToCents($amount),
                    'payment_id' => $paymentId,
                    'reason' => $reason
                ]
            ]
        ];

        return $this->makeRequest('POST', $endpoint, $payload);
    }
    
    public function verifyWebhookSignature($rawPayload, $signatureHeader) {
        if (empty($this->webhookSecret)) return false;
        $parts = explode(',', $signatureHeader);
        $timestamp = null; $testSignature = null; $liveSignature = null;
        foreach ($parts as $part) {
            $subparts = explode('=', $part, 2);
            if (count($subparts) == 2) {
                $key = trim($subparts[0]);
                $value = trim($subparts[1]);
                if ($key === 't') $timestamp = $value;
                if ($key === 'te') $testSignature = $value; 
                if ($key === 'li') $liveSignature = $value; 
            }
        }
        if (!$timestamp) return false;
        $signedPayload = $timestamp . '.' . $rawPayload;
        $computedSignature = hash_hmac('sha256', $signedPayload, $this->webhookSecret);
        return ($testSignature && hash_equals($computedSignature, $testSignature)) || 
               ($liveSignature && hash_equals($computedSignature, $liveSignature));
    }

    public function formatAmountToCents($amount) {
        return (int)($amount * 100);
    }

    public function buildLineItems($cartItems) {
        $lineItems = [];
        foreach ($cartItems as $item) {
            $lineItems[] = [
                'currency' => 'PHP',
                'amount' => $this->formatAmountToCents($item['price']),
                'name' => $item['product_name'] . ' - ' . $item['variant_name'],
                'quantity' => $item['quantity']
            ];
        }
        return $lineItems;
    }

    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->apiUrl . $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->secretKey . ':');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
        
        // --- FIX FOR INFINITYFREE SSL ERROR ---
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        // --------------------------------------

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("PayMongo API Error: " . $error);
            return false;
        }
        return json_decode($response, true);
    }
}