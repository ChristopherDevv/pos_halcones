<?php

namespace App\Exports;

use App\Models\Tickets;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TicketsExport implements FromCollection, WithHeadings
{

    protected $event_ids;
    public function __construct(array $event_ids)
    {
        $this->event_ids = $event_ids;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $tickets = DB::table('tickets')
            ->leftJoin('tickets_asiento', 'tickets.id', '=', 'tickets_asiento.tickets_id')
            ->leftJoin('partidos', 'tickets.eventos_id', '=', 'partidos.id')
            ->leftJoin('users', 'tickets.users_id', '=', 'users.id')
            ->select(
                'tickets.id AS Ticket',
                'partidos.titulo AS Partido',
                DB::raw('DAY(tickets.creation_date) AS Dia'),
                DB::raw('MONTH(tickets.creation_date) AS Mes'),
                DB::raw('YEAR(tickets.creation_date) AS Año'),
                DB::raw('TIME(tickets.creation_date) AS Hora'),
                DB::raw("IF(tickets.type_payment = 1, 'Efectivo','Tarjeta') AS 'Medio de pago'"),
                DB::raw("IF(tickets.type_reservation = 'evento', 'APP', 'Taquilla') AS 'Medio de compra'"),
                'users.correo AS Usuario',
                DB::raw('COUNT(tickets_asiento.code) AS Boletaje'),
                'tickets.zona AS Zona',
                'tickets.total AS Total',
                DB::raw("IF(tickets.type_agreement IS NULL, 'Ninguno',tickets.type_agreement) AS 'Jersey'"),
                DB::raw('tickets.total/COUNT(tickets_asiento.code) AS `precio por Ticket`')
            )
            ->whereIn('tickets.eventos_id', $this->event_ids)
            ->where('partidos.status', '!=', 0)
            ->where('tickets.status', '!=', 0)
            ->where('tickets.payed', '=', 1)
            ->where('tickets.type_payment', '!=', 3)
            ->groupBy('tickets.id', 'partidos.titulo', 'tickets.creation_date', 'tickets.type_payment', 'tickets.type_reservation', 'users.correo', 'tickets.zona', 'tickets.total', 'tickets.type_agreement')
            ->get();
        
        //retorna null si no hay tickets para despues devolver una excepcion
        if(count($tickets) < 1){
            return null;
        }
        return collect($tickets);
    }

    public function headings(): array
    {
        return [
            'Ticket',
            'Partido',
            'Dia',
            'Mes',
            'Año',
            'Hora',
            'Medio de pago',
            'Medio de comopra',
            'Usuario',
            'Boletaje',
            'Zona',
            'Total',
            'Jersey',
            'precio por Ticket',

        ];
    }
}
