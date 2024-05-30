<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use Illuminate\Http\Request;
use App\Models\PrecioAsiento;
use PHPUnit\Util\Exception;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseStatusCodeSame;
use Symfony\Component\HttpFoundation\Response;

class PrecioAsientoController extends Controller
{
    public function index() {
        try
        {
            $precioAsiento = PrecioAsiento::all();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$precioAsiento);

            return response()->json($response);

        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function store(Request $request, PrecioAsiento  $precioAsiento) {

        try
        {

            $precioAsientoTemp =  $precioAsiento->create($request->only('price'));

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$precioAsientoTemp);

            return response()->json($response);

        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function update($id, Request $request, PrecioAsiento  $precioAsiento) {
        try
        {
            if ($id != $request->get('id'))
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null );

                return response()->json($response,Response::HTTP_NOT_FOUND);
            }

            $precioAsientoTemp = $precioAsiento::find($id);

            $precioAsientoTemp-> price  = $request->get('price');

            $precioAsientoTemp-> save();

            $precioAsientoTemp-> refresh();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(), $precioAsientoTemp );

            return response()->json($response);

        }
        catch (\Exception $e)
        {
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),null);

            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function destroy($id, PrecioAsiento  $precioAsiento) {

        try{

            $precioAsientoTemp = $precioAsiento::find($id);

            if (!$precioAsientoTemp)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null);

                return response()->json($response);
            }

            $precioAsientoTemp-> delete();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getCode(), $precioAsientoTemp);

            return response()->json($response);

        } catch (\Throwable $e) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getMessage().$e->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getCode(), null);

            return response()->json($response);
        }

    }
}
