<?php

namespace App\Http\Controllers\api;

use App\Models\Resultados;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ResultadosController extends Controller
{
   public function index() 
    {
        return Resultados::where('status',true)->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
     
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $resultado = $request->all();
        $resultado['imgEquipoUno'] = app(\App\Http\Controllers\api\ImagenesController::class)->upload($resultado['imgEquipoUno']);
        $resultado['imgEquipoDos'] = app(\App\Http\Controllers\api\ImagenesController::class)->upload($resultado['imgEquipoDos']);
        $resultset = Resultados::create($resultado);
        return $resultset;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        return Resultados::where('id',$id)->where('status',true)->get();
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
        return Resultados::where('id',$id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Resultados::where('id',$id)->update([
            'status'=> false
        ]);
    }
}
