<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $this->call([
            SpecialtySeeder::class,
            UserSeeder::class,
            AreaSeeder::class,
            ProductSeeder::class,
            Cie10Seeder::class,
            PatientSeeder::class,
        ]);
    }
}
