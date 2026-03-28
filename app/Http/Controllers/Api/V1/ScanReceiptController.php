<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScanReceiptController extends Controller
{
    /**
     * Terima gambar struk, simpan (opsional), kembalikan parsing demo.
     * Di production: sambungkan OCR (Google Vision, Tesseract, dll.).
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'photo_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $user = $request->user();
        $photoUrl = $request->input('photo_url');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store("receipts/{$user->id}/scans", 'public');
            $photoUrl = Storage::disk('public')->url($path);
        }

        $defaultExpense = Category::query()
            ->visibleTo($user)
            ->where('type', 'expense')
            ->orderByRaw('user_id is null desc')
            ->orderBy('id')
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'photo_url' => $photoUrl,
                'suggested' => [
                    'category_id' => $defaultExpense?->id,
                    'amount' => null,
                    'date' => Carbon::now()->format('Y-m-d'),
                    'note' => 'Demo OCR — isi nominal & kategori manual bila perlu.',
                ],
                'raw_text' => null,
            ],
        ]);
    }
}
