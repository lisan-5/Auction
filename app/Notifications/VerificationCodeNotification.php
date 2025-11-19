<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $code,
        public int $ttlMinutes,
        public ?string $magicLink = null
    ) {
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
        $app = config('app.name', 'Our App');
        $mail = (new MailMessage)
            ->subject('Your ' . $app . ' verification code')
            ->greeting('Hi!')
            ->line('Use the following code to complete your registration:')
            ->line('')
            ->line('Code: ' . $this->code)
            ->line('')
            ->line('This code expires in ' . $this->ttlMinutes . ' minutes.');
        if ($this->magicLink) {
            $mail->line('Or click the magic link below to register instantly:')
                ->action('Register Instantly', $this->magicLink);
        }
        $mail->line('If you didn’t request this, you can ignore this email.');
        return $mail;
    }
}
