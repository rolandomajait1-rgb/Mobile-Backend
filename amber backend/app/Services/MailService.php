<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailService
{
    public function sendVerificationEmail(User $user, string $token): bool
    {
        $apiKey = config('services.brevo.key');
        if (empty($apiKey)) {
            Log::error('Brevo API key not configured. Set BREVO_API_KEY in .env (from Brevo: SMTP & API → API Keys)');
            return false;
        }

        $frontendUrl = rtrim(env('FRONTEND_URL', config('app.url', '')), '/');
        if (empty($frontendUrl)) {
            Log::error('FRONTEND_URL not set in production - verification links will be invalid');
            return false;
        }
        try {
            $url = $frontendUrl . '/verify-email?token=' . $token;
            $html = view('emails.verify-email', ['user' => $user, 'verificationUrl' => $url])->render();
            return $this->send($user, 'Verify Your Email Address', $html);
        } catch (\Exception $e) {
            Log::error('sendVerificationEmail failed', ['error' => $e->getMessage(), 'user' => $user->email]);
            return false;
        }
    }
    public function sendPasswordResetEmail(User $user, string $token): bool
    {
        $apiKey = config('services.brevo.key');
        if (empty($apiKey)) {
            Log::error('Brevo API key not configured. Set BREVO_API_KEY in .env');
            return false;
        }

        try {
            $url = rtrim(env('FRONTEND_URL'), '/') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
            $html = view('emails.reset-password', ['user' => $user, 'resetUrl' => $url])->render();
            return $this->send($user, 'Reset Your Password', $html);
        } catch (\Exception $e) {
            Log::error('sendPasswordResetEmail failed', ['error' => $e->getMessage(), 'user' => $user->email]);
            return false;
        }
    }

    private function send(User $user, string $subject, string $html): bool
    {
        try {
            $r = Http::timeout(30)->withHeaders(['api-key' => config('services.brevo.key')])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => ['email' => config('mail.from.address'), 'name' => config('mail.from.name')],
                'to' => [['email' => $user->email, 'name' => $user->name]],
                'subject' => $subject,
                'htmlContent' => $html,
            ]);
            if (!$r->successful()) {
                $body = is_string($r->body()) ? json_decode($r->body(), true) : $r->body();
                $brevoError = $body['message'] ?? $body['code'] ?? $r->body();
                Log::error('Brevo API error', [
                    'status' => $r->status(),
                    'brevo_error' => $brevoError,
                    'user' => $user->email,
                    'sender' => config('mail.from.address'),
                ]);
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
