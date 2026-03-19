<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lunch extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'restaurant_name',
        'description',
        'total_amount',
        'delivery_cost',
        'split_method',  // equal | by_price
    ];

    protected function casts(): array
    {
        return [
            'total_amount'  => 'integer',
            'delivery_cost' => 'integer',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(LunchParticipant::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    // true when all non organizer debts are paid
    public function isSettled(): bool
    {
        return $this->debts()->where('is_paid', false)->doesntExist();
    }
}
