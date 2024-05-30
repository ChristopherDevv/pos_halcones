<?php

namespace App\Http\Controllers\api;

use App\Models\Aforos;
use App\Models\Asientos;
use App\Models\ConfiguracionBloqueo;
use App\Models\Interfaces\ErroresExceptionEnum;
use App\Models\Interfaces\TipoBloqueoEnum;
use App\Models\Interfaces\EstatusPartidos;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Interfaces\EstatusAsientosEnum;
use Illuminate\Support\Collection;
use App\Models\TicketsAsientos;
use App\Models\Partidos;
use App\Models\AsientoTemporada;
use App\Models\TemporadaPartido;
use App\Models\Interfaces\DisableConfig;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\EnumTypePrecioAsiento;
use App\Http\Controllers\Controller;
use App\Models\Tickets;
use App\Models\Config;
use App\Models\PreciosAsientos;
use App\Models\PrecioAsiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use function PHPUnit\Framework\isNull;


class AsientosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $isInternal = false)
    {
        try {
            $seats = array();
            if ($request->has('zona')) {
                $seats = Asientos::where('zona',$request->get('zona'))->get();
            }else {
                $seats = Asientos::all();
            }
            $resultSet = $seats->groupBy('zona')->map(
                function ($seats, $key) {
                    return [
                        'zona' => $key,
                        'bloqueados' => $seats->where('status','<',1)->count(),
                        'disponibles' =>$seats->where('status','>',0)->count(),
                        'total' =>$seats->count(),
                        'filas' => $seats->groupBy('fila')->map(
                            function ($filas, $key) {
                                return [
                                    'fila' => $key,
                                    'asientos' =>  collect($filas)->map(
                                        function ($asiento){
                                            $asiento->status = 1;
                                            return $asiento;
                                        }
                                    )->values()
                                ];
                            }
                        )->values()
                    ];
                }
            )->values();
            if($isInternal) {
                return $resultSet;
            }else {
                return response()->json($resultSet);
            }
        }catch (\Exception $e){
            $response = new DataResponse('Ha ocurridó un error al realizar la consulta',ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),$e->getMessage());
            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findAsientoBy(Request $request)
    {
        try {
            $data = $request->all();
            $zona = strtoupper($data['zona']);
            $fila = strtoupper($data['fila']);
            $evento = null;
            if ($request->has('evento')) {
                $evento = $data['evento'];
            }
            $tickets = TicketsAsientos::whereIn('status', [
                EstatusAsientosEnum::COMPRADO,
                EstatusAsientosEnum::TAQUILLA,
                EstatusAsientosEnum::RESERVADO,
                EstatusAsientosEnum::TEMPORADA,
                EstatusAsientosEnum::VERIFICADO
            ]);
            if ($tickets->get()->count() <= 0) {
                $asientos = Asientos::where([
                    ['status', '=', EstatusAsientosEnum::DISPONIBLE],
                    ['zona', '=', $zona],
                    ['fila', '=', $fila]
                ]);
                $asientosCollect = $asientos->orderBy('code', 'asc')->get()->sortBy('code', SORT_NATURAL, false)->values();
                $asientos = $asientos->paginate(50);
                $asientos->setCollection($asientosCollect);
                return $asientos;
            } else {
                $ticketsCollect = collect($tickets->get());
                if (!is_null($evento)) {
                    $ticketsCollect = $ticketsCollect->where('eventos_id', $evento);
                }
                if ($ticketsCollect->count() > 0) {
                    $ticketsCollect = $ticketsCollect->unique('code');
                    $codes = $ticketsCollect->map(function ($item, $key) {
                        return $item->code;
                    });
                    $asientos = Asientos::where([
                        ['status', '=', EstatusAsientosEnum::DISPONIBLE],
                        ['zona', '=', $zona],
                        ['fila', '=', $fila]
                    ]);
                    $asientos = $asientos->whereNotIn('code', $codes);
                    $asientosCollect = $asientos->orderBy('code', 'asc')->get()->sortBy('code', SORT_NATURAL, false)->values();
                    $page = $asientos->paginate(50);
                    $page->setCollection($asientosCollect);
                    return $page;
                } else {
                    $asientos = Asientos::where([
                        ['status', '=', EstatusAsientosEnum::DISPONIBLE],
                        ['zona', '=', $zona],
                        ['fila', '=', $fila]
                    ]);
                    $asientosCollect = $asientos->orderBy('code', 'asc')->get()->sortBy('code', SORT_NATURAL, false)->values();
                    $page = $asientos->paginate(50);
                    $page->setCollection($asientosCollect);
                    return $page;
                }
            }
        } catch (\Throwable  $e) {
            $response = new DataResponse($e->getMessage(), 'Error', $e);
            return response()->json($response);
        }
    }

    public function taquillaFindAsientoBy(Request $request)
    {
        try {
            $data = $request->all();
            $zona = strtoupper($data['zona']);
            $fila = strtoupper($data['fila']);
            $evento = null;
            if ($request->has('evento')) {
                $evento = $data['evento'];
            }
            $tickets = TicketsAsientos::whereIn('status', [
                EstatusAsientosEnum::COMPRADO,
                EstatusAsientosEnum::TAQUILLA,
                EstatusAsientosEnum::RESERVADO,
                EstatusAsientosEnum::TEMPORADA,
                EstatusAsientosEnum::VERIFICADO
            ]);
            if ($tickets->get()->count() <= 0) {
                $asientos = Asientos::where([
                    ['status', '>', EstatusAsientosEnum::DESHABILITADO],
                    ['zona', '=', $zona],
                    ['fila', '=', $fila]
                ]);
                $asientosCollect = $asientos->orderBy('code', 'asc')->get()->sortBy('code', SORT_NATURAL, false)->values();
                $asientos = $asientos->paginate(50);
                $asientos->setCollection($asientosCollect);
                return $asientos;
            } else {
                $ticketsCollect = collect($tickets->get());
                if (!is_null($evento)) {
                    $ticketsCollect = $ticketsCollect->where('eventos_id', $evento);
                }
                if ($ticketsCollect->count() > 0) {
                    $ticketsCollect = $ticketsCollect->unique('code');
                    $codes = $ticketsCollect->map(function ($item, $key) {
                        return $item->code;
                    });
                    $asientos = Asientos::where([
                        ['status', '>', EstatusAsientosEnum::DESHABILITADO],
                        ['zona', '=', $zona],
                        ['fila', '=', $fila]
                    ]);
                    $asientos = $asientos->whereNotIn('code', $codes);
                    $asientosCollect = $asientos->orderBy('code', 'asc')->get()->sortBy('code', SORT_NATURAL, false)->values();
                    $page = $asientos->paginate(50);
                    $page->setCollection($asientosCollect);
                    return $page;
                } else {
                    $asientos = Asientos::where([
                        ['status', '>', EstatusAsientosEnum::DESHABILITADO],
                        ['zona', '=', $zona],
                        ['fila', '=', $fila]
                    ]);
                    $asientosCollect = $asientos->orderBy('code', 'asc')->get()->sortBy('code', SORT_NATURAL, false)->values();
                    $page = $asientos->paginate(50);
                    $page->setCollection($asientosCollect);
                    return $page;
                }
            }
        } catch (\Throwable  $e) {
            $response = new DataResponse($e->getMessage(), 'Error', $e);
            return response()->json($response);
        }
    }

    public function reservarBoletos($boletos, $config)
    {
        $config['asientoBoleto'] = $boletos;
        $evento = $config['idEvento'];

        $tickets = TicketsAsientos::where([ ['id', '=', $config['idTicket']] ]);

        $tickets = $tickets->whereIn('status', [
            EstatusAsientosEnum::COMPRADO,
            EstatusAsientosEnum::TAQUILLA,
            EstatusAsientosEnum::RESERVADO,
            EstatusAsientosEnum::TEMPORADA,
            EstatusAsientosEnum::VERIFICADO
        ]);

        if (!is_null($evento))
        {
            $tickets = $tickets->where('eventos_id', $evento);
        }

        $ticketsCollect = collect($tickets->get());

        $codes = $ticketsCollect->map(function ($item, $key)
        {
            return $item->code;
        });

        if (count(array_intersect($codes->toArray(), $boletos)) > 0)
        {
            throw new \Exception('Algunos de tus boletos ya fueron utilizados');
        }
        else
        {
            // Zuriel DA
                $match = Partidos::find($evento);

            switch ($config['tipeReservation'])
            {
                case 'temporada':
                        $asientos  = $this->buildAsientosTemporada($config, EstatusAsientosEnum::TEMPORADA, $match);
                        $ticketsAsiento = TicketsAsientos::insert($asientos);

                        return new DataResponse('Se han reservado los asientos por toda la temporada', 'Reservados', $ticketsAsiento);

                    break;
                case 'evento':
                        if (is_null($config['idEvento']))
                        {
                            throw new \Exception('Partido no seleccionado');
                        }

                        $asientos  = $this->buildAsientos($config, EstatusAsientosEnum::COMPRADO, $match);
                        $ticketsAsiento = TicketsAsientos::insert($asientos);

                        return new DataResponse('Se ha guardado sus asientos', 'Reservados', $ticketsAsiento);

                    break;
                case 'reservation':
                        if ($config['temporada'])
                        {
                            $asientos  = $this->buildAsientosTemporada($config, EstatusAsientosEnum::TEMPORADA, $match);
                            $ticketsAsiento = TicketsAsientos::insert($asientos);
                        }
                        else
                        {
                            $asientos  = $this->buildAsientos($config, EstatusAsientosEnum::COMPRADO, $match);
                            $ticketsAsiento = TicketsAsientos::insert($asientos);
                        }

                        return new DataResponse('Se han reservado los asientos', 'Reservados', $ticketsAsiento);

                    break;
                case 'taquilla':
                        $ticketsAsiento = null;

                        if ($config['temporada'])
                        {
                            $asientos  = $this->buildAsientosTemporada($config, EstatusAsientosEnum::TEMPORADA, $match);
                            $ticketsAsiento = TicketsAsientos::insert($asientos);
                        }
                        else
                        {
                            $asientos  = $this->buildAsientos($config, EstatusAsientosEnum::TAQUILLA, $match);
                            $ticketsAsiento = TicketsAsientos::insert($asientos);
                        }

                        return new DataResponse('Se han reservado los asientos para pagar en taquilla', 'Reservados', $ticketsAsiento);

                    break;
                default:
                    throw new \Exception('Algunos de tus asientos ya fueron utilizados');
                    break;
            }
        }
    }

    private function buildAsientos($ticketData, int $status, $match)
    {
        // ZurielDA
            $asientos = Asientos::with([
                'preciosAsientosActivos' => function($preciosAsientosActivos) use ($match)
                {
                    $preciosAsientosActivos->select(['precios_asientos.id_seat', 'precios_asientos.id_seat_price', 'price'])->where('precios_asientos.id_season' , '=', $match->id_match_season);
                }
            ])->whereIn('code', $ticketData['asientoBoleto']);
        //

        $ticketsAsientos = array();

        foreach ($asientos->get() as $asiento)
        {
            //  ZurielDA
                $idPrice = null;
                $idPriceSubcription = null;

                foreach ($asiento->preciosAsientosActivos as $precio)
                {
                    switch ($precio->pivot->typePrice)
                    {
                        case EnumTypePrecioAsiento::UNICO:
                            $idPrice = $precio->pivot->id;
                            break;

                        case EnumTypePrecioAsiento::ABONO:
                            $idPriceSubcription = $precio->pivot->id;
                            break;

                        default:break;
                    }
                }
            //

            $seat = [
                'tickets_id' => $ticketData['idTicket'],
                'code' => $asiento->code,
                'eventos_id' => $ticketData['idEvento'],
                'zona' => $asiento->zona,
                'fila' => $asiento->fila,
                'status' => $status,
                'id_grupo' => $ticketData['id_grupo'],
                'tipo_grupo' => $ticketData['tipo_grupo'],
                // ZurielDA
                    'id_seat_price' => $idPrice ? $idPrice : null,
                    'id_seat_price_subcription' => $idPriceSubcription ? $idPriceSubcription : null
                //
            ];

            array_push($ticketsAsientos, $seat);
        }
        return $ticketsAsientos;
    }

    private function buildAsientosTemporada($ticketData, int $status, $match)
    {
        // ZurielDA
            $asientos = Asientos::with([
                'preciosAsientosActivos' => function($preciosAsientosActivos) use ($match)
                {
                    $preciosAsientosActivos->select(['precios_asientos.id_seat', 'precios_asientos.id_seat_price', 'price'])->where('precios_asientos.id_season' , '=', $match->id_match_season);
                }
            ])->whereIn('code', $ticketData['asientoBoleto']);
        //

        $partidos = Partidos::whereIn('status', [1, 2]);

        $ticketsAsientos = array();

        foreach ($partidos->get() as $partido) {

            foreach ($asientos->get() as $asiento) {

                //  ZurielDA
                    $idPrice = null;
                    $idPriceSubcription = null;

                    foreach ($asiento->preciosAsientosActivos as $precio)
                    {
                        switch ($precio->pivot->typePrice)
                        {
                            case EnumTypePrecioAsiento::UNICO:
                                $idPrice = $precio->pivot->id;
                                break;

                            case EnumTypePrecioAsiento::ABONO:
                                $idPriceSubcription = $precio->pivot->id;
                                break;

                            default:break;
                        }
                    }
                //

                $seat = [
                    'tickets_id' => $ticketData['idTicket'],
                    'code' => $asiento->code,
                    'zona' => $asiento->zona,
                    'fila' => $asiento->fila,
                    'eventos_id' => $partido->id,
                    'status' => EstatusAsientosEnum::TEMPORADA,
                    // ZurielDA
                        'id_seat_price' => $idPrice ?  $idPrice : null ,
                        'id_seat_price_subcription' => $idPriceSubcription ? $idPriceSubcription : null
                    //
                ];

                array_push($ticketsAsientos, $seat);

            }

        }

        return $ticketsAsientos;
    }

    public function disableSeats($idAforo)
    {
        try {
            DB::beginTransaction();
            $aforo = Aforos::where([
                ['id','=',$idAforo],
                ['status','=',1]
            ])->with('distribucionInf')->firstOrFail();
            $configBloqueo = $aforo->configs;
            $distribucionAforo = $aforo->distribucionInf;

           if($configBloqueo) {
               foreach ($configBloqueo as $bloqueo){
                   switch ($bloqueo->tipoBloqueo){
                       case TipoBloqueoEnum::BLOQUEO_ZONA:
                           $resultSet = Asientos::whereIn('zona',$bloqueo->excludeZonas)->update([
                               'status'=> 0
                           ]);
                           if(!$resultSet) {
                               throw new \Exception('No se pudo realizar el proceso Zona');
                           }
                           break;
                       case TipoBloqueoEnum::BLOQUEO_FILA:
                           $resultSet = Asientos::where('zona',$bloqueo->zona)->whereIn(
                               'fila',$bloqueo->excludesFilas
                           )->update([
                               'status'=> 0
                           ]);
                           if(!$resultSet) {
                               throw new \Exception('No se pudo realizar el proceso Fila');
                           }
                           break;
                       case TipoBloqueoEnum::BLOQUEO_SECCION:
                           $resultSet = 0;
                           foreach ($bloqueo->excludeSecciones as $seccion){
                               $resultSet = Asientos::where([
                                   ['zona','=',$bloqueo->zona],
                                   ['fila','=',$seccion->fila],
                                   ['section_seat','=',$seccion->name]
                               ])->update([
                                   'status' => 0
                               ]);
                           }
                           if(!$resultSet) {
                               throw new \Exception('No se pudo realizar el proceso seccioones');
                           }
                           break;
                       case TipoBloqueoEnum::BLOQUEO_ASIENTOS:
                           $resultSet = Asientos::whereIn('code',$bloqueo->exludes)->update(
                               ['status' => 0]
                           );
                           if(!$resultSet) {
                               throw new \Exception('No se pudo realizar el proceso asientos');
                           }
                           break;
                       default:
                           throw new \Exception('No se encontro configuración');
                           break;
                   }
               }
           }

           $this->updateAsientosWithDistibucion($distribucionAforo, $aforo->aforo, $configBloqueo);
            DB::commit();
            $headers = ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE();
            $response = new DataResponse($headers->getMessage(),$headers->getCode(),$idAforo);
            return response()->json($response, Response::HTTP_OK);
        }catch (\Exception $exception){
            DB::rollBack();
            $headers = ErroresExceptionEnum::ERROR_PROCESS_UPDATE();
            $response = new DataResponse($exception->getMessage(),$headers->getCode(),$exception->getTrace());
            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function disable($asientosCollect)
    {
        $asientosCollect->update([
            'status' => EstatusAsientosEnum::DESHABILITADO
        ]);
        $response = new DataResponse('Asientos desahabilitados', 'Desahabilitado', $asientosCollect);
        return response()->json($response);
    }

    private function getEstatusAsiento($asientosCollect)
    {
        $seats = $asientosCollect->get()->map(function ($item, $key) {
            return $item->code;
        });
        $seatExits = TicketsAsientos::whereNotIn(
            'status',
            [
                EstatusAsientosEnum::DISPONIBLE,
                EstatusAsientosEnum::TAQUILLA,
                EstatusAsientosEnum::TEMPORADA
            ]
        )->whereIn('code', $seats)->exists();
        return $seatExits > 0 ?  true :  false;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return Asientos::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Asientos::where('id', $id)->where('status', true)->get();
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
        return Asientos::where('id', $id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Asientos::where('id', $id)->update([
            'status' => EstatusAsientosEnum::DESHABILITADO
        ]);
    }

    public function countSeats()
    {
        try {
            $id =  request(['id']);
            $partido = Partidos::where('id',$id)->first();
            if ($partido->exists()) {
                $ticketsIndicatorsVerify = Tickets::where([
                    ['eventos_id', '=', $id],
                    ['status', '>', 0],
                    ['payed', '=', true]
                ]);
                $countSeatsVerify = collect($ticketsIndicatorsVerify->get())->map(
                    function ($seat, $key) {
                        return collect($seat->asientos);
                    }
                )->flatten();
                $codes = $countSeatsVerify->pluck('code');
                $asientos = collect(Asientos::all());
                $totalGlobal =collect(Asientos::all())->count();
                $toltalAsientosDisponibles = $asientos->where('status','>',0)->count();
                $asientosParaVender = $asientos->where('status',1)->whereNotIn('code',$codes)->count();

                $asientos = $asientos->where('status',1)->whereNotIn('code',$codes)->groupBy('precio')->map(
                    function($precio,$key) {
                        return [
                            'precio' => $key,
                            'total' => count($precio)
                        ];
                    }
                )->values();

                //->where('status',EstatusAsientosEnum::VERIFICADO)
                $totalVerificados = $countSeatsVerify->where('status',EstatusAsientosEnum::VERIFICADO)->count();

                $salesByPrice = $countSeatsVerify->groupBy('precio.precio')->map(
                    function($precio,$key) {
                        return [
                            'precio' => $key,
                            'total' => count($precio)
                        ];
                    }
                )->values();
                $salesByZona = $countSeatsVerify->groupBy('zona')->map(
                    function($zona,$key) {
                        return [
                            'zona' => $key,
                            'verificados' => count($zona)
                        ];
                    }
                )->values();
                $ticketSVentas = Tickets::from('tickets as t')
                ->JOIN('tickets_asiento as ta','t.id','=','ta.tickets_id')
                ->JOIN('asientos as a','ta.code','=','a.code')
                ->JOIN('partidos as p','t.eventos_id','=','p.id')
                ->where([
                        ['t.eventos_id', '=', $id],
                        ['t.status', '>', 0],
                        ['t.payed', '=', 1]
                ])
                ->groupBy('fecha_compra','p.id','p.titulo','t.type_reservation','t.type_payment','a.precio')
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
                            WHEN t.type_payment = 2 and t.type_reservation in ('taquilla','reservation')  THEN 'Tarjeta'
                            WHEN t.type_payment = 3 THEN 'Cortesia'
                        END) as tipo_compra
                        "
                    ),
                    't.type_reservation',
                    DB::raw('count(ta.code) * a.precio as total_vendido')
                );

                $ticketSVentasCollect  =  collect($ticketSVentas->get());
                $totalesVentas = [
                    'totalGeneral' => $ticketSVentasCollect->sum('total'),
                    'totalVendidoGeneral' => $ticketSVentasCollect->sum('total_vendido'),
                    'web' => $ticketSVentasCollect->where('type_reservation','evento')->sum('total'),
                    'ventaTotalWeb' => $ticketSVentasCollect->where('type_reservation','evento')->sum('total_vendido'),
                    'webPrecios' => $ticketSVentasCollect->where('type_reservation','evento')->groupBy('precio')->map(
                        function($precios,$key) {
                           return [
                               'precio' => $key,
                               'total' => collect($precios)->sum('total')
                           ];
                        }
                    )->values(),
                    'totalTaquilla' => $ticketSVentasCollect->whereIn('type_reservation',['taquilla','reservation'])->sum('total'),
                    'ventaTotalTaquilla' => $ticketSVentasCollect->whereIn('type_reservation',['taquilla','reservation'])->sum('total_vendido'),
                    'taquillaPrecios' => $ticketSVentasCollect->whereIn('type_reservation',['taquilla','reservation'])->groupBy('precio')->map(
                        function($precios,$key) {
                           return [
                               'precio' => $key,
                               'total' => collect($precios)->sum('total')
                           ];
                        }
                    )->values(),
                    'ventaTotalTaquillaTarjeta'=> $ticketSVentasCollect->whereIn('type_reservation',['taquilla','reservation'])->where('tipo_compra','Tarjeta')->sum('total_vendido'),
                    'taquillaTarjeta' => $ticketSVentasCollect->whereIn('type_reservation',['taquilla','reservation'])->where('tipo_compra','Tarjeta')->sum('total'),
                    'taquillaTarjetaPrecios' => $ticketSVentasCollect->whereIn('type_reservation',['taquilla','reservation'])->where('tipo_compra','Tarjeta')->groupBy('precio')->map(
                        function($precios,$key) {
                            return [
                                'precio' => $key,
                                'total' => collect($precios)->sum('total')
                            ];
                        }
                    )->values(),
                    'taquillaEfectivo' => $ticketSVentasCollect->whereIn('type_reservation',['taquilla','reservation'])->where('tipo_compra','Efectivo')->sum('total'),
                    'ventaTotalTaquillaEfectivo' => $ticketSVentasCollect->whereIn('type_reservation',['taquilla','reservation'])->where('tipo_compra','Efectivo')->sum('total_vendido'),
                    'taquillaEfectivoPrecios' => $ticketSVentasCollect->whereIn('type_reservation',['taquilla','reservation'])->where('tipo_compra','Efectivo')->groupBy('precio')->map(
                        function($precios,$key) {
                            return [
                                'precio' => $key,
                                'total' => collect($precios)->sum('total')
                            ];                        }
                    )->values(),
                    'taquillaCortesia' => $ticketSVentasCollect->whereIn('type_reservation',['taquilla','reservation'])->where('tipo_compra','Cortesia')->sum('total'),
                    'ventaTotalTaquillaCortesia' => $ticketSVentasCollect->whereIn('type_reservation',['taquilla','reservation'])->where('tipo_compra','Cortesia')->sum('total_vendido'),
                    'taquillaCortesiaPrecios' => $ticketSVentasCollect->whereIn('type_reservation',['taquilla','reservation'])->where('tipo_compra','Cortesia')->groupBy('precio')->map(
                        function($precios,$key) {
                            return [
                                'precio' => $key,
                                'total' => collect($precios)->sum('total')
                            ];                        }
                    )->values(),
                ];
                $ticketsVentasHistorial = $ticketSVentasCollect->sortByDesc('fecha_compra')->groupBy('fecha_compra')->map(
                    function ($fechas,$keyFecha){
                        return [
                            'fecha' => $keyFecha,
                            'total' => collect($fechas)->sum('total'),
                            'totalVendido' => collect($fechas)->sum('total_vendido')
                        ];
                    }
                )->values();
                $ticketSVentasCollect = $ticketSVentasCollect->groupBy(['type_reservation','tipo_compra']);
                $asientosCollect = collect(Asientos::all());
                $disponiblesOnline = $asientosCollect->where('status', '=', 1)->count();
                $abiertosParaTaquilla = $asientosCollect->where('status', '=', 2)->count();
                $bloqueados = $asientosCollect->where('status', '=', 0)->count();
                $totalGloblal = DB::table('asientos')->select(DB::Raw('COUNT(*) as total'))->first()->total;
                return [
                    'partido' => $partido,
                    'asientosPorVender' => $asientosParaVender,
                    'totalAsientosDisponibles' => $toltalAsientosDisponibles,
                    'totalGlobal' => $totalGlobal,
                    'asientosDisponiblesOnline' => $disponiblesOnline,
                    'asientosDisponiblesTaquilla' => $abiertosParaTaquilla,
                    'porVender' => $asientos,
                    'totalVerificados' => $totalVerificados,
                    'ventasPorZona' => $salesByZona,
                    'ventasPorPrecio' => $salesByPrice,
                    'bloqueados' => $bloqueados,
                    'totalGeneral' => $totalGloblal,
                    'totalVentas' => $totalesVentas,
                    'ventas' => $ticketSVentasCollect,
                    'ventasHistorial' => $ticketsVentasHistorial
                ];
                }
        } catch (\Exception $e) {
            $response = new DataResponse('Ha ocurrido un error', 'Error'.$e->getMessage(), $e->getTrace());
            return response()->json($response,505);
        }
    }

    // Ambos metodos se encuentran hasta abajo con modificaciones quitarlos cuando las pruebas sean exitosas y se encuentren en produccion

    // public function getAviableSeat(Request $request)
    // {
    //     try {
    //         $evento = request(['idPartido']);
    //         $asientos = collect(Asientos::all());
    //         if(!$request->has('idPartido')){
    //             $seats = $asientos->groupBy('zona')->map(
    //                 function ($zonas,$keyZona){
    //                     return [
    //                         'zona' => $keyZona,
    //                         'filas' => collect($zonas)->groupBy('fila')->map(
    //                             function ($filas,$keyFila){
    //                                                                     return [
    //                                                                         'fila' => $keyFila,
    //                                     'count' => count(collect($filas)->where('status','>', 0)),
    //                                     'asientos' => collect($filas)
    //                                                                     ];
    //         }
    //                         )->values()
    //                     ];
    //                 }
    //             )->values();
    //             return $seats;
    //         }
    //         $tickets = TicketsAsientos::where('eventos_id', $evento);
    //         $tickets = collect($tickets->get())->whereIn('status', [
    //             EstatusAsientosEnum::COMPRADO,
    //             EstatusAsientosEnum::TAQUILLA,
    //             EstatusAsientosEnum::RESERVADO,
    //             EstatusAsientosEnum::TEMPORADA,
    //             EstatusAsientosEnum::VERIFICADO
    //         ]);
    //         $buyedCodes =  $tickets->map(function ($item, $key) {
    //             return $item->code;
    //         });

    //         if($request->has('zona')) {
    //             $allSeatsCollect = $asientos;
    //             if($request->has('zona')) {
    //                 $seats = $asientos->where('zona','=',$request->all()['zona']);
    //                 }
    //             $seats = $seats->groupBy('zona')->map(
    //                 function ($zonas, $keyZona) use ($tickets, $allSeatsCollect,$buyedCodes) {
    //                     $filas = collect($zonas)->groupBy('fila')->map(
    //                         function ($fila, $keyFila) use ($tickets, $allSeatsCollect, $keyZona,$buyedCodes) {
    //                         $s = collect();
    //                             $disponibles = collect($fila)->where('status','=',1)->whereNotIn('code',$buyedCodes)->map(function ($f) {return ['code' => $f->code, 'precio' => $f->precio, 'section_seat' => $f->section_seat];});
    //                         //esto consulta lo disponible
    //                             $countDisponibles = $this->buildStatuSeat($disponibles,1,$keyFila);
    //                             $s = $s->merge($countDisponibles);
    //                         //significa comprados
    //                             $codes = collect($tickets)->where('zona','=',$keyZona)->where('fila','=',$keyFila)->map(function ($f) {return ['code' => $f->code, 'precio' => $f->precio, 'section_seat' => $f->precio->section_seat]; })->values();
    //                             $constCodes = $this->buildStatuSeat($codes,2, $keyFila);
    //                             $s = $s->merge($constCodes);
    //                         //consulta los reservados
    //                             $reservados = collect($fila)->where('status','>',2)->map(function ($f) {return ['code' => $f->code, 'precio' => $f->precio, 'section_seat' => $f->section_seat];});
    //                             $countRervados = $this->buildStatuSeat($reservados,5,$keyFila);
    //                             $s = $s->merge($countRervados);
    //                         //consulta los bloqueados
    //                             $bloqueados = $allSeatsCollect->where('status', '=', 0)->where('zona', '=', $keyZona)->where('fila', '=', $keyFila)->map(function ($f) {return ['code' => $f->code, 'precio' => $f->precio, 'section_seat' => $f->section_seat]; })->values();
    //                             $countBloquados = $this->buildStatuSeat($bloqueados,3, $keyFila);
    //                             $s =$s->merge($countBloquados);
    //                         return [
    //                                 'fila' => $keyFila,
    //                                 'count' => collect($fila)->where('status','>',0)->count(),
    //                                 'asientos' => $s->unique('code')->sortBy('code', SORT_NATURAL, false)->values()
    //                         ];
    //                         }
    //                     );
    //                     return [
    //                         'zona' => $keyZona,
    //                         'filas' => $filas->values()
    //                     ];
    //                 }
    //             );
    //             return response()->json($seats->values());
    //         }else {
    //             $seats = $asientos->whereNotIn('code', $buyedCodes)->where('status','>',0)->groupBy('zona')->map(
    //                 function($zonas,$keyZona){
    //                     return [
    //                         'zona' => $keyZona,
    //                         'filas' => collect($zonas)->where('status','>',0)->groupBy('fila')->map(
    //                             function ($filas,$keyFila){
    //                                 return $keyFila;
    //         }
    //                         )->values()
    //                     ];
    //                 }
    //             );
    //             return response()->json($seats->values());
    //         }
    //     } catch (\Exception $e) {
    //         $response = new DataResponse('Ha ocurrido un error', 'Error', $e->getTrace());
    //         return response()->json($response);
    //     }
    // }

    // public function buildStatuSeat($codes, $status, $fila)
    // {
    //     $resultSet = array();
    //     foreach ($codes as $c) {
    //         array_push($resultSet, [
    //             'code' => $c['code'],
    //             'status' => $status,
    //             'fila' => $fila,
    //             'precio' => $c['precio'],
    //             'seccion' => $c['section_seat']
    //         ]);
    //     }
    //     return $resultSet;
    // }

    public function getAviableSeatForUser()
    {
        try {
            $evento = request(['idPartido']);
            $tickets = TicketsAsientos::where('eventos_id', $evento);
            $tickets = $tickets->get()->whereIn('status', [
                EstatusAsientosEnum::COMPRADO,
                EstatusAsientosEnum::TAQUILLA,
                EstatusAsientosEnum::RESERVADO,
                EstatusAsientosEnum::TEMPORADA,
                EstatusAsientosEnum::VERIFICADO
            ]);
            $codes = $tickets->map(function ($item, $key) {
                return $item->code;
            });
            $seats = collect(Asientos::where('status', '=', 1)->get())->whereNotIn('code', $codes);
            $seats = $seats->groupBy('zona')->map(
                function ($zonas, $keyZona) {
                    $filas = collect($zonas)->groupBy('fila')->map(
                        function ($fila, $keyFila) use ($keyZona) {
                            return [
                                'fila' => $keyFila,
                                'count' => collect($fila)->count()
                            ];
                        }
                    );
                    return [
                        'zona' => $keyZona,
                        'filas' => $filas->values()
                    ];
                }
            );
            return response()->json($seats->values());
        } catch (\Exception $e) {
            $response = new DataResponse('Ha ocurrido un error', 'Error', $e->getTrace());
            return response()->json($response);
        }
    }

    public function getPricesSeat() {
        try{
            $asientos = Asientos::where('status','>',0)->select(
                'type','zona','precio',DB::raw('count(*) as total')
            )->groupBy(
                'type','zona','precio'
            )->get();
            $asientos = collect($asientos)->groupBy('precio')->map(
                function ($asientos,$key){
                    return [
                        'precio' => $key,
                        'asientos' => $asientos
                    ];
                }
            )->values();
            return $asientos;
        }catch (\Exception $e){
            $response = new DataResponse('Ha ocurrido un error '.$e->getMessage(), 'Error', $e->getTrace());
            return response()->json($response, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function info() {
        try {
          $asientos = Asientos::all();
          $asientos = collect($asientos)->groupBy('zona')->map(
              function ($zonasFilas,$keyZona) use ($asientos) {
                return [
                    'zona' => $keyZona,
                    'totalFilas' => collect($zonasFilas)->sortBy('fila')->duplicates('fila')->values()->unique()->count(),
                    'total' => collect($zonasFilas)->count(),
                    'filaConfig' => [
                        'start' => collect($zonasFilas)->sortBy('fila')->duplicates('fila')->values()->unique()->first(),
                        'end' => collect($zonasFilas)->sortBy('fila')->duplicates('fila')->values()->unique()->last(),
                    ],
                    'filas' => collect($zonasFilas)->groupBy('fila')->map(
                        function ($filas,$key){
                            return [
                                'fila' => $key,
                                'limit' => collect($filas)->count(),
                                'secciones' => collect($filas)->groupBy('section_seat')->map(
                                    function ($secciones,$keySeccion ){
                                         $data = collect($secciones)->sortBy('code',SORT_NATURAL)->transform(
                                            function ($item) {
                                                return (int) substr($item->code,2);
                                            }
                                        );
                                        $start = $data->first();
                                        $end = $data->last();
                                        return [
                                            'start' => $start,
                                            'end' => $end,
                                            'name' =>  $keySeccion
                                        ];
                                    }
                                )->values()
                            ];
                        }
                    )->values()
                ];
              }
          )->values();
          return $asientos;
        }catch (\Exception $e) {
        }
    }

    public function updateAsientosWithDistibucion($distribucion, $aforo, $configBloqueo)
    {
        try {
            $porcentajeDistribucio = ($aforo / 100);
            $zonas = $this->index(\request(), true);
            $zonasInf = $this->info();
            foreach ($zonas as $zona) {
                $totalPermitidos = intval(($zona['total'] * $porcentajeDistribucio));
                $aplicados = collect([0]);
                $totalAplicacdo = 0;
                collect($zona['filas'])->each(
                    function ($fila) use ($aplicados, $totalPermitidos, $distribucion, $totalAplicacdo, $zona, $configBloqueo) {
                        $totalAplicacdo = $aplicados->sum();
                        $config = collect($configBloqueo)->where('zona','=',$zona['zona'])->where('fila','=',$fila['fila'])->count();
                        if(collect($configBloqueo)->contains('zona',$zona['zona'])) {
                            if ($totalAplicacdo <= $totalPermitidos) {

                                $asientosFila = $fila['asientos'];
                                collect($asientosFila)->sortBy('code', SORT_NATURAL, false)->chunk($distribucion['rango'])->each(
                                    function ($asientos) use ($aplicados, $distribucion) {
                                        $aientosSelected = $asientos;
                                        if (count($aientosSelected) >= $distribucion['rango']) {
                                            $permitidos = $aientosSelected->take($distribucion->permitidos);
                                            $aientosSelected = $aientosSelected->slice($distribucion->permitidos);
                                            $aplicados->push($distribucion->permitidos);
                                            $this->setConfig($permitidos);
                                            $bloqueados = $aientosSelected->take($distribucion->bloqueados);
                                            $this->setConfig($bloqueados, true);
                                        } else {
                                            $this->resolvedSobrantes($aientosSelected, $aplicados);
                                        }
                                    }
                                );
                            }
                        }
                    });
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    public function resolvedSobrantes($sobrantes, $aplicados) {
        $total = $sobrantes->count();
        switch ($total){
            case 1:
                $this->setConfig($sobrantes);
                $aplicados->push(1);
                break;
            case 2:
                $aplicar = $sobrantes->slice(1);
                $aplicados->push(1);
                $this->setConfig($aplicar);
                break;
            case 3:
                $aplicar = $sobrantes->slice(1);
                $aplicar = $aplicar->take(2);
                $aplicados->push(2);
                $this->setConfig($aplicar);
                break;
            case 4:
                $aplicar = $sobrantes->slice(1);
                $aplicar = $aplicar->skip(1);
                echo json_encode($aplicar);
                break;
            case 5:
                $aplicar = $sobrantes->slice(1);
                break;
        }
    }

    private function setConfig($asientos,$isBloqueo = false) {
        $codes = collect($asientos)->map(
            function ($asiento) {
                return $asiento->code;
            }
        )->values();
        $resultSet =Asientos::whereIn('code',$codes)->update(
          [
              'status' =>  $isBloqueo ? 0 : 1
          ]
        );
    }


    /**
     *
     *
     *  ZurielDA
     *
     *
     */

        public function showAllSeat($idTemporada)
        {
            try
            {

                $asientos = Asientos::with([
                    'preciosAsientosActivos' => function($preciosAsientosActivos) use ($idTemporada)
                    {
                        $preciosAsientosActivos->select(['precios_asientos.id_seat', 'precios_asientos.id_seat_price', 'price'])->where('precios_asientos.id_season' , '=', $idTemporada);

                    },
                    'asientoTemporada' => function($asientoTemporada) use ($idTemporada)
                    {
                        $asientoTemporada->where('id_season', '=', $idTemporada);
                    }
                ])->get();

                foreach ($asientos as $asiento)
                {
                    // Se actualiza el status del asiento por la status de la temporada correspondiente
                    $asiento->setAttribute('status', $asiento->asientoTemporada->first()->status);

                    // Se actualiza el precio del asiento por el precio de la temporada correspondiente
                    $asiento->setAttribute('precio', $asiento->preciosAsientosActivos->where('pivot.id_season', '=', $idTemporada)->where('pivot.typePrice', '=', EnumTypePrecioAsiento::UNICO)->first()->price);

                    // Se actualiza el precio_abono del asiento por el precio_abono de la temporada correspondiente
                    $asiento->setAttribute('precio_abono', $asiento->preciosAsientosActivos->where('pivot.id_season', '=', $idTemporada)->where('pivot.typePrice', '=', EnumTypePrecioAsiento::ABONO)->first()->price);

                    foreach ($asiento->preciosAsientosActivos as $precioAsientoActivo)
                    {
                        $precioAsientoActivo->makeHidden(['id_seat', 'id_seat_price']);
                    }
                }

                $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$asientos);

                return response()->json($response);

            }
            catch (\Exception $e)
            {
                $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),null);

                return response()->json($response);
            }

        }

        public function updateSeatPrice(Request $request)
        {
            try
            {
                $newPrice = $request->get("newPrice");
                $config = $request->get("config");
                $id_seat_price = $request->get("id_seat_price");
                $modifyPrice = $request->get("modifyPrice");
                $modifyPriceSubscription = $request->get("modifyPriceSubscription");
                $id_season = $request->get("id_season");

                $seats = Asientos::with('preciosAsientosActivos');

                foreach ($config as $key => $value)
                {
                    if ( Str::contains($value[0], 'code') && Arr::accessible($value[2]))
                    {
                        $seats->whereIn( $value[0], $value[2] );
                    }
                    else
                    {
                        $seats->where($value[0],$value[1],$value[2]);
                    }
                }

                $seatsTemp = $seats->get();

                foreach ($seatsTemp as $seat)
                {
                    if ($modifyPrice)
                    {
                        if ($newPrice)
                        {
                            if ($id_season)
                            {
                                PreciosAsientos::where([ ['id_seat','=',$seat-> id], ['status','=', 'Activo'], ['typePrice','=',EnumTypePrecioAsiento::UNICO], [ 'id_season','=', $id_season ] ])->update(['status' => 'Inactivo']);
                            }
                            else
                            {
                                PreciosAsientos::where([ ['id_seat','=',$seat-> id], ['status','=', 'Activo'], ['typePrice','=',EnumTypePrecioAsiento::UNICO], ['id_season', '=', null] ])->update(['status' => 'Inactivo']);
                            }

                            $newPreciosAsientos = new PreciosAsientos;

                            $newPreciosAsientos-> id_seat = $seat-> id;
                            $newPreciosAsientos-> id_seat_price = $id_seat_price;
                            $newPreciosAsientos-> id_season = $id_season;
                            $newPreciosAsientos-> typePrice = EnumTypePrecioAsiento::UNICO;

                            $newPreciosAsientos-> save();
                        }
                        else
                        {
                                if ($id_season)
                                {
                                    PreciosAsientos::where([ ['id_seat','=',$seat-> id], ['status','=', 'Activo'], ['typePrice','=',EnumTypePrecioAsiento::UNICO], [ 'id_season','=', $id_season ] ])->update(['id_seat_price' => $id_seat_price]);
                                }
                                else
                                {
                                    PreciosAsientos::where([ ['id_seat','=',$seat-> id], ['status','=', 'Activo'], ['typePrice','=',EnumTypePrecioAsiento::UNICO], ['id_season', '=', null] ])->update(['id_seat_price' => $id_seat_price]);
                                }
                        }

                        // Antes de la adición de temporadas en los precios
                            // Asientos::where('id','=', $seat->id )->update(['precio' => PrecioAsiento::find($id_seat_price)->price ]);
                    }

                    if ($modifyPriceSubscription)
                    {
                        if ($newPrice)
                        {
                            if ($id_season)
                            {
                                PreciosAsientos::where([ ['id_seat','=',$seat-> id], ['status','=', 'Activo'], ['typePrice','=',EnumTypePrecioAsiento::ABONO], [ 'id_season','=', $id_season ] ])->update(['status' => 'Inactivo']);

                            }
                            else
                            {
                                PreciosAsientos::where([ ['id_seat','=',$seat-> id], ['status','=', 'Activo'], ['typePrice','=',EnumTypePrecioAsiento::ABONO], ['id_season', '=', null] ])->update(['status' => 'Inactivo']);
                            }

                            $preciosAsientos = new PreciosAsientos;

                            $preciosAsientos-> id_seat = $seat-> id;
                            $preciosAsientos-> id_seat_price = $id_seat_price;
                            $preciosAsientos-> id_season = $id_season;
                            $preciosAsientos-> typePrice = EnumTypePrecioAsiento::ABONO;

                            $preciosAsientos-> save();
                        }
                        else
                        {
                            if ($id_season)
                            {
                                PreciosAsientos::where([ ['id_seat','=',$seat-> id], ['status','=', 'Activo'], ['typePrice','=',EnumTypePrecioAsiento::ABONO], [ 'id_season','=', $id_season ] ])->update(['id_seat_price' => $id_seat_price]);
                            }
                            else
                            {
                                PreciosAsientos::where([ ['id_seat','=',$seat-> id], ['status','=', 'Activo'], ['typePrice','=',EnumTypePrecioAsiento::ABONO], ['id_season', '=', null] ])->update(['id_seat_price' => $id_seat_price]);
                            }
                        }
                    };

                }

                $seatsAux = Asientos::with('preciosAsientosActivos');

                foreach ($config as $key => $value)
                {
                    if ( Str::contains($value[0], 'code') && Arr::accessible($value[2]))
                    {
                        $seatsAux->whereIn( $value[0], $value[2] );
                    }
                    else
                    {
                        $seatsAux->where($value[0],$value[1],$value[2]);
                    }
                }

                $seatsAux = $seatsAux->get();

                $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(), $seatsAux );

                return response()->json($response);

            }
            catch (\Exception $e)
            {
                $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),null);

                return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);

            }
        }

        public function reserveSubscriptionsSeats($boletos, $idTicket, $idEvent, $idGroup, $typeGroup)
        {
            try {

                $tickets = TicketsAsientos::where('eventos_id', '=', $idEvent)
                                            ->whereIn('code', $boletos)
                                            ->whereIn('status', [ EstatusAsientosEnum::COMPRADO, EstatusAsientosEnum::TAQUILLA, EstatusAsientosEnum::RESERVADO, EstatusAsientosEnum::TEMPORADA, EstatusAsientosEnum::VERIFICADO ])->get();

                if ( count( array_intersect( $tickets->pluck('code')->toArray() , $boletos ) ) )
                {
                    return response()->json(new DataResponse(ErroresExceptionEnum::OBJECT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_FOUND()->getCode(), "Boletos" ));
                }
                else
                {
                        $matchSeason = Partidos::find($idEvent);

                        $seats = Asientos::with([
                            'preciosAsientosActivos' => function($preciosAsientosActivos) use ($matchSeason)
                            {
                                $preciosAsientosActivos->select(['precios_asientos.id_seat', 'precios_asientos.id_seat_price', 'price'])->where('precios_asientos.id_season' , '=', $matchSeason->id_match_season);
                            },
                            'asientoTemporada' => function($asientoTemporada) use ($matchSeason)
                            {
                                $asientoTemporada->where('id_season', '=', $matchSeason->id_match_season);
                            }
                        ])->whereIn('code', $boletos)->get()->filter(function ($value, $key)
                        {
                            return  $value->asientoTemporada->first()->status != EstatusAsientosEnum::DESHABILITADO;
                        });

                    if ($seats->count())
                    {
                        $ticketsSeats = [];

                        foreach ($seats as $key => $seat)
                        {
                            $idPrice = null;
                            $idPriceSubcription = null;

                            foreach ($seat->preciosAsientosActivos as $precio)
                            {
                                switch ($precio->pivot->typePrice)
                                {
                                    case EnumTypePrecioAsiento::UNICO:
                                        $idPrice = $precio->pivot->id;
                                        break;

                                    case EnumTypePrecioAsiento::ABONO:
                                        $idPriceSubcription = $precio->pivot->id;
                                        break;

                                    default:
                                    break;
                                }
                            }

                            $ticketsSeats = Arr::prepend( $ticketsSeats, [ 'tickets_id' => $idTicket, 'code' => $seat->code, 'zona' => $seat->zona, 'fila' => $seat->fila,
                                                          'eventos_id' => $idEvent, 'status' => EstatusAsientosEnum::TEMPORADA, 'id_grupo' => $idGroup, 'tipo_grupo' => $typeGroup,
                                                          'id_seat_price' => $idPrice, 'id_seat_price_subcription' => $idPriceSubcription ] );
                        }

                        TicketsAsientos::insert($ticketsSeats);

                            $responseUpdateStatusSeatSeason = $this->updateStatusSeatSeason( (new Request)->merge(['seatsCodes' => $seats->pluck('code'), 'matchSeason' => $matchSeason->id_match_season, 'status' => EstatusAsientosEnum::DESHABILITADO]) )->getData(true);

                            if ( !Str::contains($responseUpdateStatusSeatSeason["status"], ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode() ) )
                            {
                                return response()->json($responseUpdateStatusSeatSeason);
                            }

                        return response()->json(new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(), "Asientos" ));

                    }
                    else
                    {
                        return response()->json(new DataResponse(ErroresExceptionEnum::OBJECT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_FOUND()->getCode(), "Asientos" ));
                    }
                }
            }
            catch (\Throwable $th)
            {
                return response()->json(new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(), "Asientos" ));
            }
        }

        public function getAviableSeat(Request $request)
        {
            try {

                $idPartido = $request->input('idPartido');
                $zona = $request->input('zona');

                // Obtiene la sona, la fila y sus asientos con su estatus, solo cuando no se reciba ningun parametro. Se tiene que modificar porque el status ya no depende de la tabla asientos, sino de la tabla asientos_temporada.
                if(!$idPartido && !$zona)
                {
                    // ZurieDA
                        return Asientos::select('code','status','zona', 'fila')->get()->groupBy('zona')->map( function ($zonas,$keyZona)
                        {
                            return [
                                'zona' => $keyZona,
                                'filas' => $zonas->groupBy('fila')->map( function ($filas,$keyFila)
                                                                        {
                                                                            return [
                                                                                'fila' => $keyFila,
                                                                                'count' => $filas->where('status','>', 0)->count(),
                                                                                'asientos' => $filas
                                                                            ];
                                                                        })->values()
                            ];
                        })->values();
                }

                // Se obtiene zonas y filas solo cuando el unico parametro recibe sea idPartido.
                if ($idPartido && !$zona)
                {
                    $tickets = TicketsAsientos::select('code')->where('eventos_id', $idPartido)->whereIn('status', [ EstatusAsientosEnum::COMPRADO, EstatusAsientosEnum::TAQUILLA, EstatusAsientosEnum::RESERVADO,EstatusAsientosEnum::TEMPORADA, EstatusAsientosEnum::VERIFICADO ])->get();

                    $match = Partidos::find($idPartido);

                    $seats = Asientos::select('id','code','status','zona', 'fila')->with([
                        'asientoTemporada' => function($asientoTemporada) use ($match)
                        {
                            $asientoTemporada->where('id_season', '=', $match->id_match_season);
                        }
                    ])->get();

                    foreach ($seats as $seat)
                    {
                        // Se actualiza el status del asiento por la status de la temporada correspondiente
                        $seat->setAttribute('status', $seat->asientoTemporada->first()->status);
                    }


                    $seats = $seats->whereNotIn( 'code', $tickets->pluck('code') )->where('status','>',0)->groupBy('zona')->map( function($zonas,$keyZona)
                    {
                        return [
                                'zona' => $keyZona,
                                    'filas' => $zonas->where('status','>',0)->groupBy('fila')->map( function ($filas,$keyFila)
                                {
                                    return $keyFila;
                                })->values()
                            ];
                        }
                    );
                    return response()->json($seats->values());
                }

                if ($idPartido && $zona)
                {

                    $match = Partidos::find($idPartido);

                    if ( !$match )
                    {
                        return response()->json( new DataResponse('No se ha encontrado el partido', 'Error', 'Partido') );
                    }

                    $asientos = Asientos::with([
                        'preciosAsientosActivos' => function($preciosAsientosActivos) use ($match)
                        {
                            $preciosAsientosActivos->select(['precios_asientos.id_seat', 'precios_asientos.id_seat_price', 'price'])->where('precios_asientos.id_season' , '=', $match->id_match_season);

                        },
                        'asientoTemporada' => function($asientoTemporada) use ($match)
                        {
                            $asientoTemporada->where('id_season', '=', $match->id_match_season);
                        }
                    ])->where('zona','=',$zona)->get();

                    foreach ($asientos as $asiento)
                    {
                        // Se actualiza el status del asiento por la status de la temporada correspondiente
                        $asiento->setAttribute('status', $asiento->asientoTemporada->first()->status);

                        // Se actualiza el precio del asiento por el precio de la temporada correspondiente
                        $asiento->setAttribute('precio', $asiento->preciosAsientosActivos->where('pivot.id_season', '=', $match->id_match_season)->where('pivot.typePrice', '=', EnumTypePrecioAsiento::UNICO)->first()->price);

                        // Se actualiza el precio_abono del asiento por el precio_abono de la temporada correspondiente
                        $asiento->setAttribute('precio_abono', $asiento->preciosAsientosActivos->where('pivot.id_season', '=', $match->id_match_season)->where('pivot.typePrice', '=', EnumTypePrecioAsiento::ABONO)->first()->price);

                        foreach ($asiento->preciosAsientosActivos as $precioAsientoActivo)
                        {
                            $precioAsientoActivo->makeHidden(['id_seat', 'id_seat_price']);
                        }
                    }

                    // $tickets = TicketsAsientos::where([['eventos_id', '=',$idPartido], ['zona','=',$zona]])->whereIn('status', [ EstatusAsientosEnum::COMPRADO, EstatusAsientosEnum::TAQUILLA, EstatusAsientosEnum::RESERVADO, EstatusAsientosEnum::TEMPORADA, EstatusAsientosEnum::VERIFICADO ])->get();

                    $tickets = TicketsAsientos::with([
                        'precio',
                        'asiento' => function ($asiento) use ($match)
                        {
                            $asiento-> with([
                                'preciosAsientosActivos' => function($preciosAsientosActivos) use ($match)
                                {
                                    $preciosAsientosActivos->select(['precios_asientos.id_seat', 'precios_asientos.id_seat_price', 'price'])->where('precios_asientos.id_season' , '=', $match->id_match_season);

                                },
                                'asientoTemporada' => function($asientoTemporada) use ($match)
                                {
                                    $asientoTemporada->where('id_season', '=', $match->id_match_season);
                                }
                            ]);
                        }
                    ])->where([['eventos_id', '=',$idPartido], ['zona','=',$zona]])->whereIn('status', [ EstatusAsientosEnum::COMPRADO, EstatusAsientosEnum::TAQUILLA, EstatusAsientosEnum::RESERVADO, EstatusAsientosEnum::TEMPORADA, EstatusAsientosEnum::VERIFICADO ])->get();


                    foreach ($tickets as $ticket)
                    {
                        // Se actualizan las propiedades del asiento por las propiedades correspondientes a la temporada del partido.
                            $ticket->asiento->setAttribute('status', $ticket->asiento->asientoTemporada->first()->status);
                            $ticket->asiento->setAttribute('precio', $ticket->asiento->preciosAsientosActivos->where('pivot.id_season', '=', $match->id_match_season)->where('pivot.typePrice', '=', EnumTypePrecioAsiento::UNICO)->first()->price);
                            $ticket->asiento->setAttribute('precio_abono', $ticket->asiento->preciosAsientosActivos->where('pivot.id_season', '=', $match->id_match_season)->where('pivot.typePrice', '=', EnumTypePrecioAsiento::ABONO)->first()->price);

                            $ticket->precio->setAttribute('status', $ticket->asiento->status);
                            $ticket->precio->setAttribute('precio', $ticket->asiento->precio);
                            $ticket->precio->setAttribute('precio_abono', $ticket->asiento->precio_abono);

                        foreach ($ticket->asiento->preciosAsientosActivos as $precioAsientoActivo)
                        {
                            $precioAsientoActivo->makeHidden(['id_seat', 'id_seat_price']);
                        }
                    }

                    $buyedCodes = $tickets->pluck('code');

                    $seats = $asientos->groupBy('zona')->map( function ($zonas, $keyZona) use ($tickets, $asientos, $buyedCodes)
                    {
                            $filas = $zonas->groupBy('fila')->map( function ($fila, $keyFila) use ($tickets, $asientos, $buyedCodes)
                            {
                                $s = collect();

                                // esto consulta lo disponible
                                    $disponibles = $fila->where('status','=',1)->whereNotIn('code',$buyedCodes)->map(function ($f)
                                    {
                                        return ['code' => $f->code, 'precio' => $f->precio, 'section_seat' => $f->section_seat, 'precios_asientos_activos' => $f->preciosAsientosActivos];
                                    });
                                    $s = $s->merge($this->buildStatuSeat($disponibles,1,$keyFila));

                                //significa comprados
                                    $codes = $tickets->where('fila','=',$keyFila)->map(function ($f)
                                    {
                                        return ['code' => $f->code, 'precio' => $f->precio, 'section_seat' => $f->precio->section_seat, 'precios_asientos_activos' => $f->asiento->preciosAsientosActivos];
                                    });
                                    $s = $s->merge($this->buildStatuSeat($codes,2, $keyFila));

                                //consulta los reservados
                                    $reservados = $fila->where('status','>',2)->map(function ($f)
                                    {
                                        return ['code' => $f->code, 'precio' => $f->precio, 'section_seat' => $f->section_seat, 'precios_asientos_activos' => $f->preciosAsientosActivos ];
                                    });
                                    $s = $s->merge($this->buildStatuSeat($reservados,5,$keyFila));

                                //consulta los bloqueados
                                    $bloqueados = $asientos->where('status', '=', 0)->where('fila', '=', $keyFila)->map(function ($f)
                                    {
                                        return ['code' => $f->code, 'precio' => $f->precio, 'section_seat' => $f->section_seat, 'precios_asientos_activos' => $f->preciosAsientosActivos];
                                    });
                                    $s = $s->merge($this->buildStatuSeat($bloqueados,3, $keyFila));

                                return [
                                        'fila' => $keyFila,
                                        'count' =>$fila->where('status','>',0)->count(),
                                        'asientos' => $s->unique('code')->sortBy('code', SORT_NATURAL, false)->values()
                                ];
                            });

                            return [
                                'zona' => $keyZona,
                                'filas' => $filas->values()
                            ];
                        }
                    );

                    return response()->json($seats->values());
                }

            } catch (\Exception $e)
            {
                return response()->json( new DataResponse('Ha ocurrido un error', 'Error', $e->getTrace()) );
            }
        }

        public function buildStatuSeat($codes, $status, $fila)
        {
            $resultSet = array();
            foreach ($codes as $c) {
                array_push($resultSet, [
                    'code' => $c['code'],
                    'status' => $status,
                    'fila' => $fila,
                    'precio' => $c['precio'],
                    'seccion' => $c['section_seat'],
                    // ZurielDA
                        'precios_asientos_activos' => $c['precios_asientos_activos']
                ]);
            }
            return $resultSet;
        }

        public function generateSeatForSeason( $id_season )
        {
            try
            {
                $seatForSeason = [];

                foreach (Asientos::all() as $seat)
                {
                    $seatForSeason = Arr::prepend($seatForSeason, [ "id_seat" => $seat->id, "id_season" => $id_season ]);
                }

                AsientoTemporada::insert($seatForSeason);

                return response()->json(new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(), "Asientos Temporadas" ),Response::HTTP_CREATED);

            }
            catch (\Throwable $th)
            {
                return response()->json(new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(), "Asientos Temporadas" ),Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        public function updateStatusSeatSeason(Request $request)
        {
            try
            {

                $zone = $request->input('zone');
                $row = $request->input('row');
                $seatsCodes = $request->input('seatsCodes');
                $matchSeason = $request->input('matchSeason');
                $status = $request->input('status');
                $revertStatus = $request->input('revertStatus');

                if( !in_array($status, [0, 1, 3]) )
                {
                    return response()->json(new DataResponse(ErroresExceptionEnum::OBJECT_NECESARY()->getMessage(), ErroresExceptionEnum::OBJECT_NECESARY()->getCode(), "Estatus" ), Response::HTTP_NOT_FOUND);
                }

                $asientos = Asientos::with([
                    'asientoTemporada' => function($asientoTemporada) use ($matchSeason)
                    {
                        $asientoTemporada->where('id_season', '=', $matchSeason );
                    }
                ]);

                if ($zone)
                {
                    $asientos->where('zona', '=', $zone);
                }

                if ($row)
                {
                    $asientos->where('fila', '=', $row);
                }

                if (Arr::accessible($seatsCodes) && count($seatsCodes))
                {
                    $asientos->whereIn('code', $seatsCodes);
                }

                $asientos = $asientos->get()->filter(function($value)
                {
                    return $value->asientoTemporada->count() > 0;
                });

                if( $asientos->count() == 0 )
                {
                    return response()->json(new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "Asientos Temporadas" ),Response::HTTP_NOT_FOUND);
                }

                foreach($asientos as $asiento)
                {
                    foreach($asiento->asientoTemporada as $asientoTemporada)
                    {
                        $asientoTemporada->lastStatus = $asientoTemporada->status;
                        $asientoTemporada->status = $revertStatus ? $asientoTemporada->status : $status;
                        $asientoTemporada->save();
                    }
                }

                return response()->json(new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(), "Asientos Temporadas" ),Response::HTTP_OK);
            }
            catch (\Throwable $th)
            {
                return response()->json(new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(), "Asientos Temporadas" ),Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


    /**
     *
     * ZurielDA
     *
     */


}
