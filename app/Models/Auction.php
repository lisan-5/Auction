<?php

namespace App\Models;

use App\Enums\AuctionCondition;
use App\Enums\AuctionStatus;
use App\Enums\AuctionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Auction extends Model implements HasMedia
{
    use InteractsWithMedia;   use HasFactory;

    protected $fillable = [
        'artist_id',
        'title',
        'description',
        'type',          // 'OPEN' | 'CLOSED'
        'status',        // 'DRAFT', 'PUBLISHED', 'LIVE', 'ENDED', 'PAYMENT_PENDING', 'PAID', 'OFFERED_TO_NEXT'
        'start_time',
        'end_time',
        'starting_price',
        'reserve_price',
        'winner_bid_id',
        'year_created',
        'dimensions',
        'province',
        'condition',
        'category_id',
    ];

    protected $casts = [
        'type' => AuctionType::class,
        'status' => AuctionStatus::class,
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'starting_price' => 'decimal:2',
        'reserve_price' => 'decimal:2',
        'condition' => AuctionCondition::class,
    ];

    public function artist()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function winnerBid()
    {
        return $this->belongsTo(Bid::class, 'winner_bid_id');
    }

    public function getEndsInSecondsAttribute()
    {
        if (empty($this->end_time)) {
            return 0;
        }

        return max(0, now()->diffInSeconds($this->end_time, false));
    }

    // Helper: current price = highest bid or starting_price
    public function getCurrentPriceAttribute(): float
    {
        $max = $this->bids()->getQuery()->withoutTrashed()->max('bid_amount');

        return (float) ($max ?? $this->starting_price);
    }

    /**
     * Returns the current highest bid model or null if none.
     */
    public function highestBid(): ?Bid
    {
        return $this->bids()->getQuery()->withoutTrashed()->orderByDesc('bid_amount')->first();
    }

    public function currentHighestBid(): float
    {
        $max = $this->bids()->getQuery()->withoutTrashed()->max('bid_amount');

        return (float) ($max ?? $this->starting_price);
    }

    /* ─────────────── Media-Library setup ─────────────── */
    public function getThumbnailUrlAttribute(): ?string
    {
        // If you defined a 'thumb' conversion, use it: getFirstMediaUrl('images', 'thumb')
        $url = $this->getFirstMediaUrl('artwork_images');

        return $url ?: null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('artwork_images')
            ->acceptsFile(function ($file) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];

                return in_array($file->mimeType, $allowed) && $file->size <= 5 * 1024 * 1024;
            })
            ->useDisk('public');
    }

    public function scopeFilter($query, $params)
    {
        if (!empty($params['category'])) {
        $query->whereHas('category', function ($q) use ($params) {
            $q->where('name', $params['category']);
        });
    }

        if (!empty($params['condition'])) {
        $query->where('condition', $params['condition']);
    }

        if (!empty($params['province'])) {
        $query->where('province', $params['province']);
    }

        if (!empty($params['keyword'])) {
        $query->where('title', 'LIKE', '%' . $params['keyword'] . '%')
                ->orWhere('description', 'LIKE', '%' . $params['keyword'] . '%');
    }

        if (!empty($params['search'])) {
        $query->where(function ($q) use ($params) {
            $q->where('title', 'LIKE', '%' . $params['search'] . '%')
                ->orWhere('description', 'LIKE', '%' . $params['search'] . '%');
        });
    }

    return $query;
}

}
