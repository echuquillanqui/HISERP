<?php

namespace Database\Seeders;

use App\Models\IopamidolBrand;
use Illuminate\Database\Seeder;

class IopamidolBrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Iopamiron',
            'Isovue',
            'Iopamidol Genérico',
        ];

        foreach ($brands as $name) {
            IopamidolBrand::query()->updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
