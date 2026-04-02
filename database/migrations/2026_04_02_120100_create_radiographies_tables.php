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
        Schema::create('radiographies', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('private_price', 10, 2)->nullable();
            $table->unsignedInteger('plate_usage')->default(0);
            $table->timestamps();
        });

        Schema::create('radiography_agreement_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radiography_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agreement_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['radiography_id', 'agreement_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiography_agreement_prices');
        Schema::dropIfExists('radiographies');
    }
};
