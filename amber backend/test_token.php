<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$record = \DB::table('email_verification_tokens')->orderBy('created_at', 'desc')->first();
if ($record) {
    echo "Found token record for email: " . $record->email . "\n";
    echo "Hashed Token in DB: " . $record->token . "\n";
} else {
    echo "No token found in database.\n";
}
