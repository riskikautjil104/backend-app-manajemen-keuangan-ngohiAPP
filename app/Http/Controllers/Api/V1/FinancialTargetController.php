<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FinancialTarget;
use Illuminate\Http\Request;

class FinancialTargetController extends Controller
{
    public function index(Request $request)
    {
        $targets = FinancialTarget::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('completed')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $targets->map(fn (FinancialTarget $t) => $this->payload($t)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:1', 'max:999999999'],
            'saved_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'target_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $target = FinancialTarget::query()->create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'target_amount' => (int) $data['target_amount'],
            'saved_amount' => isset($data['saved_amount']) ? (int) $data['saved_amount'] : 0,
            'target_date' => $data['target_date'] ?? null,
            'completed' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->payload($target),
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $target = FinancialTarget::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'target_amount' => ['sometimes', 'numeric', 'min:1', 'max:999999999'],
            'saved_amount' => ['sometimes', 'numeric', 'min:0', 'max:999999999'],
            'target_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        $target->fill($data);

        $ta = (int) $target->target_amount;
        $sa = (int) $target->saved_amount;
        if ($ta > 0 && $sa >= $ta) {
            $target->completed = true;
        }
        $target->save();

        return response()->json([
            'status' => 'success',
            'data' => $this->payload($target),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $target = FinancialTarget::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();
        $target->delete();

        return response()->json(['status' => 'success', 'data' => null]);
    }

    private function payload(FinancialTarget $t): array
    {
        return [
            'id' => $t->id,
            'name' => $t->name,
            'target_amount' => $t->target_amount,
            'saved_amount' => $t->saved_amount,
            'target_date' => $t->target_date?->format('Y-m-d'),
            'completed' => $t->completed,
            'progress' => $t->target_amount > 0
                ? round(min(100, ($t->saved_amount / $t->target_amount) * 100), 2)
                : 0,
        ];
    }
}
