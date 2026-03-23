<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commodity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'description',
        'image_path',
        'barcode',
        'expires_at',
        'is_active',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_active' => 'boolean',
            'expires_at' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Returns price converted to CZK
    public function getPriceInCzkAttribute(): float
    {
        return $this->price / 100;
    }

    // Returns fridge quantity, uses eager-loaded relation when available
    public function getFridgeQuantityAttribute(): int
    {
        if ($this->relationLoaded('stock')) {
            return $this->stock->where('location', 'fridge')->first()?->quantity ?? 0;
        }
        return $this->stock()->where('location', 'fridge')->value('quantity') ?? 0;
    }

    // Returns warehouse quantity, uses eager-loaded relation when available
    public function getWarehouseQuantityAttribute(): int
    {
        if ($this->relationLoaded('stock')) {
            return $this->stock->where('location', 'warehouse')->first()?->quantity ?? 0;
        }
        return $this->stock()->where('location', 'warehouse')->value('quantity') ?? 0;
    }

    // Returns true if the product is past its expiry date
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
