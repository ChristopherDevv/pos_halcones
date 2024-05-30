<?php

namespace Database\Seeders;

use App\Models\PointOfSale\InventoryTransactionType;
use Illuminate\Database\Seeder;

class InventoryTransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        InventoryTransactionType::create([
            'name' => 'compra_de_stock',
            'description' => 'compra de stock para el inventario global',
            'is_active' => 1
        ]);

        InventoryTransactionType::create([
            'name' => 'devolucion_de_stock',
            'description' => 'devolucion de stock de el inventario global',
            'is_active' => 1
        ]);

        InventoryTransactionType::create([
            'name' => 'actualizacion_de_propiedades',
            'description' => 'actualizacion de propiedades de el inventario global',
            'is_active' => 1
        ]);

        InventoryTransactionType::create([
            'name' => 'transferencia_de_stock_a_tienda',
            'description' => 'transferencia de stock a una tienda',
            'is_active' => 1
        ]);

        InventoryTransactionType::create([
            'name' => 'devolucion_de_stock_a_almacen',
            'description' => 'devolucion de stock de tienda a inventario global',
            'is_active' => 1
        ]);

    }
}
