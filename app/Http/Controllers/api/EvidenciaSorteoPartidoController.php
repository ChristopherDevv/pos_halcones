<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use Illuminate\Http\Request;
use App\Models\Sorteo;
use Illuminate\Support\Carbon;
use App\Models\Partidos;
use App\Models\SorteoPartido;
use App\Models\Tickets;
use App\Models\SorteoUsuario;
use App\Models\TicketsAsientos;
use App\Models\EvidenciaSorteoPartido;
use App\Models\CodigoEvidenciaSorteoPartido;
use App\Models\MultimediaEvidenciaSorteoPartido;
use PHPUnit\Util\Exception;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseStatusCodeSame;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Arr;
use App\Models\Interfaces\EstatusAsientosEnum;
use App\Models\Interfaces\TipoDePagos;
use App\Models\Interfaces\TipoDeTicket;
use App\Models\Interfaces\EstatusPartidos;
use Illuminate\Support\Str;


class EvidenciaSorteoPartidoController extends Controller
{
    public function index()
    {
        // try
        // {
        //     $sorteo = Sorteo::with('multimedia')->where('status','=','Activo')->get();

        //     $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$sorteo);

        //     return response()->json($response);

        // }
        // catch (\Exception $e)
        // {
        //     $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),null);

        //     return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        // }
    }

    public function show($id)
    {
        // try
        // {
        //     $sorteo = Sorteo::with('multimedia')->where([['status','=','Activo'], ['id', '=', $id]])->get();

        //     $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$sorteo);

        //     return response()->json($response);

        // }
        // catch (\Exception $e)
        // {
        //     $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),null);

        //     return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        // }
    }

    public function store(Request $request)
    {
        try
        {
            $id_raffle_user = $request->get('id_raffle_user');
            $id_raffle_match = $request->get('id_raffle_match');

            // Codigos Individuales
                $multimedia = $request->get('multimedia');
                $codes = $request->get('codes');

            // Codigos por Ticket
                $idTicket = $request->get('idTicket');

            // Codigo por Abono
                $codeAbono = $request->get('numberTicket');

            // Validación de participación de usuario en sorteo.
                $sorteoUsuario = null;

                if ($id_raffle_user)
                {
                    $sorteoUsuario = SorteoUsuario::with('sorteo')->find($id_raffle_user);
                    if (!$sorteoUsuario)
                    {
                        $response = new DataResponse(ErroresExceptionEnum::OBJECT_NECESARY()->getMessage(),ErroresExceptionEnum::OBJECT_NECESARY()->getCode(), "Sorteo Usuario" );

                        return response()->json($response, Response::HTTP_NOT_FOUND);
                    }
                }
                else
                {
                    $response = new DataResponse(ErroresExceptionEnum::OBJECT_NECESARY()->getMessage(),ErroresExceptionEnum::OBJECT_NECESARY()->getCode(), "Participacion de Usuario" );

                    return response()->json($response, Response::HTTP_NOT_FOUND);
                }

            // Busqueda de partido vinculado con sorteo.
                $sorteoPartido = null;
                if ($id_raffle_match)
                {
                    $sorteoPartido = SorteoPartido::with(['sorteo','partido'])->find($id_raffle_match);
                }

            // Comprueba si es obligatorio el partido.
                if ($sorteoUsuario -> sorteo -> matchNecesary == 1 && !$sorteoPartido)
                {
                    $response = new DataResponse(ErroresExceptionEnum::OBJECT_NECESARY()->getMessage(),ErroresExceptionEnum::OBJECT_NECESARY()->getCode(), "Partido Sorteo" );

                    return response()->json($response, Response::HTTP_NOT_FOUND);
                }

            // Comprueba si el sorteo del partido y el usuario es el mismo.
                if  ( $sorteoPartido && $sorteoPartido -> id_raffle != $sorteoUsuario -> id_raffle )
                {
                    $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "El sorteo del partido y el usuario no coinciden" );

                    return response()->json($response,Response::HTTP_NOT_FOUND);
                }

            // Se decartan boletos que no tengan folio o asiento.
                if (Arr::accessible($codes))
                {
                    $codes = Arr::where($codes, function ($code, $key)
                    {
                        return !empty($code['code']) && !empty($code['seat']);
                    });

                    foreach($codes as &$elemento)
                    {
                        $elemento['seat'] = Str::of($elemento['seat'])->upper();
                    }
                }

            // Se decartan multimedia que no tengan imagen.
                if (Arr::accessible($multimedia))
                {
                    foreach($multimedia as &$elemento)
                    {
                        $elemento['type'] = Str::title($elemento['type']);
                    }

                    $multimedia = Arr::where($multimedia, function ($multimedia, $key)
                    {
                        return !empty($multimedia['type']) && !empty($multimedia['name']) && Str::contains($multimedia['type'], 'Imagen');
                    });
                }

            // Busqueda de tickets_asiento que pertenecen al id_ticket recibido.
                $codesTickets = null;
                if ($idTicket)
                {
                    $whereOption = [
                        ['users_id','=',$sorteoUsuario ->id_user],
                        ['id','=',$idTicket],
                        ['status', "!=", EstatusAsientosEnum::DESHABILITADO],
                        ['payed', '=', 1],
                        ['type_payment', '!=', TipoDePagos::CORTESIA]
                    ];

                    if ($sorteoPartido)
                    {
                        $whereOption = array_merge($whereOption, [['eventos_id','=',$sorteoPartido -> id_match]]);
                    }

                    $tickets = Tickets::with([ 'asientoTicket', 'asientosCambiados' ]) -> where($whereOption)->first();

                    if ($tickets)
                    {
                        $seatsTemp = $tickets-> asientoTicket-> concat($tickets->asientosCambiados);

                        $codesTemp = collect([]);

                        $seatsTemp-> each(function ($seat, $key)  use (&$codesTemp)
                        {
                            $codesTemp -> push([
                                'code'=> $seat-> folio,
                                'seat'=> $seat-> code
                            ]);
                        });

                        $codesTickets = array_values($codesTemp->toArray());
                    }
                    else
                    {
                        $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "Ticket");

                        return response()->json($response,Response::HTTP_NOT_FOUND);
                    }
                }

            // Se buscan todos los codigos pertenecientes al abono
                $codesAbono = null;
                $seatTicketAbono = null;
                if ($codeAbono)
                {
                    $codeExplode = explode(".", $codeAbono);

                    if ( $codeExplode && Arr::accessible($codeExplode) && count($codeExplode) >= 4 )
                    {
                        $codeTicket = $codeExplode[0].'.'.$codeExplode[1].'.'.$codeExplode[2].'.'.$codeExplode[3];

                        $tickets = Tickets::with('partido.sorteoPartido','asientosCambiados','asientoTicket') -> where([
                                                ['type_ticket', '!=', TipoDeTicket::REGULAR],
                                                ['type_ticket', '!=', TipoDeTicket::BOLETO_VIP],
                                                ['type_payment', '!=', TipoDePagos::CORTESIA],
                                                ['code','=', $codeTicket]
                                            ])->first();

                        if ($tickets)
                        {
                            if ($sorteoUsuario-> sorteo-> matchNecesary == 1)
                            {
                                $participateRaffle = $tickets->partido->sorteoPartido->contains(function ($sorteoPartidoTemp, $key) use ($sorteoPartido)
                                {
                                    return $sorteoPartidoTemp->id_raffle == $sorteoPartido-> id_raffle;
                                });

                                // Obtención de los ticket_asiento solo si la temporada y el sorteo son los mismos.
                                if ( $tickets->partido->id_match_season == $sorteoPartido-> partido-> id_match_season && $participateRaffle)
                                {
                                    $seatsTemp = $tickets->asientoTicket->concat($tickets->asientosCambiados);

                                    $seatTicketAbono = $seatsTemp;

                                    $codesTemp = collect([]);

                                    $seatsTemp->each(function ($seat, $key)  use (&$codesTemp)
                                    {
                                        $codesTemp -> push([
                                            'code'=> $seat->folio,
                                            'seat'=> $seat->code,
                                        ]);
                                    });

                                    $codesAbono  = array_values($codesTemp->toArray());
                                }
                                else
                                {
                                    $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "El abono no pertenece a la temporada o al sorteo");

                                    return response()->json($response,Response::HTTP_NOT_FOUND);
                                }
                            }
                            else
                            {
                                // Hacer la busqueda cuando solo se necesite el codigo
                                // $tickets->asientoTicket->concat($tickets->asientosCambiados);

                                // $codesTemp = collect([]);

                                // $seatsTemp->each(function ($seat, $key)  use (&$codesTemp)
                                // {
                                //     $codesTemp -> push([
                                //         'code'=> $seat->folio,
                                //         'seat'=> $seat->code,
                                //     ]);
                                // });

                                // $codesAbono  = array_values($codesTemp->toArray());
                            }
                        }
                        else
                        {
                            $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "Abono");

                            return response()->json($response,Response::HTTP_NOT_FOUND);
                        }
                    }
                }

            //Comprobación de listas y concatenación de codigos.
                if ( !Arr::accessible($codes) && !Arr::accessible($codesTickets) && !Arr::accessible($codesAbono)  )
                {
                    $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "Codigos");

                    return response()->json($response,Response::HTTP_NOT_FOUND);
                }

                if ( Arr::accessible($codesTickets) && count($codesTickets) > 0 )
                {
                    $codes = array_merge($codes, $codesTickets);
                }

                if ( Arr::accessible($codesAbono) && count($codesAbono) > 0 )
                {
                    $codes = array_merge($codes, $codesAbono);
                }

                $codes = array_unique($codes, SORT_REGULAR);

            // Comprueba el tipo de sorteo y valida las evidencias que son obligatorias o no.
                $accessibleMultimedia = Arr::accessible($multimedia) && count($multimedia) > 0;
                $accessibleCodes = Arr::accessible($codes) && count($codes) > 0;

                switch ( $sorteoUsuario->sorteo->type )
                {
                    case 'Evidencia':
                        if ( !$accessibleMultimedia || !$accessibleCodes )
                        {
                            $message =  "Evidencia y codigos.";

                            if ( !$accessibleMultimedia && $accessibleCodes )
                            {
                                $message = "Evidencia.";
                            }
                            else if( $accessibleMultimedia && !$accessibleCodes )
                            {
                                $message = "Codigos";
                            }

                            $response = new DataResponse(ErroresExceptionEnum::OBJECT_NECESARY()->getMessage(),ErroresExceptionEnum::OBJECT_NECESARY()->getCode(), $message );
                            return response()->json($response,400);
                        }
                        break;
                    case 'EvidenciaCodigo':
                        if ( !$accessibleCodes )
                        {
                            $response = new DataResponse(ErroresExceptionEnum::OBJECT_NECESARY()->getMessage(),ErroresExceptionEnum::OBJECT_NECESARY()->getCode(), "Codigos" );
                            return response()->json($response,400);
                        }
                        break;
                    case 'EvidenciaMultimedia':
                        if ( !$accessibleMultimedia )
                        {
                            $response = new DataResponse(ErroresExceptionEnum::OBJECT_NECESARY()->getMessage(),ErroresExceptionEnum::OBJECT_NECESARY()->getCode(), "Evidencias" );
                            return response()->json($response,400);
                        }
                        break;
                }

            // Se busca la evidencia del usuario si es que existe, si no se crea.
                $evidenciaSorteoPartido = null;

                if (!$id_raffle_match)
                {
                    $evidenciaSorteoPartido = EvidenciaSorteoPartido::where("id_raffle_user",'=', $id_raffle_user)->first();
                }
                else
                {
                    $evidenciaSorteoPartido = EvidenciaSorteoPartido::where([["id_raffle_user",'=', $id_raffle_user], ["id_raffle_match",'=', $id_raffle_match]])->first();
                }

                if (!$evidenciaSorteoPartido)
                {
                    $evidenciaSorteoPartido =  EvidenciaSorteoPartido::create($request->only('id_raffle_match', 'id_raffle_user'));
                }


            if ($evidenciaSorteoPartido)
            {
                if ($sorteoPartido && $sorteoUsuario)
                {
                    /**
                     *  Se buscan todos los codigos existentes de el partido actual;
                    */
                        $match = Partidos::with(['tickets' => function($tickets)
                        {
                            $tickets->with([ 'asientoTicket', 'asientosCambiados' ])->where([ ['status', "!=", EstatusAsientosEnum::DESHABILITADO], ['payed', '=', 1] , ['type_payment', '!=', TipoDePagos::CORTESIA ] ]);
                        }
                        ])->where([['id', '=', $sorteoPartido -> id_match ]])->first();

                        if ($match)
                        {
                            $seatTicket = $match->tickets->pluck('asientoTicket')->collapse();

                            $seatChange = $match->tickets->pluck('asientosCambiados')->collapse();

                            $seats = $seatTicket-> concat($seatChange);

                            // Retorno todos los asientos que conciden con lo que envia el usuario
                                $seatFiltered = $seats->filter(function ($seat, $key) use ( $codes )
                                {
                                    return Arr::first($codes, function ($code, $key) use ($seat)
                                    {
                                        return $seat->folio == $code['code'] && $seat->code == $code['seat'] ;

                                    });

                                });


                            // Se agregan los asientos del abono si no se encuentran en codes, linea para el caso de que sea un partido distinto en el que se genero el ticket.
                                if (Arr::accessible( $seatTicketAbono ) && $seatTicketAbono->count() > 0)
                                {
                                    $seatTicketAbono = $seatTicketAbono-> filter(function ($seat, $key) use ( $seatFiltered )
                                    {
                                        return !$seatFiltered->contains(function ($seatFilt, $key) use ($seat) {
                                            return $seatFilt-> code == $seat->code && $seatFilt-> folio == $seat->folio ;
                                        });

                                    });

                                    if ($seatTicketAbono->count())
                                    {
                                        $seatFiltered  = $seatFiltered ->concat($seatTicketAbono);
                                    }
                                }


                            // Busco todos los codigos que ya se han registrado
                                $sorteoPartido = SorteoPartido::with('evidenciaSorteoPartido.codigoEvidenciaSorteoPartido')->find($evidenciaSorteoPartido->id_raffle_match);

                                $codesUsed = collect([]);

                                if ($sorteoPartido)
                                {
                                    $codesUsed = $sorteoPartido-> evidenciaSorteoPartido->pluck('codigoEvidenciaSorteoPartido')->collapse()->pluck('code');
                                }

                            // Se filtran todos aquellos asientos que ya esten registrados
                                $finalSeats = $seatFiltered->filter(function ($seat, $key) use ($codesUsed)
                                {
                                    return  !$codesUsed->contains(function ($code, $key) use ($seat)
                                    {
                                        $folioTemp = $seat->folio == null || $seat->folio == "null" ? 0 : $seat->folio;

                                        return Str::contains($code, $seat->id.'_'.$folioTemp.'_'.$seat->code);
                                    });
                                });


                            $codigoEvidenciaSorteoPartidoArray = collect([]);

                            $finalSeats->each(function ($seat, $key) use(&$codigoEvidenciaSorteoPartidoArray, $evidenciaSorteoPartido) {

                                $folioTemp = $seat->folio == null || $seat->folio == "null" ? 0 : $seat->folio;

                                $codigoEvidenciaSorteoPartidoArray->push([
                                    'id_evidence_raffle_match' => $evidenciaSorteoPartido->id,
                                    'code' => $seat->id.'_'.$folioTemp.'_'.$seat->code
                                ]);

                            });

                            if ( count($codigoEvidenciaSorteoPartidoArray) == 0 )
                            {
                                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "Los codigos no existen o fueron utilizados: ");

                                return response()->json($response,Response::HTTP_NOT_FOUND);
                            }

                            $insertSucces = CodigoEvidenciaSorteoPartido::insert(array_values($codigoEvidenciaSorteoPartidoArray->toArray()));

                            // Se guarda la imagen de  evidencia
                                if ($insertSucces)
                                {
                                    if ($multimedia && Arr::accessible($multimedia))
                                    {
                                        foreach ($multimedia as $key => $element)
                                        {
                                                $requestTemp = new Request;

                                                $requestTemp->merge(['id_evidence_raffle_match' => $evidenciaSorteoPartido-> id , 'name' => $element['name'], 'type' => $element['type']]);

                                                app(\App\Http\Controllers\api\ImagenesController::class)->storeMultimediaEvidenceRuffleMathc($requestTemp);
                                        }
                                    }
                                }

                            $evidenciaSorteoPartido->refresh();

                            $evidenciaSorteoPartido-> codigoEvidenciaSorteoPartido;

                            $evidenciaSorteoPartido-> multimediaEvidenciaSorteoPartido;

                            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$evidenciaSorteoPartido);

                            return response()->json($response);
                        }
                        else
                        {
                            $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "No se encontro el partido");

                            return response()->json($response,Response::HTTP_NOT_FOUND);
                        }
                }
                else if (!$sorteoPartido && $sorteoUsuario)
                {
                    $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "No se ha establecido como se hace el guardado de los codigos sin partido");

                    return response()->json($response,Response::HTTP_NOT_FOUND);
                }
            }
            else
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "No se puedo encontrar o registra la evidencia para el sorteo del partido");

                return response()->json($response,Response::HTTP_NOT_FOUND);
            }
        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),"Try catch error");

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function storeRaffleMatch(Request $request)
    {
        // try
        // {
        //     $id_raffle = $request->get('id_raffle');
        //     $id_user = $request->get('id_match') ;

        //     $existSorteoUsuario= SorteoPartido::where([ ['id_raffle',"=", $id_raffle ], ['id_match',"=", $id_user] ])->first();

        //     if ( $existSorteoUsuario )
        //     {
        //         $response = new DataResponse(ErroresExceptionEnum::OBJECT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_FOUND()->getCode(),null);

        //         return response()->json($response,Response::HTTP_NOT_FOUND);
        //     }

        //     $sorteoTemp = SorteoPartido::create($request->only('id_raffle', 'id_match', 'initial_date', 'finished_date'));

        //     $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$sorteoTemp);

        //     return response()->json($response);
        // }
        // catch (\Exception $e)
        // {
        //     $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),null);

        //     return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        // }
    }



    public function update($id, Request $request, Sorteo  $sorteo) {
        // try
        // {
        //     if ($id != $request->get('id'))
        //     {
        //         $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null );

        //         return response()->json($response,Response::HTTP_NOT_FOUND);
        //     }

        //     $sorteoTemp = $sorteo::find($id);

        //     $sorteoTemp-> price  = $request->get('price');

        //     $sorteoTemp-> save();

        //     $sorteoTemp-> refresh();

        //     $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(), $sorteoTemp );

        //     return response()->json($response);
        // }
        // catch (\Exception $e)
        // {
        //     $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),null);

        //     return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        // }
    }

    public function destroy($id, Sorteo  $sorteo)
    {
        // try
        // {
        //     $sorteoTemp = $sorteo::find($id);

        //     if (!$sorteoTemp)
        //     {
        //         $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null);

        //         return response()->json($response);
        //     }

        //     $sorteoTemp-> delete();

        //     $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getCode(), $sorteoTemp);

        //     return response()->json($response);

        // }
        // catch (\Throwable $e)
        // {

        //     $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getMessage().$e->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getCode(), null);

        //     return response()->json($response);
        // }
    }
}
