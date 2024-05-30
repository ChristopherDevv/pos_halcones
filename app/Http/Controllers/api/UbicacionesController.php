<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\UbicacionMail;
use App\Models\Interfaces\DataResponse;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Directions;
use App\Models\Ubicacion\Estados;
use App\Models\Ubicacion\Localidades;
use App\Models\Ubicacion\Municipios;
use App\Models\Interfaces\ErroresExceptionEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UbicacionesController extends Controller
{
    public function getEstados() {
        try{
            $estados = Estados::where('activo','>',0)->select([
                'id',
                'clave',
                'nombre'
            ])->orderBy('nombre', 'ASC');
            return $estados->get();
        }catch (\Exception $e){
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),$e->getMessage());
            Log::info(json_encode($response));
            return response()->json($response,505);
        }
    }
    public function getMunicipios($estadoId) {
        try{
            $municipios = array();
            if($estadoId > 0) {
                $municipios = Municipios::where([
                    ['activo','>',0],
                    ['estado_id','=',$estadoId]
                ])->select([
                    'id',
                    'clave',
                    'nombre'
                ])->orderBy('nombre', 'ASC')->get();
            }else {
                $municipios = Municipios::where('activo','>',0)->select([
                    'id',
                    'clave',
                    'nombre'
                ])->orderBy('nombre', 'ASC')->get();
            }
            return $municipios;
        }catch (\Exception $e){
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }

    public function getLocalidades($municipioId) {
            try{
                $localidades = array();
                if($municipioId > 0) {
                    $localidades = Localidades::where([
                        ['activo','>',0],
                        ['municipio_id','=',$municipioId]
                    ])->select([
                        'id',
                        'clave',
                        'nombre'
                    ])->orderBy('nombre', 'ASC')->get();
                }else {
                    $localidades = Localidades::where('activo','>',0)->select([
                        'id',
                        'clave',
                        'nombre'
                    ])->orderBy('nombre', 'ASC')->get();
                }
                return $localidades;
        }catch (\Exception $e){
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }
    public function store(Request $request, Directions $directions){
        try{
            $dataRequest = $request->all();
            DB::beginTransaction();
            if($dataRequest['status'] == 2) {
                $this->setDefaultUbication($request,$directions);
            }
            if(is_null($dataRequest['numInt'] )) {
                $dataRequest['numInt'] = '0';
            }
            $ubicacion = $directions->create($dataRequest);
            $user = User::where('id',$ubicacion->users_id)->first();
            $this->enviarNotificacion($user->correo);

            DB::commit();
            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(),$ubicacion);
            return response()->json($response);
        }catch (\Exception $e){
            DB::rollBack();
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }


    public function list(Request $request,Directions $directions){
        try{
            $ubicaciones = array();
            $ubicaciones = $directions->where(
                [
                    [$request->get('key'),$request->get('operador'),$request->get('value')],
                    ['status','>',0]
                ]
            )->with(['estado','municipio','ciudad','user']);
            $ubicaciones = collect($ubicaciones->get())->sortByDesc('status')->values();
            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$ubicaciones);
            return response()->json($response);
        }catch (\Exception $e){
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),$e->getMessage());
            return response()->json($response,500);
        }
    }
    public function destroy($id, Directions $directions){
        try{
            DB::beginTransaction();
            DB::enableQueryLog();
            $result = $directions->where('id','=',$id)->update(
                [
                    'status' => 0
                ]
            );
            DB::commit();
            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getCode(),$result);
            return response()->json($response);
        }catch (\Exception $e){
            DB::rollBack();
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }
    public function  update($id, Request $request,Directions $directions) {
        try{
            $dataUpdate = $request->all();
            DB::beginTransaction();
            DB::connection()->enableQueryLog();
            $ubicacion = $directions->findOrFail($id);
            if(!is_null($dataUpdate['status']) && $dataUpdate['status'] == 2) {
                Log::info(json_encode($dataUpdate));
                $this->setDefaultUbication($request,$directions);
            }
            $ubicacion->update(
                $dataUpdate
            );
            $queries = DB::getQueryLog();
            DB::commit();
            Log::info("Querires ",$queries);
            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(),$ubicacion->get());
            return response()->json($response);
        }catch (\Exception $e) {
            DB::rollBack();
            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),$e->getMessage());
            return response()->json($response,505);
        }
    }

    public function setDefaultUbication(Request $request, Directions $directions) {
        $dataRequest = $request->all();
        $request->validate([
           'users_id' => 'required'
        ]);
        $ubicaciones = $directions->where(
            [
                ['users_id', '=', $dataRequest['users_id']],
                ['status','>',0]
            ]
        );
        if($ubicaciones->exists()) {
            $ubicaciones = $ubicaciones->update(
                ['status' => 1]
            );
            if(!$ubicaciones) {
                throw  new \Exception('No se pudo guardar correctamente la ubicación '.$ubicaciones);
            }
        }
    }
    public function enviarNotificacion($correo) {
        Mail::to($correo)->send(new UbicacionMail());
    }
}
