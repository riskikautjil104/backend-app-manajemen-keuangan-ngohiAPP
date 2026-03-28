<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        });
    }
}
