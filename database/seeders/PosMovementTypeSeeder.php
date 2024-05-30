<?php

namespace Database\Seeders;

use App\Models\PointOfSale\PosMovementType;
use Illuminate\Database\Seeder;

class PosMovementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PosMovementType::create([
            'name' => 'venta',
            'description' => 'venta de productos',
        ]);
        PosMovementType::create([
            'name' => 'cancelacion_ticket',
            'description' => 'cancelacion de un ticket de venta',
        ]);
        PosMovementType::create([
            'name' => 'cancelacion_producto',
            'description' => 'cancelacion de un producto de un ticket de venta',
        ]);
    }
}
