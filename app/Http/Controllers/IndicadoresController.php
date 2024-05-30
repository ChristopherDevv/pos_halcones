<?php

namespace App\Http\Controllers;

use App\Exports\TicketsExport;
use App\Models\Partidos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class IndicadoresController extends Controller
{
    /**
     *
     * Christoper Patiño
     *
     */

public function indexLogVenta(){
        $partidos = $this->searchEvents();

    return view('exportables.ticketsExportables')->with('partidos', $partidos);
}

public function ticketsSold(Request $request)
{
    try {
        $request->validate([
            'id' => 'required'
        ]);

        $export = new TicketsExport($request->get('id'));
        $data = $export->collection();
    
        if ($data === null) {
            $partidos = $this->searchEvents();
            return view('exportables.ticketsExportables', [
                'error' => 'No existen datos para exportar',
                'partidos' => $partidos
            ]);
        }
    
        return Excel::download($export, 'tickets-vendidos.xlsx');
        
    } catch (\Exception $e) {
        $partidos = $this->searchEvents();
        return view('exportables.ticketsExportables', [
                'error' => 'Error al exportar los datos, intente de nuevo.',
                'partidos' => $partidos
            ]);
    }
}

public function indexCodigoAsientos()
{
    $partidos = $this->searchEvents();
    return view('boletos.codigoAsientos')->with('partidos', $partidos);
}

public function findSeatCode(Request $request) 
{
    try {

        $request->validate([
            'email' => 'required|email',
            'eventId' => 'required|numeric'
        ]);
       
        $seatCodeAndStatus = DB::table('tickets_asiento as ta')
            ->leftJoin('tickets as ti', 'ta.tickets_id', '=', 'ti.id')
            ->leftJoin('partidos as p', 'ti.eventos_id', '=', 'p.id')
            ->leftJoin('users as u', 'ti.users_id', '=', 'u.id')
            ->where('u.correo', '=', $request->email)
            ->where('p.id', '=', $request->eventId)
            ->where('ti.payed', '!=', 0)
            ->select('ta.code', 'ta.status')
            ->get();

        $partidos = $this->searchEvents();
        $email = $request->email;
        $eventTitle = DB::table('partidos')->where('id', '=', $request->eventId)->select('titulo')->first();
        

        if( $seatCodeAndStatus->count() <= 0) {
            return view('boletos.codigoAsientos', ['errorSeatCode' => 'No se encontró ningun código de asiento asociado a este correo electronico: ' . $email, 'partidos' => $partidos]);
        }
        $seatCode = $seatCodeAndStatus->pluck('code');
        $seatStatus = $seatCodeAndStatus->pluck('status');

       return view('boletos.codigoAsientos', ['seatCode' => $seatCode, 'seatStatus' => $seatStatus, 'partidos' => $partidos, 'email' => $email, 'eventTitle' => $eventTitle, 'messageSuccess' => 'Se encontraron los codigos de asiento asociados a este correo electronico: ' . $email]);

    }catch (\Exception $e) {
        return redirect()->back()->with('error', 'Hubo errores al encontrar los codigos de asiento');
    }
}

public function searchEvents()
{
   
    /* $partidos = DB::table('tickets AS ti')
    ->select('ti.eventos_id', 'p.titulo', 'ti.fecha')
    ->distinct()
    ->leftJoin('partidos AS p', 'ti.eventos_id', '=', 'p.id')
    ->where('p.status', '!=', 0)
    ->orderBy('ti.fecha', 'desc')
    ->get(); */

    $partidos = Partidos::where('status', '!=', 0)->orderBy('fecha', 'desc')->get();

    return $partidos;
}
    

}
