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
        Schema::create('lab_items', function (Blueprint $table) {
            $table->id();
            // Relación con la historia clínica
            $table->foreignId('history_id')->constrained()->onDelete('cascade');
            
            // Guardamos el nombre del examen o perfil (Ej: "Hemograma completo")
            $table->string('name');
            
            // Opcional: Si quieres vincularlo después a un resultado real
            $table->foreignId('order_detail_id')->nullable()->constrained()->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_items');
    }
};
