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
        Schema::create('order_tomographies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('radiography_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agreement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // User that registers the order
            $table->enum('service_type', ['EMERGENCY', 'PRIVATE', 'AGREEMENT']);
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('payment_type', ['PENDING_PAYMENT', 'TRANSFER', 'YAPE', 'CASH'])->default('CASH');
            $table->enum('care_medium', ['AMBULANCE', 'OUTPATIENT'])->default('OUTPATIENT');
            $table->enum('document_type', ['RECEIPT', 'INVOICE'])->nullable();
            $table->string('document_number')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tomographies');
    }
};
