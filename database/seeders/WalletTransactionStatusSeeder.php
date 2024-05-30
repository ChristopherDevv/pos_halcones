<?php

namespace Database\Seeders;

use App\Models\Wallet\WalletTransactionStatus;
use Illuminate\Database\Seeder;

class WalletTransactionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WalletTransactionStatus::create([
            'name' => 'pendiente',
            'description' => 'Transacción pendiente',
            'color' => 'warning primary',
        ]);

        WalletTransactionStatus::create([
            'name' => 'completada', 
            'description' => 'Transacción completada',
            'color' => 'success primary',
        ]);

        WalletTransactionStatus::create([
            'name' => 'cancelada',
            'description' => 'Transacción cancelada',
            'color' => 'danger primary',
        ]);

        WalletTransactionStatus::create([
            'name' => 'parcialmente_cancelada',
            'description' => 'Transacción parcialmente cancelada',
            'color' => 'danger primary',
        ]);

        WalletTransactionStatus::create([
            'name' => 'rechazada',
            'description' => 'Transacción rechazada',
            'color' => 'danger secondary',
        ]);
    }
}
