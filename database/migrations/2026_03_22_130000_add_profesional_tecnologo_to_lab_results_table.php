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
        Schema::table('lab_results', function (Blueprint $table) {
            $table->foreignId('profesional_id')
                ->nullable()
                ->after('catalog_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('tecnologo_id')
                ->nullable()
                ->after('profesional_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tecnologo_id');
            $table->dropConstrainedForeignId('profesional_id');
        });
    }
};
