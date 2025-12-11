<!-- app/views/checkout/failed.view.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - Lumora</title>
    <link rel="stylesheet" href="/assets/css/checkout.css">
    <style>
        .failed-container {
            max-width: 700px;
            margin: 0 auto;
            padding: var(--spacing-2xl) var(--spacing-lg);
            text-align: center;
        }
        
        .failed-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto var(--spacing-xl);
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, var(--color-error) 0%, #c62828 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-white);
            font-size: 3.5rem;
            box-shadow: 0 10px 30px rgba(244, 67, 54, 0.3);
        }
        
        .failed-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--color-black);
            margin-bottom: var(--spacing-md);
            font-family: var(--font-secondary);
        }
        
        .failed-subtitle {
            font-size: 1.125rem;
            color: var(--color-medium-gray);
            margin-bottom: var(--spacing-2xl);
            line-height: 1.6;
        }
        
        .failed-reasons {
            background: var(--color-white);
            border: 1px solid var(--color-light-gray);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-md);
            text-align: left;
        }
        
        .reasons-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-black);
            margin-bottom: var(--spacing-md);
            text-align: center;
        }
        
        .reasons-list {
            list-style: none;
            padding: 0;
        }
        
        .reasons-list li {
            padding: var(--spacing-sm) var(--spacing-md);
            margin-bottom: var(--spacing-sm);
            background: var(--color-off-white);
            border-left: 4px solid var(--color-error);
            border-radius: var(--radius-sm);
            font-size: 0.9375rem;
            color: var(--color-dark-gray);
        }
        
        .reasons-list li i {
            margin-right: var(--spacing-sm);
            color: var(--color-error);
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
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
            justify-content: center;
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
            color: var(--color-dark-gray);
            border: 2px solid var(--color-light-gray);
        }
        
        .btn-secondary:hover {
            border-color: var(--color-primary);
            color: var(--color-primary);
        }
        
        .support-info {
            margin-top: var(--spacing-2xl);
            padding: var(--spacing-lg);
            background: var(--color-off-white);
            border-radius: var(--radius-lg);
            font-size: 0.875rem;
            color: var(--color-medium-gray);
        }
        
        .support-info i {
            color: var(--color-primary);
            margin-right: var(--spacing-xs);
        }
    </style>
</head>
<body>
    <div class="failed-container">
        <div class="failed-icon">
            <i class="fas fa-times"></i>
        </div>
        
        <h1 class="failed-title">Payment Failed</h1>
        <p class="failed-subtitle">
            Unfortunately, your payment could not be processed. Your order has not been confirmed.
        </p>
        
        <?php if (isset($orderId)): ?>
            <div class="failed-reasons">
                <div class="reasons-title">Common reasons for payment failure:</div>
                <ul class="reasons-list">
                    <li>
                        <i class="fas fa-credit-card"></i>
                        Insufficient funds in your account
                    </li>
                    <li>
                        <i class="fas fa-ban"></i>
                        Payment was cancelled or timed out
                    </li>
                    <li>
                        <i class="fas fa-exclamation-triangle"></i>
                        Incorrect payment information entered
                    </li>
                    <li>
                        <i class="fas fa-shield-alt"></i>
                        Transaction blocked by your bank for security reasons
                    </li>
                    <li>
                        <i class="fas fa-wifi"></i>
                        Network connection issues during payment
                    </li>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <?php if (isset($orderId)): ?>
                <a href="/orders/<?= htmlspecialchars($orderId) ?>/retry" class="btn-primary">
                    <i class="fas fa-redo"></i>
                    Try Payment Again
                </a>
            <?php endif; ?>
            
            <a href="/cart" class="btn-primary">
                <i class="fas fa-shopping-cart"></i>
                Return to Cart
            </a>
            
            <a href="/" class="btn-secondary">
                <i class="fas fa-home"></i>
                Back to Home
            </a>
        </div>
        
        <div class="support-info">
            <i class="fas fa-headset"></i>
            Need help? Contact our support team at 
            <strong>support@lumora.com</strong>
        </div>
    </div>
</body>
</html>