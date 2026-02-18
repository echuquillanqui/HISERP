<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // ANALGÉSICOS Y ANTIINFLAMATORIOS
            ['code' => 'MED001', 'name' => 'PARACETAMOL', 'concentration' => '500MG', 'presentation' => 'TABLETAS', 'stock' => 100, 'min_stock' => 20, 'purchase_price' => 0.05, 'selling_price' => 0.50, 'expiration_date' => '2027-12-30'],
            ['code' => 'MED002', 'name' => 'IBUPROFENO', 'concentration' => '400MG', 'presentation' => 'TABLETAS', 'stock' => 15, 'min_stock' => 25, 'purchase_price' => 0.10, 'selling_price' => 0.70, 'expiration_date' => '2026-11-15'], // ALERTA AMARILLA
            ['code' => 'MED003', 'name' => 'NAPROXENO', 'concentration' => '550MG', 'presentation' => 'TABLETAS', 'stock' => 50, 'min_stock' => 10, 'purchase_price' => 0.15, 'selling_price' => 1.00, 'expiration_date' => '2027-05-20'],
            ['code' => 'MED004', 'name' => 'DICLOFENACO', 'concentration' => '75MG/3ML', 'presentation' => 'AMPOLLA', 'stock' => 0, 'min_stock' => 10, 'purchase_price' => 1.20, 'selling_price' => 4.50, 'expiration_date' => '2026-08-10'], // AGOTADO (ROJO)
            
            // ANTIBIÓTICOS
            ['code' => 'MED005', 'name' => 'AMOXICILINA', 'concentration' => '500MG', 'presentation' => 'CÁPSULAS', 'stock' => 80, 'min_stock' => 15, 'purchase_price' => 0.40, 'selling_price' => 1.50, 'expiration_date' => '2027-01-15'],
            ['code' => 'MED006', 'name' => 'AZITROMICINA', 'concentration' => '500MG', 'presentation' => 'TABLETAS', 'stock' => 30, 'min_stock' => 10, 'purchase_price' => 1.50, 'selling_price' => 5.00, 'expiration_date' => '2026-12-01'],
            ['code' => 'MED007', 'name' => 'CIPROFLOXACINO', 'concentration' => '500MG', 'presentation' => 'TABLETAS', 'stock' => 12, 'min_stock' => 20, 'purchase_price' => 0.60, 'selling_price' => 2.00, 'expiration_date' => '2027-03-10'], // STOCK BAJO
            ['code' => 'MED008', 'name' => 'CEFALEXINA', 'concentration' => '500MG', 'presentation' => 'CÁPSULAS', 'stock' => 45, 'min_stock' => 10, 'purchase_price' => 0.50, 'selling_price' => 1.80, 'expiration_date' => '2026-09-25'],

            // CORTICOIDES Y ANTIHISTAMÍNICOS
            ['code' => 'MED009', 'name' => 'DEXAMETASONA', 'concentration' => '4MG/2ML', 'presentation' => 'AMPOLLA', 'stock' => 25, 'min_stock' => 5, 'purchase_price' => 1.10, 'selling_price' => 4.00, 'expiration_date' => '2027-10-12'],
            ['code' => 'MED010', 'name' => 'PREDNISONA', 'concentration' => '20MG', 'presentation' => 'TABLETAS', 'stock' => 100, 'min_stock' => 20, 'purchase_price' => 0.20, 'selling_price' => 0.80, 'expiration_date' => '2028-02-15'],
            ['code' => 'MED011', 'name' => 'CETIRIZINA', 'concentration' => '10MG', 'presentation' => 'TABLETAS', 'stock' => 200, 'min_stock' => 30, 'purchase_price' => 0.05, 'selling_price' => 0.40, 'expiration_date' => '2027-07-20'],
            ['code' => 'MED012', 'name' => 'CLORFENAMINA', 'concentration' => '4MG', 'presentation' => 'TABLETAS', 'stock' => 150, 'min_stock' => 20, 'purchase_price' => 0.03, 'selling_price' => 0.30, 'expiration_date' => '2028-05-11'],

            // GASTROINTESTINALES
            ['code' => 'MED013', 'name' => 'OMEPRAZOL', 'concentration' => '20MG', 'presentation' => 'CÁPSULAS', 'stock' => 60, 'min_stock' => 15, 'purchase_price' => 0.15, 'selling_price' => 1.00, 'expiration_date' => '2027-11-30'],
            ['code' => 'MED014', 'name' => 'RANITIDINA', 'concentration' => '150MG', 'presentation' => 'TABLETAS', 'stock' => 0, 'min_stock' => 20, 'purchase_price' => 0.10, 'selling_price' => 0.60, 'expiration_date' => '2026-04-12'], // AGOTADO
            ['code' => 'MED015', 'name' => 'HIOSCINA', 'concentration' => '10MG', 'presentation' => 'TABLETAS', 'stock' => 40, 'min_stock' => 10, 'purchase_price' => 0.35, 'selling_price' => 1.50, 'expiration_date' => '2027-08-01'],

            // RESPIRATORIOS Y OTROS
            ['code' => 'MED016', 'name' => 'SALBUTAMOL', 'concentration' => '100MCG', 'presentation' => 'INHALADOR', 'stock' => 8, 'min_stock' => 10, 'purchase_price' => 8.50, 'selling_price' => 15.00, 'expiration_date' => '2026-10-30'], // STOCK BAJO
            ['code' => 'MED017', 'name' => 'AMBROXOL', 'concentration' => '30MG/5ML', 'presentation' => 'JARABE', 'stock' => 20, 'min_stock' => 5, 'purchase_price' => 2.50, 'selling_price' => 7.00, 'expiration_date' => '2027-02-28'],
            ['code' => 'MED018', 'name' => 'ENALAPRIL', 'concentration' => '10MG', 'presentation' => 'TABLETAS', 'stock' => 90, 'min_stock' => 20, 'purchase_price' => 0.12, 'selling_price' => 0.60, 'expiration_date' => '2028-06-14'],
            ['code' => 'MED019', 'name' => 'METFORMINA', 'concentration' => '850MG', 'presentation' => 'TABLETAS', 'stock' => 120, 'min_stock' => 30, 'purchase_price' => 0.25, 'selling_price' => 1.20, 'expiration_date' => '2028-09-20'],
            ['code' => 'MED020', 'name' => 'COMPLEJO B', 'concentration' => '---', 'presentation' => 'AMPOLLA', 'stock' => 50, 'min_stock' => 15, 'purchase_price' => 1.80, 'selling_price' => 6.00, 'expiration_date' => '2027-12-15'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
