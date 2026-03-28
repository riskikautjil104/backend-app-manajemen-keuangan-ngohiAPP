<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $categories = Category::query()
            ->visibleTo($user)
            ->orderByRaw('user_id is null desc')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories->map(fn (Category $c) => $this->categoryPayload($c)),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:64'],
            'type' => ['required', Rule::in(['income', 'expense'])],
        ]);

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'icon' => $data['icon'] ?? 'category',
            'type' => $data['type'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->categoryPayload($category),
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $user = $request->user();
        $category = Category::query()->where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'icon' => ['sometimes', 'string', 'max:64'],
            'type' => ['sometimes', Rule::in(['income', 'expense'])],
        ]);

        $category->fill($data);
        $category->save();

        return response()->json([
            'status' => 'success',
            'data' => $this->categoryPayload($category),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        $category = Category::query()->where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $category->delete();

        return response()->json(['status' => 'success', 'data' => null]);
    }

    private function categoryPayload(Category $c): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'icon' => $c->icon,
            'type' => $c->type,
            'is_system' => $c->user_id === null,
        ];
    }
}
