<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * ALTERNATIVE IMPLEMENTATION - Use Laravel's built-in mail system
 * This is simpler and more reliable than custom MailService
 * 
 * To use this:
 * 1. Rename this file to CustomVerifyEmail.php (backup the old one)
 * 2. Make sure MAIL_MAILER=smtp in .env.production
 * 3. Deploy and test
 */
class CustomVerifyEmailSimple extends Notification
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
     * Build the mail message using Laravel's built-in system
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

        $verificationUrl = config('app.url') . '/api/email/verify-token?token=' . $token;

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Thank you for registering with La Verdad Herald!')
            ->line('Please verify your email address by clicking the button below:')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you didn\'t create an account, please ignore this email.')
            ->salutation('Best regards, La Verdad Herald Team');
    }
}
