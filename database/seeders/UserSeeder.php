<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Specialty;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtenemos IDs de referencia para los médicos
        $medicina = Specialty::where('name', 'LIKE', '%MEDICINA GENERAL%')->first();
        $laboratorio = Specialty::where('name', 'LIKE', '%LABORATORIO%')->first();

        // 1. TU USUARIO (SUPERADMIN)
        User::create([
            'name' => 'Raul Eduardo Chuquillanqui Yupanqui',
            'email' => 'echuquillanquiy@gmail.com',
            'username' => 'echuquillanqui',
            'password' => Hash::make('12345678'),
            'role' => 'superadmin',
            'specialty_id' => $medicina ? $medicina->id : null,
            'status' => true,
        ]);

        // 2. USUARIO DE PRUEBA: MÉDICO
        User::create([
            'name' => 'Dr. Manuel Perez',
            'email' => 'medico@sistema.com',
            'username' => 'mperez',
            'password' => Hash::make('12345678'),
            'role' => 'medicina',
            'specialty_id' => $medicina ? $medicina->id : null,
            'status' => true,
        ]);

        // 3. USUARIO DE PRUEBA: LABORATORIO
        User::create([
            'name' => 'Lic. Ana Martinez',
            'email' => 'lab@sistema.com',
            'username' => 'amartinez',
            'password' => Hash::make('12345678'),
            'role' => 'laboratorio',
            'specialty_id' => $laboratorio ? $laboratorio->id : null,
            'status' => true,
        ]);
    }
}
