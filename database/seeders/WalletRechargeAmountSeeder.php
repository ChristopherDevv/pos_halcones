<?php

namespace Database\Seeders;

use App\Models\Wallet\WalletRechargeAmount;
use Illuminate\Database\Seeder;

class WalletRechargeAmountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WalletRechargeAmount::create([
            'amount' => 50.00,
            'description' => 'Recarga de 50.00',
            'is_active' => true
        ]);

        WalletRechargeAmount::create([
            'amount' => 100.00,
            'description' => 'Recarga de 100.00',
            'is_active' => true
        ]);

        WalletRechargeAmount::create([
            'amount' => 200.00,
            'description' => 'Recarga de 200.00',
            'is_active' => true
        ]);

        WalletRechargeAmount::create([
            'amount' => 500.00,
            'description' => 'Recarga de 500.00',
            'is_active' => true
        ]);

        WalletRechargeAmount::create([
            'amount' => 1000.00,
            'description' => 'Recarga de 1000.00',
            'is_active' => true
        ]);
    }
}
