<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Seller - Lumora</title>
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
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .form-content {
            padding: 50px;
        }

        .alert {
            padding: 16px 20px;
            margin-bottom: 30px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert.error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .alert.success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }

        .alert i {
            font-size: 20px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #1e4d3d;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }

        .form-group {
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #444;
            font-size: 14px;
        }

        label .required {
            color: #e74c3c;
            margin-left: 3px;
        }

        input, textarea, select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #1e4d3d;
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 77, 61, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            pointer-events: none;
        }

        .input-icon input {
            padding-left: 45px;
        }

        .helper-text {
            font-size: 13px;
            color: #777;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .helper-text i {
            font-size: 12px;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
            color: white;
            padding: 18px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(30, 77, 61, 0.4);
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 77, 61, 0.5);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .terms-checkbox {
            display: flex;
            align-items: start;
            gap: 10px;
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .terms-checkbox input[type="checkbox"] {
            width: auto;
            margin-top: 3px;
            cursor: pointer;
        }

        .terms-checkbox label {
            margin: 0;
            font-weight: 400;
            font-size: 14px;
            cursor: pointer;
        }

        .terms-checkbox a {
            color: #1e4d3d;
            text-decoration: none;
            font-weight: 600;
        }

        .terms-checkbox a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .form-content {
                padding: 30px 25px;
            }

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 26px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 18px;
            }
        }

        .info-banner {
            background: #e8f4f8;
            border-left: 4px solid #3498db;
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            align-items: start;
            gap: 12px;
        }

        .info-banner i {
            color: #3498db;
            font-size: 20px;
            margin-top: 2px;
        }

        .info-banner p {
            margin: 0;
            color: #2c3e50;
            font-size: 14px;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-store"></i> Become a Seller on Lumora</h1>
        <p>Join our marketplace and start selling your products today</p>
    </div>

    <div class="form-content">
        <!-- Success or Error Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Info Banner -->
        <div class="info-banner">
            <i class="fas fa-info-circle"></i>
            <div>
                <p><strong>Getting Started:</strong> Complete this registration form to create your seller account. Once submitted, your application will be reviewed by our team. You'll receive a confirmation email within 24-48 hours.</p>
            </div>
        </div>

        <form action="/seller/register" method="POST" id="sellerRegistrationForm">
            
            <!-- Shop Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-store-alt"></i>
                    <span>Shop Information</span>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="shop_name">Shop Name <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-shop"></i>
                            <input type="text" id="shop_name" name="shop_name" placeholder="Enter your shop name" required>
                        </div>
                        <div class="helper-text">
                            <i class="fas fa-lightbulb"></i>
                            <span>Choose a unique and memorable name for your shop</span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="shop_description">Shop Description</label>
                        <textarea id="shop_description" name="shop_description" placeholder="Describe what your shop offers, your product categories, and what makes your shop unique..."></textarea>
                        <div class="helper-text">
                            <i class="fas fa-info-circle"></i>
                            <span>This will be displayed on your shop profile page</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-address-card"></i>
                    <span>Contact Information</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_email">Contact Email <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="contact_email" name="contact_email" placeholder="shop@example.com" required>
                        </div>
                        <div class="helper-text">
                            <i class="fas fa-info-circle"></i>
                            <span>For customer inquiries and order notifications</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact_phone">Contact Phone <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="contact_phone" name="contact_phone" placeholder="+63 912 345 6789" required>
                        </div>
                        <div class="helper-text">
                            <i class="fas fa-info-circle"></i>
                            <span>Include country code (e.g., +63 for Philippines)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shop Address Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Shop Address</span>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="address_line_1">Address Line 1 <span class="required">*</span></label>
                        <input type="text" id="address_line_1" name="address_line_1" placeholder="Street address, building name, unit number" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="address_line_2">Address Line 2</label>
                        <input type="text" id="address_line_2" name="address_line_2" placeholder="Additional address information (optional)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="barangay">Barangay <span class="required">*</span></label>
                        <input type="text" id="barangay" name="barangay" placeholder="Enter barangay" required>
                    </div>

                    <div class="form-group">
                        <label for="city">City/Municipality <span class="required">*</span></label>
                        <input type="text" id="city" name="city" placeholder="Enter city" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="province">Province <span class="required">*</span></label>
                        <input type="text" id="province" name="province" placeholder="Enter province" required>
                    </div>

                    <div class="form-group">
                        <label for="region">Region <span class="required">*</span></label>
                        <select id="region" name="region" required>
                            <option value="">Select region</option>
                            <option value="NCR">NCR - National Capital Region</option>
                            <option value="CAR">CAR - Cordillera Administrative Region</option>
                            <option value="Region I">Region I - Ilocos Region</option>
                            <option value="Region II">Region II - Cagayan Valley</option>
                            <option value="Region III">Region III - Central Luzon</option>
                            <option value="Region IV-A">Region IV-A - CALABARZON</option>
                            <option value="Region IV-B">Region IV-B - MIMAROPA</option>
                            <option value="Region V">Region V - Bicol Region</option>
                            <option value="Region VI">Region VI - Western Visayas</option>
                            <option value="Region VII">Region VII - Central Visayas</option>
                            <option value="Region VIII">Region VIII - Eastern Visayas</option>
                            <option value="Region IX">Region IX - Zamboanga Peninsula</option>
                            <option value="Region X">Region X - Northern Mindanao</option>
                            <option value="Region XI">Region XI - Davao Region</option>
                            <option value="Region XII">Region XII - SOCCSKSARGEN</option>
                            <option value="Region XIII">Region XIII - Caraga</option>
                            <option value="BARMM">BARMM - Bangsamoro Autonomous Region</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="postal_code">Postal Code <span class="required">*</span></label>
                        <input type="text" id="postal_code" name="postal_code" placeholder="e.g., 1600" pattern="[0-9]{4}" maxlength="4" required>
                        <div class="helper-text">
                            <i class="fas fa-info-circle"></i>
                            <span>4-digit postal code</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="terms-checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    I agree to the <a href="/terms" target="_blank">Terms and Conditions</a> and <a href="/seller-policy" target="_blank">Seller Policy</a>. I understand that all information provided will be verified and my shop will be subject to Lumora's quality standards.
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Submit Application
            </button>
        </form>
    </div>
</div>

<script>
    // Form validation
    document.getElementById('sellerRegistrationForm').addEventListener('submit', function(e) {
        const phone = document.getElementById('contact_phone').value;
        const postalCode = document.getElementById('postal_code').value;
        
        // Validate phone format
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
        if (!phoneRegex.test(phone)) {
            e.preventDefault();
            alert('Please enter a valid phone number');
            return false;
        }
        
        // Validate postal code
        if (!/^\d{4}$/.test(postalCode)) {
            e.preventDefault();
            alert('Please enter a valid 4-digit postal code');
            return false;
        }
    });

    // Auto-format phone number
    document.getElementById('contact_phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.startsWith('63')) {
            value = '+' + value;
        }
        e.target.value = value;
    });
</script>

</body>
</html>