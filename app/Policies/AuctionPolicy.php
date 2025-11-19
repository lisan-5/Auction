<?php

namespace App\Policies;

use App\Models\Auction;
use App\Models\User;

class AuctionPolicy
{
    // Only admin or users with 'artist' role can create
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    // Admin or the artist who owns the auction can update
    public function update(User $user, Auction $auction): bool
    {
        return $user->hasRole('admin') || $user->id === $auction->artist_id;
    }

    public function delete(User $user, Auction $auction): bool
    {
        return $user->hasRole('admin') || $user->id === $auction->artist_id;
    }

    // Public can view index/show, so we leave viewAny/view to true by default if you want,
    // or return true here explicitly.
    public function viewAny(?User $user = null): bool
    {
        return $user->hasRole('admin');
    }

    public function view(?User $user, Auction $auction): bool
    {
        return true;
    }
}
