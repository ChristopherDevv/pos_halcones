<?php

namespace Database\Seeders;

use App\Models\PointOfSale\GlobalCombo;
use App\Models\PointOfSale\PosProductWarehouse;
use Illuminate\Database\Seeder;

class GlobalComboSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $posProductWarehouseCafeteriaId = PosProductWarehouse::where('name', 'snack_bar')->first()->id;

        GlobalCombo::create([
            'pos_product_warehouse_id' => $posProductWarehouseCafeteriaId,
            'name' => 'falkombo',
            'sale_price' => 139.0000,
            'permitted_products' => 3,
            'description' => 'combo conformado por palomitas y 2 bebidas',
            'is_active' => true
        ]);

        GlobalCombo::create([
            'pos_product_warehouse_id' => $posProductWarehouseCafeteriaId,
            'name' => '3_pointer',
            'sale_price' => 189.0000,
            'permitted_products' => 4,
            'description' => 'combo conformado por palomitas, nachos y 2 bebidas',
            'is_active' => true
        ]);

        GlobalCombo::create([
            'pos_product_warehouse_id' => $posProductWarehouseCafeteriaId,
            'name' => 'pick_and_roll',
            'sale_price' => 189.0000,
            'permitted_products' => 4,
            'description' => 'combo conformado por 2 hot dogs jumbo y 2 bebidas',
            'is_active' => true
        ]);

        GlobalCombo::create([
            'pos_product_warehouse_id' => $posProductWarehouseCafeteriaId,
            'name' => 'mvp',
            'sale_price' => 289.0000,
            'permitted_products' => 6,
            'description' => 'combo conformado por palomitas, nachos, hot dogs jumbo y 3 bebidas',
            'is_active' => true
        ]);

        GlobalCombo::create([
            'pos_product_warehouse_id' => $posProductWarehouseCafeteriaId,
            'name' => 'halcon',
            'sale_price' => 119.0000,
            'permitted_products' => 3,
            'description' => 'combo conformado por palomitas y 2 bebidas',
            'is_active' => true
        ]);
    }
}
