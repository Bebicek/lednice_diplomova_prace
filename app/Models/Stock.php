<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stock';

    protected $fillable = [
        'commodity_id',
        'location',
        'quantity',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'expires_at' => 'date',
        ];
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    // Returns true if the stock is past its expiry date
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    // Returns days until expiry (negative = already expired, null = no expiry set)
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if ($this->expires_at === null) {
            return null;
        }
        return (int) now()->startOfDay()->diffInDays($this->expires_at->startOfDay(), false);
    }

    // Returns expiry status: 'expired' | 'critical' (≤3 days) | 'warning' (≤7 days) | 'ok' | null
    public function getExpiryStatusAttribute(): ?string
    {
        $days = $this->days_until_expiry;
        if ($days === null) return null;
        if ($days < 0)  return 'expired';
        if ($days <= 3) return 'critical';
        if ($days <= 7) return 'warning';
        return 'ok';
    }
}
