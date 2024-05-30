<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\Partidos;
use App\Models\Tickets;
use Illuminate\Support\Arr;
use App\Models\TicketsAsientos;
use App\Models\Asientos;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Interfaces\GruposAsientos as grupo;
use DB;
use Carbon\Carbon;
use App\Models\Interfaces\ErroresExceptionEnum;

use App\Http\Controllers\api\IndicadoresController;
use Illuminate\Support\Facades\DB as FacadesDB;

class HomeController extends Controller
{
    protected $indicadoresController;

    public function __construct( IndicadoresController $indicadoresController )
    {
        $this->middleware('auth');

        $this->indicadoresController = $indicadoresController;
    }

//ESTO ES PARA LOS INDICADORES

    public function index()
    {
        if(strtolower(auth()->user()->id_rol) === 'secondary'){
            return redirect()->route('indicador');
        }

        $data['partidos'] = Partidos::where('status','!=',0)->get();

        return view('commons.home', $data);
    }

    public function indicador_carga(Request $request)
    {
        $data['partidos'] = Partidos::where('status','!=',0)->get();
    
        $dataMatchWithTicketsAndSeatTickets = $this->indicadoresController->matchWithTicketsAndSeatTickets($request->get('idJornada'))->getData(true);
    
        $data['match'] = "";
        $data['ticketsSeat'] = "";
        $data['ticketsSeatsForDate'] = "";
        $courtesyTickets = FacadesDB::table('tickets_asiento AS ta')
        ->leftJoin('tickets AS ti', 'ta.tickets_id', '=', 'ti.id')
        ->select('ti.id AS ticket', 'ta.code AS Boletaje')
        ->where('ti.eventos_id', $request->get('idJornada'))
        ->where('ti.type_payment', 3)
        ->get();
    
        if (ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getCode() === $dataMatchWithTicketsAndSeatTickets['status']) 
        {        
            $data['match'] = $dataMatchWithTicketsAndSeatTickets['data']['match'];
            $data['ticketsSeatsForDate'] = $dataMatchWithTicketsAndSeatTickets['data']['ticketsSeatsForDate'];            
            $data['ticketsSeat'] = array_map(function($ticketsSeat)
            {   
                $ticketsSeat['groupTicketsSeatSubscription'] = array_map(function($groupTicketsSeatSubscription)
                {
                    $totalGroupSeatPrice = [];                    
    
                    $groupTicketsSeatSubscription['groupTicketsSeatTypePayment'] = array_map(function($groupTicketsSeatTypePayment) use (&$totalGroupSeatPrice)
                    {
                        // Se agrupan los boeltos por su precio siempre y cuando sean una venta diferente a cortesia
                        if (Str::lower($groupTicketsSeatTypePayment['payment']) != 'cortesía')
                        {   
                            foreach ($groupTicketsSeatTypePayment['groupTicketsSeatPrices'] as $clave => $iterator) 
                            {                                                                                                                                         
                                $index = array_search($iterator['price'], Arr::pluck($totalGroupSeatPrice, 'price'));                                                 
    
                                if (is_int($index)) 
                                {                                    
                                    $totalGroupSeatPrice[$index]['quantity'] += $iterator['quantity'];
                                    $totalGroupSeatPrice[$index]['total'] += $iterator['total'];
                                } 
                                else 
                                {                                                                    
                                    array_push($totalGroupSeatPrice, $iterator);
                                }
                            }                            
                        }
    
                        return $groupTicketsSeatTypePayment;
    
                    }, $groupTicketsSeatSubscription['groupTicketsSeatTypePayment']);
    
                    $groupTicketsSeatSubscription['totalGroupSeatPrice'] = $totalGroupSeatPrice;
    
                    return $groupTicketsSeatSubscription;
    
                }, $ticketsSeat['groupTicketsSeatSubscription']);
    
                return $ticketsSeat;
    
            }, $dataMatchWithTicketsAndSeatTickets['data']['ticketsSeat']);
        }

        $partidos = Partidos::where('status','!=',0)->get();
        
        if($request->get('indicador_carga') == 'indicador_carga_second'){
           return view('commons.indicadorHome', ['data' => $data, 'courtesyTickets' => $courtesyTickets, 'partidos' => $partidos, 'messageSuccess' => 'Se ha cargado la información correctamente']);
        }else{
            return view('commons.home', ['data' => $data, 'messageSuccess' => 'Se ha cargado la información correctamente', 'partidos' => $partidos]);
        }
    
    }

//ESTO ES PARA EL CORTE QUE NO ES CORTE    

    public function corte()
    {
        $usuarios = User::where('status',true)
        ->where('webaccess', 1)
        ->where('taquilla', 1)->get();

        return view('commons.corte', ['usuarios' => $usuarios, 'fechas' => [], 'jornadas' => []]);
    }

    public function corte_carga(Request $request)
    {
        $usuarios = User::where('status',true)
        ->where('webaccess', 1)
        ->where('taquilla', 1)->get();

        $idTaquillero = $request['nombreTaquillero'];

        $usuario = User::where('status',true)
        ->where('id', $idTaquillero)->first();

        $nombreUser = $usuario['nombre'];

        $fechas = Tickets::from('tickets as t')
        ->JOIN('tickets_asiento as ta', 't.id', '=', 'ta.tickets_id')
        ->JOIN('asientos as a', 'ta.code', '=', 'a.code')
        ->JOIN('partidos as p', 't.eventos_id', '=', 'p.id')
        ->where([
            ['t.status', '>', 1],
            ['t.payed', '=', 1],
            ['t.users_id', '=', $idTaquillero]
        ])
        ->groupBy('fecha_compra', 'p.id', 'p.titulo', 't.type_reservation', 't.type_payment', 'a.precio')
        ->select(
            'p.id as partido',
            'p.titulo as titulo',
            DB::raw('count(ta.code) AS total'),
            'a.precio',
            DB::Raw("date(t.creation_date) as fecha_compra"),
            DB::Raw(
                "(CASE
                    WHEN t.type_payment = 1 THEN 'Efectivo'
                    WHEN t.type_payment = 2 and t.type_reservation='evento' THEN 'Paypal'
                    WHEN t.type_payment = 2 and t.type_reservation='taquilla' THEN 'Tarjeta'
                    WHEN t.type_payment = 3 THEN 'Cortesia'
                END) as tipo_compra
                "
            ),
            't.type_reservation',
            DB::raw('count(ta.code) * a.precio as total_vendido')
        )->get();

        $jornadas = Tickets::from('tickets as t')
        ->JOIN('tickets_asiento as ta', 't.id', '=', 'ta.tickets_id')
        ->JOIN('asientos as a', 'ta.code', '=', 'a.code')
        ->JOIN('partidos as p', 't.eventos_id', '=', 'p.id')
        ->where([
            ['t.status', '>', 1],
            ['t.payed', '=', 1],
            ['t.users_id', '=', $idTaquillero]
        ])
        ->groupBy('fecha_compra', 'p.id', 'p.titulo', 't.type_reservation', 't.type_payment', 'a.precio')
        ->select(
            'p.id as partido',
            'p.titulo as titulo',
            DB::raw('count(ta.code) AS total'),
            'a.precio',
            DB::Raw("date(t.creation_date) as fecha_compra"),
            DB::Raw(
                "(CASE
                    WHEN t.type_payment = 1 THEN 'Efectivo'
                    WHEN t.type_payment = 2 and t.type_reservation='evento' THEN 'Paypal'
                    WHEN t.type_payment = 2 and t.type_reservation='taquilla' THEN 'Tarjeta'
                    WHEN t.type_payment = 3 THEN 'Cortesia'
                END) as tipo_compra
                "
            ),
            't.type_reservation',
            DB::raw('count(ta.code) * a.precio as total_vendido')
        )->get();

        return view('commons.corte', ['usuarios' => $usuarios, 'fechas' => $fechas, 'jornadas' => $jornadas, 'user' => $nombreUser, 'idUser' => $idTaquillero]);
    }

    public function corte_resultado(Request $request)
    {
        $usuarios = User::where('status',true)
        ->where('webaccess', 1)
        ->where('taquilla', 1)->get();

        $idTaquillero = $request['dato_taquillero'];
        $fechaTaquillero = $request['fecha'];
        $jornadaTaquillero = $request['jornada'];

        $data = Tickets::from('tickets as t')
        ->JOIN('tickets_asiento as ta', 't.id', '=', 'ta.tickets_id')
        ->JOIN('asientos as a', 'ta.code', '=', 'a.code')
        ->JOIN('partidos as p', 't.eventos_id', '=', 'p.id')
        ->where([
            ['t.status', '>', 1],
            ['t.fecha', '=', $fechaTaquillero],
            ['p.titulo', '=', $jornadaTaquillero],
            ['t.payed', '=', 1],
            ['t.users_id', '=', $idTaquillero]
        ])
        ->groupBy('fecha_compra', 'p.id', 'p.titulo', 't.type_reservation', 't.type_payment', 'a.precio')
        ->select(
            'p.id as partido',
            'p.titulo as titulo',
            DB::raw('count(ta.code) AS total'),
            'a.precio',
            DB::Raw("date(t.creation_date) as fecha_compra"),
            DB::Raw(
                "(CASE
                    WHEN t.type_payment = 1 THEN 'Efectivo'
                    WHEN t.type_payment = 2 and t.type_reservation='evento' THEN 'Paypal'
                    WHEN t.type_payment = 2 and t.type_reservation='taquilla' THEN 'Tarjeta'
                    WHEN t.type_payment = 3 THEN 'Cortesia'
                END) as tipo_compra
                "
            ),
            't.type_reservation',
            DB::raw('count(ta.code) * a.precio as total_vendido')
        )->get();

        return view('commons.corte', ['usuarios' => $usuarios, 'fechas' => [], 'jornadas' => [], 'data' => $data]);
    }

    /* 
    * Christoper Patiño
    *
    */
    public function indexIndicadores()
    {
        $partidos = Partidos::where('status','!=',0)->get();

        return view('commons.indicadorHome', ['partidos' => $partidos]);

    }
}


