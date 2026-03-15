<?php
/**
 * LOCAL DEBUG SCRIPT - Test Registration Flow
 * Run: php debug_registration.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUGGING REGISTRATION SYSTEM ===\n\n";

// Test 1: Check if BrevoVerificationChannel exists
echo "[1] Checking BrevoVerificationChannel class...\n";
$channelPath = __DIR__ . '/app/Channels/BrevoVerificationChannel.php';
if (file_exists($channelPath)) {
    echo "✅ File exists: $channelPath\n";
    require_once $channelPath;
    if (class_exists('App\Channels\BrevoVerificationChannel')) {
        echo "✅ Class can be loaded\n";
    } else {
        echo "❌ Class cannot be loaded\n";
    }
} else {
    echo "❌ File NOT found: $channelPath\n";
    echo "   This is the CRITICAL BUG!\n";
}
echo "\n";

// Test 2: Check if CustomVerifyEmail notification exists
echo "[2] Checking CustomVerifyEmail notification...\n";
if (class_exists('App\Notifications\CustomVerifyEmail')) {
    echo "✅ CustomVerifyEmail class exists\n";
} else {
    echo "❌ CustomVerifyEmail class NOT found\n";
}
echo "\n";

// Test 3: Check database connection
echo "[3] Testing database connection...\n";
try {
    DB::connection()->getPdo();
    echo "✅ Database connected\n";
    echo "   Database: " . DB::connection()->getDatabaseName() . "\n";
} catch (\Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Check if email_verification_tokens table exists
echo "[4] Checking email_verification_tokens table...\n";
try {
    if (Schema::hasTable('email_verification_tokens')) {
        echo "✅ Table exists\n";
        $columns = Schema::getColumnListing('email_verification_tokens');
        echo "   Columns: " . implode(', ', $columns) . "\n";
    } else {
        echo "❌ Table does NOT exist\n";
        echo "   Run: php artisan migrate\n";
    }
} catch (\Exception $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Check Brevo API key
echo "[5] Checking Brevo configuration...\n";
$brevoKey = config('services.brevo.key');
if (!empty($brevoKey)) {
    echo "✅ BREVO_API_KEY is set\n";
    echo "   Key: " . substr($brevoKey, 0, 20) . "...\n";
} else {
    echo "❌ BREVO_API_KEY is NOT set\n";
}
echo "\n";

// Test 6: Check FRONTEND_URL
echo "[6] Checking FRONTEND_URL...\n";
$frontendUrl = env('FRONTEND_URL');
if (!empty($frontendUrl)) {
    echo "✅ FRONTEND_URL is set: $frontendUrl\n";
} else {
    echo "❌ FRONTEND_URL is NOT set\n";
}
echo "\n";

// Test 7: Check mail configuration
echo "[7] Checking mail configuration...\n";
echo "   MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n";
echo "   MAIL_FROM_NAME: " . config('mail.from.name') . "\n";
echo "   MAIL_MAILER: " . config('mail.default') . "\n";
echo "\n";

// Test 8: Try to instantiate notification channel
echo "[8] Testing notification channel registration...\n";
try {
    $channel = app()->make('App\Channels\BrevoVerificationChannel');
    echo "✅ Channel can be instantiated\n";
} catch (\Exception $e) {
    echo "❌ Channel instantiation failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 9: Check AppServiceProvider boot method
echo "[9] Checking AppServiceProvider...\n";
try {
    $provider = new App\Providers\AppServiceProvider($app);
    echo "✅ AppServiceProvider can be instantiated\n";
} catch (\Exception $e) {
    echo "❌ AppServiceProvider error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 10: Simulate registration
echo "[10] Simulating registration flow...\n";
try {
    $testEmail = 'debug' . time() . '@student.laverdad.edu.ph';
    echo "   Creating test user: $testEmail\n";
    
    $user = new App\Models\User();
    $user->name = 'Debug Test';
    $user->email = $testEmail;
    $user->password = Hash::make('TestPass123');
    $user->role = 'user';
    
    echo "   User object created\n";
    
    // Don't actually save, just test the notification
    echo "   Testing notification...\n";
    try {
        $notification = new App\Notifications\CustomVerifyEmail();
        echo "   ✅ Notification created\n";
        
        // Check via method
        $via = $notification->via($user);
        echo "   Via channels: " . implode(', ', $via) . "\n";
        
    } catch (\Exception $e) {
        echo "   ❌ Notification error: " . $e->getMessage() . "\n";
        echo "   Stack trace:\n";
        echo $e->getTraceAsString() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Simulation failed: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
echo "\n";

echo "=== DEBUG COMPLETE ===\n\n";

echo "SUMMARY:\n";
echo "--------\n";
echo "If you see ❌ errors above, those are the problems!\n";
echo "Most common issues:\n";
echo "1. BrevoVerificationChannel file missing\n";
echo "2. email_verification_tokens table not migrated\n";
echo "3. BREVO_API_KEY not set\n";
echo "4. FRONTEND_URL not set\n";
echo "\n";
