<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LunchItem extends Model
{
    protected $fillable = [
        'lunch_participant_id',
        'name',
        'price'
    ];

    protected function casts(): array
    {
        return ['price' => 'integer'];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(LunchParticipant::class, 'lunch_participant_id');
    }
}
