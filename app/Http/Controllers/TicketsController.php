<?php
//Tengo motivos para creer que este controllador está en desuso. 
namespace App\Http\Controllers;

use App\Models\Tickets;
use App\Models\Asientos;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\TicketsAsientos;
use Illuminate\Support\Facades\DB;
use App\Models\Interfaces\DataResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\AsientosController;
use App\Models\Interfaces\EstatusAsientosEnum;
use App\Models\Partidos;

class TicketsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() 
    {
        return Tickets::where('status',true)->with(['user','asientos','evento'])->get();
    }

    public function findByIdUser($idusuario) 
    {
        return Tickets::where([
            ['status',true],
            ['users_id',$idusuario]
        ])->with(['user','asientos','evento'])->get();
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */ 
    public function store(Request $request)
    {
        $ticketData = $request->all();
        $ticket = Tickets::create($ticketData);
        if($request->has('config')) {
            $config = $ticketData['config'];
            $config['idTicket'] = $ticket->id;
            $config['idEvento'] = $ticket->eventos_id;
            return app(\App\Http\Controllers\AsientosController::class)->reservarBoletos($ticketData['asientoBoleto'],$config);
        }else {
           if($ticket->temporada) {
                $config = [
                    'tipeReservation' => 'temporada',
                    'idTicket' => $ticket->id,
                    'idEvento' => $ticket->eventos_id
                ];
                return app(\App\Http\Controllers\AsientosController::class)->reservarBoletos($ticketData['asientoBoleto'],$config);
           }else {
                $asientos = Asientos::whereIn('code',$ticketData['asientoBoleto']);
                $ticketsAsientos = array();
                foreach ($asientos->get() as $asiento) {
                    $seat = [
                        'tickets_id' => $ticket->id,
                        'code' => $asiento->code,
                        'eventos_id' => $ticket->eventos_id,
                        'status' => EstatusAsientosEnum::COMPRADO
                    ];
                    array_push($ticketsAsientos,$seat);
                }
                $ticketsAsiento =  TicketsAsientos::insert($ticketsAsientos);
                return response()->json(new DataResponse('Se han reservado los asientos','Reservados',$asientos));    
           }
        }
    }

    /**
     * Display the specifie->resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        return Tickets::where('id',$id)->where('status',true)->with(['user','boletos'])->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return Tickets::where('id',$id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Tickets::where('id',$id)->update([
            'status'=> false
        ]);
    }

    public function indexCancelacionBoletos()
    {
        $partidos = $this->searchEvents();
        return view('boletos.cancelacionBoletos')->with('partidos', $partidos);
    }

    public function indexBoletosNoVendidos()
    {
        $partidos = $this->searchEvents();
        return view('boletos.boletosNoVendidos')->with('partidos', $partidos);
    }

    public function findUnsoldTickets(Request $request)
    {
        $request->validate([
            'eventId' => 'required|numeric',
            'bydate' => 'required',
        ]);
    
        $eventId = $request->eventId;
        $bydate = $request->bydate;

        $tickets = Tickets::where('eventos_id', $eventId)
            ->where('type_reservation', 'evento')
            ->where('payed', 0)
            ->where('status', '!=', 0);
    
        if ($bydate == '20') {
            $tickets = $tickets->where('creation_date', '<', now()->subMinutes(20));
        } elseif ($bydate == '1') {
            $tickets = $tickets->where('creation_date', '<', now()->subHour());
        } elseif ($bydate == '24') {
            $tickets = $tickets->where('creation_date', '<', now()->subHours(24));
        }
        
        $tickets = $tickets->orderBy('creation_date', 'desc')->get();

        //retornamos si no hay tickets
        if($tickets->count() == 0){
            $partidos = $this->searchEvents();
            return view('boletos.boletosNoVendidos', ['tickets' => $tickets, 'dateSelected' => $bydate, 'partidos' => $partidos, 'messageError' => 'No se han encontrado tickets no vendidos']);
        }
        
        $tickets = $tickets->map(function ($ticket) {
            return [
                'ticket_id' => $ticket->id,
                'lugar' => $ticket->lugar,
                'creation_date' => $ticket->creation_date,
                'paid_ticket' => $ticket->payed,
                'ticket_total' => $ticket->total,
            ];
        });
    
        $partidos = $this->searchEvents();
        return view('boletos.boletosNoVendidos', ['tickets' => $tickets, 'dateSelected' => $bydate, 'partidos' => $partidos, 'messageSuccess' => 'Se han encontrado ' . $tickets->count() . ' tickets no vendidos']);
    }

    public function ticketSeatCodes(Request $request)
    {
        try{

            $request->validate([
                'seatCode' => 'required',
                'eventId' => 'required|numeric'
            ]);

            $seatCode = $request->seatCode;
            $tickets = $this->seatByTicket( $request->seatCode, $request->eventId);
            $partidos = $this->searchEvents();

            if($tickets->isEmpty()){
                return view('boletos.cancelacionBoletos', ['errorSeatCode' => 'No se encontró ningun ticket asociado a este codigo de asiento: ' . $seatCode , 'partidos' => $partidos]);
            }
    
            $tickets = $tickets->map(function ($ticket) {
                return [
                    'ticket_id' => $ticket->ticket_id,
                    'ticket_type_agreement' => $ticket->type_agreement,
                    'ticket_type_payment' => $ticket->type_payment,
                    'ticket_type_reservation' => $ticket->type_reservation,
                    'ticket_total' => $ticket->total,
                    'ticket_status' => $ticket->status,
                    'seat_id' => $ticket->seat_id, 
                    'seat_code' => $ticket->code,
                    'paid_ticket' => $ticket->payed,
                ];
            });
    
             return view('boletos.cancelacionBoletos', ['tickets' => $tickets, 'partidos' => $partidos, 'eventSelected' => $request->eventId]);

        }catch(\Exception $e){
            return view('boletos.cancelacionBoletos', ['errorSeatCode' => 'Error al encontrar datos', 'partidos' => $partidos]);
        }

    }

    public function updateFieldsTicket(Request $request)
    {        
        $request->validate([
            'modelId' => 'required',
            'modelValue' => 'required',
            'fieldName' => 'required',
            'eventSelected' => 'required'
        ]);

        $partidos = $this->searchEvents();
    
        $ticket = Tickets::find($request->modelId);
        if(!$ticket){
            return view('boletos.cancelacionBoletos', ['errorMessage' => 'No se encontro el ticket', 'partidos' => $partidos]);
        }

        $field = $request->fieldName;
        $ticket->update([$field => $request->modelValue]);

        // Obtén los asientos asociados al ticket después de actualizarlo
        $seatCode = DB::table('tickets_asiento')
        ->where('tickets_id', $ticket->id)
        ->value('code');

        $eventId = $request->eventSelected; // Asegúrate de que 'event_id' es el campo correcto
            
        $tickets = $this->seatByTicket($seatCode, $eventId);

        if($tickets->isEmpty()){
            return view('boletos.cancelacionBoletos', ['errorMessage' => 'No se encontraron asientos asociados a este ticket', 'partidos' => $partidos]);
        }

        $tickets = $tickets->map(function ($ticket) {
            return [
                'ticket_id' => $ticket->ticket_id,
                'ticket_type_agreement' => $ticket->type_agreement,
                'ticket_type_payment' => $ticket->type_payment,
                'ticket_type_reservation' => $ticket->type_reservation,
                'ticket_total' => $ticket->total,
                'ticket_status' => $ticket->status,
                'seat_id' => $ticket->seat_id, 
                'seat_code' => $ticket->code,
                'paid_ticket' => $ticket->payed,
            ];
        });
    
         return view('boletos.cancelacionBoletos', ['messageSuccess' => 'Se ha actualizado el campo ' . $field . ' del ticket con ID: ' . $request->modelId, 'tickets' => $tickets, 'partidos' => $partidos, 'eventSelected' => $eventId]);
    }

    public function deleteSeatFromTicket(Request $request)
    {
        try{
            $request->validate([
                'ticketId' => 'required|numeric',
                'seatId' => 'required|numeric',
                'eventSelected' => 'required'
            ]); 

            $ticketId = $request->ticketId;
            $seatId = $request->seatId;

            $ticket = Tickets::find($ticketId);
            $seat = TicketsAsientos::find($seatId);
            $partidos = $this->searchEvents();

            if (!$ticket || !$seat) {
            return view('boletos.cancelacionBoletos', ['errorSeatCode' => 'No se encontró el ticket', 'partidos' => $partidos]);
            }   

            //calculamos el nuevo tatal del ticket
            $newTotal = ($ticket->total / $ticket->asientos->count()) * ($ticket->asientos->count() - 1);
            //eliminamos el asiento del ticket
            $seat->delete();
            //actualizamos el total del ticket
            $ticket->update(['total' => $newTotal]);

            // Obtén los asientos asociados al ticket después de actualizarlo
            $seatCode = DB::table('tickets_asiento')
            ->where('tickets_id', $ticket->id)
            ->value('code');

            $eventId = $request->eventSelected; // Asegúrate de que 'event_id' es el campo correcto
                
            $tickets = $this->seatByTicket($seatCode, $eventId);

            if($tickets->isEmpty()){
                return view('boletos.cancelacionBoletos', ['errorMessage' => 'No se encontraron asientos asociados a este ticket', 'partidos' => $partidos]);
            }

            $tickets = $tickets->map(function ($ticket) {
                return [
                    'ticket_id' => $ticket->ticket_id,
                    'ticket_type_agreement' => $ticket->type_agreement,
                    'ticket_type_payment' => $ticket->type_payment,
                    'ticket_type_reservation' => $ticket->type_reservation,
                    'ticket_total' => $ticket->total,
                    'ticket_status' => $ticket->status,
                    'seat_id' => $ticket->seat_id, 
                    'seat_code' => $ticket->code,
                    'paid_ticket' => $ticket->payed,
                ];
            });

            return view('boletos.cancelacionBoletos', ['tickets' => $tickets, 'partidos' => $partidos, 'eventSelected' => $eventId, 'messageSuccess' => 'Se ha eliminado el asiento con ID: ' . $seatId . ' del ticket con ID: ' . $ticketId]);

        }catch(\Exception $e){
            return view('boletos.cancelacionBoletos', ['errorSeatCode' => 'Error al encontrar datos', 'partidos' => $partidos]);
        }
    }

    public function seatByTicket($seatCode, $eventId)
    {
        $subQuery = DB::table('tickets_asiento as ta2')
            ->select('ti2.id')
            ->leftJoin('tickets as ti2', 'ta2.tickets_id', '=', 'ti2.id')
            ->where('ta2.code', '=', $seatCode)
            ->where('ti2.eventos_id', '=', $eventId);
    
        $tickets = DB::table('tickets_asiento as ta')
            ->select('ta.id as seat_id', 'ti.id as ticket_id', 'ta.code', 'ti.status', 'ti.type_payment as type_payment', 'ti.payed', 'ti.type_agreement', 'ti.type_reservation', 'ti.total')
            ->leftJoin('tickets as ti', 'ta.tickets_id', '=', 'ti.id')
            ->whereIn('ti.id', $subQuery)
            ->where('ta.eventos_id', '=', $eventId)
            ->get();

        return $tickets;
    }

    public function cancelTicket(Request $request)
    {
        try{
            $request->validate([
                'ticketId' => 'required|numeric',
            ]);

            $ticket = Tickets::find($request->ticketId);
            $partidos = $this->searchEvents();

            if($request->UnsoldTicket == 'true' && !$ticket){
                return view('boletos.cancelacionBoletos', ['errorSeatCode' => 'No se encontró el ticket', 'partidos' => $partidos]);
            }

            if(!$ticket){
               return view('boletos.cancelacionBoletos', ['errorSeatCode' => 'No se encontró el ticket', 'partidos' => $partidos]);
            }

            $ticket->update(['status' => 0, 'total' => 0]);

            if($request->UnsoldTicket == 'true'){
                return view('boletos.boletosNoVendidos', ['messageSuccessCancelation' => 'Se ha cancelado el ticket', 'partidos' => $partidos]);
            }

            return view('boletos.cancelacionBoletos', ['messageSuccess' => 'Se ha cancelado el ticket', 'partidos' => $partidos]);

        }catch(\Exception $e){
            return view('boletos.cancelacionBoletos', ['errorSeatCode' => 'Error al encontrar datos', 'partidos' => $partidos]);
        }

    }

    public function cancelAllTicket(Request $request)
    {
        $allTickets = json_decode($request->get('all_tickets'), true);
        $ticketIds = array_column($allTickets, 'ticket_id');

        Tickets::whereIn('id', $ticketIds)->update(['status' => 0, 'total' => 0]);

        $partidos = $this->searchEvents();
        return view('boletos.boletosNoVendidos', ['messageSuccessCancelation' => 'Se han cancelado los tickets', 'partidos' => $partidos]);
    }

    public function searchEvents()
    {
      /*   $partidos = DB::table('tickets AS ti')
        ->select('ti.eventos_id', 'p.titulo', 'ti.fecha')
        ->distinct()
        ->leftJoin('partidos AS p', 'ti.eventos_id', '=', 'p.id')
        ->where('p.status', '!=', 0)
        ->orderBy('ti.fecha', 'desc')
        ->get(); */

        $partidos = Partidos::where('status', '!=', 0)->orderBy('fecha', 'desc')->get();

        return $partidos;
    }
    
    public function changeTypePayment(Request $request)
    {

        $request->validate([
            'modelId' => 'required',
            'modelValue' => 'required',
            'eventSelected' => 'required'
        ]);

        $ticket = Tickets::find($request->modelId);
        $ticket->update(['type_payment' => $request->modelValue]);

        $newTypePayment = "";
        switch ($request->modelValue) {
            case 1:
                $newTypePayment = "Efectivo";
                break;
            case 2:
                $newTypePayment = "Tarjeta";
                break;
            case 3:
                $newTypePayment = "Cortesía";
                break;
            default:
                break;
        }

        $partidos = $this->searchEvents();

        // Obtén los asientos asociados al ticket después de actualizarlo
        $seatCode = DB::table('tickets_asiento')
        ->where('tickets_id', $ticket->id)
        ->value('code');

        $eventId = $request->eventSelected; // Asegúrate de que 'event_id' es el campo correcto
            
        $tickets = $this->seatByTicket($seatCode, $eventId);

        if($tickets->isEmpty()){
            return view('boletos.cancelacionBoletos', ['errorMessage' => 'No se encontraron asientos asociados a este ticket', 'partidos' => $partidos]);
        }

        $tickets = $tickets->map(function ($ticket) {
            return [
                'ticket_id' => $ticket->ticket_id,
                'ticket_type_agreement' => $ticket->type_agreement,
                'ticket_type_payment' => $ticket->type_payment,
                'ticket_type_reservation' => $ticket->type_reservation,
                'ticket_total' => $ticket->total,
                'ticket_status' => $ticket->status,
                'seat_id' => $ticket->seat_id, 
                'seat_code' => $ticket->code,
                'paid_ticket' => $ticket->payed,
            ];
        });

        return view('boletos.cancelacionBoletos', ['tickets' => $tickets, 'partidos' => $partidos, 'eventSelected' => $eventId, 'messageSuccessPayment' => 'El ticket con ID: ' . $request->modelId . ' se ha actualizado al tipo de pago "'. $newTypePayment . '"']);

    }

}
