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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');

            // 2.- ANTECEDENTES
            $table->boolean('habito_tabaco')->default(false);
            $table->boolean('habito_alcohol')->default(false);
            $table->boolean('habito_coca')->default(false);
            $table->text('alergias')->nullable();
            $table->text('antecedentes_familiares')->nullable();
            $table->text('antecedentes_otros')->nullable(); // Para el campo "Especificar"

            // 3.- ANAMNESIS
            $table->text('anamnesis')->nullable(); // Texto libre para relato y síntomas

            // 4.- EXAMEN FÍSICO (Funciones Vitales)
            $table->string('pa')->nullable();   // Presión Arterial
            $table->string('fc')->nullable();   // Frecuencia Cardiaca
            $table->string('temp')->nullable(); // Temperatura
            $table->string('fr')->nullable();   // Frecuencia Respiratoria
            $table->string('so2')->nullable();  // Saturación
            $table->string('peso')->nullable();
            $table->string('talla')->nullable();
            $table->string('imc')->nullable();   // Índice de Masa Corporal
            $table->text('examen_fisico_detalle')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
