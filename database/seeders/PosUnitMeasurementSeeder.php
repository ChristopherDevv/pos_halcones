<?php

namespace Database\Seeders;

use App\Models\PointOfSale\PosUnitMeasurement;
use Illuminate\Database\Seeder;

class PosUnitMeasurementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PosUnitMeasurement::create([
            'name' => 'litros',
            'description' => 'Medida de volumen',
            'abbreviation' => 'L',
        ]);

        PosUnitMeasurement::create([
            'name' => 'mililitros',
            'description' => 'Medida de volumen',
            'abbreviation' => 'ml',
        ]);

        PosUnitMeasurement::create([
            'name' => 'kilogramos',
            'description' => 'Medida de peso',
            'abbreviation' => 'kg',
        ]);

        PosUnitMeasurement::create([
            'name' => 'gramos',
            'description' => 'Medida de peso',
            'abbreviation' => 'g',
        ]);

        PosUnitMeasurement::create([
            'name' => 'unidades',
            'description' => 'Medida de cantidad',
            'abbreviation' => 'u',
        ]);
    }
}
