<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_wallets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->bigInteger('shopping_balance')->default(0);
            $table->bigInteger('savings_balance')->default(0);
            $table->bigInteger('toguruga_balance')->default(0);
            $table->timestamps();
        });

        $ids = DB::table('users')->pluck('id');
        $now = now();
        foreach ($ids as $userId) {
            DB::table('user_wallets')->insert([
                'user_id' => $userId,
                'shopping_balance' => 0,
                'savings_balance' => 0,
                'toguruga_balance' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_wallets');
    }
};
