<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AlmacenesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    ##inicia administración de almacenes##
    //registro de nuevo almacén
    public function nuevoAlmacenPlatform(){
        return view('almacenes_productos.nuevo_almacen_form');
    }
    public function transferenciasPlatform(){
        return view('almacenes_productos.transferencias');
    }
    public function almacenesPlatform(){
        return view('almacenes_productos.almacenes');
    }
    ##termina administración de almacenes##
}
