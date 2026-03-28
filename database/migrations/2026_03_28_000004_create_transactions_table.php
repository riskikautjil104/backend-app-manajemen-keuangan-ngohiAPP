<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('date');
            $table->text('note')->nullable();
            $table->string('photo_url', 2048)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
