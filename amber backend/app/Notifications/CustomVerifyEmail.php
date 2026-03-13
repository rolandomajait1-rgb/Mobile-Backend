<?php

namespace App\Notifications;

use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        
        // Return null since we handled sending manually
        return null;
    }
}
