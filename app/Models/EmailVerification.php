<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    use HasFactory;

    protected $table = 'email_verification';

    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'consumed_at',
        'attempts',
        'ip',
        'magic_token',
        'magic_token_expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'magic_token_expires_at' => 'datetime',
    ];
    public static function generateMagicToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function isMagicTokenExpired(): bool
    {
        return is_null($this->magic_token_expires_at) || now()->greaterThan($this->magic_token_expires_at);
    }

    public function hasValidMagicToken(string $token): bool
    {
        return $this->magic_token === $token && !$this->isMagicTokenExpired() && !$this->isConsumed();
    }

    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    public function isConsumed(): bool
    {
        return !is_null($this->consumed_at);
    }
}
