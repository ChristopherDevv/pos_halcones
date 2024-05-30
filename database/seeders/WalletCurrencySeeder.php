<?php

namespace Database\Seeders;

use App\Models\Wallet\WalletCurrency;
use Illuminate\Database\Seeder;

class WalletCurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WalletCurrency::create([
            'name' => 'halcones_wallet',
            'description' => 'Moneda de halcones de Xalapa',
            'symbol' => 'HW',
            'image_file' => null,
            'is_active' => true,
        ]);

        WalletCurrency::create([
            'name' => 'peso_mexicano',
            'description' => 'Moneda de pesos mexicanos',
            'symbol' => 'MXN',
            'image_file' => null,
            'is_active' => true,
        ]);
    }
}
