<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use Illuminate\Http\Request;
use App\Models\Distribuciones;
use Illuminate\Http\Response as ResponseData;
use Illuminate\Support\Facades\DB;

class DistribucionesController extends Controller
{
    public function index() {
        $resultSet = Distribuciones::where('status',1)->get();
        return response()->json($resultSet,ResponseData::HTTP_OK);
    }

    public function store(Request $request, Distribuciones  $distribuciones) {
        try {
            DB::beginTransaction();
            $resultSet = $distribuciones->create($request->all());
            DB::commit();
            $headers = ErroresExceptionEnum::SUCCESS_PROCESS_INSERT();
            $response = new DataResponse('Se ha agregado la nueva distribución',$headers->getCode(),$request);
            return response()->json($response,ResponseData::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            $headers = ErroresExceptionEnum::ERROR_PROCESS_INSERT();
            $response = new DataResponse($headers->getMessage(),$headers->getCode(),$request);
            return response()->json($response,ResponseData::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function update($id,Request $request, Distribuciones  $distribuciones) {
        try {
            DB::beginTransaction();;
            $result = $distribuciones->findOrFail($id);
            $result->update($request->all());
            DB::commit();
            $header = ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE();
            $response = new DataResponse($header->getMessage(),$header->getCode(),$result);
            return response()->json();

        }catch (\Exception $exception) {
            $header = ErroresExceptionEnum::ERROR_PROCESS_UPDATE();
            $response = new DataResponse($header->getMessage(),$header->getCode(),$request->all());
            return response()->json($response,ResponseData::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id, Distribuciones $distribuciones) {
        try {
            DB::beginTransaction();;
            $result = $distribuciones->findOrFail($id);
            $result->delete();
            DB::commit();
            $header = ErroresExceptionEnum::SUCCESS_PROCESS_DELETE();
            $response = new DataResponse($header->getMessage(),$header->getCode(),$result);
            return response()->json();

        }catch (\Exception $exception) {
            $header = ErroresExceptionEnum::ERROR_PROCESS_DELETE();
            $response = new DataResponse($header->getMessage(),$header->getCode(),$id);
            return response()->json($response,ResponseData::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
