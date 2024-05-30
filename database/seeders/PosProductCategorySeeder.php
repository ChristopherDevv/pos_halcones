<?php

namespace Database\Seeders;

use App\Models\PointOfSale\PosProductCategory;
use Illuminate\Database\Seeder;

class PosProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PosProductCategory::create([
            'name' => 'bebidas',
            'description' => 'Bebidas de todo tipo',
            'image_file' => null
        ]);

        PosProductCategory::create([
            'name' => 'comidas',
            'description' => 'Comidas de todo tipo',
            'image_file' => null
        ]);
        
        PosProductCategory::create([
            'name' => 'merch',
            'description' => 'Merch de todo tipo',
            'image_file' => null
        ]);


    }
}
