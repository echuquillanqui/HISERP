<?php

namespace Database\Seeders;

use App\Models\Cie10;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Cie10Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $fileName = 'cie10.csv';
    $path = storage_path('app/' . $fileName);

    if (!file_exists($path)) {
        $this->command->error("¡ERROR! No encuentro el archivo en: $path");
        return;
    }

    $this->command->info('Iniciando carga de CIE10...');

    $file = fopen($path, 'r');

    // Detectar delimitador automáticamente
    $firstLine = fgets($file);
    $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
    rewind($file);

    // Leer encabezado
    $header = fgetcsv($file, 0, $delimiter);

    $batch = [];
    $count = 0;

    DB::beginTransaction();

    try {

        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {

            if (!isset($row[0]) || trim($row[0]) === '') {
                continue;
            }

            $batch[] = [
                'codigo'       => trim($row[0]),
                'descripcion'  => trim($row[1] ?? ''),
                'cotejo_final' => trim($row[2] ?? ''),
                'created_at'   => now(),
                'updated_at'   => now(),
            ];

            if (count($batch) === 1000) {
                DB::table('cie10s')->insert($batch);
                $count += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('cie10s')->insert($batch);
            $count += count($batch);
        }

        DB::commit();

        $this->command->info("✔ Carga terminada. Se importaron {$count} registros.");

    } catch (\Throwable $e) {

        DB::rollBack();

        $this->command->error("❌ Error después de {$count} registros.");
        $this->command->error($e->getMessage());
    }

    fclose($file);
}

}
