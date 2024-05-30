<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TicketsAsientos;
use App\Models\Tickets;
use App\Models\Partidos;
use DB;

class BoletosController extends Controller
{

    public function validar(){
        $data['partidos'] = Partidos::where('status','!=', 0) ->GET();
        return view('boletos.validar',$data);
    }

    public function buscar(Request $r){
        $ticketdata = TicketsAsientos::where('eventos_id',$r->idPartido)
        ->where('code',$r->code)
        ->with(['ticket'=>function($query){
            $query->GET();
        }])->first();

        if($ticketdata == null){
            return response()->json(['msg'=>'No se encontró el boleto','status'=>204]);
        }else{
            return response()->json([
                'boleto'=>$ticketdata,
                'encontrado'=>true
              ]);
        }
    }

    public function conteo(){

        $abonos = Tickets::withCount(['conteo'])
        ->where('eventos_id',67)
        ->where('payed',1)
        ->where('status','!=',0)
        ->where('type_ticket',2)
        ->get();

        $abonosvip = Tickets::withCount(['conteo'])
        ->where('eventos_id',67)
        ->where('payed',1)
        ->where('status','!=',0)
        ->where('type_ticket',3)
        ->get();

        return view ('abonos.conteo',compact('abonos','abonosvip'));


        // $data['abonos'] = DB::table('tickets as t')
        // ->join('tickets_asiento as ta', 't.id', '=', 'ta.tickets_id')
        // ->where('t.eventos_id',79)
        // ->where('t.type_ticket',2)
        // ->where('t.status','!=',0)
        // ->where('t.payed',1)
        // ->count('ta.code');

        // $data['abonosvip'] = DB::table('tickets as t')
        // ->join('tickets_asiento as ta', 't.id', '=', 'ta.tickets_id')
        // ->where('t.eventos_id',79)
        // ->where('t.type_ticket',3)
        // ->where('t.status','!=',0)
        // ->where('t.payed',1)
        // ->count('ta.code');
        // return view ('abonos.conteo',$data);
        
    }

    
}


