<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTarget extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'saved_amount',
        'target_date',
        'completed',
    ];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
