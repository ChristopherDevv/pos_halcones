<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paqueterias;
use App\Models\Interfaces\DataResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Interfaces\ErroresExceptionEnum;

class PaqueteriasController extends Controller
{
    public function index() {
        $paqueterias = Paqueterias::where('status','>',0);
        return $paqueterias->get();
    }

    public function store(Request $request,Paqueterias $paqueterias) {
        try{
            DB::beginTransaction();
            $result = $paqueterias->create($request->all());
            $response = new DataResponse('Se registrado una nueva paquetería',ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$request);
            DB::commit();
            return response()->json($response);
        }catch (\Exception $e){
            $response = new DataResponse('Ha ocurrido un error al guardar su paqueteria',ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }

    public function update($id,Request $request,Paqueterias $paqueterias) {
        try{
            DB::beginTransaction();
            $result = $paqueterias->findOrFail($id);
            $result = $result->update($request->all());
            $response = new DataResponse('Se ha actualizado la paqueteria',ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(),$result);
            DB::commit();
            return response()->json($response);
        }catch (\Exception $e){
            $response = new DataResponse('Error al actualizar la paqueteria',ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }

    public function delete($id, Paqueterias $paqueterias) {
        try{
            DB::beginTransaction();

            $result = $paqueterias-> where('id','=',$id)-> update(['status' => 0]);

            DB::commit();
            $response = new DataResponse('Se ha actualizado la paqueteria',ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getCode(),$result);
            return response()->json($response);
        }catch (\Exception $e){
            $response = new DataResponse('Error al actualizar la paqueteria',ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }
}
