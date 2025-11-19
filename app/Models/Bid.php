<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Bid extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'auction_id',
        'user_id',
        'bid_amount',
        'is_visible',
        'max_auto_bid',
        'note',
    ];

    public function casts(): array
    {
        return [
            'bid_amount' => 'decimal:2',
            'max_auto_bid' => 'decimal:2',
            'is_visible' => 'boolean',
        ];
    }

    // ─────────────── Relationships ───────────────

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─────────────── Query Scopes ───────────────

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeForAuction(Builder $query, int $auctionId): Builder
    {
        return $query->where('auction_id', $auctionId);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeHighestFirst(Builder $query): Builder
    {
        return $query->orderBy('bid_amount', 'desc');
    }

    // ─────────────── Media Library Configuration ───────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
            ])
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('attachments');
    }
}
