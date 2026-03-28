<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppBroadcast;
use Illuminate\Http\Request;

class AppBroadcastController extends Controller
{
    /**
     * Iklan / pengumuman aktif yang belum pernah dilihat (id > since_id).
     */
    public function index(Request $request)
    {
        $sinceId = max(0, (int) $request->query('since_id', 0));

        $rows = AppBroadcast::query()
            ->where('is_active', true)
            ->where(function ($q): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where('id', '>', $sinceId)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $rows->map(fn (AppBroadcast $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'body' => $b->body,
                'image_url' => $b->publicImageUrl(),
                'updated_at' => $b->updated_at?->toIso8601String(),
            ])->values()->all(),
        ]);
    }

    /**
     * Pengumuman aktif terbaru (satu baris) — untuk notifikasi di mobile.
     */
    public function latest(Request $request)
    {
        $b = AppBroadcast::query()
            ->where('is_active', true)
            ->where(function ($q): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderByDesc('id')
            ->first();

        if ($b === null) {
            return response()->json([
                'status' => 'success',
                'data' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $b->id,
                'title' => $b->title,
                'body' => $b->body,
                'image_url' => $b->publicImageUrl(),
                'updated_at' => $b->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
