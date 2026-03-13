<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class MailService {
public function sendVerificationEmail(User $user, string $token): void {
$url = config('app.url').'/api/email/verify-token?token='.$token;
$html = view('emails.verify-email', ['user' => $user, 'verificationUrl' => $url])->render();
$this->send($user, 'Verify Your Email Address', $html);
}
public function sendPasswordResetEmail(User $user, string $token): void {
$url = env('FRONTEND_URL').'/reset-password?token='.$token.'&email='.urlencode($user->email);
$html = view('emails.reset-password', ['user' => $user, 'resetUrl' => $url])->render();
$this->send($user, 'Reset Your Password', $html);
}
private function send(User $user, string $subject, string $html): void {
$r = Http::withHeaders(['api-key' => config('services.brevo.key')])->post('https://api.brevo.com/v3/smtp/email', [
'sender' => ['email' => config('mail.from.address'), 'name' => config('mail.from.name')],
'to' => [['email' => $user->email, 'name' => $user->name]],
'subject' => $subject,
'htmlContent' => $html
]);
if (!$r->successful()) throw new \RuntimeException('Brevo error');
Log::info('Email sent to '.$user->email);
}
}
