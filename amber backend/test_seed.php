<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \Illuminate\Support\Facades\Artisan::call('db:seed');
    echo \Illuminate\Support\Facades\Artisan::output();
} catch (\Throwable $e) {
    file_put_contents('seeder_error.txt', "Exception: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
}
