<?php

namespace App\Models;

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
        'is_active',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_active' => 'boolean',
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

    // Returns the fridge stock record (delegating expiry info)
    private function fridgeStock(): ?Stock
    {
        if ($this->relationLoaded('stock')) {
            return $this->stock->where('location', 'fridge')->first();
        }
        return $this->stock()->where('location', 'fridge')->first();
    }

    // Returns the expiry date of the fridge stock (null if not set)
    public function getFridgeExpiryDateAttribute()
    {
        return $this->fridgeStock()?->expires_at;
    }

    // Returns true if the fridge stock is past its expiry date
    public function getIsExpiredAttribute(): bool
    {
        return $this->fridgeStock()?->is_expired ?? false;
    }

    // Returns days until fridge stock expiry (negative = expired, null = no date)
    public function getDaysUntilExpiryAttribute(): ?int
    {
        return $this->fridgeStock()?->days_until_expiry;
    }

    // Returns expiry status based on fridge stock: 'expired' | 'critical' | 'warning' | 'ok' | null
    public function getExpiryStatusAttribute(): ?string
    {
        return $this->fridgeStock()?->expiry_status;
    }
}
