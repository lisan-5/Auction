<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $resetUrl)
    {
        $this->afterCommit = true;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function viaQueues(): array
    {
        return ['mail' => 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $app = config('app.name', 'Art Auction');
        return (new MailMessage)
            ->subject('Reset your password for ' . $app)
            ->greeting('Hello ' . ($notifiable->name ?? ''))
            ->line('We received a request to reset your password for your ' . $app . ' account.')
            ->action('Reset Password', $this->resetUrl)
            ->line('This link will expire in 60 minutes and can only be used once.')
            ->line('If you did not request a password reset, no action is required.');
    }
}