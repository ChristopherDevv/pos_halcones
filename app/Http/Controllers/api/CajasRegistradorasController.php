<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use Illuminate\Http\Request;
use App\Models\CajasRegistradoras;
use App\Models\Sucursales;
use App\Models\Tickets;
use Laravel\Ui\Presets\React;
use PHPUnit\Util\Exception;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseStatusCodeSame;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Interfaces\EstatusAsientosEnum;

class CajasRegistradorasController extends Controller
{
    public function index()
    {
        try {
            $cajasRegistradoras = CajasRegistradoras::all();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(), $cajasRegistradoras);

            return response()->json($response);
        } catch (\Exception $e) {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage() . $e->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(), null);

            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {

        try {
            $cajaRegistradora = CajasRegistradoras::find($id);

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getCode(), $cajaRegistradora);

            return response()->json($response);
        } catch (\Exception $e) {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage() . $e->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(), null);

            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request, CajasRegistradoras  $cajasRegistradoras)
    {

        try {
            $cajaRegistradora =  $cajasRegistradoras->create($request->only('id_sucursal', 'name', 'description'));

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(), $cajaRegistradora);

            return response()->json($response);
        } catch (\Exception $e) {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage() . $e->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(), null);

            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($id, Request $request, CajasRegistradoras  $cajasRegistradoras)
    {
        try {
            $cajaRegistradora = $cajasRegistradoras::find($id);

            if ($id != $request->get('id') || !$cajaRegistradora) {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null);

                return response()->json($response, Response::HTTP_NOT_FOUND);
            }

            $cajaRegistradora->id_sucursal = $request->get('id_sucursal');
            $cajaRegistradora->name = $request->get('name');
            $cajaRegistradora->description = $request->get('description');

            $cajaRegistradora->save();

            $cajaRegistradora->refresh();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(), $cajaRegistradora);

            return response()->json($response);
        } catch (\Exception $e) {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage() . $e->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(), null);

            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id, CajasRegistradoras  $cajasRegistradoras)
    {

        try {

            $cajaRegistradora = $cajasRegistradoras::find($id);

            if (!$cajaRegistradora) {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null);

                return response()->json($response);
            }

            $cajaRegistradora->delete();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getCode(), $cajaRegistradora);

            return response()->json($response);
        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getCode(), null);

            return response()->json($response);
        }
    }

    public function cashCut(Request $request)
    {
        try {

        $idBranchOffice = $request->route('idBranchOffice');
        $idCashRegister = $request->route('idCashRegister');
        $startDate = $request->query('start');
        $endDate = $request->query('end');

        $sucursal = Sucursales::with(['cajasRegistradoras' => function ($cajasRegistradoras) use ($idCashRegister, $startDate, $endDate)
        {
            $cajasRegistradoras->with(['registrosCajas' => function ($registrosCajas) use ($startDate, $endDate)
            {
                $registrosCajas->with(['tickets'=>function($tickets)
                {
                    $tickets->select([ 'id', 'id_registro_caja' ,'eventos_id', 'fecha', 'horario', 'lugar', 'abono', 'temporada', 'code', 'zona', 'fila', 'total', 'creation_date', 'updated_date', 'status', 'type_reservation', 'payed', 'type_payment', 'type_ticket', 'is_generate_for_seat' ])->with(['asientoTicket' => function ($asientoTicket)
                    {
                        $asientoTicket->select(['id', 'tickets_id', 'id_seat_price', 'id_seat_price_subcription', 'zona', 'fila', 'code', 'status', 'id_grupo', 'tipo_grupo', 'grupo', 'creation_date', 'updated_date', 'folio', 'qr']) ->with(['precioAsientoAbono.precioAsiento', 'precioAsiento.precioAsiento']);
                    },
                    'asientosCambiados' => function ($asientosCambiados)
                    {
                        $asientosCambiados ->with(['ticketAsiento' => function ($ticketAsiento)
                        {
                            $ticketAsiento->select(['id', 'tickets_id', 'id_seat_price', 'id_seat_price_subcription', 'zona', 'fila', 'code', 'status', 'id_grupo', 'tipo_grupo', 'grupo', 'creation_date', 'updated_date', 'folio', 'qr']) ->with(['precioAsientoAbono.precioAsiento', 'precioAsiento.precioAsiento']);
                        }]);
                    }
                    ])->where([ ['status', "!=", EstatusAsientosEnum::DESHABILITADO], ['payed', '=', 1] ]);

                }, 'responsable' => function($responsable){
                    $responsable-> select('id', 'nombre', 'correo', 'apellidoP', 'apellidoM', 'sexo');
                }])-> whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);

            }])->where('id', '=', $idCashRegister);

        }])->find($idBranchOffice);

        if (!$sucursal)
        {
            $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null);

            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        $sucursal-> setRelation('cajasRegistradoras',  $sucursal-> cajasRegistradoras->map(function ($cajaRegistradora)
        {
            $cajaRegistradora -> quantityRegister = $cajaRegistradora-> registrosCajas -> count();

            $cashRegisterSalesTotal = 0;
            $cashRegisterCashOutflow = 0;
            $attendedTo = $cajaRegistradora-> registrosCajas->pluck("responsable")-> groupBy('id');

            $cajaRegistradora-> registrosCajas -> each( function ($cajaRegistradora) use (&$cashRegisterSalesTotal, &$cashRegisterCashOutflow)
            {
                $cashRegisterSalesTotal += $cajaRegistradora-> cash_diference;
                $cashRegisterCashOutflow += $cajaRegistradora-> sell_total;
            });


            $cajaRegistradora-> cashRegisterSalesTotal = $cashRegisterSalesTotal;
            $cajaRegistradora-> cashRegisterCashOutflow = $cashRegisterCashOutflow;
            $cajaRegistradora-> attendedTo = $attendedTo;


            $cajaRegistradora-> registrosCajas ->map(function ($registroCaja, $price) {

                $registroCaja->quantityTickets = $registroCaja-> tickets ->count();

                $registroCaja-> totalSeatForTickets = $registroCaja-> tickets->pluck('asientoTicket')->collapse()->count() + $registroCaja-> tickets->pluck('asientosCambiados')->collapse()->count();

                $registroCaja->detailTickets = app(\App\Http\Controllers\api\IndicadoresController::class)->detailsTicketsForPayment( $registroCaja-> tickets );

                unset($registroCaja-> tickets);

                return $registroCaja;
            });

            return $cajaRegistradora;
        }));


        $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), true, $sucursal);

        return response()->json($response);

        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, []);

            return response()->json($response);
        }

    }
}
