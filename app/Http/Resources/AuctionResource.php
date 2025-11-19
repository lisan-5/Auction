<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type?->value ?? (string) $this->type,
            'status' => $this->status?->value ?? (string) $this->status,
            'thumbnail_url' => $this->thumbnail_url,
            'images' => $this->getMedia('artwork_images')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ];
            }),
            'start_time' => $this->start_time?->toIso8601String(),
            'end_time' => $this->end_time?->toIso8601String(),
            'starting_price' => (float) $this->starting_price,
            'current_price' => $this->current_price,
            'reserve_price' => $this->reserve_price !== null ? (float) $this->reserve_price : null,
            'ends_in_seconds' => $this->ends_in_seconds,
            'winner_bid_id' => $this->winner_bid_id,
            'year_created' => $this->year_created,
            'dimensions' => $this->dimensions,
            'province' => $this->province,
            'condition' => $this->condition?->value ?? (string) $this->condition,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'artist' => [
                'id' => $this->artist?->id,
                'name' => $this->artist?->name,
                'email' => $this->artist?->email,
            ],
        ];
    }
}
