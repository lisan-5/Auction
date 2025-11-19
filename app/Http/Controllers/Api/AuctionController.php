<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuctionRequest;
use App\Http\Requests\UpdateAuctionRequest;
use App\Http\Resources\AuctionResource;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    /**
 * @OA\Get(
 *     path="/auctions",
 *     summary="Get filtered auctions",
 *     @OA\Parameter(
 *         name="category", in="query", required=false, @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="condition", in="query", required=false, @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="province", in="query", required=false, @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="keyword", in="query", required=false, @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="search", in="query", required=false, @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response=200, description="Successful response")
 * )
 */

    public function __construct()
    {
        // Public index/show; all other routes require authentication
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**
     * List all auctions (public)
     */
    public function index(Request $request)
{
    $query = Auction::with(['artist', 'category'])
        ->where('status', 'LIVE'); // only live auctions

    // Apply filters
    if ($request->filled('condition')) {
        $query->where('condition', $request->condition);
    }

    if ($request->filled('province')) {
        $query->where('province', $request->province);
    }

    // Apply ordering
    $query->orderByRaw(
        $request->get('order_by') === 'ending_soon'
            ? 'end_time asc'
            : 'created_at desc'
    );

    // Return paginated resource collection
    $auctions = $query->paginate(10);

    return AuctionResource::collection($auctions);
}

    /**
     * Store a new auction
     */
    public function store(StoreAuctionRequest $request)
    {
        $this->authorize('create', Auction::class);

        $data = $request->validated();
        // Automatically set the artist_id from authenticated user
        $data['artist_id'] = Auth::id();

        // Normalize files before transaction and attach after commit to avoid file I/O inside DB transactions
        $files = $this->normalizeFiles($request);

        $auction = DB::transaction(function () use ($data) {
            return Auction::create($data);
        });

        if (! empty($files)) {
            DB::afterCommit(function () use ($auction, $files): void {
                $this->attachImages($auction->fresh(), $files);
            });
        }

        return new AuctionResource($auction->load(['artist','category']));
    }

    /**
     * Show a single auction (public)
     */
    public function show(Auction $auction)
    {
        $auction->load(['artist','category']);

        return new AuctionResource($auction);
    }

    /**
     * Update an existing auction
     */
    public function update(UpdateAuctionRequest $request, Auction $auction)
    {
        $this->authorize('update', $auction);

        $data = $request->validated();

        // Normalize files before transaction (if provided) and perform file operations after commit
        $files = $this->normalizeFiles($request);

        DB::transaction(function () use ($auction, $data): void {
            $auction->update($data);
        });

        if (! empty($files)) {
            DB::afterCommit(function () use ($auction, $files): void {
                $fresh = $auction->fresh();
                $this->clearImages($fresh);
                $this->attachImages($fresh, $files);
            });
        }

        return new AuctionResource($auction->fresh()->load(['artist','category']));
    }

    /**
     * Delete an auction
     */
    public function destroy(Auction $auction)
    {
        $this->authorize('delete', $auction);

        // Spatie Media Library will automatically delete media when the model is deleted
        $auction->delete();

        return response()->noContent();
    }

    /**
     * Normalize uploaded image files to a flat array.
     */
    private function normalizeFiles(Request $request): array
    {
        $files = $request->file('images');

        if ($files === null) {
            return [];
        }

        if (! is_array($files)) {
            $files = [$files];
        }

        return array_values(array_filter($files));
    }

    /**
     * Attach images to the artwork_images media collection.
     */
    private function attachImages(Auction $auction, array $files): void
    {
        foreach ($files as $file) {
            $auction->addMedia($file)->toMediaCollection('artwork_images');
        }
    }

    /**
     * Clear existing images from the artwork_images media collection.
     */
    private function clearImages(Auction $auction): void
    {
        $auction->clearMediaCollection('artwork_images');
    }
}
