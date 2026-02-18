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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique(); // Código de barras o SKU
            $table->string('name');           // Nombre del medicamento (Ej: Paracetamol)
            $table->string('concentration')->nullable(); // Ej: 500mg
            $table->string('presentation')->nullable();  // Ej: Tabletas, Jarabe
            $table->integer('stock')->default(0)->nullable();
            $table->integer('min_stock')->default(5)->nullable();    // Alerta de stock bajo
            $table->decimal('purchase_price', 10, 2)->nullable();    // Precio de compra
            $table->decimal('selling_price', 10, 2)->nullable();     // Precio al paciente
            $table->date('expiration_date')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
