<?php

namespace Database\Seeders;

use App\Models\PointOfSale\PosTicketStatus;
use Illuminate\Database\Seeder;

class PosTicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PosTicketStatus::create([
            'name' => 'pendiente',
            'description' => 'ticket de venta pendiente de pago',
            'color' => 'warning primary',
        ]);

        PosTicketStatus::create([
            'name' => 'pagado',
            'description' => 'ticket de venta pagado',
            'color' => 'success primary',
        ]);

        PosTicketStatus::create([
            'name' => 'cancelado',
            'description' => 'ticket de venta cancelado',
            'color' => 'danger primary',
        ]);

        PosTicketStatus::create([
            'name' => 'parcialmente_cancelado',
            'description' => 'ticket de venta con cancelación de algun o algunos productos',
            'color' => 'warning secondary',
        ]);
    }
}
