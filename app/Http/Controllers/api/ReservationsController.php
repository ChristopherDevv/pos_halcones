<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Interfaces\DataResponse;

use App\Models\Interfaces\ErroresExceptionEnum;
use App\Models\Tickets;
use Illuminate\Http\Request;
use App\Models\Reservations;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseData;

class ReservationsController extends Controller
{
    public function index() {
        $reservations = Reservations::where('status','>',0)->with([
            'partido',
            'ticket',
            'createdBy'
        ])->get();
        return $reservations;
    }

    public function findByUser($id) {
        $reservations = Reservations::where([
            ['status','>',0],
            ['created_by','=',$id]
        ])->with([
            'partido',
            'ticket',
            'createdBy'
        ])->get();
    }
    public function store(Request $request, Reservations $reservations) {
        try{
            DB::beginTransaction();
            Log::info('Request data'.json_encode($request->all()));
            $dataRequest = $request->only([
                'eventos_id',
                'motivo',
                'tickets_id',
                'created_by',
                'status',
                'payed'
            ]);
            $dataTickets = $request->except([
                'motivo',
                'created_by',
                'tickets_id',
                'status'
            ]);
            $dataTickets['users_id'] = $dataRequest['created_by'];
            $ticket = app(TicketsController::class)->store($request,$dataTickets);
            if($request->has('is_generate_for_seat') && $request->get('is_generate_for_seat')) {
                $dataRequest['tickets_id'] = $ticket[0]['idGeneral'];
            }else {
                $dataRequest['tickets_id'] = $ticket['id'];
            }
            $resultSet = $reservations->create($dataRequest);
            DB::commit();
            $success = ErroresExceptionEnum::SUCCESS_PROCESS_INSERT();
            $response  = new DataResponse('Se ha guardado su reservación',$success->getCode(),$resultSet);
            return response()->json($response);
        }catch (\Exception $e){
            $error = ErroresExceptionEnum::ERROR_PROCESS_INSERT();
            $response = new DataResponse($error->getMessage().$e->getMessage(),$error->getCode(),$e->getTrace());
            DB::rollBack();
            return response()->json($response, ResponseData::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($id,Request $request,Reservations $reservations) {
        try{
            DB::beginTransaction();
            $resultSet = $reservations->where(
                'id',$id
            )->update(
                $request->get('reservation')
            );
            $dataTicket = $request->get('ticket');
            $resultSetTicket = Tickets::where('id',$dataTicket['id'])->update(
                $dataTicket
            );
            $success = ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE();
            if(!$resultSet || !$resultSetTicket) {
                throw new \Exception('Error al actualizar la reservación');
            }
            DB::commit();
            $response  = new DataResponse('Se ha guardado su reservación',$success->getCode(),$resultSet);
            return response()->json($response);

        }catch (\Exception $e){
            $error = ErroresExceptionEnum::ERROR_PROCESS_UPDATE();
            DB::rollBack();
            $response = new DataResponse($error->getMessage().$e->getMessage(),$error->getCode(),$e->getTrace());
            return response()->json($response, ResponseData::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id, Reservations $reservations) {
        try{
            DB::beginTransaction();
            $reservation = $reservations->where('id',$id);
            $data = $reservation->first();
            $ticket =  Tickets::where('id', $data->tickets_id)->update([
                'status' => 0
            ]);
            $reservation->update([
              'status' => 0
            ]);
            DB::commit();
            $sucess = ErroresExceptionEnum::SUCCESS_PROCESS_DELETE();
            $response = new DataResponse($sucess->getMessage(),$sucess->getCode(),$id);
            DB::rollBack();
            return response()->json($response, ResponseData::HTTP_OK);
        }catch (\Exception $e){
            $error = ErroresExceptionEnum::ERROR_PROCESS_DELETE();
            $response = new DataResponse($error->getMessage().$e->getMessage(),$error->getCode(),$e->getTrace());
            DB::rollBack();
            return response()->json($response, ResponseData::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
