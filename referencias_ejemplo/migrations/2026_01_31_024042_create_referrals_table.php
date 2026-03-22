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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->string('referral_code')->nullable()->unique(); // Ej: 2026-001
            
            // Relación con Paciente
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            
            // Destinos
            $table->string('origin_facility')->nullable(); 
            $table->string('destination_facility')->nullable(); 
            $table->string('destination_specialty')->nullable(); // Especialidad de destino
            
            // Examen Clínico (Físico)
            $table->text('anamnesis')->nullable(); 
            $table->string('general_state')->nullable(); // Estado General
            $table->string('temperature')->nullable(); // T°
            $table->string('blood_pressure')->nullable(); // PA
            $table->string('respiratory_rate')->nullable(); // FR
            $table->string('heart_rate')->nullable(); // FC
            $table->string('oxygen_saturation')->nullable(); // SaO2
            $table->string('skin_subcutaneous')->nullable(); // TCSC
            $table->string('lungs')->nullable(); 
            $table->string('cardiovascular')->nullable(); // CV
            $table->string('neurological')->nullable(); 
            $table->text('auxiliary_exams')->nullable(); 
            $table->text('others')->nullable(); 
            
            // Logística de la Referencia
            $table->enum('referral_type', ['EMERGENCIA', 'CONSULTA EXTERNA', 'APOYO AL DX'])->nullable(); 
            $table->date('appointment_date')->nullable(); 
            $table->time('appointment_time')->nullable(); 
            $table->string('attending_physician_name')->nullable(); 
            $table->string('coordination_name')->nullable(); 
            $table->enum('patient_condition', ['ESTABLE', 'MAL ESTADO'])->nullable(); 
            
            // Los 4 Usuarios Responsables (Relacionados a tabla users)
            $table->foreignId('referral_responsible_id')->nullable()->constrained('users'); 
            $table->foreignId('facility_responsible_id')->nullable()->constrained('users'); 
            $table->foreignId('escort_staff_id')->nullable()->constrained('users'); 
            $table->foreignId('receiving_staff_id')->nullable()->constrained('users'); 
            
            // Estado de Llegada
            $table->enum('arrival_condition', ['ESTABLE', 'MAL ESTADO', 'FALLECIDO'])->nullable(); 
            $table->foreignId('numeration_id')->nullable()->constrained('referral_numerations');
            
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
