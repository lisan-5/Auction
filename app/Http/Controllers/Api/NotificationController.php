<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Query notifications directly to avoid static analyzer issues with the Notifiable trait
        $notifications = DatabaseNotification::query()
            ->whereMorphedTo('notifiable', $user)
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return NotificationResource::collection($notifications);
    }
}
