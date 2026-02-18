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
        Schema::create('history_diagnostics', function (Blueprint $table) {
            $table->id();

            // Relación con la tabla histories
            $table->foreignId('history_id')
                  ->constrained('histories')
                  ->onDelete('cascade');
            $table->foreignId('cie10_id')->constrained('cie10s');
            
            // Campos para diagnóstico y tratamiento vinculados
            $table->text('diagnostico'); 
            $table->text('tratamiento');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_diagnostics');
    }
};
