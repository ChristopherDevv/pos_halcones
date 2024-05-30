<?php

namespace Database\Seeders;

use App\Models\PointOfSALE\ClothingCategory;
use Illuminate\Database\Seeder;

class ClothingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ClothingCategory::create([
            'name' => 'femenino',
            'description' => 'ropa para mujeres',
            'is_active' => true
        ]);

        ClothingCategory::create([
            'name' => 'masculino',
            'description' => 'ropa para hombres',
            'is_active' => true
        ]);

        ClothingCategory::create([
            'name' => 'unisex',
            'description' => 'ropa para ambos géneros',
            'is_active' => true
        ]);

    }
}
