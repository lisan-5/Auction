<?php

namespace App\Notifications;

use App\Enums\AuctionType;
use App\Models\Bid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // Basic retry policy
    public int $tries = 3;

    public function backoff(): array
    {
        // Seconds between retries
        return [10, 60, 300];
    }

    // Carries the bid that triggered this notification
    public function __construct(public Bid $bid)
    {
        // Ensure queued notification dispatches only after DB commit
        $this->afterCommit = true;
    }

    // Use both mail and database channels for delivery
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    // Send mail and database notifications to separate queues
    public function viaQueues(): array
    {
        return [
            'mail' => 'mail',
            'database' => 'notifications',
        ];
    }

    // Compose the email sent to recipients
    public function toMail(object $notifiable): MailMessage
    {
        $auction = $this->bid->auction()->first();

        // Sealed-auction privacy: for CLOSED auctions before end_time, don't reveal bid amounts in emails.
        // Old (always revealed amount) — commented out to prevent leakage in sealed bids:
        // return (new MailMessage)
        //     ->subject('New bid on ' . ($auction?->title ?? 'an auction'))
        //     ->greeting('Hello ' . ($notifiable->name ?? 'there'))
        //     ->line('A new bid of ' . number_format((float) $this->bid->bid_amount, 2) . ' was placed.')
        //     ->line('Auction: ' . ($auction?->title ?? 'Unknown'))
        //     ->action('View Auction', url('/auctions/' . ($auction?->id ?? '')))
        //     ->line('Thank you for using our application!');

        $type = $auction?->type instanceof AuctionType ? $auction->type->value : ($auction->type ?? null);
        $isSealedActive = $type === AuctionType::CLOSED->value && now()->lt($auction?->end_time);

        $mail = (new MailMessage)
            ->subject('New bid on '.($auction?->title ?? 'an auction'))
            ->greeting('Hello '.($notifiable->name ?? 'there'))
            ->line('Auction: '.($auction?->title ?? 'Unknown'))
            ->action('View Auction', url('/auctions/'.($auction?->id ?? '')))
            ->line('Thank you for using our application!');

        if ($isSealedActive) {
            $mail->line('A new sealed bid was placed. Amount is hidden until the auction ends.');
        } else {
            $mail->line('A new bid of '.number_format((float) $this->bid->bid_amount, 2).' was placed.');
        }

        return $mail;
    }

    // Structure of the database notification payload
    public function toArray(object $notifiable): array
    {
        $auction = $this->bid->auction()->first();

        // Sealed-auction privacy: don't reveal bid_amount for CLOSED auctions before end_time in database payload.
        // Old (always included amount) — commented out:
        // return [
        //     'type' => 'new_bid',
        //     'auction_id' => $this->bid->auction_id,
        //     'auction_title' => $auction?->title,
        //     'bid_id' => $this->bid->id,
        //     'bid_amount' => (float) $this->bid->bid_amount,
        //     'placed_by' => $this->bid->user_id,
        // ];

        $type = $auction?->type instanceof AuctionType ? $auction->type->value : ($auction->type ?? null);
        $isSealedActive = $type === AuctionType::CLOSED->value && now()->lt($auction?->end_time);

        return [
            'type' => 'new_bid',
            'auction_id' => $this->bid->auction_id,
            'auction_title' => $auction?->title,
            'bid_id' => $this->bid->id,
            'bid_amount' => $isSealedActive ? null : (float) $this->bid->bid_amount,
            'placed_by' => $this->bid->user_id,
            'sealed_active' => $isSealedActive,
        ];
    }
}
