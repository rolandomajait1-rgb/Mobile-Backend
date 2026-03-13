<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class MailService {
public function sendVerificationEmail(User $user, string $token): bool {
try {
$url = config('app.url').'/api/email/verify-token?token='.$token;
$html = view('emails.verify-email', ['user' => $user, 'verificationUrl' => $url])->render();
return $this->send($user, 'Verify Your Email Address', $html);
} catch (\Exception $e) {
Log::error('sendVerificationEmail failed', ['error' => $e->getMessage(), 'user' => $user->email]);
return false;
}
}
public function sendPasswordResetEmail(User $user, string $token): bool {
try {
$url = env('FRONTEND_URL').'/reset-password?token='.$token.'&email='.urlencode($user->email);
$html = view('emails.reset-password', ['user' => $user, 'resetUrl' => $url])->render();
return $this->send($user, 'Reset Your Password', $html);
} catch (\Exception $e) {
Log::error('sendPasswordResetEmail failed', ['error' => $e->getMessage(), 'user' => $user->email]);
return false;
}
}
private function send(User $user, string $subject, string $html): bool {
try {
$r = Http::timeout(30)->withHeaders(['api-key' => config('services.brevo.key')])->post('https://api.brevo.com/v3/smtp/email', [
'sender' => ['email' => config('mail.from.address'), 'name' => config('mail.from.name')],
'to' => [['email' => $user->email, 'name' => $user->name]],
'subject' => $subject,
'htmlContent' => $html
]);
if (!$r->successful()) {
Log::error('Brevo API error', ['status' => $r->status(), 'body' => $r->body(), 'user' => $user->email]);
return false;
}
Log::info('Email sent successfully', ['to' => $user->email, 'subject' => $subject]);
return true;
} catch (\Exception $e) {
Log::error('Email send exception', ['error' => $e->getMessage(), 'user' => $user->email, 'subject' => $subject]);
return false;
}
}
}
