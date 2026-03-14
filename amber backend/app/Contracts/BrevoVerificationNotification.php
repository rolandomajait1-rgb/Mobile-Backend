<?php

namespace App\Contracts;

interface BrevoVerificationNotification
{
    public function toBrevoVerification(object $notifiable): void;
}
