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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ORD-2024-0001
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('user_id')->constrained(); // Bioquímico/Recepcionista
            
            // Finanzas
            $table->decimal('total', 10, 2)->default(0.00);
            $table->enum('payment_status', ['pendiente', 'pagado', 'anulado'])->default('pendiente')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('operation_number')->nullable();
            
            $table->ipAddress('ip_address')->nullable(); // Seguridad
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
