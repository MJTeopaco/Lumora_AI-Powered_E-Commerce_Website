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
                        <td style="padding: 40px 30px; text-align: center;">
                            <div style="width: 80px; height: 80px; margin: 0 auto 24px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 40px; color: white;">✓</span>
                            </div>
                            <h2 style="margin: 0 0 16px; color: #1e4d3d; font-size: 24px; font-weight: 600;">Password Changed Successfully</h2>
                            <p style="margin: 0 0 24px; color: #1f2937; font-size: 16px; font-weight: 500;">Hello, <?= htmlspecialchars($username) ?>!</p>
                            <p style="margin: 0 0 32px; color: #6b7280; font-size: 15px; line-height: 1.6;">Your password has been successfully changed. You can now login with your new password.</p>
                            
                            <div style="margin: 32px 0; padding: 16px; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px;">
                                <p style="margin: 0; color: #92400e; font-size: 13px; line-height: 1.6;"><strong>Security Alert:</strong> If you didn't make this change, please contact our support team immediately at support@lumora.com</p>
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