<?php

namespace App\Policies;

use App\Models\Bid;
use App\Models\User;

class BidPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bid $bid): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Disallow creating bids from the admin panel.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bid $bid): bool
    {
        return false;
    }

    /**
     * Admin-only delete (void) a bid.
     */
    public function delete(User $user, Bid $bid): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Bid $bid): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Bid $bid): bool
    {
        return false;
    }
}
