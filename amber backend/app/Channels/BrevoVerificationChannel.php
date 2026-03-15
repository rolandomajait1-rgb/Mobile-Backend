<?php

namespace App\Channels;

use App\Contracts\BrevoVerificationNotification;
use Illuminate\Notifications\Notification;

class BrevoVerificationChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (!$notification instanceof BrevoVerificationNotification) {
            return;
        }

        $notification->toBrevoVerification($notifiable);
    }
}
