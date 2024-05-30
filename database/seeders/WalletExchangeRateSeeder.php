<?php

namespace Database\Seeders;

use App\Models\Wallet\WalletExchangeRate;
use Illuminate\Database\Seeder;

class WalletExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WalletExchangeRate::create([
            'from_wallet_currency_id' => 1,
            'to_wallet_currency_id' => 2,
            'rate' => 1.00,
        ]);
    }
}
