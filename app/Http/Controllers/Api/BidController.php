<?php

namespace App\Http\Controllers\Api;

use App\Events\BidPlaced;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBidRequest;
use App\Http\Resources\BidResource;
use App\Models\Auction;
use App\Models\Bid;
use App\Services\BidPlacementService;
use Illuminate\Support\Facades\Auth;

class BidController extends Controller
{
    /**
     * GET /api/v1/auctions/{auction}/bids
     * Public: list visible bids for an auction, highest first (paginated)
     */
    public function index(Auction $auction)
    {
        $bids = $auction->bids()
            ->withoutTrashed()
            ->with('user')
            ->where('is_visible', true)
            ->orderByDesc('bid_amount')
            ->paginate(10);

        return BidResource::collection($bids);
    }

    /**
     * GET /api/v1/auctions/{auction}/highest
     * Public: fetch the current highest visible bid
     */
    public function highest(Auction $auction)
    {
        $bid = $auction->bids()
            ->withoutTrashed()
            ->with('user')
            ->where('is_visible', true)
            ->orderByDesc('bid_amount')
            ->first();

        if (! $bid) {
            return response()->json(['message' => 'No bids for this auction'], 404);
        }

        return new BidResource($bid);
    }

    /**
     * POST /api/v1/auctions/{auction}/bids
     */
    public function store(StoreBidRequest $request, Auction $auction, BidPlacementService $service)
    {
        $user = Auth::user();

        $data = $request->validated();
        $amount = (float) $data['bid_amount'];

        $result = $service->place($user, $auction, $amount);

        BidPlaced::dispatch($result['bid'], $auction);

        return response()->json([
            'message' => 'Bid accepted',
            'current_price' => $result['current_price'],
        ]);
    }

    /**
     * DELETE /api/v1/bids/{bid}
     * Admin-only via policy/role check.
     */
    public function destroy(Bid $bid)
    {
        $this->authorize('delete', $bid);

        $bid->delete();

        return response()->noContent();
    }
}
