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
        Schema::create('cie10s', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique()->index(); // Ejemplo: A00.0
            $table->text('descripcion');
            $table->string('cotejo_final', 5)->nullable(); // El campo A o M del CSV
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cie10s');
    }
};
