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
        Schema::create('tomography_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_tomography_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requesting_doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('report_signer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('result_date');
            $table->unsignedInteger('plates_used')->default(0); // Deduct from control table
            $table->decimal('iopamidol_used', 10, 2)->default(0); // Deduct from control table
            $table->text('general_description')->nullable();
            $table->longText('result_description')->nullable();
            $table->text('conclusion')->nullable();
            $table->longText('result_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tomography_results');
    }
};
