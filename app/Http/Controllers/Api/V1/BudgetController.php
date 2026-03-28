<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $budgets = Budget::query()
            ->where('user_id', $user->id)
            ->with('category')
            ->orderByDesc('month')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $budgets->map(fn (Budget $b) => $this->budgetPayload($b)),
        ]);
    }

    public function current(Request $request)
    {
        $user = $request->user();
        $month = Carbon::now()->startOfMonth();

        $budgets = Budget::query()
            ->where('user_id', $user->id)
            ->whereDate('month', $month->toDateString())
            ->with('category')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'month' => $month->format('Y-m'),
                'budgets' => $budgets->map(fn (Budget $b) => $this->budgetPayload($b)),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'month' => ['required', 'date_format:Y-m'],
            'amount_limit' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        $category = Category::query()->where('id', $data['category_id'])->firstOrFail();
        abort_if($category->type !== 'expense', 422, 'Budget hanya untuk kategori pengeluaran.');
        $this->assertCategoryAllowed($user->id, (int) $data['category_id']);

        $monthDate = Carbon::parse($data['month'].'-01')->startOfMonth()->toDateString();

        $budget = Budget::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'month' => $monthDate,
            ],
            ['amount_limit' => (int) $data['amount_limit']]
        );

        $budget->load('category');

        return response()->json([
            'status' => 'success',
            'data' => $this->budgetPayload($budget),
        ], 201);
    }

    private function assertCategoryAllowed(int $userId, int $categoryId): void
    {
        $ok = Category::query()
            ->where('id', $categoryId)
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->exists();

        abort_unless($ok, 422, 'Kategori tidak valid untuk akun ini.');
    }

    private function budgetPayload(Budget $b): array
    {
        $b->loadMissing('category');
        $c = $b->category;

        return [
            'id' => $b->id,
            'category' => [
                'id' => $c->id,
                'name' => $c->name,
                'icon' => $c->icon,
                'type' => $c->type,
            ],
            'month' => $b->month->format('Y-m'),
            'amount_limit' => $b->amount_limit,
        ];
    }
}
