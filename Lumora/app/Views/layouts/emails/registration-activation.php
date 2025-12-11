<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #D4AF37 0%, #D4AF37 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .content h2 {
            color: #2d3748;
            margin-top: 0;
            font-size: 24px;
        }
        .content p {
            color: #4a5568;
            line-height: 1.6;
            font-size: 16px;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #D4AF37 0%, #D4AF37 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .footer {
            background: #f7fafc;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
            color: #718096;
            font-size: 14px;
        }
        .warning {
            background: #fff5f5;
            border-left: 4px solid #f56565;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning p {
            margin: 0;
            color: #742a2a;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Welcome to Lumora!</h1>
        </div>
        
        <div class="content">
            <h2>Activate Your Account</h2>
            <p>Thank you for registering with Lumora! We're excited to have you join our community of style enthusiasts.</p>
            <p>To complete your registration and start shopping, please click the button below to activate your account:</p>
            
            <div class="btn-container">
                <a href="<?= htmlspecialchars($activationLink) ?>" class="btn">Activate My Account</a>
            </div>
            
            <div class="warning">
                <p><strong>⏰ Important:</strong> This activation link will expire in 3 minutes. Please activate your account soon!</p>
            </div>
            
        </div>
        
        <div class="footer">
            <p><strong>Didn't create this account?</strong></p>
            <p>If you didn't register for Lumora, please ignore this email and your information will not be stored.</p>
            <p style="margin-top: 15px;">© <?= date('Y') ?> Lumora. All rights reserved.</p>
        </div>
    </div>
</body>
</html>