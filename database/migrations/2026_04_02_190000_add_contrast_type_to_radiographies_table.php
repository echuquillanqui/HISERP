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
        Schema::table('radiographies', function (Blueprint $table) {
            $table->enum('contrast_type', ['CON_CONTRASTE', 'SIN_CONTRASTE'])
                ->default('SIN_CONTRASTE')
                ->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radiographies', function (Blueprint $table) {
            $table->dropColumn('contrast_type');
        });
    }
};
