<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use Illuminate\Http\Request;
use App\Models\RegistroCajas;
use PHPUnit\Util\Exception;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseStatusCodeSame;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Interfaces\EstatusAsientosEnum;

class RegistroCajasController extends Controller
{
    public function index() {
        try
        {
            $registroCajas = RegistroCajas::with(['tickets' => function($tickets){
                $tickets->with('partido')->where('status', '!=', EstatusAsientosEnum::DESHABILITADO);
            }])->get();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$registroCajas);

            return response()->json($response);

        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id) {
        try
        {
            $registroCaja = RegistroCajas::with(['tickets' => function($tickets){
                $tickets->with('partido')->where('status', '!=', EstatusAsientosEnum::DESHABILITADO);
            }])->find($id);

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$registroCaja);

            return response()->json($response);

        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function store(Request $request, RegistroCajas  $registroCajas) {

        try
        {

            $cajaRegistradora =  $registroCajas->create($request->only('id_responsible', 'id_caja_registradora', 'cash_received', 'finaly_money', 'cash_outflow', 'sell_total', 'cash_diference'));

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$cajaRegistradora);

            return response()->json($response);

        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function update($id, Request $request, RegistroCajas  $registroCajas) {
        try
        {
            $cajaRegistradora = $registroCajas::with('tickets')->find($id);

            if ($id != $request->get('id') || !$cajaRegistradora)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null );

                return response()->json($response,Response::HTTP_NOT_FOUND);
            }


            $cajaRegistradora-> finaly_money = $request->get('finaly_money');
            $cajaRegistradora-> cash_outflow  = $request->get('cash_outflow');
            $cajaRegistradora-> sell_total  = $request->get('sell_total');
            $cajaRegistradora-> cash_diference  = $request->get('cash_diference');
            $cajaRegistradora-> status  = $request->get('status');

            $cajaRegistradora-> save();

            $cajaRegistradora-> refresh();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(), $cajaRegistradora );

            return response()->json($response);

        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function destroy($id, RegistroCajas  $registroCajas) {

        try{

            $cajaRegistradora = $registroCajas::find($id);

            if (!$cajaRegistradora)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null);

                return response()->json($response);
            }

            $cajaRegistradora-> delete();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getCode(), $cajaRegistradora);

            return response()->json($response);

        } catch (\Throwable $e) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getMessage().$e->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getCode(), null);

            return response()->json($response);
        }

    }
}
