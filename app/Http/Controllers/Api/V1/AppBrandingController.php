<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;

class AppBrandingController extends Controller
{
    public function show()
    {
        $s = AppSetting::query()->orderBy('id')->first();

        if (! $s) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'display_name' => 'NGOHI',
                    'tagline' => 'Wujudkan mimpi finansialmu',
                    'primary_color' => '#1E3A8A',
                    'secondary_color' => '#FBBF24',
                    'accent_color' => '#FFFFFF',
                ],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'display_name' => $s->display_name,
                'tagline' => $s->tagline,
                'primary_color' => $s->primary_color,
                'secondary_color' => $s->secondary_color,
                'accent_color' => $s->accent_color,
            ],
        ]);
    }
}
