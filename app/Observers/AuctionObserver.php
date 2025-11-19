<?php

namespace App\Observers;

use App\Enums\AuctionStatus;
use App\Models\Auction;
use App\Notifications\AuctionWonNotification;

class AuctionObserver
{
    public function updated(Auction $auction): void
    {
        // When the auction status changes to ENDED, notify the winner
        $status = $auction->status instanceof AuctionStatus ? $auction->status : AuctionStatus::tryFrom((string) $auction->status);

        if ($auction->wasChanged('status') && $status === AuctionStatus::ENDED) {
            $winningBid = $auction->highestBid();
            if ($winningBid && $winningBid->user) {
                $winningBid->user->notify(new AuctionWonNotification($auction, $winningBid));
            }
        }
    }
}
