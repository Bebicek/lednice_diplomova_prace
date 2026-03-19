<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Debt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'creditor_id',
        'order_id',
        'lunch_id',
        'amount',
        'is_paid',
        'paid_at',
        'is_accepted',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'      => 'integer',
            'is_paid'     => 'boolean',
            'paid_at'     => 'datetime',
            'is_accepted' => 'boolean',
            'accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creditor_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lunch(): BelongsTo
    {
        return $this->belongsTo(Lunch::class);
    }

    public function isSystemDebt(): bool
    {
        return $this->creditor_id === null;
    }
}
