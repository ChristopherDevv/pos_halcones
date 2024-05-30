<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use Illuminate\Http\Request;
use App\Models\MetodosCobro;
use App\Models\Comision;
use Mockery\Undefined;
use Symfony\Component\HttpFoundation\Response;

class MetodosCobroYComisionController extends Controller
{
    public function index() {
        try
        {
            $metodosCobro = MetodosCobro::with('comisionesActivas')->get();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$metodosCobro);

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
            $metodosCobro = MetodosCobro::with('comisionesActivas')->find($id);

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$metodosCobro);

            return response()->json($response);

        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function store(Request $request, MetodosCobro  $metodosCobro, Comision  $comision) {

        try
        {
            $metodosCobro =  $metodosCobro->create($request->only('name','description','deadlines'));

            if ($metodosCobro) {

                $comisionTemp = $request->get('comission');

                data_set($comisionTemp, 'id_method_payment', $metodosCobro-> id);

                $comision =  $comision->create($comisionTemp);

                $metodosCobro -> comisiones;

            }

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$metodosCobro);

            return response()->json($response);

        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function update($id, Request $request, MetodosCobro  $metodosCobro, Comision  $comision) {
        try
        {
            if ($id != $request->get('id'))
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null );

                return response()->json($response,Response::HTTP_NOT_FOUND);
            }

            $metodosCobro = $metodosCobro::find($id);

            $metodosCobro-> name = $request->get('name');
            $metodosCobro-> description  = $request->get('description');
            $metodosCobro-> deadlines  = $request->get('deadlines');

            if ($metodosCobro-> save()) {

                $comisionCount = $comision::where([ ['id_method_payment', '=', $metodosCobro-> id], ['status', '=', "Activo"]])->count();

                $isComisionUpdate = null;

                if ($comisionCount)
                {
                    $isComisionUpdate = $comision::where([ ['id_method_payment', '=', $metodosCobro-> id], ['status', '=', "Activo"]])->update(['status' => "Inactivo"]);
                }


                if ($isComisionUpdate || !$comisionCount) {

                    $comisionTemp = $request->get('comission');

                    data_set($comisionTemp, 'id_method_payment', $metodosCobro-> id);

                    $comision =  $comision->create($comisionTemp);

                    $metodosCobro -> comisionesActivas;

                }

            }

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(), $metodosCobro );

            return response()->json($response);

        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function destroy($id, MetodosCobro  $metodosCobro) {

        try{

            $metodosCobro = $metodosCobro::find($id);

            if (!$metodosCobro)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null);

                return response()->json($response);
            }

            $metodosCobro-> delete();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getCode(), $metodosCobro);

            return response()->json($response);

        } catch (\Throwable $e) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getMessage().$e->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getCode(), null);

            return response()->json($response);
        }

    }
}
