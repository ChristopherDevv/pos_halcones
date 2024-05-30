<?php

namespace Database\Seeders;

use App\Models\PointOfSale\PosProductWarehouse;
use Illuminate\Database\Seeder;

class PosProductWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PosProductWarehouse::create([
            'stadium_location_id' => 1,
            'user_manager_id' => 99,
            'name' => 'snack_bar',
            'description' => 'cafeteria de el nido del halcon',
            'email' => 'cafeteria_elnidodelhalcon@gmail.com',
            'phone' => '0000000000',
       ]);
       PosProductWarehouse::create([
            'stadium_location_id' => 1,
            'user_manager_id' => 99,
            'name' => 'bar_vip',
            'description' => 'barra de bebidas de el nido del halcon',
            'email' => 'bar_elnidodelhalcon@gmail.com',
            'phone' => '0000000000',
       ]);
       PosProductWarehouse::create([
            'stadium_location_id' => 1,
            'user_manager_id' => 99,
            'name' => 'tienda_fan',
            'description' => 'tienda de merch de el nido del halcon',
            'email' => 'fanstore_elnidodelhalcon@gmail.com',
            'phone' => '0000000000',
       ]);
       PosProductWarehouse::create([
            'stadium_location_id' => 1,
            'user_manager_id' => 99,
            'name' => 'tienda test',
            'description' => 'tienda de test',
            'email' => 'tiendatest@gmail.com',
            'phone' => '0000000000',
       ]);
    }
}
