<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    /**
     * GET /api/v1/auctions/{auction}/stats
     *
     * Returns lightweight auction stats, cached for 30s.
     *
     * OPEN  => total + highest
     * CLOSED running => total only
     * CLOSED ended   => total + winner info
     */
    public function show(Auction $auction): JsonResponse
    {
        $cacheKey = "auction:{$auction->id}:stats";

        $payload = Cache::remember($cacheKey, now()->addSeconds(30), function () use ($auction) {
            $closed = $auction->end_time ? now()->gt($auction->end_time) : false;

            // Safely get type (fallback to 'OPEN' if enum fails)
            $type = 'OPEN';
            try {
                $type = strtoupper($auction->type ?? 'OPEN');
            } catch (\Throwable $e) {
                $type = 'OPEN';
            }

            $total = $auction->bids()->withoutTrashed()->count();

            $base = [
                'auction_id' => (int) $auction->id,
                'type' => $type,
                'closed' => $closed,
                'total_bids' => $total,
            ];

            // Always preload highest bid
            $highestBid = $auction->bids()
                ->withoutTrashed()
                ->orderByDesc('bid_amount')
                ->orderBy('created_at')
                ->with('user:id,name')
                ->first();

            // Case 1: OPEN auctions => return highest
            if ($type === 'OPEN') {
                return $base + [
                    'highest' => $highestBid ? [
                        'bid_id' => (int) $highestBid->id,
                        'amount' => (float) $highestBid->bid_amount,
                        'bidder' => $highestBid->user ? [
                            'id' => (int) $highestBid->user->id,
                            'name' => (string) $highestBid->user->name,
                        ] : null,
                    ] : null,
                ];
            }

            // Case 2: CLOSED but still running => only totals
            if ($type === 'CLOSED' && ! $closed) {
                return $base;
            }

            // Case 3: CLOSED and ended => winner info
            $winnerBid = null;

            if ($auction->winner_bid_id) {
                $winnerBid = Bid::with('user:id,name')->find($auction->winner_bid_id);
            }

            if (! $winnerBid) {
                $winnerBid = $highestBid;
            }

            return $base + [
                'winner' => $winnerBid ? [
                    'bid_id' => (int) $winnerBid->id,
                    'amount' => (float) $winnerBid->bid_amount,
                    'bidder' => $winnerBid->user ? [
                        'id' => (int) $winnerBid->user->id,
                        'name' => (string) $winnerBid->user->name,
                    ] : null,
                ] : null,
            ];
        });

        return response()->json($payload);
    }
}
