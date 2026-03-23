<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LunchParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'lunch_id',
        'user_id',
        'amount',
        'is_approved',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function lunch(): BelongsTo
    {
        return $this->belongsTo(Lunch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LunchItem::class);
    }
}
