<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopping_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('month');
            $table->string('area', 100);
            $table->string('item_name');
            $table->decimal('quantity', 12, 2)->nullable()->default(1);
            $table->unsignedBigInteger('estimated_price');
            $table->text('note')->nullable();
            $table->boolean('is_purchased')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_list_items');
    }
};
