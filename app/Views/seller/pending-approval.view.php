<?php
// app/Views/seller/pending-approval.view.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Pending - Lumora</title>
    <link rel="stylesheet" href="/css/main.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .pending-container {
            max-width: 700px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            text-align: center;
        }

        .pending-header {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            color: white;
            padding: 50px 40px;
        }

        .pending-icon {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .pending-icon i {
            font-size: 60px;
        }

        .pending-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .pending-header p {
            font-size: 18px;
            opacity: 0.95;
        }

        .pending-content {
            padding: 50px 40px;
        }

        .status-card {
            background: #fff8f0;
            border: 2px solid #f59e0b;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .status-card h2 {
            color: #1e4d3d;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .status-card p {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .shop-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .shop-info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .shop-info-item:last-child {
            border-bottom: none;
        }

        .shop-info-label {
            font-weight: 600;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .shop-info-label i {
            color: #1e4d3d;
        }

        .shop-info-value {
            color: #333;
            font-weight: 500;
        }

        .timeline {
            margin: 30px 0;
        }

        .timeline-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            text-align: left;
        }

        .timeline-icon {
            width: 40px;
            height: 40px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .timeline-icon i {
            color: white;
            font-size: 18px;
        }

        .timeline-icon.pending {
            background: #f59e0b;
        }

        .timeline-icon.future {
            background: #d1d5db;
        }

        .timeline-content h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }

        .timeline-content p {
            font-size: 14px;
            color: #777;
            margin: 0;
        }

        .btn-home {
            display: inline-block;
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            color: white;
            padding: 15px 40px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(30, 77, 61, 0.4);
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 77, 61, 0.5);
        }

        .btn-home i {
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .pending-header {
                padding: 40px 30px;
            }

            .pending-header h1 {
                font-size: 26px;
            }

            .pending-content {
                padding: 40px 30px;
            }

            .status-card {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>

<div class="pending-container">
    <div class="pending-header">
        <div class="pending-icon">
            <i class="fas fa-clock"></i>
        </div>
        <h1>Application Under Review</h1>
        <p>Your seller application is being processed</p>
    </div>

    <div class="pending-content">
        <div class="status-card">
            <h2>We're Reviewing Your Application</h2>
            <p>Thank you for submitting your seller application! Our team is currently reviewing your information to ensure everything meets our quality standards.</p>
            
            <div class="shop-info">
                <div class="shop-info-item">
                    <span class="shop-info-label">
                        <i class="fas fa-store"></i>
                        Shop Name
                    </span>
                    <span class="shop-info-value"><?= htmlspecialchars($shopName) ?></span>
                </div>
                <div class="shop-info-item">
                    <span class="shop-info-label">
                        <i class="fas fa-calendar"></i>
                        Applied On
                    </span>
                    <span class="shop-info-value"><?= date('F d, Y', strtotime($appliedAt)) ?></span>
                </div>
            </div>
        </div>

        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="timeline-content">
                    <h3>Application Submitted</h3>
                    <p>Your application has been successfully received</p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-icon pending">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="timeline-content">
                    <h3>Under Review</h3>
                    <p>Our team is verifying your information (24-48 hours)</p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-icon future">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="timeline-content">
                    <h3>Approval & Setup</h3>
                    <p>Once approved, you'll receive an email with next steps</p>
                </div>
            </div>
        </div>

        <p style="color: #555; font-size: 15px; margin-bottom: 25px;">
            <i class="fas fa-envelope" style="color: #1e4d3d;"></i>
            You will receive an email notification once your application has been reviewed. This typically takes 24-48 hours.
        </p>

        <a href="/" class="btn-home">
            <i class="fas fa-home"></i>
            Return to Homepage
        </a>
    </div>
</div>

</body>
</html>