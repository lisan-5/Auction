<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 60, 300];
    }

    // Simple welcome notification sent after a user registers
    public function __construct()
    {
        // Avoid sending before the new user is committed
        $this->afterCommit = true;
    }

    // Deliver through email and store a copy in the database
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'mail',
            'database' => 'notifications',
        ];
    }

    // Email contents for the welcome message
    public function toMail(object $notifiable): MailMessage
    {
        $app = config('app.name', 'Our App');

        return (new MailMessage)
            ->subject('Welcome to ' . $app)
            ->greeting('Welcome, ' . ($notifiable->name ?? 'there') . '!')
            ->line('Thanks for joining ' . $app . '. We\'re excited to have you!')
            ->action('Go to Dashboard', url('/'))
            ->line('If you have any questions, just reply to this email.');
    }

    // JSON payload for the database notification
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'message' => 'Welcome to ' . config('app.name', 'our app') . '!',
        ];
    }
}
