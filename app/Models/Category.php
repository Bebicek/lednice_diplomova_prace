<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($category) {
            if ($category->sort_order === 0) {
                $category->sort_order = static::max('sort_order') + 1;
            }
        });
    }

    public function commodities(): HasMany
    {
        return $this->hasMany(Commodity::class);
    }
}
