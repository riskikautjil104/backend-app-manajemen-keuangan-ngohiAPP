<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppBroadcast extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image_path',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function publicImageUrl(): ?string
    {
        if ($this->image_path === null || $this->image_path === '') {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
