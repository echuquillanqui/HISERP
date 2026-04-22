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
        Schema::table('tomography_results', function (Blueprint $table) {
            $table->foreignId('iopamidol_brand_id')->nullable()->after('iopamidol_used')->constrained('iopamidol_brands')->nullOnDelete();
            $table->unsignedSmallInteger('iopamidol_presentation_ml')->nullable()->after('iopamidol_brand_id');
            $table->unsignedInteger('iopamidol_units')->default(0)->after('iopamidol_presentation_ml');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tomography_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('iopamidol_brand_id');
            $table->dropColumn(['iopamidol_presentation_ml', 'iopamidol_units']);
        });
    }
};
