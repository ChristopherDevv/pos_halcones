<?php

namespace Database\Seeders;

use App\Models\PointOfSale\PosCashRegisterType;
use Illuminate\Database\Seeder;

class PosCashRegisterTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PosCashRegisterType::create([
            'name' => 'caja_registradora_1',
            'description' => 'tipo de caja registradora generica',
            'cash_register_number' => 1,
        ]);

        PosCashRegisterType::create([
            'name' => 'caja_registradora_2',
            'description' => 'tipo de caja registradora generica',
            'cash_register_number' => 2,
        ]);

        PosCashRegisterType::create([
            'name' => 'caja_registradora_3',
            'description' => 'tipo de caja registradora generica',
            'cash_register_number' => 3,
        ]);
    }
}
