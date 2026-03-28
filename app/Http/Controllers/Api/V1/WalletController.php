<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserWallet;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request)
    {
        $w = WalletService::forUser($request->user());

        return response()->json([
            'status' => 'success',
            'data' => $this->payload($w),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'shopping_balance' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'savings_balance' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'toguruga_balance' => ['required', 'integer', 'min:0', 'max:999999999999'],
        ]);

        $w = WalletService::forUser($request->user());
        $w->update([
            'shopping_balance' => (int) $data['shopping_balance'],
            'savings_balance' => (int) $data['savings_balance'],
            'toguruga_balance' => (int) $data['toguruga_balance'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->payload($w->refresh()),
        ]);
    }

    private function payload(UserWallet $w): array
    {
        return [
            'shopping_balance' => (int) $w->shopping_balance,
            'savings_balance' => (int) $w->savings_balance,
            'toguruga_balance' => (int) $w->toguruga_balance,
        ];
    }
}
