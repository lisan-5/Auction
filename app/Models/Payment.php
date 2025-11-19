<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PaymentStatus;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tx_ref',
        'status',
        'amount',
        'currency',
        'email',
        'user_id',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
