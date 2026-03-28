<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'display_name',
        'tagline',
        'primary_color',
        'secondary_color',
        'accent_color',
    ];

    public static function current(): self
    {
        return static::query()->orderBy('id')->firstOrFail();
    }
}
