<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWallet extends Model
{
    protected $fillable = [
        'user_id',
        'shopping_balance',
        'savings_balance',
        'toguruga_balance',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
