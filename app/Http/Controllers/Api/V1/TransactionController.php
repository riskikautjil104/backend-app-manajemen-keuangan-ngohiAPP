<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ShoppingListItem;
use App\Models\Transaction;
use App\Services\WalletService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Transaction::query()
            ->where('user_id', $user->id)
            ->with('category')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($request->filled('month')) {
            $start = Carbon::parse($request->query('month').'-01')->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $query->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        }

        $transactions = $query->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json([
            'status' => 'success',
            'data' => $transactions->getCollection()->map(fn (Transaction $t) => $this->transactionPayload($t))->values(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'date' => ['required', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:5000'],
            'photo_url' => ['nullable', 'string', 'max:2048', 'url'],
            'wallet' => ['required', 'string', Rule::in(WalletService::WALLETS)],
        ]);

        $this->assertCategoryAllowed($user->id, (int) $data['category_id']);

        $category = Category::query()->where('id', $data['category_id'])->firstOrFail();

        return DB::transaction(function () use ($user, $data, $category) {
            $transaction = Transaction::query()->create([
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'amount' => (int) $data['amount'],
                'date' => $data['date'],
                'note' => $data['note'] ?? null,
                'photo_url' => $data['photo_url'] ?? null,
                'wallet' => WalletService::assertWallet($data['wallet']),
            ]);

            WalletService::applyForNewTransaction($user, $category, $data['wallet'], (int) $data['amount']);
            $transaction->load('category');

            return response()->json([
                'status' => 'success',
                'data' => $this->transactionPayload($transaction),
            ], 201);
        });
    }

    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $transaction = Transaction::query()
            ->where('user_id', $user->id)
            ->with('category')
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $this->transactionPayload($transaction),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $user = $request->user();
        $transaction = Transaction::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $data = $request->validate([
            'category_id' => ['sometimes', 'integer', Rule::exists('categories', 'id')],
            'amount' => ['sometimes', 'numeric', 'min:0', 'max:999999999'],
            'date' => ['sometimes', 'date_format:Y-m-d'],
            'note' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'photo_url' => ['sometimes', 'nullable', 'string', 'max:2048', 'url'],
            'wallet' => ['sometimes', 'string', Rule::in(WalletService::WALLETS)],
        ]);

        if (isset($data['category_id'])) {
            $this->assertCategoryAllowed($user->id, (int) $data['category_id']);
        }

        return DB::transaction(function () use ($transaction, $user, $data) {
            $transaction->load('category', 'user');

            WalletService::reverseTransaction($transaction);

            $transaction->fill($data);
            $transaction->save();
            $transaction->load('category');

            $wallet = $transaction->wallet ?? 'shopping';
            WalletService::applyForNewTransaction(
                $user,
                $transaction->category,
                WalletService::assertWallet($wallet),
                (int) $transaction->amount
            );

            return response()->json([
                'status' => 'success',
                'data' => $this->transactionPayload($transaction),
            ]);
        });
    }

    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        $transaction = Transaction::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        DB::transaction(function () use ($transaction): void {
            WalletService::reverseTransaction($transaction);
            $transaction->delete();
        });

        return response()->json(['status' => 'success', 'data' => null]);
    }

    public function monthlySummary(Request $request)
    {
        $user = $request->user();
        $month = $request->query('month', now()->format('Y-m'));
        $start = Carbon::parse($month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $base = Transaction::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('category');

        $income = (clone $base)->whereHas('category', fn ($q) => $q->where('type', 'income'))->sum('amount');
        $expense = (clone $base)->whereHas('category', fn ($q) => $q->where('type', 'expense'))->sum('amount');
        $count = (clone $base)->count();

        $byCategory = (clone $base)
            ->whereHas('category', fn ($q) => $q->where('type', 'expense'))
            ->selectRaw('category_id, sum(amount) as total')
            ->groupBy('category_id')
            ->get();

        $expenseTotal = (float) $expense ?: 1.0;
        $categories = Category::query()->whereIn('id', $byCategory->pluck('category_id'))->get()->keyBy('id');

        $byCategoryPayload = $byCategory->map(function ($row) use ($expenseTotal, $categories) {
            $cat = $categories->get($row->category_id);

            return [
                'category_id' => (int) $row->category_id,
                'category_name' => $cat?->name ?? '—',
                'icon' => $cat?->icon ?? 'category',
                'total' => (int) $row->total,
                'percentage' => round(((int) $row->total / $expenseTotal) * 100, 2),
            ];
        })->sortByDesc('total')->values()->all();

        $target = $user->financialTargets()
            ->where('completed', false)
            ->orderBy('id')
            ->first();

        $shoppingItems = ShoppingListItem::query()
            ->where('user_id', $user->id)
            ->whereDate('month', $start->toDateString())
            ->get();
        $shoppingListRemaining = (int) $shoppingItems->where('is_purchased', false)->sum('estimated_price');

        $uw = WalletService::forUser($user);

        $lead = $byCategoryPayload[0] ?? null;

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_income' => (int) $income,
                'total_expense' => (int) $expense,
                'balance' => (int) $income - (int) $expense,
                'wallets' => [
                    'shopping' => (int) $uw->shopping_balance,
                    'savings' => (int) $uw->savings_balance,
                    'toguruga' => (int) $uw->toguruga_balance,
                ],
                'shopping_list_remaining' => $shoppingListRemaining,
                'transactions_count' => $count,
                'by_category' => $byCategoryPayload,
                'expense_insights' => [
                    'month' => $start->format('Y-m'),
                    'highest_category' => $lead,
                    'ranked_expenses' => $byCategoryPayload,
                    'headline_id' => $lead
                        ? sprintf(
                            'Pengeluaran terbesar bulan ini: %s (%s%% dari total pengeluaran).',
                            $lead['category_name'],
                            number_format($lead['percentage'], 1, ',', '.')
                        )
                        : 'Belum ada pengeluaran tercatat di bulan ini.',
                ],
                'toguruga' => $target ? [
                    'id' => $target->id,
                    'name' => $target->name,
                    'target_amount' => $target->target_amount,
                    'saved_amount' => $target->saved_amount,
                    'target_date' => $target->target_date?->format('Y-m-d'),
                ] : null,
            ],
        ]);
    }

    public function byCategorySummary(Request $request)
    {
        $user = $request->user();
        $month = $request->query('month', now()->format('Y-m'));
        $start = Carbon::parse($month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $base = Transaction::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

        $totalExpense = (clone $base)->whereHas('category', fn ($q) => $q->where('type', 'expense'))->sum('amount');

        $byCategory = (clone $base)
            ->whereHas('category', fn ($q) => $q->where('type', 'expense'))
            ->selectRaw('category_id, sum(amount) as total')
            ->groupBy('category_id')
            ->get();

        $expenseTotal = (float) $totalExpense ?: 1.0;
        $categories = Category::query()->whereIn('id', $byCategory->pluck('category_id'))->get()->keyBy('id');

        $byCategoryPayload = $byCategory->map(function ($row) use ($expenseTotal, $categories) {
            $cat = $categories->get($row->category_id);

            return [
                'category_id' => (int) $row->category_id,
                'category_name' => $cat?->name ?? '—',
                'icon' => $cat?->icon ?? 'category',
                'total' => (int) $row->total,
                'percentage' => round(((int) $row->total / $expenseTotal) * 100, 2),
            ];
        })->sortByDesc('total')->values()->all();

        return response()->json([
            'status' => 'success',
            'data' => [
                'month' => $start->format('Y-m'),
                'total_expense' => (int) $totalExpense,
                'by_category' => $byCategoryPayload,
            ],
        ]);
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

    private function transactionPayload(Transaction $t): array
    {
        $t->loadMissing('category');
        $c = $t->category;

        return [
            'id' => $t->id,
            'category' => [
                'id' => $c->id,
                'name' => $c->name,
                'icon' => $c->icon,
                'type' => $c->type,
            ],
            'amount' => $t->amount,
            'date' => $t->date->format('Y-m-d'),
            'note' => $t->note,
            'photo_url' => $t->photo_url,
            'wallet' => $t->wallet ?? 'shopping',
            'created_at' => $t->created_at?->toIso8601String(),
        ];
    }
}