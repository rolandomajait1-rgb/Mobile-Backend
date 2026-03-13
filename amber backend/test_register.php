<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/register', 'POST', [
    'name' => 'Test User',
    'email' => 'test_error_x3@laverdad.edu.ph',
    'password' => 'password123',
    'password_confirmation' => 'password123'
]);

$response = app()->handle($request);
echo "Status: " . $response->status() . "\n";
echo "Content: " . $response->getContent() . "\n";
