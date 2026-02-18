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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('ruc', 11)->unique(); // RUC suele ser de 11 dígitos
            $table->string('razon_social');
            $table->string('direccion');
            $table->string('correo')->nullable();
            $table->string('telefono')->nullable();
            $table->string('logo')->nullable(); // Guardaremos la ruta de la imagen
            $table->boolean('estado')->default(true)->nullable(); // Útil para desactivar sucursales
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
