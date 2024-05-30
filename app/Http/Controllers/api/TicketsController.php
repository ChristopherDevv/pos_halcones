<?php

namespace App\Http\Controllers\api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Config;
use App\Models\Tickets;
use App\Models\Abonados;
use App\Models\Asientos;
use App\Models\Partidos;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TicketsCambio;
use Illuminate\Http\Response;
use App\Models\AbonadoPartido;
use App\Models\GruposAsientos;
use App\Models\TicketsAsientos;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Interfaces\TipoDePagos;
use App\Models\Interfaces\DataResponse;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;
use App\Models\Interfaces\EstatusPartidos;
use App\Http\Controllers\AsientosController;
use App\Models\Interfaces\EstatusAsientosEnum;
use App\Models\Interfaces\ErroresExceptionEnum;
use Symfony\Component\HttpFoundation\Response as ResponseData;


class TicketsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Tickets::with(['user', 'asientos', 'evento'])->get();
    }


    public function aviabledTicket()
    {
        try {
            $id = request(['id']);
            $ticked = Tickets::where('id', $id)->first();
            if ($ticked->status) {
                $response =  new DataResponse('Ticket disponible', 'Disponible', $ticked);
                return response()->json($response);
            } else {
                $response =  new DataResponse('El tiempo de espera de compra del boleto a expirado, boleto no disponible', 'Error', $ticked);
                return response()->json($response);
            }
        } catch (\Exception $e) {
            $response =  new DataResponse('Error al consultar', 'Error', $e->getMessage());
            return response()->json($response);
        }
    }

    public function findByIdUser($idusuario)
    {
        try {
            $tickets = Tickets::where([
                ['users_id', '=', $idusuario],
                ['payed', '=', true],
                ['status', '>', 2]
            ])->with(['user', 'evento', 'asientos','codigos','codigo']);
            
            /* $partidoId = $tickets->get()->pluck('eventos_id'); */

           /*  return response()->json([
                'partidoId' => $tickets,
            ]); */

            $ticketsCollect = collect($tickets->get());

            $ticketsCollect = $ticketsCollect->map(function ($item, $key) {
                if ($item->temporada) {
                   /*   // Asegurarse de que cada asiento tenga el código en su QR
                    foreach ($item->asientos_unique as $asiento) {
                        if (strpos($asiento->qr, $asiento->code) === false) {
                            $asiento->qr .= '.' . $asiento->code;
                        }
                    } */
                    $ticketsAsientos = TicketsAsientos::where('tickets_id', $item->id);
                    $partidos = array();
                    foreach ($ticketsAsientos->get() as $ticketsAsiento) {
                        array_push($partidos, $ticketsAsiento->eventos_id);
                    }
                    $partidosCollect = collect(Partidos::whereIn('id', $partidos)->get());
                    $partidosCollect = $partidosCollect->whereIn('status', [EstatusPartidos::CREADO, EstatusPartidos::MOSTRAR]);
                    if ($partidosCollect->count() >= 1) {
                      /*   //imagen del partido
                        $imagePartido = Partidos::where('id', $partidosCollect->first()->id)->with('images')->first();
                        $item->imagen_partido = $imagePartido->images->first()->uri_path;
                  */       $item->partidos = $partidosCollect;
                        $asientos_collect =  collect($item->asientos);
                        $asientos_collect =  $asientos_collect->unique('code');
                        $item->asientos_unique = $asientos_collect;
                        unset($item->asientos);
                        $item->asientos = [];
                        $item->logo = base64_encode(file_get_contents(public_path() . '/logos/logo-nuevo.png'));
                        return $item;
                    }
                } else {
                   /*   // Asegúrarse de que cada asiento tenga el código en su QR
                    foreach ($item->asientos as $asiento) {
                        if (strpos($asiento->qr, $asiento->code) === false) {
                            $asiento->qr .= '.' . $asiento->code;
                        }
                    } */
                    
                    $item->logo = base64_encode(file_get_contents(public_path() . '/logos/logo-nuevo.png'));
                    if(!$item->is_generate_for_seat){
                        if ($item->status == EstatusAsientosEnum::VERIFICADO) {
                            unset($item->codigos);
                            unset($item->codigo);
                        }
                    }else {
                        $aux = $item->asientos->where('status','!=',EstatusAsientosEnum::VERIFICADO);
                        $auxCodigos = $item->codigos;
                        unset($item->codigos);
                        $resultSet = $aux->map( function ($i) { return $i->code;} )->values();
                        $item->codigos = $auxCodigos->whereIn('name',$resultSet)->values();
                    }
                    // Imagen del partido
                    $imagePartido = Partidos::where('id', $item->eventos_id)->with('images')->first();
                    if ($imagePartido && $imagePartido->images->first()) {
                        $item->imagen_partido = $imagePartido->images->first()->uri_path;
                    }
                    return $item;
                }
            })->filter(function ($item) {
                return $item !== null;
            })->values();

            if (!in_array(null, $ticketsCollect->toArray(), true)) {
                return $ticketsCollect;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            $response = new DataResponse('Error al consultar sus boletos', 'Error', $e->getMessage());
            return response()->json($response);
        }
    }

    public function findBySeat(Request $request)
    {
        try {
            $request = request(['idPartido', 'asiento']);
            $ticket = TicketsAsientos::where([
                ['eventos_id', '=', $request['idPartido']],
                ['code', '=', $request['asiento']]
            ]);
            $response = null;
            if ($ticket->exists()) {
                $ticketResult = $ticket->first();
                $tickectData = Tickets::where('id', $ticketResult->tickets_id)->first();
                if($tickectData->payed) {
                    if ($tickectData->is_generate_for_seat) {
                        $tickectData = Tickets::where('id', $ticketResult->tickets_id)->with(['user', 'evento', 'asientos', 'codigos'])->first();
                        $data = $this->buildTicketForQrCode($tickectData);
                        $response = new DataResponse('Boleto encontrado', 'Atención', $data);
                    } else {
                        $tickectData = Tickets::where('id', $ticketResult->tickets_id)->with(['user', 'evento', 'asientos', 'codigo'])->first();
                        $data = $this->buildTicketForQrCode($tickectData);
                        $response = new DataResponse('Boleto encontrado', 'Atención', $data);
                    }
                }else {
                    throw new \Exception('Este boleto todavía no ha sido validado');
                }
                return response()->json($response);
            } else {
                $response = new DataResponse('No se encontró su boleto', 'Error', $request);
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = new DataResponse('Error al consultar sus boletos', 'Error', $e->getMessage());
            return response()->json($response, 401);
        }
    }

    public function buildTicketForQrCode ($ticket) {
        if($ticket->is_generate_for_seat) {
            $aux = $ticket->codigos;
            unset($ticket->codigos);
            $aux = collect($aux)->map(
                function ($codigo) {
                    $image = base64_encode(file_get_contents(public_path() . '/'. $codigo->uri_path));
                    $codigo['qr'] = $image;
                    return $codigo;
            }
            )->values();
            $ticket->codigos = $aux;
            return $ticket;
        }else {
            $ticket->codigo['qr'] = $image = base64_encode(file_get_contents(public_path() . '/'. $ticket->codigo->uri_path));
            return $ticket;
        }
    }

    public function validateTicketById()
    {
        try {
            $ticketRequest = request(['id', 'asiento', 'idPartido']);
            $ticketData = Tickets::where('id', $ticketRequest['id']);
            $ticket = $ticketData->first();
            $response = null;
            if ($ticketData->exists()) {
                if ($ticket->status === EstatusAsientosEnum::VERIFICADO) {
                    $response = new DataResponse('Este boleto ya fue verificado', 'Error', $ticketData);
                } else {
                    if ($ticket->is_generate_for_seat) {
                        $ticketAsiento = TicketsAsientos::where([
                            ['tickets_id', $ticketRequest['id']],
                            ['code', $ticketRequest['asiento']],
                            ['eventos_id', $ticketRequest['idPartido']]
                        ]);
                        $result = $ticketAsiento->update([
                            'status' => EstatusAsientosEnum::VERIFICADO
                        ]);
                        if ($result > 0) {
                            $response = new DataResponse('Se ha verficado el boleto', 'Verificado', $ticketData);
                        } else {
                            $response = new DataResponse('No se pudo verficar el boleto, intente de nuevo', 'Error', $ticketData);
                        }
                    } else {
                        $result = $ticketData->update([
                            'status' => EstatusAsientosEnum::VERIFICADO
                        ]);
                        if ($result > 0) {
                            $response = new DataResponse('Se ha verficado el boleto', 'Verificado', $ticketData);
                        } else {
                            $response = new DataResponse('No se pudo verficar el boleto, intente de nuevo', 'Error', $ticketData);
                        }
                    }
                }
                return response()->json($response);
            } else {
                $response = new DataResponse('Boleto no valido', 'Error', null);
                return response()->json($response);
            }
        } catch (\Exception $e) {
            $response = new DataResponse('No se pudo validar el boleto', 'Error', null);
            return response()->json($response);
        }
    }


    // public function validateTicket(Request $request)
    // {

    //     $requestData = $request->all();

    //     $code = $requestData['code'];

    //     if (empty($code)) {

    //         $response = new DataResponse('Código no valido', 'Error', $requestData);

    //         return response()->json($response, ResponseData::HTTP_INTERNAL_SERVER_ERROR);

    //     }

    //     $codeExplode = explode(".", $code);

    //     if (count($codeExplode) <= 3 ) {

    //         return response()->json(new DataResponse('Este boleto no es valido', 'Error', $code));

    //     }

    //     $ticket = $codeExplode[3];

    //     // $ticketData = Tickets::where('id', $ticket)->with(['user', 'asientos', 'evento'])->first();

    //     // Zuriel DA
    //         $ticketData = Tickets::with(['user', 'asientos', 'evento'])->find($ticket);


    //     // if ($ticketData->temporada) {
    //     //     $response = null;
    //     //     if (Partidos::whereIn('status', [EstatusPartidos::MOSTRAR])->count()) {
    //     //         $response = new DataResponse('Se ha verficado el boleto por temporada', 'Verificado', $ticketData);
    //     //     } else {
    //     //         $response = new DataResponse('La temporada ya finalizó, boleto no válido', 'Atención', $ticketData);
    //     //     }
    //     //     return response()->json($response);
    //     // }

    //     // Zuriel DA
    //     if ($ticketData->temporada) {

    //         $isValid = Partidos::whereIn('status', [EstatusPartidos::MOSTRAR])->count();

    //         $message = $isValid ? 'Se ha verificado el boleto por temporada' : 'La temporada ya finalizó, boleto no válido';

    //         $status = $isValid ? 'Verificado' : 'Atención';

    //         return response()->json(new DataResponse($message, $status, $ticketData));
    //     }

    //     if (count($codeExplode) >= 5) {
    //         $asiento = $codeExplode[4];
    //         $partido = $codeExplode[2];
    //         $ticketAsiento = TicketsAsientos::where([
    //             ['tickets_id', $ticket],
    //             ['code', $asiento],
    //             ['eventos_id', $partido]
    //         ]);
    //         $dataAsientos = $ticketAsiento->first();
    //         if ($ticketAsiento->exists()) {
    //             unset($ticketData->asientos);
    //             $ticketData->asientos = array([
    //                 'code' => $dataAsientos->code
    //             ]);
    //             if ($dataAsientos->status == EstatusAsientosEnum::VERIFICADO) {
    //                 $response = new DataResponse('Este boleto ya ha sido verificado', 'Error', $ticketData);
    //                 return response()->json($response);
    //             } else {

    //                 // $dateEvent = Partidos::where('id', $partido)->first()->fecha;
    //                 $dateEvent = Partidos::find($partido)->fecha;

    //                 $resultData = $this->compareDateWithToday($dateEvent);
    //                 if($resultData->valid){
    //                     $ticketAsiento->update([ 'status' => EstatusAsientosEnum::VERIFICADO ]);
    //                     $ticketAsiento = TicketsAsientos::where('tickets_id', $ticketAsiento->first()->id);
    //                     $ticketAsiento->update([ 'status' => EstatusAsientosEnum::VERIFICADO ]);
    //                     $response = new DataResponse('Boleto verificado, Muchas Gracias', 'Verificado', $ticketData);
    //                 }else {
    //                     $response = new DataResponse($resultData->message, 'Ocurrió un error', $ticketData);
    //                     return response()->json($response);
    //                 }
    //                 $result = $ticketAsiento->update([
    //                     'status' => EstatusAsientosEnum::VERIFICADO
    //                 ]);
    //                 $response = new DataResponse('Boleto verificado, Muchas Gracias', 'Verificado', $ticketData);
    //                 return response()->json($response);
    //             }
    //         } else {
    //             $response = new DataResponse('Boleto no valido', 'Error', null);
    //             return response()->json($response);
    //         }
    //     } else {
    //         $ticketResult = Tickets::where('id', $ticket)->with('evento');
    //         $response = null;
    //         if ($ticketResult->exists()) {
    //             if ($ticketResult->first()->status == EstatusAsientosEnum::VERIFICADO) {
    //                 $response = new DataResponse('Este boleto ya fue verificado', 'Error', $ticketData);
    //             } else {
    //                 $ticketData = $ticketResult->first();
    //                 $dateEvent = $ticketData->evento->fecha;
    //                 $resultData = $this->compareDateWithToday($dateEvent);
    //                 if($resultData->valid){
    //                     $ticketResult->update([
    //                         'status' => EstatusAsientosEnum::VERIFICADO
    //                     ]);
    //                     $ticketAsiento = TicketsAsientos::where('tickets_id', $ticketResult->first()->id);
    //                     $ticketAsiento->update([
    //                         'status' => EstatusAsientosEnum::VERIFICADO
    //                     ]);
    //                     $response = new DataResponse('Boleto verificado, Muchas Gracias', 'Verificado', $ticketData);
    //                 }else {
    //                     $response = new DataResponse($resultData->message, 'Ocurrió un error', $ticketData);
    //                 }
    //             }
    //             return response()->json($response);
    //         } else {
    //             $response = new DataResponse('Boleto no valido', 'Error', null);
    //             return response()->json($response);
    //         }
    //     }
    // }

    public function compareDateWithToday($date){
        $d1 = Carbon::createFromDate($date);
        $hoy = Carbon::now();
        $formatDate = $d1->format('Y-m-d');
        $formatDateToday = $hoy->isoFormat('Y-MM-DD');
        $response = new \stdClass();
        if($formatDate === $formatDateToday) {
                $response->valid = true;
                $response->message = 'Fechas Iguales';
        }else if($d1->gt($hoy)) {
                $response->valid = false;
                $response->message ='No se pudo validar el boleto, el boleto pertenece a un partido con una fecha posterior';
        }else if($d1->lt($hoy)) {
                $response->valid = false;
                $response->message = 'Boleto no válido, este boleto pertenece a un partido con una fecha pasada';
        }
        return $response;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $dataReservation = NULL)
    {
        $ticketData = $dataReservation ? $dataReservation : $request->all();
        if (Partidos::whereIn('status', [1, EstatusPartidos::MOSTRAR])->count()) {
            try {
                DB::beginTransaction();
                if (is_null($ticketData['payed'])) {
                    $ticketData['payed'] = false;
                }
                $countSeat = $this->getBuyedSeat($ticketData['users_id'], $ticketData['eventos_id']);
                $sumCount = $countSeat + collect($ticketData['asientoBoleto'])->count();
                if ($countSeat >= 8 && $ticketData['type_reservation'] === 'evento') {
                    throw new \Exception('Solo tienes un número máximo para comprar asientos por partido');
                } else if ($sumCount > 8 && $ticketData['type_reservation'] === 'evento') {
                    throw new \Exception('Solo tienes un número máximo para comprar asientos por partido');
                }
                $ticketData['status'] = $this->setEstatusTicket($ticketData);

                if (!empty($ticketData['grupo'])){
                    $exists = GruposAsientos::where('nombre', '=', $ticketData['grupo'])->first();
                    if ($exists != null){
                        $id_grupo = $exists->id;
                        $tipo_grupo = $exists->tipo_grupo;
                    } else {
                        $grupo = [
                            'grupo' => 2,
                            'nombre' => $ticketData['grupo'],
                            'descripcion' => 'Sin descripcion',
                            'tipo_grupo' => 2
                        ];
                        $grupoAsiento = GruposAsientos::create($grupo);
                        $id_grupo = $grupoAsiento->id;
                        $tipo_grupo = $grupoAsiento->tipo_grupo;
                    }
                } else {
                    $id_grupo = 0;
                    $tipo_grupo = 0;
                }

                $ticket = Tickets::create($ticketData);

                $config = [
                    'idTicket' => $ticket->id,
                    'idEvento' => $ticket->eventos_id,
                    'tipeReservation' => $ticket->type_reservation,
                    'zona' => $ticket->zona,
                    'fila' => $ticket->fila,
                    'temporada' => boolval($ticket->temporada),
                    'id_grupo' => $id_grupo,
                    'tipo_grupo' => $tipo_grupo
                ];

                try
                {
                    $response = app(\App\Http\Controllers\api\AsientosController::class)->reservarBoletos($ticketData['asientoBoleto'], $config);
                }
                catch(\Throwable $th)
                {
                    DB::rollBack();

                    $response = new DataResponse('Ha ocurrido un error, No se pudieron comprar los bolestos. Intente de nuevo.', 'Error', $th->getTrace());

                    return response()->json($response);
                }

                $ticketShow = Tickets::where('id', $ticket->id)->with(
                    ['asientos', 'evento']
                )->first();

                $response->data = $this->validateForGenerateQr($ticketShow);
                DB::commit();
                if(!is_null($dataReservation)) {
                    return $response->data;
                }
                return response()->json($response);
            } catch (\Throwable $e) {
                DB::rollBack();
                if(!is_null($dataReservation)) {
                    throw  new \Exception($e->getMessage());
                }else{
                    $response = new DataResponse('Ha ocurrido un error, intente de nuevo '.$e->getMessage(), 'Error', $e->getTrace());
                    return response()->json($response);
                }
            }
        } else {
            $response = new DataResponse('Partidos no disponibles', 'Error', $ticketData);
            return response()->json($response);
        }
    }

    public function validateForGenerateQr(Tickets $ticket)
    {
        $tickets = array();
        if ($ticket->is_generate_for_seat) {
            foreach ($ticket->asientos as $asiento) {
                $tickeXplode = explode('.', $ticket->code);
                $tickeXplode[2] = $asiento->eventos_id;
                $tickeXplode[4] = $asiento->code;
                $codeGenerate = implode('.', $tickeXplode);
                $urlQr = $this->generateQr($codeGenerate);

                TicketsAsientos::where('id', '=', $asiento-> id)->update(['qr' => $codeGenerate]);

                $data = [
                    'idOrigin' => $ticket->id,
                    'type' => 'codigo'
                ];
                app(\App\Http\Controllers\api\ImagenesController::class)->presSave($urlQr, $data, $asiento->code);
                array_push($tickets, $this->buildTickets($urlQr, $ticket, $asiento->code, $asiento->id, $asiento->precioAsiento->precioAsiento->price, $asiento->folio));
            }
            return $tickets;
        } else {
            $urlQr = $this->generateQr($ticket->code);

            $data = [
                'idOrigin' => $ticket->id,
                'type' => 'codigo'
            ];
            app(\App\Http\Controllers\api\ImagenesController::class)->presSave($urlQr, $data);

            $asientos = collect($ticket->asientos)->map( function ($asiento, $key) use ( $ticket) {

                TicketsAsientos::where('id', '=', $asiento-> id)->update(['qr' => $ticket->code]);

                return $asiento->code;

            });

            return $this->buildTickets($urlQr, $ticket, $asientos->implode(','));
        }
    }

    private function buildTickets($urlCode, Tickets $ticket, $asiento, $idAsiento = NULL, $precio = null, $folio = NULL)
    {
        $image = base64_encode(file_get_contents(public_path() . '/'. $urlCode));
        $id = $ticket->is_generate_for_seat ? $idAsiento : $ticket->id;
        $precio = $ticket->is_generate_for_seat ? $precio : $ticket->total;
        if ($ticket->type_payment == 3) {
            $precio = 0;
        }
        $logo =  base64_encode(file_get_contents(public_path() . '/uploads/logo-negro.png'));
        $ticketData = [
            'id' => $id,
            'titulo' => $ticket->evento->titulo,
            'descripcion' => $ticket->evento->descripcion,
            'fecha' => $ticket->evento->fecha,
            'horario' => $ticket->evento->horario,
            'total' => $precio,
            'eventos_id' => $ticket->evento->id,
            'lugar' => $ticket->evento->lugar,
            'asiento' => $asiento,
            'logo' => $logo,
            'folio' => $folio,
            'qr' => $image
        ];
        $ticket->is_generate_for_seat ? $ticketData['idGeneral'] = $ticket->id : null;
        return $ticketData;
    }

    /**
     * Display the specifie->resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $ticket = Tickets::where('id', $id);
            if ($ticket->first()->is_generate_for_seat) {
                $ticket = $ticket->with(['codigos', 'asientos'])->first();
                $asientos = collect($ticket->asientos)->whereNotIn('status', [EstatusAsientosEnum::VERIFICADO]);
                $codigos = collect($ticket->codigos);
                $tickets = $asientos->map(function ($asiento, $key) use ($codigos, $ticket) {
                    $imageQr = $codigos->where('name', $asiento->code)->first();
                    return $this->buildTickets($imageQr->uri_path, $ticket, $asiento->code);
                });
                return $tickets->values();
            } else {
                $ticket = $ticket->with(['user', 'asientos', 'codigo'])->first();
                $ticket->logo_base = base64_encode(file_get_contents(public_path() . '/uploads/logo.jpg'));
                return $ticket;
            }
        } catch (\Exception $e) {
            return response('Error' . $e->getMessage(), 500);
        }
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
        return Tickets::where('id', $id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try
        {
            // Zuriel DA
                $ticket = Tickets::with(['abonados'=> function($abonados)
                {
                    $abonados->select('id', 'id_ticket', 'id_ticket_seat')->with(['ticketAsiento' => function( $ticketAsiento )
                    {
                        $ticketAsiento->select('id','code');
                    }]);

                }])->find($id);

                    $codes = $ticket->abonados->pluck('ticketAsiento')->pluck('code');

                    $matchSeason = Partidos::find($ticket->eventos_id);

                    if ($codes->count())
                    {
                        $responseUpdateStatusSeatSeason = app(\App\Http\Controllers\api\AsientosController::class)->updateStatusSeatSeason( (new Request)->merge(['seatsCodes' => $codes, 'matchSeason' => $matchSeason->id_match_season, 'status' => EstatusAsientosEnum::DISPONIBLE]) )->getData(true);

                        if ( !Str::contains($responseUpdateStatusSeatSeason["status"], ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode() ) )
                        {
                            return response()->json($responseUpdateStatusSeatSeason);
                        }
                    }

                $ticket->update([ 'status' => EstatusAsientosEnum::DESHABILITADO]);


            $response =  new DataResponse('Se ha actualizaddo el modelo', 'Eliminado', $id);


            return response()->json($response);

        } catch (\Exception $e) {

            $response =  new DataResponse('Error al actualizar', 'Error', $id);

            return response()->json($response);

        }
    }

    public function destroyForSeat($id)
    {
        try {
            $asiento = TicketsAsientos::where('id', $id)->firstOrFail();
            $asiento->delete();
            $response =  new DataResponse('Se ha actualizaddo el modelo', 'Eliminado', $id);
            return response()->json($response);
        } catch (\Exception $e) {
            $response =  new DataResponse('Error al actualizar', 'Error', $id);
            return response()->json($response);
        }
    }

    public function destroySeatOfTicket(Request $request) {
        try {
            DB::beginTransaction();
            $idTicket = $request->get('idTicket');
            $seat = $request->get('code');
            $ticketSeat  = TicketsAsientos::where([
                ['tickets_id','=',$idTicket],
                ['code','=',$seat]
            ]);
            if($ticketSeat->exists()) {
                $ticketSeat->delete();
                DB::select('CALL prcdr_update_total_ticket(?)',[
                    $idTicket
                ]);
            }else {
                throw new \Exception('No se ha encotrado el asiento para este boleto');
            }
            DB::commit();
            $response = new DataResponse('Se ha actualizado correctamente el boleto',ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(),$idTicket);
            return response()->json($response);
        }catch (\Exception $e) {
            DB::rollBack();
            $response = new DataResponse('Ocurrió un erro al eliminar el asiento del boleto',ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getCode(),$e->getMessage());
            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function generateQr($code)
    { // cambiar por code
        $image = \QrCode::size(500)->generate($code);
        $imageName = Str::random(10) . '.svg';
        Storage::disk('upload')->put($imageName, $image);
        $url = 'upload/' . $imageName;
        return $url;
    }

    public function updatePayed()
    {
        try {
            DB::beginTransaction();
            $id = request(['idTicket']);

            $result = 0;
            $ticket = Tickets::where('id', $id);
            if ($ticket->exists()) {
                $result = $ticket->update([
                    'payed' => true
                ]);
                if ($result <= 0) {
                    throw new \Exception('Error al actualizar' . $result);
                }
            } else {
                throw new \Exception('ID NO ENCONTRADO' . json_encode($id));
            }
            DB::commit();
            $response = new DataResponse('Se ha comprado el boleto', 'Atención', $id);
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = new DataResponse($e->getMessage(), 'Error', $e->getTrace());
            return response(json_encode($response), 500);
        }
    }

    private function setEstatusTicket($ticket)
    {
        switch ($ticket['type_reservation']) {
            case 'temporada':
                return EstatusAsientosEnum::TEMPORADA;
                break;
            case 'evento':
                return EstatusAsientosEnum::COMPRADO;
                break;
            case 'reservation':
                return EstatusAsientosEnum::RESERVADO;
                break;
            case 'taquilla':
                return EstatusAsientosEnum::TAQUILLA;
                break;
                break;
            default:
                throw new \Exception('No se encontro configuracion para el ticket');
                break;
        }
    }

    public function boxCutGameUser(Request $r, $idPartido, $idUser)
    {
        try {
            $ticketSVentas = Tickets::from('tickets as t')
                ->JOIN('tickets_asiento as ta', 't.id', '=', 'ta.tickets_id')
                ->JOIN('asientos as a', 'ta.code', '=', 'a.code')
                ->JOIN('partidos as p', 't.eventos_id', '=', 'p.id')
                ->where([
                    ['t.eventos_id', '=', $idPartido],
                    ['t.status', '>', 0],
                    ['t.payed', '=', 1],
                    ['t.users_id', '=', $idUser]
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
                );
            $cortes = collect($ticketSVentas->get())->sortByDesc('fecha_compra')->groupBy('fecha_compra')->map(
                function ($fecha, $key) {
                    return [
                        'fecha' => $key,
                        'partido' => collect($fecha)->unique('titulo')->first()->titulo,
                        'totalVendido' => collect($fecha)->where('type_reservation', 'taquilla')

                        ->sum('total'),
                        'totalPrecios' => collect($fecha)->where('type_reservation', 'taquilla')->groupBy('precio')->map(
                            function ($precio, $key) {
                                return [
                                    'precio' => $key,
                                    'total' => collect($precio)->sum('total')
                                ];
                            }
                        )->values(),
                        'tiposCompras' => collect($fecha)->where('type_reservation', 'taquilla')->groupBy('tipo_compra')->map(
                            function ($tipoCompra, $key) {
                                return [
                                    'tipoCompra' => $key,
                                    'precios' => collect($tipoCompra)->groupBy('precio')->map(
                                        function ($asiento, $key) {
                                            return [
                                                'precio' => $key,
                                                'total' => collect($asiento)->sum('total'),
                                            ];
                                        }
                                    )->values(),
                                    'totalVenta' => collect($tipoCompra)
                                    ->where('type_payment', '!=',3)
                                    ->sum('total_vendido'),
                                    'totalAsientos' => collect($tipoCompra)->sum('total')
                                ];
                            }
                        )->values(),
                        'ventaTotal' => collect($fecha)->where('type_reservation', 'taquilla')
                        ->where('type_payment', '!=',3)
                        ->sum('total_vendido')
                    ];
                }
            )->values();

            return response()->json($cortes, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No hay datos para mostrar' . $e->getMessage()], 400);
        }
    }

    public function boxCut(Request $r, $idUser)
    {
        try {
            $ticketSVentas = Tickets::from('tickets as t')
                ->JOIN('tickets_asiento as ta', 't.id', '=', 'ta.tickets_id')
                ->JOIN('asientos as a', 'ta.code', '=', 'a.code')
                ->JOIN('partidos as p', 't.eventos_id', '=', 'p.id')
                ->where([
                    ['t.status', '>', 1],
                    ['t.payed', '=', 1],
                    ['t.users_id', '=', $idUser]
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
                );

            $cortes = collect($ticketSVentas->get())->sortByDesc('fecha_compra')->groupBy('partido')->map(
                function ($partidos, $keyPartido) {
                    return [
                        'partido' => collect($partidos)->unique('titulo')->first()->titulo,
                        'compras' => collect($partidos)->where('partido', $keyPartido)->groupBy('fecha_compra')->map(
                            function ($fecha, $keyFecha) {
                                return [
                                    'fecha' => $keyFecha,
                                    'partido' => collect($fecha)->unique('titulo')->first()->titulo,
                                    'totalVendido' => collect($fecha)->where('type_reservation', 'taquilla')
                                    ->where('type_payment', '!=',3)
                                    ->sum('total'),
                                    'totalPrecios' => collect($fecha)->where('type_reservation', 'taquilla')->groupBy('precio')->map(
                                        function ($precio, $key) {
                                            return [
                                                'precio' => $key,
                                                'total' => collect($precio)->sum('total')
                                            ];
                                        }
                                    )->values(),
                                    'tiposCompras' => collect($fecha)->where('type_reservation', 'taquilla')->groupBy('tipo_compra')->map(
                                        function ($tipoCompra, $key) {
                                            return [
                                                'tipoCompra' => $key,
                                                'precios' => collect($tipoCompra)->groupBy('precio')->map(
                                                    function ($asiento, $key) {
                                                        return [
                                                            'precio' => $key,
                                                            'total' => collect($asiento)->sum('total'),
                                                        ];
                                                    }
                                                )->values(),
                                                'totalVenta' => collect($tipoCompra)->sum('total_vendido'),
                                                'totalAsientos' => collect($tipoCompra)->sum('total')
                                            ];
                                        }
                                    )->values(),
                                    'ventaTotal' => collect($fecha)->where('type_reservation', 'taquilla')->sum('total_vendido')
                                ];
                            }
                        )->values()
                    ];
                }
            )->values();
            return response()->json($cortes, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No hay datos para mostrar' . $e->getMessage()], 400);
        }
    }

    public function getBuyedSeat($userid, $partidoid)
    {
        $tickets = collect(Tickets::where([
            ['users_id', '=', $userid],
            ['eventos_id', '=', $partidoid],
            ['status', '>=', 3],
            ['payed', '=', 1]
        ])->with('asientos')->get());


        $seats = $tickets->map(
            function ($item, $key) {
                $asientos = collect($item->asientos)->map(
                    function ($asiento) {
                        return $asiento->code;
                    }
                );
                return $asientos->count();
            }
        )->sum();

        return $seats;
    }


    /***
     *
     * ZurielDA
     *
     */


        public function changeSeatAcquisition(Request $request)
        {
            try
            {
                $id_user = $request-> get('id_user');
                $codeQRList = $request-> get('codeQr');
                $change = $request-> get('change');  // 'cortesia-venta' y 'VENTA-CORTESIA'
                $id_registro_caja = $request-> get('id_registro_caja');
                $id_method_payment = $request-> get('id_method_payment');

                if ( !count($codeQRList) || !$change )
                {
                    $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null );

                    return response()->json($response,Response::HTTP_NOT_FOUND);
                }

                $ticketsSeatFound = collect([]);
                $ticketsSeatNotFound = collect([]);
                $ticket = null;

                Arr::first($codeQRList, function ($codeQR, $key) use (&$ticketsSeatFound, &$ticketsSeatNotFound, &$ticket)
                {
                $ticketSeat = TicketsAsientos::where([ ['change', '=', null], ['qr', '=', $codeQR] ])->first();

                $isCortesy = false;

                if ($ticketSeat)
                {
                    $isCortesy =  $ticketSeat-> ticket -> type_payment == TipoDePagos::CORTESIA;
                }

                if ($ticketSeat && $isCortesy)
                {
                        $exists = TicketsCambio::where('id_ticket_seat','=', $ticketSeat->id)->first();

                        if ($ticketSeat-> ticket-> type_payment == 3 && !$exists)
                        {
                            $ticket = $ticketSeat-> ticket;

                            $ticketsSeatFound->add( [ 'id' => $ticketSeat-> id, 'qr' => $codeQR ]);
                        }
                        else
                        {
                            $ticketsSeatNotFound->add( ['qr' => $codeQR ] );
                        }
                }
                else
                {
                        $ticketsSeatNotFound->add( ['qr' => $codeQR ] );
                }

                });

                if (!$ticketsSeatFound->count()) {

                    $response = new DataResponse(ErroresExceptionEnum::OBJECT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_FOUND()->getCode(), null );

                    return response()->json($response,Response::HTTP_NOT_FOUND);
                }

                if ($ticket) {

                    if (Partidos::whereIn('status', [1, EstatusPartidos::MOSTRAR])->count())
                                {
                                    try
                                    {
                                        $priceTotal = 0;

                                        $ticketsSeatFound->each(function ($ticketsSeat, $key) use (&$priceTotal, $change)
                                        {
                                            $ticketSeat = TicketsAsientos::with('precio')->find($ticketsSeat['id']);

                                            //  Actualiza a tipo de cambio que tendra el ticket-asiento
                                                $ticketSeat-> change = $change;
                                            //

                                            $ticketSeat-> save();

                                            // Solo actualizar el precio total de venta en el ticket que se genera como una consignia
                                                if ($change == "consigna-venta") {

                                                    $tickets = Tickets::find($ticketSeat-> ticket-> id);

                                                    $tickets-> total -=  $ticketSeat-> precio-> precio;

                                                    $tickets-> save();

                                                }
                                            //

                                            $priceTotal += $ticketSeat-> precio-> precio;

                                        });

                                        DB::beginTransaction();

                                            $ticketTemp = new Tickets;
                                            $ticketTemp-> abono = false;
                                            $ticketTemp-> id_registro_caja = $id_registro_caja;
                                            $ticketTemp-> abono = $id_method_payment;
                                            $ticketTemp-> lugar = $ticket -> lugar;
                                            $ticketTemp-> fecha = $ticket -> fecha;
                                            $ticketTemp-> horario = $ticket -> horario;
                                            $ticketTemp-> zona = $ticket -> zona;
                                            $ticketTemp-> fila = $ticket -> fila;
                                            $ticketTemp-> temporada = $ticket -> temporada;
                                            $ticketTemp-> total = $priceTotal;
                                            $ticketTemp-> payed = true;
                                            $ticketTemp-> eventos_id = $ticket -> eventos_id;
                                            $ticketTemp-> users_id = $id_user;
                                            $ticketTemp-> is_generate_for_seat = false;
                                            $ticketTemp-> type_reservation = "taquilla";
                                            $ticketTemp-> type_payment = 1; // 1 => efectivo
                                            $ticketTemp-> status = $this->setEstatusTicket($ticket);

                                            $ticketTemp-> save();

                                            $ticketsSeatFound->each(function ($ticketSeat, $key) use ( $ticketTemp )
                                            {
                                                $ticketsCambio = new TicketsCambio;

                                                $ticketsCambio-> id_ticket = $ticketTemp->id;
                                                $ticketsCambio-> id_ticket_seat = $ticketSeat['id'];

                                                $ticketsCambio-> save();
                                            });

                                        DB::commit();

                                        $ticketShow = Tickets::where('id','=',$ticketTemp->id)->with(['asientosCambiados.ticketAsiento.precio', 'evento'])->first();

                                        data_set($ticketShow, 'asientos', $ticketShow->asientosCambiados->pluck('ticketAsiento'));

                                        $ticketShow->makeHidden('asientosCambiados');

                                        $tickets = array();

                                        $ticketShow-> asientos-> each(function ($ticketSeat, $key) use ( $ticketShow, &$tickets )
                                        {
                                            $urlQr = $this->generateQr($ticketSeat-> qr);

                                            array_push($tickets, $this->buildTickets($urlQr, $ticketShow, $ticketSeat->code, $ticketSeat->id, $ticketSeat->precio->precio, $ticketSeat->folio));

                                        } );

                                        $response = new DataResponse('Se han cambiado el boleto de cortesia a venta', 'Reservados', $tickets);

                                        return response()->json($response);

                                    }
                                    catch (\Exception $e)
                                    {
                                        DB::rollBack();

                                        $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),null);

                                        return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
                                    }
                                }
                                else
                                {
                                    $response = new DataResponse('Partidos no disponibles', 'Error', $ticket);
                                    return response()->json($response);
                                }
                }

                $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),null);

                return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);

            }
            catch (\Exception $e)
            {
                $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),null);

                return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);

            }
        }

        public function storeSubscription(Request $request)
        {
            try
            {
                $request->merge(['temporada' => true]);

                $ticket = $request->only('id_registro_caja','id_method_payment', 'fecha', 'horario','lugar', 'temporada','eventos_id','type_reservation','users_id','zona', 'fila','total', 'payed', 'type_payment', 'is_generate_for_seat','type_agreement');
                $group = $request->only('grupo')['grupo'];
                $subscriptors = $request->only('abonos')['abonos'];

                $ticket['status'] = $this->setEstatusTicket($ticket);

                $id_grupo = null;
                $tipo_grupo = null;

                // Se agrega el grupo correspondiente de cortesia si es que existe, si no se crea.
                if (!empty($group))
                {
                    $group = app(\App\Http\Controllers\api\GrupoController::class)->storage( (new Request)->merge( [ 'grupo' => 2, 'nombre' => $group, 'descripcion' => 'Sin descripcion', 'tipo_grupo' => 2 ]) )->getData(true);

                    if ( Str::contains($group["status"], ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode()) || Str::contains($group["status"], ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getCode()) )
                    {
                        $id_grupo = $group["data"]["id"];
                        $tipo_grupo = $group["data"]["tipo_grupo"];
                    }
                    else
                    {
                        return response()->json( $group );
                    }
                }

                if ( Arr::accessible( $subscriptors ) && count($subscriptors) )
                {
                    DB::beginTransaction();

                    try
                    {
                        $ticket = Tickets::create($ticket);

                        // Reserva de asientos
                        $seats = app(\App\Http\Controllers\api\AsientosController::class)->reserveSubscriptionsSeats( Arr::pluck($subscriptors, 'seat'), $ticket->id,  $ticket->eventos_id, $id_grupo, $tipo_grupo )->getData(true);

                        if ( Str::contains($seats["status"], ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode()) )
                        {
                            $ticket = Tickets::with( [ 'asientos', 'evento' ] )->find($ticket->id);

                            // Comprobar que el ticket en verdad tenga asientos.
                            if ($ticket->asientos->count() > 0)
                            {
                                $subscriptorsTemp = [];

                                foreach ($ticket->asientos as $asientos)
                                {
                                    $subscriptor = Arr::first($subscriptors, function ($value, $key) use ($asientos)
                                    {
                                        return Str::contains( Str::lower( $value['seat'] ), Str::lower($asientos->code) );
                                    });

                                    $subscriptorsTemp = Arr::prepend($subscriptorsTemp, [
                                        "id_ticket" => $ticket-> id,
                                        "id_ticket_seat" => $asientos-> id,
                                        "holder" => $subscriptor['holder'],
                                        "name" => $subscriptor['name'],
                                        "paternalSurname" => $subscriptor['paternalSurname'],
                                        "maternalSurname" => $subscriptor['maternalSurname'],
                                    ]);

                                }

                                Abonados::insert($subscriptorsTemp);

                                DB::commit();

                                return response()->json(new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), "Reservados" , $this->validateForGenerateQrOfSubscription($ticket) ));
                            }
                            else
                            {
                                DB::rollBack();
                                return response()->json(new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "Boletos" ));
                            }
                        }
                        else
                        {
                            DB::rollBack();
                            return response()->json( $seats );
                        }
                    }
                    catch(\Throwable $th)
                    {
                        DB::rollBack();
                        return response()->json(new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(), "Boletos".$th->getMessage() ));
                    }
                }
                else
                {
                    return response()->json(new DataResponse(ErroresExceptionEnum::OBJECT_NECESARY()->getMessage(), ErroresExceptionEnum::OBJECT_NECESARY()->getCode(), "Abonos"));
                }
            }
            catch (\Throwable $th)
            {
                return response()->json(new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(), "Ticket" ));
            }
        }

        public function validateForGenerateQrOfSubscription(Tickets $ticket)
        {
            $tickets = array();

            if ($ticket->is_generate_for_seat) {

                foreach ($ticket->asientos as $asiento) {

                    $tickeXplode = explode('.', $ticket->code);
                    $tickeXplode[2] = $asiento->eventos_id;
                    $tickeXplode[4] = $asiento->code;
                    $codeGenerate = implode('.', $tickeXplode);
                    $urlQr = $this->generateQr($codeGenerate);

                    TicketsAsientos::where('id', '=', $asiento-> id)->update(['qr' => $codeGenerate]);

                    $data = [ 'idOrigin' => $ticket->id, 'type' => 'codigo' ];

                    app(\App\Http\Controllers\api\ImagenesController::class)->presSave($urlQr, $data, $asiento->code);

                    array_push($tickets, $this->buildTickets($urlQr, $ticket, $asiento->code, $asiento->id, $asiento->precioAsientoAbono->precioAsiento->price, $asiento->folio));


                }

                return $tickets;

            } else {

                $urlQr = $this->generateQr($ticket->code);

                $data = [
                    'idOrigin' => $ticket->id,
                    'type' => 'codigo'
                ];
                app(\App\Http\Controllers\api\ImagenesController::class)->presSave($urlQr, $data);

                $asientos = collect($ticket->asientos)->map( function ($asiento, $key) use ( $ticket) {

                    TicketsAsientos::where('id', '=', $asiento-> id)->update(['qr' => $ticket->code]);

                    return $asiento->code;

                });

                return $this->buildTickets($urlQr, $ticket, $asientos->implode(','));
            }
        }

        public function validateTicket(Request $request)
        {
            try
            {
                $code = $request->input('code');

                if (empty($code))
                {
                    return response()->json( new DataResponse('Es necesario que se introduzca el codigo del boleto.', 'Error', $code), ResponseData::HTTP_NOT_FOUND);
                }

                $ticketsAsientos = TicketsAsientos::with(['ticket' => function( $ticket )
                {
                    $ticket->select('id', 'fecha', 'horario', 'temporada', 'eventos_id', 'zona', 'fila', 'status', 'payed', 'is_generate_for_seat')->where([ ["status", ">", 2 ], [ "payed", "=", true ] ]);

                }])->where('qr', '=', $code)->first();

                if (is_null($ticketsAsientos))
                {
                    return response()->json( new DataResponse('El codigo ingresado no se ha encontrado', 'Error', $code), ResponseData::HTTP_NOT_FOUND);
                }

                if (is_null($ticketsAsientos->ticket))
                {
                    return response()->json(new DataResponse('El ticket al que pertenece el boleto no es valido', 'Error', $code));
                }

                if ($ticketsAsientos->ticket->temporada)
                { // Verificación de boletos para una temporada

                    $matchTicket = Partidos::find($ticketsAsientos->ticket->eventos_id);

                    // Se toma el primero porque se entiende que solo pueden jugar un partido por día en una temporada ( femenil o varonil u otro)
                    $matchFound =  Partidos::where( 'id_match_season', '=', $matchTicket->id_match_season )->whereDate( 'fecha','=', Carbon::now()->toDateString() )->first();

                    if (!is_null($matchFound))
                    {
                        //
                        $arraySeatGenerateForGroup = [];

                        for ($i=0; $i < $ticketsAsientos->ticket->abonados->count() ; $i++)
                        {
                            if ($ticketsAsientos->ticket->is_generate_for_seat && $ticketsAsientos->ticket->abonados[$i]->id_ticket_seat == $ticketsAsientos->id)
                            {
                                $foundMatch = $ticketsAsientos->ticket->abonados[$i]->partidos->first(function ($value, $key) use ($matchFound)
                                {
                                    return $value->id == $matchFound->id;
                                });

                                if ($foundMatch)
                                {
                                    return response()->json(new DataResponse('El boleto de temporada ya ha sido verificado', 'Error', $code));
                                }
                                else
                                {
                                    AbonadoPartido::create(["id_subscribers" => $ticketsAsientos->ticket->abonados[$i]->id, "id_match" => $matchFound->id]);

                                    return response()->json(new DataResponse('Boleto de temporada verificado, Muchas Gracias', 'Verificado', $code));
                                }
                                break;
                            }
                            else if(!$ticketsAsientos->ticket->is_generate_for_seat)
                            {
                                $foundMatch = $ticketsAsientos->ticket->abonados[$i]->partidos->first(function ($value, $key) use ($matchFound)
                                {
                                    return $value->id == $matchFound->id;
                                });

                                if ($foundMatch)
                                {
                                    return response()->json(new DataResponse('Los boletos de temporada ya han sido verificados', 'Error', $code));
                                }
                                else
                                {
                                    $arraySeatGenerateForGroup = Arr::prepend($arraySeatGenerateForGroup, ["id_subscribers" => $ticketsAsientos->ticket->abonados[$i]->id, "id_match" => $matchFound->id]);
                                }
                            }
                        }

                        if (count($arraySeatGenerateForGroup) > 0)
                        {
                            AbonadoPartido::insert($arraySeatGenerateForGroup);

                            return response()->json(new DataResponse('Boletos de temporada verificados, Muchas Gracias', 'Verificados', $code));
                        }

                        return response()->json(new DataResponse('No se encontro que el abono pertenezca a algun ticket', 'Error', $code));
                    }
                    else
                    {
                        return response()->json( new DataResponse('No se encontro partido para el abono ingresado', 'Error', $code), ResponseData::HTTP_NOT_FOUND);
                    }
                }
                else
                { // Verificación de boletos para un unico partido

                    if ($ticketsAsientos->status == EstatusAsientosEnum::VERIFICADO)
                    {
                        return response()->json(new DataResponse('Este boleto ya ha sido verificado', 'Error', $code));
                    }
                    else
                    {
                        $responseCompare = $this->compareDateWithToday(Partidos::find($ticketsAsientos->ticket->eventos_id)->fecha);

                        if ($responseCompare->valid)
                        {
                            $ticketsAsientos->status = EstatusAsientosEnum::VERIFICADO;
                            $ticketsAsientos->save();

                            return response()->json(new DataResponse('Boleto verificado, Muchas Gracias', 'Verificado', $code));
                        }
                        else
                        {
                            return response()->json(new DataResponse($responseCompare->message, 'Ocurrió un error', $code));
                        }
                    }
                }
            }
            catch (\Throwable $th)
            {
                return response()->json(new DataResponse("No se pudo verificar su boleto", 'Ocurrió un error', $code));
            }
        }

/***
 *
 *
 *
 */
    public function ticketSeatCodes($eventId, $seatCode)
    {
        try{
            $validator = Validator::make(
                [
                    'eventId' => $eventId,
                    'seatCode' => $seatCode
                ],
                [
                    'eventId' => 'required|numeric',
                    'seatCode' => 'required'
                ]
            );
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error of validation',
                    'error' => $validator->errors()
                ], 400);
            }
    
            $subQuery = DB::table('tickets_asiento as ta2')
                ->select('ti2.id')
                ->leftJoin('tickets as ti2', 'ta2.tickets_id', '=', 'ti2.id')
                ->where('ta2.code', '=', $seatCode)
                ->where('ti2.eventos_id', '=', $eventId);
    
            $tickets = DB::table('tickets_asiento as ta')
                ->select('ta.id as seat_id', 'ti.id as ticket_id', 'ta.code', 'ti.status')
                ->leftJoin('tickets as ti', 'ta.tickets_id', '=', 'ti.id')
                ->whereIn('ti.id', $subQuery)
                ->where('ta.eventos_id', '=', $eventId)
                ->get();

            if($tickets->isEmpty()){
                return response()->json([
                    'message' => 'Error',
                    'error' => 'Ticket not found'
                ], 404);
            }
    
            $tickets = $tickets->map(function ($ticket) {
                return [
                    'ticket_id' => $ticket->ticket_id,
                    'ticket_status' => $ticket->status,
                    'seat_id' => $ticket->seat_id, 
                    'seat_code' => $ticket->code,
                ];
            });
    
             return response()->json([
                'message' => 'Success',
                'data' => $tickets
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function deleteSeatFromTicket($ticketId, $seatId)
    {
        try{
            $validador = Validator::make(
                [
                    'ticketId' => $ticketId,
                    'seatId' => $seatId
                ],
                [
                    'ticketId' => 'required|numeric',
                    'seatId' => 'required'
                ]
            );

            if($validador->fails()){
                return response()->json([
                    'message' => 'Error of validation',
                    'error' => $validador->errors()
                ]);
            }

            $ticket = Tickets::find($ticketId);
            $seat = TicketsAsientos::find($seatId);

            if (!$ticket || !$seat) {
                return response()->json([
                    'message' => 'Ticket or seat not found'
                ], 404);
            }
            
            if ($seat->tickets_id != $ticket->id) {
                return response()->json([
                    'message' => 'The seat does not belong to the ticket'
                ], 400);
            }
           

            //calculamos el nuevo tatal del ticket
            $newTotal = ($ticket->total / $ticket->asientos->count()) * ($ticket->asientos->count() - 1);
            //eliminamos el asiento del ticket
            $seat->delete();
            //actualizamos el total del ticket
            $ticket->update(['total' => $newTotal]);

            return response()->json([
                'message' => 'Success, has been deleted the seat',
                'ticket' => $ticket
            ], 200);
    
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancelTicket($ticketId)
    {
        try{
            $validador = Validator::make(
                [
                    'ticketId' => $ticketId
                ],
                [
                    'ticketId' => 'required|numeric'
                ]
            );

            if($validador->fails()){
                return response()->json([
                    'message' => 'Error of validation',
                    'error' => $validador->errors()
                ]);
            }

            $ticket = Tickets::find($ticketId);
            if(!$ticket){
                return response()->json([
                    'message' => 'Error',
                    'error' => 'Ticket not found'
                ], 404);
            }

            $ticket->update(['status' => 0, 'total' => 0]);

            return response()->json([
                'message' => 'Success, has been canceled the ticket',
                'ticket' => $ticket
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage()
            ]);
        }

    }

    public function transferSeatOfTicket(Request $request)
    {
        try {

            DB::beginTransaction();

            $request->validate([
                'ticket_id' => 'required',
                'seat_codes' => 'required|array',
                'user_receiver_email' => 'required|email',
            ]);

            $ticket = Tickets::find($request->ticket_id);

            if(!$ticket) { //$ticket->is_generate_for_seat != 0
                return response()->json([
                    'message' => 'Ticket not found or not allowed to transfer seats'
                ], 500);
            }

            $user = User::where('correo', $request->user_receiver_email)->first();
            if(!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            $seats = TicketsAsientos::whereIn('code', $request->seat_codes)->where('tickets_id', $ticket->id)->get();
            if($seats->count() != count($request->seat_codes)) {
                return response()->json([
                    'message' => 'Some seats not found'
                ], 404);
            }

            //creamos un nuevo ticket
            $newTicket = $ticket->replicate();
            $newTicket->users_id = $user->id;
            $newTicket->user_sender_id = $ticket->users_id;
            $newTicket->user_receiver_id = $user->id;
            $newTicket->total = 0;
            $newTicket->save();

            //creamos las relaciones a nivel de base de datos para el nuevo ticket
            if($ticket->metodoCobro) {
               $newTicket->id_method_payment = $ticket->metodoCobro->id;
               $newTicket->save();
            }

            foreach ($ticket->asientosCambiados as $asientoCambiado) {
                $newAsientoCambiado = $asientoCambiado->replicate();
                $newAsientoCambiado->id_ticket = $newTicket->id;
                $newAsientoCambiado->save();
            }

            //actualizamos los asientos para que pertenezcan al nuevo ticket
            foreach ($seats as $seat) {
                $seat->tickets_id = $newTicket->id;
                // Asegurarse de que cada asiento tenga el código en su QR
                if (strpos($seat->qr, $seat->code) === false) {
                    $seat->qr .= '.' . $seat->code;
                }
                
                $seat->save();
            }
             /* foreach ($ticket->codigos as $imagen) {
                $newImagen = $imagen->replicate();
                $newImagen->rel_id = $newTicket->id;
                $newImagen->save();
            } */
            
           /*  if ($ticket->codigo) {
                $newImagen = $ticket->codigo->replicate();
                $newImagen->rel_id = $newTicket->id;
                $newImagen->save();
            } */


          /*   // Genera un código QR para el nuevo ticket
            $responseData = $this->validateForGenerateQr($newTicket); */

            DB::commit();

            return response()->json([
                'message' => 'Success, has been transfer seat of ticket',
                'ticket' => $newTicket
            ], 200);


        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error to transfer seat of ticket',
                'error' => $e->getMessage()
            ]);
        }
    }
}
