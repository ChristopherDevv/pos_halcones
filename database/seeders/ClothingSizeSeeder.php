<?php

namespace Database\Seeders;

use App\Models\PointOfSALE\ClothingSize;
use Illuminate\Database\Seeder;

class ClothingSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ClothingSize::create([
            'name' => 'extra_chica',
            'abbreviation' => 'XS',
            'description' => 'talla extra chica',
            'is_active' => true
        ]);

        ClothingSize::create([
            'name' => 'chica',
            'abbreviation' => 'S',
            'description' => 'talla chica',
            'is_active' => true
        ]);

        ClothingSize::create([
            'name' => 'mediana',
            'abbreviation' => 'M',
            'description' => 'talla mediana',
            'is_active' => true
        ]);

        ClothingSize::create([
            'name' => 'grande',
            'abbreviation' => 'L',
            'description' => 'talla grande',
            'is_active' => true
        ]);

        ClothingSize::create([
            'name' => 'extra_grande',
            'abbreviation' => 'XL',
            'description' => 'talla extra grande',
            'is_active' => true
        ]);

        ClothingSize::create([
            'name' => 'extra_extra_grande',
            'abbreviation' => 'XXL',
            'description' => 'talla extra extra grande',
            'is_active' => true
        ]);

    }
}
