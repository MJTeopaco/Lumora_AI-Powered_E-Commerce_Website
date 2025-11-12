<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: 2px;">LUMORA</h1>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 16px; color: #1f2937; font-size: 24px; font-weight: 600;">Password Reset Request</h2>
                            <p style="margin: 0 0 24px; color: #6b7280; font-size: 15px; line-height: 1.6;">Hello <?= htmlspecialchars($username) ?>,</p>
                            <p style="margin: 0 0 24px; color: #6b7280; font-size: 15px; line-height: 1.6;">We received a request to reset your password. Use the verification code below to proceed:</p>
                            
                            <table role="presentation" style="width: 100%; margin: 32px 0;">
                                <tr>
                                    <td style="text-align: center;">
                                        <div style="display: inline-block; background-color: #f0f5f3; border: 2px solid #2d5a4a; border-radius: 12px; padding: 20px 40px;">
                                            <span style="font-size: 36px; font-weight: 700; color: #1e4d3d; letter-spacing: 8px;"><?= htmlspecialchars($otp) ?></span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 24px 0 0; color: #6b7280; font-size: 14px; line-height: 1.6;">This code will expire in <strong style="color: #1e4d3d;">2 minutes</strong>.</p>
                            <div style="margin: 24px 0; padding: 16px; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px;">
                                <p style="margin: 0; color: #92400e; font-size: 13px; line-height: 1.6;"><strong>Security Notice:</strong> If you didn't request a password reset, please ignore this email and your password will remain unchanged.</p>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="background-color: #f9fafb; padding: 24px 30px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; color: #6b7280; font-size: 13px; text-align: center; line-height: 1.6;">
                                © 2025 Lumora. All rights reserved.<br>
                                Exquisite Accessories for Every Occasion
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>