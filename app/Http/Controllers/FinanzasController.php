<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use DB;

//modelos
use App\Models\Ingresos as I;
use App\Models\Egresos as E;
use App\Models\tiposIngreso as TI;


class FinanzasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //Formulario para registrar un nuevo ingreso
    public function nuevoIngresoForm()
    {
        //lee los registros de la tabla tipos de usuario para llenar el select
        $datos['tipo_ingreso'] = DB::table('tipos_ingreso')->get();
        //lee los registros de la tabla ingresos haciendo  join en la tabla tipos_ingreso y usuarios para determinar
        //el tipo de ingreso y el responsable a cargo del registro del ingreso.
        $datos['resumenIngreso'] = DB::table('ingresos as i')
        ->join('users as u','i.idUsuario','=','u.id')
        ->join('tipos_ingreso as ti','i.id_tipo_ingreso','=','ti.id')
        ->select(
            'i.id as transaccion',
            'i.numero_referencia as referencia',
            'ti.tipo as tipo',
            'i.concepto as concepto',
            'i.monto as monto',
            'u.nombre as nombre',
            'i.created_at as fecha'
        )
        ->where('estatus',1)
        ->get();
        //suma los ingresos para determinar el total de ingresos
        $datos['total'] = DB::table('ingresos')
        ->select(
            'monto'
        )
        ->where('estatus',1)
        ->sum('monto');

        return view('finanzas.ingresos.nuevoIngreso', $datos);
    }

    //Función que guarda el ingreso
    public function guardarIngresoPlatform(Request $r)
    {
        $i = new I();
        $i-> numero_referencia = $r->numero_referencia;
        $i->id_tipo_ingreso = $r->id_tipo_ingreso;
        $i->concepto = $r->concepto;
        $i->monto = $r->monto;
        $i->idUsuario = Auth::user()->id;
        $i->estatus = 1;
        $i->created_at = Carbon::now();
        DB::Begintransaction();
        try {
            if ($i->save()) {

                DB::commit();

                return \Redirect::to('/finanzas/nuevo_ingreso')->with('status','Registro guardado con éxito');
            } else {
                DB::rollback();
                return \Redirect::back()->withErrors(['Ocurrió un error al intentar guardar el ingreso']);
            }
        } catch (\Exception $e) {
            return \Redirect::back()->withErrors(['Ocurrió un error al intentar guardar el ingreso']);
        }
    }
    //Busca el ingreso para devolverlo a un formulario para editarlo
    public function buscarIngresoPlatform(Request $r)
    {
        $datos = DB::table('ingresos as i')
        ->join('users as u','i.idUsuario','=','u.id')
        ->join('tipos_ingreso as ti','i.id_tipo_ingreso','=','ti.id')
        ->select(
            'i.id as transaccion',
            'i.numero_referencia as referencia',
            'ti.tipo as tipo',
            'i.concepto as concepto',
            'i.id_tipo_ingreso as idTipo',
            'i.monto as monto',
            'u.nombre as nombre',
            'i.created_at as fecha',
            'i.estatus as estatus'
        )
        ->where('i.id',$r->idIngreso)
        ->first();
        return response()->json($datos);
    }
    //Edita los datos del ingreso
    public function actualizarIngresoPlatform(Request $r)
    {
        $update = I::find($r->transaccion);
        $update -> id_tipo_ingreso = $r->id_tipo_ingreso;
        $update -> numero_referencia = $r->numero_referencia;
        $update -> monto = $r->monto;
        DB::Begintransaction();
        try {
            if ($update->save()) {
                DB::commit();
                return \Redirect::to('/finanzas/nuevo_ingreso')->with('status','Registro editado con éxito');;
            } else {
                DB::rollback();
                return \Redirect::back()->withErrors(['Ocurrió un error al intentar editar el ingreso']);
            }
        } catch (\Exception $e) {
            return \Redirect::back()->withErrors(['Ocurrió un error al intentar editar el ingreso']);
        }
    }

    //Borra ingresos de la base de datos
    public function borrarIngresoPlatform(Request $r)
    {
        $delete = I::find($r->transaccion);
        $delete -> estatus = 0;
        DB::Begintransaction();
        try{
            if($delete->save()){
                DB::commit();
                return \Redirect::to('/finanzas/nuevo_ingreso')->with('status','Registro borrado con éxito');
            }else{
                DB::rollback();
                return \Redirect::back()->withErrors(['Ocurrió un error al intentar borrar el ingreso']);
            }
        } catch(\Exception $e){
            return \Redirect::back()->withErrors(['Ocurrió un error al intentar borrar el ingreso']);
        }
    }

    //Administración de los tipos de ingresos
    public function tipoIngresosForm()
    {
        $datos['tipos_ingresos'] = DB::table('tipos_ingreso as ti')
        ->join('users as u','ti.idUser','=','u.id')
        ->select(
            'ti.id as id',
            'ti.tipo as tipo',
            'ti.estatus as estatus',
            'u.nombre as usuario',
            'ti.created_at as created_at'
        )
        ->get();
        return view('finanzas.ingresos.tipo_ingresos_form',$datos);
    }

    //Functión que guarda los tipos de ingreso
    public function guardarTipoIngreso(Request $r)
    {
        $ti = new TI();
        $ti -> tipo = $r -> tipo_ingreso;
        $ti -> idUser = Auth::user()->id;
        $ti -> estatus = 1;
        $ti -> created_at = Carbon::now();
        DB::Begintransaction();
        try{
            if($ti->save()){
                DB::commit();
                return \Redirect::to('/finanzas/tipos_ingreso')->with('status','Registro guardado con éxito');
            }else{
                DB::rollback();
                return \Redirect::back()->withErrors(['Ocurrió un error al intentar guardar el tipo de ingreso']);
            }

        }catch(\Exception $e){
            return \Redirect::back()->withErrors(['Ocurrió un error al intentar guardar el tipo de ingreso']);
        }
    }

    //EGRESOS
    public function nuevoEgresoForm()
    {
        //Lee los registros de la tabla tipo egresos para llenar el select
        $datos['tipo_egreso'] = DB::table('tipos_egreso')->get();
        //Lee los registros de la tabla egresos haciendo join en la tabla tipos_egresos y usuarios para determinar
        //El tipo de egreso y el responsable a cargo del registro del egreso.
        $datos['resumenEgresos'] = DB::table('egresos as e')
        ->join('users as u','e.idUsuario','=','u.id')
        ->join('tipos_egreso as te', 'e.id_tipo_egreso','=','te.id')
        ->select(
            'e.id as operacion',
            'e.numero_referencia as referencia',
            'te.tipo as tipo',
            'e.concepto as concepto',
            'u.nombre as nombre',
            'e.created_at as fecha',
            'e.monto as monto')
        ->where('estatus',1)
        ->get();
        //suma los egresos para determinar el total de egresos
        $datos['total'] = DB::table('egresos')
        ->select(
            'monto'
        )
        ->where('estatus',1)
        ->sum('monto');
        return view('finanzas.egresos.nuevoEgreso', $datos);
    }
    //Esta función guarda egresos
    public function guardarEgresoPlatform(Request $r)
    {
        $e = new E();
        $e -> numero_referencia = $r-> numero_referencia;
        $e -> id_tipo_egreso = $r -> id_tipo_egreso;
        $e -> concepto = $r -> concepto;
        $e -> monto = $r -> monto;
        $e -> idUsuario = Auth::user()->id;
        $e -> estatus = 1;
        $e -> created_at = Carbon::now();
        DB::Begintransaction();
        try{
            if($e->save()){
                DB::commit();
                return \Redirect::to('/finanzas/nuevo_egreso')->with('status','Registro guardado con éxito');
            }else{
                DB::rollback();
                return \Redirect::back()->withErrors(['Ocurrió un error al intentar guardar el Egreso']);
            }
        } catch(\Exception $e){
            return \Redirect::back()->withErrors(['Ocurrió un error de servidor intente más tarde']);
        }
    }
    //Esta función busca los datos con el id del egreso para editar o borrar
    public function buscarEgresoPlatform(Request $r)
    {
        $datos = DB::table('egresos as e')
        ->join('tipos_egreso as te', 'e.id_tipo_egreso','=','te.id')
        ->select(
            'e.id as idEgreso',
            'te.tipo as tipo',
            'e.id_tipo_egreso as idTipo',
            'e.concepto as concepto',
            'e.numero_referencia as referencia',
            'e.monto as monto',
            'e.estatus as estatus'
        )
        ->where('e.id',$r->idEgreso)
        ->first();
        return response()->json($datos);
    }
    //Edita los datos de Egreso
    public function actualizarEgresoPlatform(Request $r)
    {
        $update = E::find($r->operacion);
        $update ->id_tipo_egreso =  $r->id_tipo_egreso;
        $update ->concepto = $r->concepto;
        $update ->numero_referencia = $r->numero_referencia;
        $update ->monto = $r->monto;
        DB::Begintransaction();
        try{
            if($update->save()){
                DB::commit();
                return \Redirect::to('/finanzas/nuevo_egreso')->with('status','Registro editado con éxito');;
            }else{
                DB::rollback();
                return \Redirect::back()->withErrors(['Ocurrió un error al intentar editar el egreso']);
            }
        }catch(\Exception $e){
            return \Redirect::back()->withErrors(['Ocurrió un error al intentar editar el egreso']);
        }
    }
    //Borra egresos de la base de datos
    public function borrarEgresoPlatform(Request $r){
        $delete = E::find($r->operacion);
        $delete -> estatus = 0;
        DB::Begintransaction();
        try{
            if($delete->save()){
                DB::commit();
                return \Redirect::to('/finanzas/nuevo_egreso')->with('status','Registro borrado con éxito');
            }else{
                DB::rollback();
                return \Redirect::back()->withErrors(['Ocurrió un error al intentar borrar el egreso']);
            }
        } catch(\Exception $e){
            return \Redirect::back()->withErrors(['Ocurrió un error al intentar borrar el egreso']);
        }
    }

}
