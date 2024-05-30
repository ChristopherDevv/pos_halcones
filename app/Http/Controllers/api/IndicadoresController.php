<?php

namespace App\Http\Controllers\api;

use Carbon\Carbon;
use App\Mail\Compra;
use App\Models\User;
use App\Mail\SendMail;
use App\Models\Orders;
use App\Models\Tallas;
use App\Models\Partidos;
use App\Models\Productos;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PHPUnit\Util\Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exports\TicketsExport;
use App\Mail\PedidoEnviadoMail;
use App\Models\OrdersProductos;
use App\Models\ProductosTallas;
use App\Models\TemporadaPartido;
use App\Mail\PedidoEntregadoMail;
use Illuminate\Support\Facades\DB;
use App\Mail\PedidoPorEntregarMail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Interfaces\TipoDePagos;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\TipoDeTicket;
use Illuminate\Support\Facades\Validator;
use App\Models\Interfaces\TipoDeReservacion;
use App\Models\Interfaces\EstatusOrdenesEnum;
use App\Models\Interfaces\EstatusAsientosEnum;
use App\Models\Interfaces\ErroresExceptionEnum;

class IndicadoresController extends Controller
{

    /**
     *
     * ZurielDA
     *
     */

    function SalesForDate($initialDate, $finishedDate)
    {

        try {

            $orders = Orders::select('*')->whereDate('creation_date', '>=', $initialDate)->whereDate('creation_date', '<=', $finishedDate)->where('status', '=', EstatusOrdenesEnum::COMPLETED)->get();

            $dataStatistic = collect([]);

            $orders->each(function ($item, $key) use (&$dataStatistic) {
                if ($dataStatistic->contains('name', $item->creation_date->isoFormat('DD-MM-Y'))) {

                    $dataStatistic = $dataStatistic->map(function ($order) use ($item) {

                        if ($order['name'] == $item->creation_date->isoFormat('DD-MM-Y')) {

                            $order['value'] += $item->total;

                            $order['extra']['cant'] += $item->cant_total;
                        }

                        return $order;
                    });
                } else {
                    $dataStatistic->push([
                        'name'  => $item->creation_date->isoFormat('DD-MM-Y'),
                        'value' => $item->total,
                        'extra' => [
                            'cant' => $item->cant_total
                        ]
                    ]);
                }
            });

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), true, $dataStatistic);

            return response()->json($response);
        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, []);

            return response()->json($response);
        }
    }

    function ProductsSale($initialDate, $finishedDate)
    {
        try {

            $products = Orders::select('id')->with('productos')->whereDate('creation_date', '>=', $initialDate)->whereDate('creation_date', '<=', $finishedDate)->where('status', '=', EstatusOrdenesEnum::COMPLETED)->get()->pluck('productos')->collapse();

            $dataStatistic = collect([]);

            $products->each(function ($item, $key) use (&$dataStatistic) {
                $isFound =  $dataStatistic->contains(function ($product, $key) use ($item) {

                    return $product['name'] == Str::of($item->title . ' | ' . $item->abreviacion_talla)->upper() &&  $product['extra']['size'] == $item->titulo_talla;
                });

                if ($isFound) {
                    $dataStatistic = $dataStatistic->map(function ($product) use ($item) {
                        if ($product['name'] == Str::of($item->title . ' | ' . $item->abreviacion_talla)->upper() &&  $product['extra']['size'] == $item->titulo_talla) {
                            $product['value'] += 1;
                        }

                        return $product;
                    });
                } else {
                    $dataStatistic->push([
                        'name'  => Str::of($item->title . ' | ' . $item->abreviacion_talla)->upper(),
                        'value' => 1,
                        'extra' => [
                            'idCategory' => $item->categorias->padre_id,
                            'idSubcategory' => $item->categorias->id,
                            'size' => $item->titulo_talla,
                            'img' => $item->images ? $item->images[0]->uri_path : null
                        ]
                    ]);
                }
            });

            $dataStatistic = $dataStatistic->sortByDesc('value');

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), true,  $dataStatistic->values()->all());

            return response()->json($response);
        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, []);

            return response()->json($response);
        }
    }

    public function detailsTicketsForPayment( $ticket ){
        // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // //
        $ticketsForPaymentMethod = collect([]);

        $ticket->groupBy('type_payment')->each(function ($tickets, $paymentMethod) use (&$ticketsForPaymentMethod)
        {
            if ($paymentMethod == TipoDePagos::CORTESIA)
            {
                $totalSale = 0;

                $tickets->pluck('total')->each(function ($total) use (&$totalSale)
                {
                    $totalSale += $total;
                });

                $allSeat = $tickets->pluck('asientoTicket')->collapse();

                $totalSeat = $allSeat->count();

                $seatForPrice = $allSeat->groupBy(function ($item)
                {
                    return sprintf('%.2f', data_get($item, 'precioAsiento.precioAsiento.price'));

                })->map(function ($seats, $price)
                {
                    return [
                        "price" => $price,
                        "seats" => $seats->count()
                    ];
                })->values()->toArray();

                $seatForZone = $allSeat->groupBy('zona')->map(function ($seats, $zone)
                {
                    $totalSale = 0;
                    $seats->pluck('precioAsiento.precioAsiento.price')->each(function ($price) use (&$totalSale)
                    {
                        $totalSale += $price;
                    });

                    return [
                        "zone" => $zone,
                        "seats" => $seats->count(),
                        "totalSale" => $totalSale
                    ];
                })->values()->toArray();

                $ticketsForPaymentMethod->put(TipoDePagos::TYPEPAYMENT($paymentMethod), [
                    "typePaymentMethod"=> TipoDePagos::TYPEPAYMENT($paymentMethod),
                    "ticketsTemp" => $tickets,
                    "totalSale" => $totalSale,
                    "totalSeat" => $totalSeat,
                    "seatForPrice" => $seatForPrice,
                    "seatForZone" => $seatForZone,
                    "totalTickets" => $tickets->count()
                ]);
            } else {
                if ($ticketsForPaymentMethod->contains(function ($value, $key){ return $key ==  "Venta"; }))
                {
                    $ticketsForPaymentMethod = $ticketsForPaymentMethod->map(function ($item, $key) use ($tickets)
                    {
                        if ($key == "Venta")
                        {
                            $item['ticketsTemp'] = $item['ticketsTemp']->concat($tickets);
                            $item['totalTickets'] = $item['ticketsTemp']->count();

                            $ticketsTypePayment = $item['ticketsTemp']->groupBy('type_payment')->map(function ($tickets, $paymentMethod) {

                                $unchangedTickets = $tickets->filter(function ($ticket)
                                {
                                    return $ticket-> asientosCambiados-> count() == 0;

                                });

                                $exchangedTickets = $tickets->filter(function ($ticket)
                                {
                                    return $ticket-> asientosCambiados-> count() > 0 ;

                                })-> map(function ($ticket)
                                {
                                    $ticket-> setRelation('asientoTicket', $ticket-> asientoTicket->concat( $ticket-> asientosCambiados-> pluck('ticketAsiento') ));

                                    return $ticket;
                                });

                                return [
                                    "unchangedTickets" => $this-> detailsTickets( $unchangedTickets, $paymentMethod),
                                    "exchangedTickets" => $this-> detailsTickets( $exchangedTickets, $paymentMethod)
                                ];

                            })->values()->toArray();


                            $totalSale = 0;
                            $totalSeat = 0;

                            foreach ($ticketsTypePayment as $ticketsTemp)
                            {
                                $totalSale += $ticketsTemp['unchangedTickets']['totalSale'];
                                $totalSeat += $ticketsTemp['unchangedTickets']['totalSeat'];

                                $totalSale += $ticketsTemp['exchangedTickets']['totalSale'];
                                $totalSeat += $ticketsTemp['exchangedTickets']['totalSeat'];
                            }

                            $item["totalSale"] = $totalSale;
                            $item["totalSeat"] = $totalSeat;


                            $item["tickets"] =  $ticketsTypePayment;
                        }

                        return $item;

                    });
                } else
                {
                    $ticketsTypePayment = $tickets->groupBy('type_payment')->map(function ($tickets, $paymentMethod)
                    {

                        $unchangedTickets = $tickets->filter(function ($ticket)
                        {
                            return $ticket-> asientosCambiados-> count() == 0;

                        });

                        $exchangedTickets = $tickets->filter(function ($ticket)
                        {
                            return $ticket-> asientosCambiados-> count() > 0 ;

                        })-> map(function ($ticket)
                        {
                            $ticket-> setRelation('asientoTicket', $ticket-> asientoTicket->concat( $ticket-> asientosCambiados-> pluck('ticketAsiento') ));

                            return $ticket;
                        });

                        return [
                            "unchangedTickets" => $this-> detailsTickets( $unchangedTickets, $paymentMethod),
                            "exchangedTickets" => $this-> detailsTickets( $exchangedTickets, $paymentMethod)
                        ];

                    })->values()->toArray();
                    //
                    $ticketsForType = $tickets->groupBy('type_ticket')->map(function ($tickets, $paymentMethod) {
                        $totalSale = 0;
                        $tickets->each(function ($ticket, $key) use (&$totalSale) {
                            $totalSale += $ticket->total;
                        });

                        return [
                            "tickets" => $tickets->count(),
                            "typeTicket" => TipoDeTicket::TYPETICKE($paymentMethod),
                            "totalSale" => $totalSale
                        ];
                    })->values()->toArray();

                    $ticketsForTypeReservation = $tickets->groupBy('type_reservation')->map(function ($tickets, $typeReservation) {
                        $totalSale = 0;
                        $tickets->each(function ($ticket, $key) use (&$totalSale) {
                            $totalSale += $ticket->total;
                        });

                        return [
                            "typeReservation" => $typeReservation,
                            "tickets" => $tickets->count(),
                            "totalSeat" =>  $tickets->pluck('asientoTicket')->collapse()->count(),
                            "totalSale" => $totalSale
                        ];
                    })->values()->toArray();
                    //

                    $totalSale = 0;
                    $totalSeat = 0;

                    foreach ($ticketsTypePayment as $ticketsTemp)
                    {
                        $totalSale += $ticketsTemp['unchangedTickets']['totalSale'];
                        $totalSeat += $ticketsTemp['unchangedTickets']['totalSeat'];

                        $totalSale += $ticketsTemp['exchangedTickets']['totalSale'];
                        $totalSeat += $ticketsTemp['exchangedTickets']['totalSeat'];
                    }

                    $ticketsForPaymentMethod->put("Venta", [
                        "typePaymentMethod"=> "Venta",
                        "totalTickets" => $tickets-> count(),
                        "totalSale" => $totalSale,
                        "totalSeat" => $totalSeat,
                        "ticketsTemp" => $tickets,
                        "tickets" => $ticketsTypePayment,
                        "ticketsForType" => $ticketsForType,
                        "ticketsForTypeReservation" => $ticketsForTypeReservation
                    ]);
                }
            }
        });

        return $ticketsForPaymentMethod->values()->toArray();;
        // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // // //
    }

    //  Metodo para matchAndTicketsAndSeatTickets(id)
    public function detailsTickets( $tickets, $paymentMethod)
    {
        $totalSale = 0;

        $tickets->pluck('total')->each(function ($total) use (&$totalSale) {
            $totalSale += $total;
        });

        $allSeat = $tickets->pluck('asientoTicket')->collapse();

        $totalSeat = $allSeat->count();

        $seatForPrice = $allSeat->groupBy(function ($item) {
            return sprintf('%.2f', data_get($item, 'precioAsiento.precioAsiento.price'));
        })->map(function ($seats, $price) {
            return [
                "price" => $price,
                "seats" => $seats->count()
            ];
        })->values()->toArray();

        $seatForZone = $allSeat->groupBy('zona')->map(function ($seats, $zone) {
            $totalSale = 0;
            $seats->pluck('precioAsiento.precioAsiento.price')->each(function ($price) use (&$totalSale) {
                $totalSale += $price;
            });

            return [
                "zone" => $zone,
                "seats" => $seats->count(),
                "totalSale" => $totalSale
            ];
        })->values()->toArray();

        return [
            "tickets" => $tickets->count(),
            "paymentMethod" => TipoDePagos::TYPEPAYMENT($paymentMethod),
            "totalSale" => $totalSale,
            "totalSeat" => $totalSeat,
            "seatForZone" => $seatForZone,
            "seatForPrice" => $seatForPrice
        ];
    }

    public function matchWithTicketsAndSeatTickets($id)
    {
        try {
            $partidos = Partidos::with([
                'tickets' => function ($tickets)
                {
                    $tickets->select([ 'id', 'eventos_id', 'fecha', 'horario', 'lugar', 'abono', 'temporada', 'code', 'zona', 'fila', 'total', 'creation_date', 'updated_date', 'status', 'type_reservation', 'payed', 'type_payment', 'type_ticket', 'is_generate_for_seat'])
                            ->with(
                                ['asientoTicket' => function ($asientoTicket)
                                {
                                    $asientoTicket->select(['id', 'tickets_id', 'id_seat_price', 'id_seat_price_subcription', 'zona', 'fila', 'code', 'status', 'id_grupo', 'tipo_grupo', 'grupo', 'change','creation_date', 'updated_date', 'folio', 'qr'])
                                                  ->with(['precioAsientoAbono'=>function($precioAsientoAbono)
                                                  {
                                                    $precioAsientoAbono->select('id','id_seat','id_seat_price', 'id_season', 'status', 'typePrice')
                                                                       ->with([
                                                                        'precioAsiento'=>function($precioAsiento)
                                                                        {
                                                                            $precioAsiento->select('id', 'price');
                                                                        }]);

                                                  },
                                                  'precioAsiento' => function ($precioAsiento)
                                                  {
                                                      $precioAsiento->select('id','id_seat','id_seat_price', 'id_season', 'status', 'typePrice')
                                                                    ->with([
                                                                    'precioAsiento'=>function($precioAsiento)
                                                                    {
                                                                        $precioAsiento->select('id', 'price');
                                                                    }]);
                                                  }])->where('change', '=', null);
                                },
                                'asientosCambiados' => function ($asientosCambiados)
                                {
                                    $asientosCambiados ->with([
                                        'ticketAsiento' => function ($ticketAsiento)
                                        {
                                            $ticketAsiento->select(['id', 'tickets_id', 'id_seat_price', 'id_seat_price_subcription', 'zona', 'fila', 'code', 'status', 'id_grupo', 'tipo_grupo', 'grupo', 'change','creation_date', 'updated_date', 'folio', 'qr'])
                                                          ->with(['precioAsientoAbono'=>function($precioAsientoAbono)
                                                          {
                                                            $precioAsientoAbono->select('id','id_seat','id_seat_price', 'id_season', 'status', 'typePrice')
                                                                               ->with([
                                                                                'precioAsiento'=>function($precioAsiento)
                                                                                {
                                                                                    $precioAsiento->select('id', 'price');
                                                                                }]);

                                                            },
                                                            'precioAsiento' => function ($precioAsiento)
                                                            {
                                                                $precioAsiento->select('id','id_seat','id_seat_price', 'id_season', 'status', 'typePrice')
                                                                                ->with([
                                                                                'precioAsiento'=>function($precioAsiento)
                                                                                {
                                                                                    $precioAsiento->select('id', 'price');
                                                                                }]);
                                                            }]);
                                        }
                                    ]);
                                }
                ])->where([['status', "!=", EstatusAsientosEnum::DESHABILITADO],['payed', '=', 1]]);

            }])->find($id);

            if (!$partidos)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null);
                return response()->json($response, Response::HTTP_NOT_FOUND);
            }


            $groupData = function($tickets, &$arrayPrice)
            {
                return $tickets->groupBy('type_reservation')->map(function ($value, $type_reservation) use (&$arrayPrice)
                {
                    $value = $value->groupBy('temporada')->map(function ($value, $season) use (&$arrayPrice)
                        {
                            $value = $value->groupBy('type_payment')->map(function ($value, $type_payment) use (&$arrayPrice, $season)
                                {
                                    $value = $value->filter(function ($ticket)
                                    {
                                        return $ticket->asientoTicket->count() > 0 || $ticket->asientosCambiados->count() > 0;

                                    })->values();

                                    $seatsForPrices = $value->pluck('asientoTicket')->collapse()->concat( $value->pluck('asientosCambiados')->collapse())->groupBy(function ($seat) use (&$arrayPrice, $season)
                                    {

                                        $price = sprintf('%.2f', data_get($seat, $season ? 'precioAsientoAbono.precioAsiento.price' : 'precioAsiento.precioAsiento.price' ));

                                        if(!in_array($price,$arrayPrice))
                                        {
                                            $arrayPrice = Arr::prepend($arrayPrice, $price);
                                        }

                                        return $price;

                                    })->map(function ($value, $price) use ($season)
                                    {
                                        return [
                                            "price" => (double)$price,
                                            "quantity"=>$value->count(),
                                            "total" => (double)sprintf('%.2f', $value->pluck($season ? 'precioAsientoAbono.precioAsiento.price' : 'precioAsiento.precioAsiento.price')->sum()),
                                            // "ticketsAsiento" => $value // Se comenta porque no se neceita información de los tickets
                                        ];

                                    })->values();

                                    return [
                                        "payment" => TipoDePagos::TYPEPAYMENT($type_payment),
                                        "totalQuantityTicketsSeat" => $seatsForPrices->pluck('quantity')->sum(),
                                        "totalSellTicketSeat" => $seatsForPrices->pluck('total')->sum(),
                                        "groupTicketsSeatPrices" => $seatsForPrices
                                    ];
                                })->values();



                            return [
                                "isSubscription" => $season ? true : false,
                                "totalQuantityTicketsSeat" => $value->pluck('totalQuantityTicketsSeat')->sum(),
                                "totalSellTicketSeat" => $value->pluck('totalSellTicketSeat')->sum(),
                                "groupTicketsSeatTypePayment" => $value
                            ];
                        })->values();

                    return [
                        "typeReservation" => TipoDeReservacion::TYPERESERVATION($type_reservation),
                        "totalQuantityTicketsSeat" => $value->pluck('totalQuantityTicketsSeat')->sum(),
                        "totalSellTicketSeat" => $value->pluck('totalSellTicketSeat')->sum(),
                        "groupTicketsSeatSubscription" => $value
                    ];
                })->values();
            };

            $addValues = function($tickets, $arrayPrice)
            {
                return $tickets->transform(function ($ticket, $key) use ($arrayPrice)
                {
                    $ticket['groupTicketsSeatSubscription']->transform(function ($subscription, $key) use ($arrayPrice)
                    {
                        $subscription['groupTicketsSeatTypePayment']->transform(function ($typePayment, $key) use ($arrayPrice)
                        {
                            foreach ($arrayPrice as $price)
                            {
                                $first = Arr::first($typePayment['groupTicketsSeatPrices'], function ($value) use ($price)
                                {
                                    return $value['price'] == $price;
                                });

                                if($first == null)
                                {
                                    $typePayment['groupTicketsSeatPrices']->push([
                                        "price" => (double)$price,
                                        "quantity"=> 0,
                                        "total" => 0.00,
                                        // "ticketsAsiento" => [] // Se comenta porque no se neceita información de los tickets
                                    ]);
                                }
                            }

                            $typePayment['groupTicketsSeatPrices'] = $typePayment['groupTicketsSeatPrices']->sortBy(function ($seatsForPrices, $key)
                            {
                                return intval($seatsForPrices['price']);

                            })->values();

                            return $typePayment;
                        });
                        return $subscription;
                    });
                    return $ticket;
                });
            };

            $arrayPrice = [];

            $ticketsSeat = $addValues($groupData($partidos->tickets, $arrayPrice), $arrayPrice);

            $ticketsSeatsForDate = $partidos->tickets->groupBy(function ($ticket)
            {
                return  $ticket['updated_date']->set('minute',0)->set('second', 0)->toDateTimeString();

            })->map(function($value, $key) use ($groupData, $arrayPrice, $addValues)
            {
                return [
                    "date" => $key,
                    "ticketsSeat" =>  $addValues($groupData($value, $arrayPrice), $arrayPrice)
                ];

            })->values();


            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getCode(), [
                "match" => $partidos->makeHidden('tickets'),
                "ticketsSeat" => $ticketsSeat,
                "ticketsSeatsForDate" => $ticketsSeatsForDate
            ]);

            return response()->json($response);

        } catch (\Throwable $th) {

            return response()->json(new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getCode().$th->getMessage(), 'Tickets de Partidos'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function ticketsForMatch($id)
    {
        try {
            $partidos = Partidos::with([
                'tickets' => function ($tickets)
                {
                    $tickets->select([ 'id', 'eventos_id', 'fecha', 'horario', 'lugar', 'abono', 'temporada', 'code', 'zona', 'fila', 'total', 'creation_date', 'updated_date', 'status', 'type_reservation', 'payed', 'type_payment', 'type_ticket', 'is_generate_for_seat'])
                            ->with(
                                ['asientoTicket' => function ($asientoTicket)
                                {
                                    $asientoTicket->select(['id', 'tickets_id', 'id_seat_price', 'id_seat_price_subcription', 'zona', 'fila', 'code', 'status', 'id_grupo', 'tipo_grupo', 'grupo', 'change','creation_date', 'updated_date', 'folio', 'qr'])
                                                  ->with(['precioAsientoAbono'=>function($precioAsientoAbono)
                                                  {
                                                    $precioAsientoAbono->select('id','id_seat','id_seat_price', 'id_season', 'status', 'typePrice')
                                                                       ->with([
                                                                        'precioAsiento'=>function($precioAsiento)
                                                                        {
                                                                            $precioAsiento->select('id', 'price');
                                                                        }]);
                                                  },
                                                  'precioAsiento' => function ($precioAsiento)
                                                  {
                                                      $precioAsiento->select('id','id_seat','id_seat_price', 'id_season', 'status', 'typePrice')
                                                                    ->with([
                                                                    'precioAsiento'=>function($precioAsiento)
                                                                    {
                                                                        $precioAsiento->select('id', 'price');
                                                                    }]);
                                                  }])->where('change', '=', null);
                                },
                                'asientosCambiados' => function ($asientosCambiados)
                                {
                                    $asientosCambiados ->with([
                                        'ticketAsiento' => function ($ticketAsiento)
                                        {
                                            $ticketAsiento->select(['id', 'tickets_id', 'id_seat_price', 'id_seat_price_subcription', 'zona', 'fila', 'code', 'status', 'id_grupo', 'tipo_grupo', 'grupo', 'change','creation_date', 'updated_date', 'folio', 'qr'])
                                                          ->with(['precioAsientoAbono'=>function($precioAsientoAbono)
                                                          {
                                                            $precioAsientoAbono->select('id','id_seat','id_seat_price', 'id_season', 'status', 'typePrice')
                                                                               ->with([
                                                                                'precioAsiento'=>function($precioAsiento)
                                                                                {
                                                                                    $precioAsiento->select('id', 'price');
                                                                                }]);
                                                            },
                                                            'precioAsiento' => function ($precioAsiento)
                                                            {
                                                                $precioAsiento->select('id','id_seat','id_seat_price', 'id_season', 'status', 'typePrice')
                                                                                ->with([
                                                                                'precioAsiento'=>function($precioAsiento)
                                                                                {
                                                                                    $precioAsiento->select('id', 'price');
                                                                                }]);
                                                            }]);
                                        }
                                    ]);
                                }
                ]);

            }])->find($id);

            if (!$partidos)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "Partido");

                return response()->json($response);
            }


            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getCode(),  $partidos->tickets );

            return response()->json($response);

        } catch (\Throwable $th) {

            return response()->json(new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getCode().$th->getMessage(), 'Tickets de Partidos'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function attendanceForMatch( Int $idMatch)
    {
        try {
            // Tickets comprandos unicamente para un partido
            $partidos = Partidos::with(['tickets' => function($tickets)
            {
                $tickets->select('id', 'eventos_id', 'temporada', 'status', 'type_reservation', 'payed', 'type_payment', 'type_ticket', 'is_generate_for_seat', 'id_method_payment')->with(['asientoTicket' => function($asientoTicket)
                {
                    $asientoTicket->select('id', 'tickets_id','zona', 'fila', 'status', 'id_grupo')->with('grupo');

                }])->where([ ['status', "!=", EstatusAsientosEnum::DESHABILITADO], ['payed', '=', 1], ['temporada', '!=', true]] );

            }])->find($idMatch);

            if (!$partidos)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "Partido");

                return response()->json($response);
            }

            //Tickets de abonados
            $dataSubscriptionForMatch = $this->subscriptionForSeason($partidos->id_match_season)->getData(true);

            if($dataSubscriptionForMatch['status'] != ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getCode() )
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "Temporada");

                return response()->json($response);
            }

            $partidos->setRelation('tickets', $partidos->tickets->groupBy("type_payment")->map( function($type_payment, $key)
            {
                return [
                    'typePayment' => TipoDePagos::TYPEPAYMENT($key),
                    'ticketsSeatsForGroup' => $type_payment->pluck('asientoTicket')->collapse()->groupBy(function ($grupo)
                    {
                        return $grupo->grupo ? $grupo->grupo->nombre : 'Sin Grupo';

                    })->map(function($value, $key)
                    {
                        return [ 'group' => $key, 'tickets' => $value ];

                    })->values()
                ];
            }
            )->values());

            $temporadaPartido = collect($dataSubscriptionForMatch['data']['partidos'])->pluck('tickets')->collapse()->groupBy("type_payment")->map( function($type_payment, $key) use ($idMatch)
            {
                return [
                    'typePayment' => TipoDePagos::TYPEPAYMENT($key),
                    'ticketsSeatsForGroup' => $type_payment->pluck('abonados')->collapse()->map(function($abonado) use ($idMatch)
                                    {
                                        $first = Arr::first($abonado['partidos'], function ($abonoPartido) use ($idMatch)
                                        {
                                            return $abonoPartido['pivot']['id_match'] === $idMatch;
                                        });

                                        if ($first)
                                        {
                                            $abonado['ticket_asiento']['status'] = 6;
                                        }

                                        return $abonado;
                                    })->map(function($value, $key) use ($idMatch){

                                        $valueTemp = $value;

                                        $value['ticket_asiento']['match'] = Arr::first($valueTemp['partidos'], function ($value, $key) use ($idMatch){
                                            return $value['pivot']['id_match'] === $idMatch;
                                        });

                                        unset($valueTemp['ticket_asiento']);
                                        unset($valueTemp['partidos']);

                                        $value['ticket_asiento']['user'] = $valueTemp;

                                        return $value;

                                    })->pluck('ticket_asiento')->groupBy(function ($grupo)
                                    {
                                        return $grupo['grupo'] ? $grupo['grupo']['nombre'] : 'Sin Grupo';
                                    })->map(function($value, $key)
                                    {
                                        return [ 'group' => $key, 'tickets' => $value ];

                                    })->values()
                ];
            })->values();


            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getCode(), [ 'temporada'=> $temporadaPartido, 'partido' => $partidos->tickets ] );

            return response()->json($response);

        } catch (\Throwable $th) {

            return response()->json(new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getCode().$th->getMessage(), 'Tickets de Partidos'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function subscriptionForSeason($idSeason)
    {
        try {
            // Recuperar todos los tickets que son abonos de cualquier partido
            $temporadaPartido = TemporadaPartido::with(['partidos'=> function($partidos)
            {
                $partidos->select('id', 'id_match_season', 'fecha','horario', 'titulo')->with(['tickets' => function($tickets)
                {
                    $tickets->select('id', 'eventos_id', 'temporada', 'status', 'type_reservation', 'payed', 'type_payment', 'type_ticket', 'is_generate_for_seat', 'id_method_payment')->with(['abonados' => function($abonados)
                    {
                        $abonados->select('id','id_ticket', 'id_ticket_seat', 'holder', 'name', 'paternalSurname', 'maternalSurname')
                                 ->with(['ticketAsiento' => function($ticketAsiento)
                                          {
                                            $ticketAsiento->select('id', 'tickets_id','zona', 'fila', 'status', 'id_grupo')->with('grupo');
                                          },
                                          'partidos' => function($partidos)
                                          {
                                            $partidos->select('id_match_season', 'fecha', 'horario', 'titulo');
                                          }
                                 ]);
                    }])->where([ ['status', "!=", EstatusAsientosEnum::DESHABILITADO], ['payed', '=', 1], ['temporada', '=', true]] );
                }]);
            }])->find($idSeason);


            if (!$temporadaPartido)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), "Temporada");

                return response()->json($response);
            }

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getCode(), $temporadaPartido);

            return response()->json($response);

        } catch (\Throwable $th) {

            return response()->json(new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getCode().$th->getMessage(), 'Tickets de Partidos'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**  
    *
    * CheistoperPatiño
    *
    */

    public function ticketsSold(Request $request)
    {
        try {
            $ids = explode(',', $request->query('ids'));

            $validator = Validator::make(['ids' => $ids], [
                'ids' => 'required|array',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            $export = new TicketsExport($ids);
            $data = $export->collection();
    
            if ($data === null) {
                return response()->json([
                    'message' => 'Error to export file',
                    'status' => false,
                    'error' => 'there are no tickets to export'
                ], 400);
            }
    
            return Excel::download($export, 'tickets-vendidos.xlsx');
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error to export file',
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

     /**
     *
     * Christoper Patiño
     *
     */

     public function findSeatCode($email, $eventId) 
{
    try {

        $validator = Validator::make(
            [
                'email' => $email,
                'eventId' => $eventId
            ],
            [
                'email' => 'required|email',
                'eventId' => 'required|numeric'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid data'
            ]);
        }
       
        $seatCodeAndStatus = DB::table('tickets_asiento as ta')
            ->leftJoin('tickets as ti', 'ta.tickets_id', '=', 'ti.id')
            ->leftJoin('partidos as p', 'ti.eventos_id', '=', 'p.id')
            ->leftJoin('users as u', 'ti.users_id', '=', 'u.id')
            ->where('u.correo', '=', $email)
            ->where('p.id', '=', $eventId)
            ->where('ti.payed', '!=', 0)
            ->select('ta.code', 'ta.status') // Aquí agregamos 'ta.status' a la selección
            ->get();
        
            if($seatCodeAndStatus->count() <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Not found seat code for '.$email
                ]);
            }
            
            $seatCode = $seatCodeAndStatus->pluck('code');
            $seatStatus = $seatCodeAndStatus->pluck('status');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Seat code found',
                'seatCode' => $seatCode,
                'seatStatus' => $seatStatus
            ]);


    }catch (\Exception $e) {
        return response()->json(null);
    }
}




}
