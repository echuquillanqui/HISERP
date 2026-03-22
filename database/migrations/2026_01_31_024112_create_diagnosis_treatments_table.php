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
        Schema::create('diagnosis_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->constrained('referrals')->onDelete('cascade');
            
            $table->string('icd_10_code')->nullable(); // CIE-10
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('P')->nullable();
            $table->text('D')->nullable();
            $table->text('R')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnosis_treatments');
    }
};
