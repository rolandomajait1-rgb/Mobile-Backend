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
        <h2>Verify Your Email Address</h2>
        
        <p>Hi {{ $user->name }},</p>
        
        <p>Thank you for registering with La Verdad Herald! Please verify your email address by clicking the button below:</p>
        
        <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
        
        <p>Or copy and paste this link into your browser:</p>
        <p style="word-break: break-all; color: #4F46E5;">{{ $verificationUrl }}</p>
        
        <p>If you didn't create an account, please ignore this email.</p>
        
        <div class="footer">
            <p>Best regards,<br>La Verdad Herald Team</p>
        </div>
    </div>
</body>
</html>
