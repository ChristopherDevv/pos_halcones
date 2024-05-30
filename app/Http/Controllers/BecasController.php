<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use DB;
//modelo
use App\Models\Becas as Beca;

class BecasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function becasIndex()
    {
        return view ('academias.becas.index');
    }

    public function guardarBeca(Request $r)
    {
        DB::Begintransaction();
        try{
            $beca = new Beca();
            $beca -> nombre_beca = $r -> nombreBeca;
            $beca -> comentarios = $r -> comentariosBeca;
            $beca -> created_at = Carbon::now();
            if($beca->save()){
                DB::commit();
                return \Redirect::to('/academias/becas')->with('status','Beca publicada con éxito');
            }else{
                DB::rollback();
                throw new Exception("Ocurrió un error al intentar publicar la beca",400);
            }
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['errores'=>$e->getMessage()],$e->getCode());
        }
    }
}
