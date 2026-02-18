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
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            
            /**
             * IMPORTANTE: Aquí estaba el error. 
             * Al usar ->constrained() sin parámetros, Laravel buscaba la tabla 'lab_items'.
             * Debemos forzar que apunte a 'order_details'.
             */
            $table->foreignId('lab_item_id')
                  ->constrained('order_details') 
                  ->onDelete('cascade');

            $table->foreignId('catalog_id')->constrained();
            
            $table->string('result_value')->nullable();
            $table->text('observations')->nullable();
            $table->string('status')->default('pendiente');
            
            // Campos históricos copiados del catálogo
            $table->string('reference_range')->nullable(); 
            $table->string('unit')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};
