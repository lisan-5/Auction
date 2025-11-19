<?php

namespace App\Listeners;

use App\Events\BidPlaced;
use App\Notifications\NewBidNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBidNotifications implements ShouldQueue
{
    // Run this listener only after the DB transaction commits
    public bool $afterCommit = true;

    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 60, 300];
    }

    /**
     * Handle the event.
     */
    public function handle(BidPlaced $event): void
    {
        // Eager load related artist and bidders
        $auction = $event->auction->loadMissing(['artist', 'bids.user']);

        // 1) Notify the auction owner (artist), unless they placed the bid
        if ($auction->artist && $auction->artist->id !== $event->bid->user_id) {
            $auction->artist->notify(new NewBidNotification($event->bid));
        }

        // 2) Notify other bidders (exclude the user who just placed the bid)
        $bidders = $auction->bids
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->reject(fn ($user) => (int) $user->id === (int) $event->bid->user_id);

        foreach ($bidders as $user) {
            $user->notify(new NewBidNotification($event->bid));
        }
    }
}
