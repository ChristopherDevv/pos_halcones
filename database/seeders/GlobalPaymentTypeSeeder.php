<?php

namespace Database\Seeders;

use App\Models\PointOfSale\GlobalPaymentType;
use Illuminate\Database\Seeder;

class GlobalPaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GlobalPaymentType::create([
            'name' => 'efectivo',
            'description' => 'pago en efectivo',
            'is_active' => true
        ]);
        GlobalPaymentType::create([
            'name' => 'tarjeta',
            'description' => 'pago con tarjeta',
            'is_active' => true
        ]);
        GlobalPaymentType::create([
            'name' => 'halcones_wallet',
            'description' => 'pago con cartera halcones',
            'is_active' => true
        ]);
        GlobalPaymentType::create([
            'name' => 'cortesia',
            'description' => 'pago de cortesia',
            'is_active' => true
        ]);
        GlobalPaymentType::create([
            'name' => 'credito',
            'description' => 'pago a credito',
            'is_active' => true
        ]);
        GlobalPaymentType::create([
            'name' => 'donacion',
            'description' => 'pago con donacion',
            'is_active' => true
        ]);
        GlobalPaymentType::create([
            'name' => 'por_cobrar',
            'description' => 'pago por cobrar',
            'is_active' => true
        ]);


    }
}
