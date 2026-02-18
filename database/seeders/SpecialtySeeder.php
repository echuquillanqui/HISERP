<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Specialty;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = [
            'MEDICINA GENERAL',
            'LABORATORIO', // Requerido
            'RADIOLOGIA', // Requerido
            'PEDIATRIA',
            'GINECOLOGIA',
            'CARDIOLOGIA',
            'TRAUMATOLOGIA',
            'ODONTOLOGIA',
            'PSIQUIATRIA',
            'ANESTESIOLOGIA'
        ];

        foreach ($specialties as $name) {
            Specialty::create([
                'name' => $name
            ]);
        }
    }
}
