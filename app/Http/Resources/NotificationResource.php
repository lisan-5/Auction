<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Illuminate\Notifications\DatabaseNotification */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'data' => $this->data,
            'unread' => $this->read_at === null,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
