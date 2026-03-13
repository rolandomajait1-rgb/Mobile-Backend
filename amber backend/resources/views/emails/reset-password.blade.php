<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { display: inline-block; padding: 12px 24px; background: #4F46E5; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Your Password</h2>
        
        <p>Hi {{ $user->name }},</p>
        
        <p>You requested to reset your password. Click the button below to reset it:</p>
        
        <a href="{{ $resetUrl }}" class="button">Reset Password</a>
        
        <p>Or copy and paste this link into your browser:</p>
        <p style="word-break: break-all; color: #4F46E5;">{{ $resetUrl }}</p>
        
        <p>This link will expire in 60 minutes.</p>
        
        <p>If you didn't request a password reset, please ignore this email.</p>
        
        <div class="footer">
            <p>Best regards,<br>La Verdad Herald Team</p>
        </div>
    </div>
</body>
</html>
