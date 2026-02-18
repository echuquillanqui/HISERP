<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\Catalog;
use App\Models\Profile;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear Áreas
        $medicina = Area::create(['name' => 'MEDICINA']);
        $hematologia = Area::create(['name' => 'HEMATOLOGÍA']);
        $bioquimica = Area::create(['name' => 'BIOQUÍMICA']);
        $inmunologia = Area::create(['name' => 'INMUNOLOGÍA']);

        $hcl = Profile::create([
            'area_id' => $medicina->id,
            'name' => 'HISTORIA CLINICA',
            'price' => 100.00 // Precio promocional por el paquete
        ]);

        $historia = Catalog::create([
            'area_id' => $medicina->id,
            'name' => 'HISTORIA',
            'unit' => '-',
            'reference_range' => '-',
            'price' => 100.00
        ]);

        // 2. Crear Exámenes (Catalog) para Hematología
        $hemoglobina = Catalog::create([
            'area_id' => $hematologia->id,
            'name' => 'Hemoglobina',
            'unit' => 'g/dL',
            'reference_range' => '12.0 - 16.0',
            'price' => 15.00
        ]);

        $leucocitos = Catalog::create([
            'area_id' => $hematologia->id,
            'name' => 'Leucocitos',
            'unit' => 'mm3',
            'reference_range' => '4500 - 11000',
            'price' => 15.00
        ]);

        // 3. Crear Exámenes (Catalog) para Bioquímica
        $glucosa = Catalog::create([
            'area_id' => $bioquimica->id,
            'name' => 'Glucosa',
            'unit' => 'mg/dL',
            'reference_range' => '70 - 110',
            'price' => 20.00
        ]);

        $colesterol = Catalog::create([
            'area_id' => $bioquimica->id,
            'name' => 'Colesterol Total',
            'unit' => 'mg/dL',
            'reference_range' => '< 200',
            'price' => 25.00
        ]);

        $trigliceridos = Catalog::create([
            'area_id' => $bioquimica->id,
            'name' => 'Triglicéridos',
            'unit' => 'mg/dL',
            'reference_range' => '< 150',
            'price' => 25.00
        ]);

        // 4. Crear Perfiles (Profiles) y asociar exámenes
        // Ejemplo: Perfil Lipídico (Bioquímica)
        $perfilLipidico = Profile::create([
            'area_id' => $bioquimica->id,
            'name' => 'Perfil Lipídico',
            'price' => 40.00 // Precio promocional por el paquete
        ]);

        // Asociamos los exámenes al perfil (Relación Many-to-Many)
        $perfilLipidico->catalogs()->attach([
            $colesterol->id,
            $trigliceridos->id
        ]);

        // Ejemplo: Hemograma Completo
        $hemograma = Profile::create([
            'area_id' => $hematologia->id,
            'name' => 'Hemograma Completo',
            'price' => 25.00
        ]);

        $hemograma->catalogs()->attach([
            $hemoglobina->id,
            $leucocitos->id
        ]);
    }
}
