<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea 50 pacientes aleatorios usando el factory
        Patient::factory()->count(50)->create();

        // Ejemplo: Crear un paciente específico para pruebas
        Patient::create([
            'dni' => '12345678',
            'first_name' => 'Usuario',
            'last_name' => 'Prueba',
            'birth_date' => '1990-01-01',
            'gender' => 'M',
            'phone' => '999888777',
            'email' => 'prueba@clinica.com',
            'address' => 'Av. Siempre Viva 123'
        ]);
    }
}
