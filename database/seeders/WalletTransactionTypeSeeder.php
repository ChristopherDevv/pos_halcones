<?php

namespace Database\Seeders;

use App\Models\Wallet\WalletTransactionType;
use Illuminate\Database\Seeder;

class WalletTransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WalletTransactionType::create([
            'name' => 'recarga',
            'description' => 'Recarga de saldo',
            'color' => 'success primary',
        ]);

        WalletTransactionType::create([
            'name' => 'compra',
            'description' => 'Compra de producto',
            'color' => 'primary',
        ]);

        WalletTransactionType::create([
            'name' => 'transferencia',
            'description' => 'transferencia de saldo',
            'color' => 'warning primary',
        ]);

        WalletTransactionType::create([
            'name' => 'cancelacion_compra',
            'description' => 'cancelacion de productos de una compra',
            'color' => 'warning primary',
        ]);
    }
}
