<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_detail_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('movement_type', ['entrada', 'salida', 'ajuste']);
            $table->enum('source', ['manual', 'orden', 'ajuste'])->default('manual');
            $table->unsignedInteger('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('movement_at');
            $table->timestamps();

            $table->index(['product_id', 'movement_at']);
            $table->index(['source', 'movement_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
