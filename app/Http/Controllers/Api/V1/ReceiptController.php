<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $file = $request->file('image');
        $path = $file->store("receipts/{$user->id}", 'public');
        $url = Storage::disk('public')->url($path);

        return response()->json([
            'status' => 'success',
            'data' => [
                'path' => $path,
                'photo_url' => $url,
            ],
        ], 201);
    }
}
