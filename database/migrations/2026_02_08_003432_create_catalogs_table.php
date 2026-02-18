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
        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->onDelete('cascade'); // Relación con Area
            $table->string('name'); // Ej: Colesterol
            $table->string('unit')->nullable(); // Ej: mg/dL
            $table->string('reference_range')->nullable(); // Ej: 70-110
            $table->decimal('price', 10, 2)->nullable(); // Ej: 150.00
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
};
