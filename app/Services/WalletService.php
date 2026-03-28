<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Validation\ValidationException;

class WalletService
{
    /** @var list<string> */
    public const WALLETS = ['shopping', 'savings', 'toguruga'];

    public static function assertWallet(string $wallet): string
    {
        if (! in_array($wallet, self::WALLETS, true)) {
            throw ValidationException::withMessages([
                'wallet' => 'Dompet harus: shopping, savings, atau toguruga.',
            ]);
        }

        return $wallet;
    }

    public static function column(string $wallet): string
    {
        self::assertWallet($wallet);

        return match ($wallet) {
            'shopping' => 'shopping_balance',
            'savings' => 'savings_balance',
            'toguruga' => 'toguruga_balance',
        };
    }

    public static function forUser(User $user): UserWallet
    {
        return UserWallet::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['shopping_balance' => 0, 'savings_balance' => 0, 'toguruga_balance' => 0],
        );
    }

    public static function applyCredit(UserWallet $w, string $wallet, int $amount): void
    {
        $w->increment(self::column($wallet), $amount);
    }

    public static function applyDebitStrict(UserWallet $w, string $wallet, int $amount): void
    {
        $col = self::column($wallet);
        $w->refresh();
        if ($w->{$col} < $amount) {
            throw ValidationException::withMessages([
                'wallet' => 'Saldo dompet tidak mencukupi untuk transaksi ini.',
            ]);
        }
        $w->decrement($col, $amount);
    }

    public static function forceDebit(UserWallet $w, string $wallet, int $amount): void
    {
        $w->decrement(self::column($wallet), $amount);
    }

    public static function forceCredit(UserWallet $w, string $wallet, int $amount): void
    {
        $w->increment(self::column($wallet), $amount);
    }

    public static function syncTogurugaTargetIncome(User $user, int $amount): void
    {
        $t = $user->financialTargets()->where('completed', false)->orderBy('id')->first();
        if (! $t) {
            return;
        }
        $room = max(0, $t->target_amount - $t->saved_amount);
        $add = min($amount, $room);
        if ($add > 0) {
            $t->increment('saved_amount', $add);
        }
    }

    public static function syncTogurugaTargetExpense(User $user, int $amount): void
    {
        $t = $user->financialTargets()->where('completed', false)->orderBy('id')->first();
        if (! $t) {
            return;
        }
        $sub = min($amount, $t->saved_amount);
        if ($sub > 0) {
            $t->decrement('saved_amount', $sub);
        }
    }

    public static function applyForNewTransaction(User $user, Category $category, string $wallet, int $amount): void
    {
        $wallet = self::assertWallet($wallet);
        $w = self::forUser($user);
        if ($category->type === 'income') {
            self::applyCredit($w, $wallet, $amount);
            if ($wallet === 'toguruga') {
                self::syncTogurugaTargetIncome($user, $amount);
            }
        } else {
            self::applyDebitStrict($w, $wallet, $amount);
            if ($wallet === 'toguruga') {
                self::syncTogurugaTargetExpense($user, $amount);
            }
        }
    }

    public static function reverseTransaction(Transaction $transaction): void
    {
        $transaction->loadMissing('category', 'user');
        /** @var Category $c */
        $c = $transaction->category;
        $user = $transaction->user;
        $wallet = self::assertWallet($transaction->wallet ?? 'shopping');
        $amount = (int) $transaction->amount;
        $w = self::forUser($user);

        if ($c->type === 'income') {
            self::forceDebit($w, $wallet, $amount);
            if ($wallet === 'toguruga') {
                self::syncTogurugaTargetExpense($user, $amount);
            }
        } else {
            self::forceCredit($w, $wallet, $amount);
            if ($wallet === 'toguruga') {
                self::syncTogurugaTargetIncome($user, $amount);
            }
        }
    }
}
