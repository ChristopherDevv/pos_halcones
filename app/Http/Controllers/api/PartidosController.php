<?php

namespace App\Http\Controllers\api;

use App\Models\Interfaces\ErroresExceptionEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Partidos;
use App\Http\Controllers\Controller;
use App\Models\Interfaces\EstatusPartidos;
use App\Models\Interfaces\DataResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PartidosController extends Controller
{
   public function index()
    {
        $partidos =  Partidos::where('status',EstatusPartidos::MOSTRAR);
        $partidos = $partidos->with('images')->get();
        return response()->json($partidos);
    }

    public function all() {
        $partidos =  Partidos::whereIn('status',[
            EstatusPartidos::MOSTRAR,
            EstatusPartidos::CREADO,
        ]);
        $partidos = $partidos->with('images')->get();
        return response()->json($partidos);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    public function mostrar($id) {
        try {
            Partidos::where('id',$id)->update([
                'status'=> EstatusPartidos::MOSTRAR
            ]);
        }catch(\Exception $e){
            $response = new DataResponse('Al mostrar el partido', 'Error',$id);
            return  response()->json($response);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return Partidos::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        return Partidos::where('id',$id)->where('status',true)->get();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return Partidos::where('id',$id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Partidos::where('id',$id)->update([
                'status'=> EstatusPartidos::DESHABILITADO
            ]);
        }catch(\Exception $e){
            $response = new DataResponse('Error al eliminar el partido', 'Error',$id);
            return  response()->json($response);
        }
    }

    public function getAllDateOfGames() {
        try{
            $partidos = collect(Partidos::where('status','>',0)->get())->unique('fecha')->map(
                function($partido){
                    return [
                        'partido'=> $partido->titulo,
                        'fecha'=> $partido->fecha
                    ];
                }
            );
            return $partidos;
        }catch(\Exception $e){

        }
    }

    public function getCurrentPartido() {
        try {
            $hoy = Carbon::now()->isoFormat('Y-M-DD');
            $partidos = Partidos::select('*',DB::raw('date(fecha) as fecha_actual'))->having('fecha_actual',$hoy)->firstOrFail();
            return  $partidos;
        }catch (\Exception $exception) {
            $headers = ErroresExceptionEnum::ERROR_PROCESS_SHOW();
            $response = new DataResponse($exception->getMessage(),$headers->getCode(),[]);
            return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
