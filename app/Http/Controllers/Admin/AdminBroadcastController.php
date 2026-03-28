<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppBroadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminBroadcastController extends Controller
{
    public function index()
    {
        $items = AppBroadcast::query()->orderByDesc('id')->limit(100)->get();

        return view('admin.broadcasts.index', compact('items'));
    }

    public function create()
    {
        return view('admin.broadcasts.form', ['broadcast' => new AppBroadcast]);
    }

    public function store(Request $request)
    {
        $row = $this->validatedRow($request);

        if ($request->hasFile('image')) {
            $row['image_path'] = $request->file('image')->store('broadcasts', 'public');
        }

        AppBroadcast::query()->create($row);

        return redirect()->route('admin.broadcasts.index')->with('status', 'Pengumuman disimpan.');
    }

    public function edit(AppBroadcast $broadcast)
    {
        return view('admin.broadcasts.form', compact('broadcast'));
    }

    public function update(Request $request, AppBroadcast $broadcast)
    {
        $row = $this->validatedRow($request);

        if ($request->hasFile('image')) {
            if ($broadcast->image_path) {
                Storage::disk('public')->delete($broadcast->image_path);
            }
            $row['image_path'] = $request->file('image')->store('broadcasts', 'public');
        }

        $broadcast->update($row);

        return redirect()->route('admin.broadcasts.index')->with('status', 'Pengumuman diperbarui.');
    }

    public function destroy(AppBroadcast $broadcast)
    {
        if ($broadcast->image_path) {
            Storage::disk('public')->delete($broadcast->image_path);
        }
        $broadcast->delete();

        return redirect()->route('admin.broadcasts.index')->with('status', 'Pengumuman dihapus.');
    }

    /**
     * @return array{title: string, body: string, is_active: bool, starts_at: ?string, ends_at: ?string}
     */
    private function validatedRow(Request $request): array
    {
        $v = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        return [
            'title' => $v['title'],
            'body' => $v['body'],
            'is_active' => $request->boolean('is_active'),
            'starts_at' => $v['starts_at'] ?? null,
            'ends_at' => $v['ends_at'] ?? null,
        ];
    }
}
