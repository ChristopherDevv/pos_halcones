<?php

namespace Database\Seeders;

use App\Models\PointOfSale\GlobalTypeCardPayment;
use Illuminate\Database\Seeder;

class GlobalCardPaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GlobalTypeCardPayment::create([
            'name' => 'credito',
            'description' => 'pago con tarjeta de credito',
            'is_active' => true
        ]);
        GlobalTypeCardPayment::create([
            'name' => 'debito',
            'description' => 'pago con tarjeta de debito',
            'is_active' => true
        ]);
    }
}
