<?php

namespace App\Notifications;

use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Send the notification using MailService
     */
    public function toMail($notifiable)
    {
        try {
            $token = Str::random(64);
            
            // Store verification token
            \DB::table('email_verification_tokens')->updateOrInsert(
                ['email' => $notifiable->email],
                [
                    'token' => hash('sha256', $token),
                    'created_at' => now()
                ]
            );

            $mailService = app(MailService::class);
            $mailService->sendVerificationEmail($notifiable, $token);
            
            \Log::info('Verification email sent successfully', ['email' => $notifiable->email]);
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email', [
                'email' => $notifiable->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - just log the error
        }
        
        // Return a dummy MailMessage to satisfy Laravel's notification system
        return new MailMessage();
    }
}
