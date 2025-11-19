<?php

namespace App\Events;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Event dispatched after a bid is stored; carries the bid and auction
    public function __construct(
        public Bid $bid,
        public Auction $auction,
    ) {
        // No additional setup needed
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('auction.'.$this->auction->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'BidPlaced';
    }

    /**
     * The data to broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'auction_id' => (int) $this->auction->id,
            'current_price' => (float) ($this->auction->highestBid()?->bid_amount ?? $this->auction->starting_price),
            'bid' => [
                'id' => (int) $this->bid->id,
                'amount' => (float) $this->bid->bid_amount,
                'created_at' => optional($this->bid->created_at)->toISOString(),
                'user' => [
                    'id' => (int) $this->bid->user->id,
                    'name' => (string) $this->bid->user->name,
                ],
            ],
        ];
    }
}
