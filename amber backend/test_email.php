<?php

// Quick email test script
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing email configuration...\n\n";

// Check mail config
echo "MAIL_MAILER: " . config('mail.default') . "\n";
echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n";
echo "MAIL_FROM: " . config('mail.from.address') . "\n\n";

// Try sending a test email
try {
    $testEmail = 'test@example.com'; // Change this to your email
    
    Mail::raw('This is a test email from your Laravel app', function ($message) use ($testEmail) {
        $message->to($testEmail)
                ->subject('Test Email');
    });
    
    echo "✅ Email sent successfully!\n";
    echo "Check your inbox at: $testEmail\n";
} catch (\Exception $e) {
    echo "❌ Email failed: " . $e->getMessage() . "\n";
}
