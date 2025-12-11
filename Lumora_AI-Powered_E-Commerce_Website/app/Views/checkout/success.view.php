<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - Lumora</title>
    <link rel="stylesheet" href="/assets/css/checkout.css">
    <style>
        .success-container {
            max-width: 800px;
            margin: 0 auto;
            padding: var(--spacing-2xl) var(--spacing-lg);
            text-align: center;
        }
        
        .success-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto var(--spacing-xl);
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, var(--color-success) 0%, #2d7a4f 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-white);
            font-size: 3.5rem;
            box-shadow: 0 10px 30px rgba(46, 125, 50, 0.3);
            animation: successPulse 2s ease-in-out infinite;
        }
        
        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .success-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--color-black);
            margin-bottom: var(--spacing-md);
            font-family: var(--font-secondary);
        }
        
        .success-subtitle {
            font-size: 1.125rem;
            color: var(--color-medium-gray);
            margin-bottom: var(--spacing-2xl);
        }
        
        .order-details {
            background: var(--color-white);
            border: 1px solid var(--color-light-gray);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-md);
            text-align: left;
        }
        
        .order-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-primary);
            margin-bottom: var(--spacing-md);
            text-align: center;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: var(--spacing-md) 0;
            border-bottom: 1px solid var(--color-light-gray);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--color-dark-gray);
        }
        
        .detail-value {
            color: var(--color-black);
        }
        
        .action-buttons {
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
            margin-top: var(--spacing-xl);
        }
        
        .btn-primary, .btn-secondary {
            padding: var(--spacing-md) var(--spacing-xl);
            border-radius: var(--radius-lg);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-base);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary) 0%, #c4961f 100%);
            color: var(--color-white);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(212, 175, 55, 0.4);
        }
        
        .btn-secondary {
            background: var(--color-white);
            color: var(--color-primary);
            border: 2px solid var(--color-primary);
        }
        
        .btn-secondary:hover {
            background: var(--color-primary);
            color: var(--color-white);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 class="success-title">Payment Successful!</h1>
        <p class="success-subtitle">
            Thank you for your purchase. Your order has been confirmed.
        </p>
        
        <div class="order-details">
            <div class="order-number">
                Order #<?= htmlspecialchars($order['order_id']) ?>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Order Date:</span>
                <span class="detail-value"><?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Total Amount:</span>
                <span class="detail-value">₱<?= number_format($order['total_amount'], 2) ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Payment Status:</span>
                <span class="detail-value" style="color: var(--color-success);">
                    <i class="fas fa-check-circle"></i> Paid
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Order Status:</span>
                <span class="detail-value"><?= htmlspecialchars($order['order_status']) ?></span>
            </div>
            
            <?php if (isset($transaction)): ?>
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value"><?= htmlspecialchars($transaction['transaction_id']) ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="action-buttons">
            <a href="/profile/orders/details?order_id=<?= $order['order_id'] ?>" class="btn-primary">
                <i class="fas fa-receipt"></i>
                View Order Details
            </a>
            <a href="/" class="btn-secondary">
                <i class="fas fa-home"></i>
                Back to Home
            </a>
        </div>
    </div>
</body>
</html>