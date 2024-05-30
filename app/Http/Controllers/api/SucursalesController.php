<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use Illuminate\Http\Request;
use \App\Models\Sucursales;
use PHPUnit\Util\Exception;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseStatusCodeSame;
use Symfony\Component\HttpFoundation\Response;


class SucursalesController extends Controller
{
    public function index() {
        try {
            $sucursales = Sucursales::where('status',1)->with('direccion')->get();
            $sucess  = ErroresExceptionEnum::SUCCESS_PROCESS_LIST();
            $response = new DataResponse($sucess->getMessage(),$sucess->getCode(),$sucursales);
            return response()->json($sucursales);
        }   catch (\Exception $e) {
            $error  = ErroresExceptionEnum::ERROR_PROCESS_LIST();
            $response = new DataResponse($error->getMessage().$e->getMessage(),$error->getCode(),null);
            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function store(Request $request, Sucursales  $sucursal) {
        try {
            $dataSucursal = $request->all();
            $sucursales = $sucursal->insert($dataSucursal);
            $sucess  = ErroresExceptionEnum::SUCCESS_PROCESS_INSERT();
            $response = new DataResponse($sucess->getMessage(),$sucess->getCode(),$sucursales);
            return response()->json($response);
        }catch (\Exception $e) {
            $error  = ErroresExceptionEnum::ERROR_PROCESS_INSERT();
            $response = new DataResponse($error->getMessage().$e->getMessage(),$error->getCode(),null);
            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($id, Request $request, Sucursales  $sucursal) {
        try {
            $dataSucursal = $request->all();
            $resultSet = $sucursal->where('id',$id)->update($dataSucursal);
            if($resultSet) {
                $sucess  = ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE();
                $response = new DataResponse($sucess->getMessage(),$sucess->getCode(),$resultSet);
                return response()->json($response);
            }else {
                throw new Exception('No se pudo actualizar correctamente');
            }

        }   catch (\Exception $e) {
            $error  = ErroresExceptionEnum::ERROR_PROCESS_UPDATE();
            $response = new DataResponse($error->getMessage().$e->getMessage(),$error->getCode(),null);
            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id,Request $request, Sucursales  $sucursal) {
        try {

        }   catch (\Exception $e) {

        }
    }
}
