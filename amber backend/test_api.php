<?php
$data = [
    'name' => 'Test User',
    'email' => 'test_user_p' . rand(1, 1000) . '@laverdad.edu.ph',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

$ch = curl_init('http://localhost:8000/api/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch) . "\n";
}

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";
