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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique()->default(); // El nombre de usuario siempre debe ser único
            $table->string('dni', 8)->unique()->nullable(); // DNI suele ser de 8 dígitos           
            // Campos Médicos
            $table->string('colegiatura')->unique()->nullable();
            $table->string('rne')->unique()->nullable();
            $table->string('firma')->nullable(); // Aquí guardarás la ruta de la imagen

            $table->foreignId('specialty_id')->nullable()->constrained('specialties')->onDelete('set null');
            
            // Roles y Estado
            $table->enum('role', ['superadmin', 'administracion', 'medicina', 'laboratorio'])->default('administracion');
            $table->boolean('status')->default(true); // true = activo, false = inactivo
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
