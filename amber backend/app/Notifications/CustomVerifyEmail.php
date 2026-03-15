<?php

namespace App\Notifications;

use App\Contracts\BrevoVerificationNotification;
use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomVerifyEmail extends Notification implements BrevoVerificationNotification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['brevo_verification'];
    }

    public function toBrevoVerification($notifiable): void
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('email_verification_tokens')) {
                Log::error('email_verification_tokens table missing');
                throw new \Exception('Verification system not configured');
            }

            $token = Str::random(64);
            DB::table('email_verification_tokens')->updateOrInsert(
                ['email' => $notifiable->email],
                ['token' => hash('sha256', $token), 'created_at' => now()]
            );
            
            $sent = app(MailService::class)->sendVerificationEmail($notifiable, $token);
            if (!$sent) {
                throw new \Exception('Failed to send verification email');
            }
            
            Log::info('Verification email sent', ['email' => $notifiable->email]);
        } catch (\Exception $e) {
            Log::error('Verification email failed', ['email' => $notifiable->email, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
