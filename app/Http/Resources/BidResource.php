<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BidResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'auction_id' => $this->auction_id,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'bid_amount' => (float) $this->bid_amount,
            'is_visible' => (bool) $this->is_visible,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
