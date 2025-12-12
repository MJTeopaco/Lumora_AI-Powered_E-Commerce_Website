<?php
// app/Views/seller/pending-approval.view.php
// Main layout handles HTML/CSS/JS inclusions
?>
<div class="pending-container">
    <div class="pending-header">
        <div class="pending-icon">
            <i class="fas fa-clock"></i>
        </div>
        <h1>Application Under Review</h1>
        <p>Your seller application is being processed</p>
    </div>

    <div class="form-content">
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
                <div class="timeline-icon completed">
                    <i class="fas fa-check"></i>
                </div>
                <div class="timeline-content">
                    <h3>Application Submitted</h3>
                    <p>Your application has been successfully received</p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-icon active">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="timeline-content">
                    <h3>Under Review</h3>
                    <p>Our team is verifying your information (24-48 hours)</p>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="timeline-content">
                    <h3>Approval & Setup</h3>
                    <p>Once approved, you'll receive an email with next steps</p>
                </div>
            </div>
        </div>

        <p style="color: #555; font-size: 15px; margin-bottom: 25px;">
            <i class="fas fa-envelope" style="color: #D4AF37; margin-right: 5px;"></i>
            You will receive an email notification once your application has been reviewed.
        </p>

        <a href="/" class="btn-home">
            <i class="fas fa-home"></i>
            Return to Homepage
        </a>
    </div>
</div>