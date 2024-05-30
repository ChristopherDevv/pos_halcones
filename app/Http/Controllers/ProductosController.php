<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Productos;


class ProductosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Productos::where('status',true)->with('categorias')->get();
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
        return Productos::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        return Productos::where('id',$id)->where('status',true)->get();
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
        return Productos::where('id',$id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Productos::where('id',$id)->update([
            'status'=> false
        ]);
    }
    ////////////////////////////////////////////////////
    //A partir de aquí todo lo que se escribe es parte de la plataforma de escritorio.

    ##inicia administración de productos#
    //registro de nuevos productos
    public function nuevoProductoPlatform(){
        return view('almacenes_productos.nuevo_producto_form');
    }
    public function productosPlatform(){
        return view('almacenes_productos.productos');
    }


    ##termina Administración de productos




}
