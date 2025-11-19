<?php

namespace App\Notifications;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionWonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 60, 300];
    }

    // Provides the auction and the winning bid context
    public function __construct(
        public Auction $auction,
        public Bid $winningBid,
    ) {
        // Send only after commit to avoid ghost emails
        $this->afterCommit = true;
    }

    // Deliver via email and database
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    // Route each channel to a dedicated queue
    public function viaQueues(): array
    {
        return [
            'mail' => 'mail',
            'database' => 'notifications',
        ];
    }

    // Email body sent to the winner
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You won the auction: ' . $this->auction->title)
            ->greeting('Congratulations!')
            ->line('You have the highest bid of ' . number_format((float) $this->winningBid->bid_amount, 2))
            ->action('View Auction', url('/auctions/' . $this->auction->id))
            ->line('We will contact you with next steps.');
    }

    // Database notification payload
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_won',
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'bid_id' => $this->winningBid->id,
            'bid_amount' => (float) $this->winningBid->bid_amount,
        ];
    }
}
