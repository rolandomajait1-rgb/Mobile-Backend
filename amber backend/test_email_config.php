<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Email Configuration Test ===\n\n";

// Check mail configuration
echo "MAIL_MAILER: " . config('mail.default') . "\n";
echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n";
echo "MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption') . "\n";
echo "MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n";
echo "MAIL_FROM_NAME: " . config('mail.from.name') . "\n\n";

// Check Brevo configuration
echo "BREVO_API_KEY: " . (config('services.brevo.key') ? 'Set (***' . substr(config('services.brevo.key'), -8) . ')' : 'NOT SET') . "\n\n";

// Test Brevo API connection
echo "Testing Brevo API connection...\n";
try {
    $response = \Illuminate\Support\Facades\Http::timeout(10)
        ->withHeaders(['api-key' => config('services.brevo.key')])
        ->get('https://api.brevo.com/v3/account');
    
    if ($response->successful()) {
        echo "✓ Brevo API connection successful!\n";
        $data = $response->json();
        echo "  Account email: " . ($data['email'] ?? 'N/A') . "\n";
    } else {
        echo "✗ Brevo API connection failed!\n";
        echo "  Status: " . $response->status() . "\n";
        echo "  Response: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "✗ Error connecting to Brevo API: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
