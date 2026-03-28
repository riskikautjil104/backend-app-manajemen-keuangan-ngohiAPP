<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ShoppingListItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShoppingListController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $monthDate = Carbon::parse($month.'-01')->startOfMonth()->toDateString();

        $items = ShoppingListItem::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('month', $monthDate)
            ->orderBy('area')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'month' => $month,
                'items' => $items->map(fn (ShoppingListItem $i) => $this->payload($i)),
                'totals' => [
                    'estimated' => $items->sum('estimated_price'),
                    'unchecked' => $items->where('is_purchased', false)->sum('estimated_price'),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'area' => ['required', 'string', 'max:100'],
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'estimated_price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $monthDate = Carbon::parse($data['month'].'-01')->startOfMonth()->toDateString();

        $item = ShoppingListItem::query()->create([
            'user_id' => $request->user()->id,
            'month' => $monthDate,
            'area' => $data['area'],
            'item_name' => $data['item_name'],
            'quantity' => $data['quantity'] ?? 1,
            'estimated_price' => (int) $data['estimated_price'],
            'note' => $data['note'] ?? null,
            'is_purchased' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->payload($item),
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $item = ShoppingListItem::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $data = $request->validate([
            'area' => ['sometimes', 'string', 'max:100'],
            'item_name' => ['sometimes', 'string', 'max:255'],
            'quantity' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'estimated_price' => ['sometimes', 'numeric', 'min:0', 'max:999999999'],
            'note' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'is_purchased' => ['sometimes', 'boolean'],
        ]);

        $item->fill($data);
        $item->save();

        return response()->json([
            'status' => 'success',
            'data' => $this->payload($item),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $item = ShoppingListItem::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();
        $item->delete();

        return response()->json(['status' => 'success', 'data' => null]);
    }

    private function payload(ShoppingListItem $i): array
    {
        return [
            'id' => $i->id,
            'month' => $i->month->format('Y-m'),
            'area' => $i->area,
            'item_name' => $i->item_name,
            'quantity' => (float) $i->quantity,
            'estimated_price' => $i->estimated_price,
            'note' => $i->note,
            'is_purchased' => $i->is_purchased,
        ];
    }
}
