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
        Schema::table('histories', function (Blueprint $table) {
            $table->string('tiempo_enfermedad')->nullable()->after('anamnesis');
            $table->text('signos_sintomas')->nullable()->after('tiempo_enfermedad');
        });

        Schema::table('history_diagnostics', function (Blueprint $table) {
            $table->string('clasificacion', 1)->nullable()->after('diagnostico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_diagnostics', function (Blueprint $table) {
            $table->dropColumn('clasificacion');
        });

        Schema::table('histories', function (Blueprint $table) {
            $table->dropColumn(['tiempo_enfermedad', 'signos_sintomas']);
        });
    }
};
