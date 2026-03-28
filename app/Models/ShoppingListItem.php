<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingListItem extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'area',
        'item_name',
        'quantity',
        'estimated_price',
        'note',
        'is_purchased',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'quantity' => 'decimal:2',
            'is_purchased' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
