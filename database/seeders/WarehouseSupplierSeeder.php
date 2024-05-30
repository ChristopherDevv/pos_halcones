<?php

namespace Database\Seeders;

use App\Models\PointOfSale\WarehouseSupplier;
use Illuminate\Database\Seeder;

class WarehouseSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WarehouseSupplier::create([
            'name' => 'proveedor generico',
            'last_name' => 'generico',
            'company_name' => 'generico S.A.',
            'email' => 'proveerdorgenerico@gmail.com',
            'phone_number' => '1234567890',
            'address' => 'calle 123',
            'city' => 'ciudad generica',
            'state' => 'estado generico',
            'country' => 'pais generico',
            'zip_code' => '12345',
            'description' => 'proveedor generico para pruebas y datos estaticos'
        ]);
    }
}
