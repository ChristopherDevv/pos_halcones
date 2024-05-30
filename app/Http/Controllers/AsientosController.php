<?php

namespace App\Http\Controllers\api;

use App\Models\Asientos;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Interfaces\EstatusAsientosEnum;
use Illuminate\Support\Collection;
use App\Models\TicketsAsientos;
use App\Models\Partidos;
use App\Models\Interfaces\DisableConfig;
use App\Models\Interfaces\DataResponse;
use App\Http\Controllers\Controller;

class AsientosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Asientos::where('status',EstatusAsientosEnum::DISPONIBLE)->whereNull('tickets_id')->get();
    }

    public function findAsientoBy(Request $request){
       try{
        $data = $request->all();
        $zona = strtoupper($data['zona']);
        $fila = strtoupper($data['fila']);
        $evento = $data['evento'];
        $tickets = TicketsAsientos::whereIn('status',[
            EstatusAsientosEnum::COMPRADO,
            EstatusAsientosEnum::TAQUILLA,
            EstatusAsientosEnum::RESERVADO,
            EstatusAsientosEnum::TEMPORADA,
            EstatusAsientosEnum::VERIFICADO
        ]);

        if($tickets->get()->count() <= 0) {
            $asientos = Asientos::where([
                ['status','=',EstatusAsientosEnum::DISPONIBLE],
                ['zona','=',$zona],
                ['fila','=',$fila]
            ])->paginate(20);
            return $asientos;
        }else {
            $ticketsCollect = collect($tickets->get());

            $tickets = $ticketsCollect->where('eventos_id', $evento);

            $codes = $tickets->map(function ($item, $key) {
                return $item->code;
            });

            if($ticketsCollect->count() > 0){
                $asientos = Asientos::where([
                    ['status','=',EstatusAsientosEnum::DISPONIBLE],
                    ['zona','=',$zona],
                    ['fila','=',$fila]
                ]);
                $page = $asientos->whereNotIn('code',$codes)->paginate(20);
                return $page;
            }else {
                $asientos = Asientos::where([
                    ['status','=',EstatusAsientosEnum::DISPONIBLE],
                    ['zona','=',$zona],
                    ['fila','=',$fila]
                ])->paginate(20);
                return $asientos;
            }
        }

       }catch(\Throwable  $e){
           $response = new DataResponse($e->getMessage(),'Error',$e);
           return response()->json($response);
        }
    }

    public function reservarBoletos($boletos,$config) {
        $config['asientoBoleto'] = $boletos;
        $evento = $config['idEvento'];
        $tickets = TicketsAsientos::whereIn('status',[
            EstatusAsientosEnum::COMPRADO,
            EstatusAsientosEnum::TAQUILLA,
            EstatusAsientosEnum::RESERVADO,
            EstatusAsientosEnum::TEMPORADA,
            EstatusAsientosEnum::VERIFICADO
        ]);
        $tickets = $tickets->where('eventos_id', $evento)->get();
        $ticketsCollect = collect($tickets);
        $codes = $ticketsCollect->map(function ($item, $key) {
            return $item->code;
        });

        if(count(array_intersect($codes->toArray(), $boletos)) > 0){
            throw new \Exception('Algunos de tus boletos ya fueron utilizados');
        }else {
            switch ($config['tipeReservation']) {
                case 'temporada':
                    $asientos  = $this->buildAsientosTemporada($config, EstatusAsientosEnum::TEMPORADA);
                    $ticketsAsiento = TicketsAsientos::insert($asientos);
                    return new DataResponse('Se han reservado los asientos por toda la temporada','Reservados',$ticketsAsiento);
                    break;
                case 'evento':
                    $asientos  = $this->buildAsientos($config, EstatusAsientosEnum::COMPRADO);
                    $ticketsAsiento = TicketsAsientos::insert($asientos);
                    return new DataResponse('Se ha guardado sus asientos','Reservados',$ticketsAsiento);
                    break;
                case 'reservation':
                    $asientos  = $this->buildAsientos($config, EstatusAsientosEnum::RESERVADO);
                    $ticketsAsiento = TicketsAsientos::insert($asientos);
                    return new DataResponse('Se han reservado los asientos','Reservados',$ticketsAsiento);
                    break;
                case 'taquilla':
                    $asientos  = $this->buildAsientos($config, EstatusAsientosEnum::TAQUILLA);
                    $ticketsAsiento = TicketsAsientos::insert($asientos);
                    return new DataResponse('Se han reservado los asientos para pagar en taquilla','Reservados',$ticketsAsiento);
                    break;
                break;
               default:
                  throw new \Exception('Algunos de tus asientos ya fueron utilizados');
                   break;
            }
        }
    }

    private function buildAsientos($ticketData, int $status) {
        $asientos = Asientos::whereIn('code',$ticketData['asientoBoleto']);
        $ticketsAsientos = array();
        foreach ($asientos->get() as $asiento) {
            $seat = [
                'tickets_id' => $ticketData['idTicket'],
                'code' => $asiento->code,
                'eventos_id' => $ticketData['idEvento'],
                'status' => $status
            ];
            array_push($ticketsAsientos,$seat);
        }
        return $ticketsAsientos;
    }
    private function buildAsientosTemporada($ticketData) {
        $asientos = Asientos::whereIn('code',$ticketData['asientoBoleto']);
        $partidos = Partidos::where('status',true)->get();
        $ticketsAsientos = array();
        foreach ($partidos as $partido) {
            foreach ($asientos->get() as $asiento) {
                $seat = [
                    'tickets_id' => $ticketData['idTicket'],
                    'code' => $asiento->code,
                    'eventos_id' => $partido->id,
                    'status' => EstatusAsientosEnum::TEMPORADA
                ];
                array_push($ticketsAsientos,$seat);
            }
        }

        return $ticketsAsientos;
    }
    public function disableSeats(Request $request) {
        $configData = new DisableConfig($request);
        switch ($configData->config['typeReservation']) {
             case 'zona':
                $asientosCollect = Asientos::where('zona',$configData->config['zona']);
                if($this->getEstatusAsiento($asientosCollect)) {
                     return response()->json(new DataResponse('Algunos de los boletos todavia estan siendo actualizados
                    ','Error', $asientosCollect->get()));
                }else {
                    return $this->disable($asientosCollect);
                }
               break;
           case 'fila':
                $asientosCollect = Asientos::where([
                    ['fila','=',$configData->config['fila']],
                    ['zona','=',$configData->config['zona']]
                ]);
                if($this->getEstatusAsiento($asientosCollect)) {
                     $response = new DataResponse('Algunos de los boletos todavia estan siendo actualizados
                    ','Error', $asientosCollect->get());
                     return response()->json($response);
                }else {
                    return $this->disable($asientosCollect);
                }
                break;
           case 'filas':
                $asientosCollect = Asientos::where('zona',$configData->config['zona'])->whereIn(
                    'fila',$configData->config['filas']
                );
                if($this->getEstatusAsiento($asientosCollect)) {
                     return response()->json(new DataResponse('Algunos de los boletos todavia estan siendo actualizados
                    ','Error', $asientosCollect->get() ));
                }else {
                    return $this->disable($asientosCollect);
                }
                break;
           case 'boletos':
                $asientosCollect = Asientos::whereIn('code',$configData->bolts);
                if($this->getEstatusAsiento($asientosCollect)) {
                    $response =  new DataResponse('Algunos de los boletos todavia estan siendo actualizados
                    ','Error',$asientosCollect->get());
                    return response()->json($response);
                }else {
                    return $this->disable($asientosCollect);
                }
                break;
             default:
                return response()->json(new DataResponse('No se encontro una actualizacion
                    ','Error',$configData));
            break;
        }

        return response()->json();
    }
    private function disable($asientosCollect) {
        $asientosCollect->update([
            'status' => EstatusAsientosEnum::DESHABILITADO
        ]);
        $response = new DataResponse('Asientos desahabilitados','Desahabilitado',$asientosCollect);
        return response()->json($response);
    }

    private function getEstatusAsiento($asientosCollect) {
        $seats = $asientosCollect->get()->map(function ($item, $key){
            return $item->code;
        });
        $seatExits = TicketsAsientos::whereNotIn(
            'status',[
                EstatusAsientosEnum::DISPONIBLE,
                EstatusAsientosEnum::TAQUILLA,
                EstatusAsientosEnum::TEMPORADA
            ])->whereIn('code',$seats)->exists();
          return $seatExits > 0 ?  true :  false;
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
        return Asientos::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        return Asientos::where('id',$id)->where('status',true)->get();
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
        return Asientos::where('id',$id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return Asientos::where('id',$id)->update([
            'status'=> EstatusAsientosEnum::DESHABILITADO
        ]);
    }

    public function countSeats(Request $request) {
        $config = $request->all();
        $seats = Asientos::where('zona',$config['zona'])->get();
        $seatsCollections = collect($seats);
        $countSeatsDeshabilited = $seatsCollections->where('status',EstatusAsientosEnum::DESHABILITADO)->count();
        $countSeatsAviabled = $seatsCollections->where('status',EstatusAsientosEnum::DISPONIBLE)->count();
        $countSeatsReserved = $seatsCollections->where('status',EstatusAsientosEnum::RESERVADO)->count();
        $countSeatsBuyed = $seatsCollections->where('status',EstatusAsientosEnum::COMPRADO)->count();
        return [
            'countSeatsDeshabilited' => $countSeatsDeshabilited,
            'countSeatsAviabled' => $countSeatsAviabled,
            'countSeatsReserved' => $countSeatsReserved,
            'countSeatsBuyed' => $countSeatsBuyed
        ];
    }
}
