<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh');
    echo \Illuminate\Support\Facades\Artisan::output();
} catch (\Throwable $e) {
    file_put_contents('migration_error.txt', "Exception: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
}
