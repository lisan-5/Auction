<?php

use App\Models\Auction;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes([
    'middleware' => ['auth:sanctum'],
]);

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('auction.{auctionId}', function ($user, int $auctionId) {
    if (! Auction::query()->whereKey($auctionId)->exists()) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
