<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use Illuminate\Http\Request;
use App\Models\Sorteo;
use Illuminate\Support\Carbon;
use App\Models\Partidos;
use App\Models\SorteoPartido;
use App\Models\Tickets;
use App\Models\SorteoUsuario;
use App\Models\TicketsAsientos;
use App\Models\TemporadaPartido;
use App\Models\EvidenciaSorteoPartido;
use App\Models\CodigoEvidenciaSorteoPartido;
use App\Models\MultimediaEvidenciaSorteoPartido;
use PHPUnit\Util\Exception;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseStatusCodeSame;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Arr;
use App\Models\Interfaces\EstatusAsientosEnum;
use App\Models\Interfaces\TipoDePagos;
use App\Models\Interfaces\EstatusPartidos;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class TemporadaPartidoController extends Controller
{
    /**
     *
     * Zuriel DA
     *
     */

        public function index()
        {
            try
            {
                $sorteo = TemporadaPartido::where('status','=','Activo')->get();

                $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$sorteo);

                return response()->json($response);

            }
            catch (\Exception $e)
            {
                $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),null);

                return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        public function show($id)
        {
            try
            {
                $temporadaPartido = TemporadaPartido::where([['status','=','Activo'], ['id', '=', $id]])->first();

                $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_LIST()->getCode(),$temporadaPartido);

                return response()->json($response);

            }
            catch (\Exception $e)
            {
                $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_LIST()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_LIST()->getCode(),null);

                return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        public function store(Request $request)
        {
            try
            {
                DB::beginTransaction();

                $temporadaPartido = TemporadaPartido::create($request->only('name', 'description'));

                $listSeat = app(\App\Http\Controllers\api\AsientosController::class)->generateSeatForSeason( $temporadaPartido->id )->getData(true);


                if ( $listSeat['status'] == ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode() )
                {
                    DB::commit();

                    $response = new DataResponse( ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(), $temporadaPartido);

                    return response()->json($response);
                }
                else
                {
                    DB::rollBack();

                    return response()->json($listSeat);
                }
            }
            catch (\Exception $e)
            {
                DB::rollBack();

                $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(),'Temporada');

                return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }



        public function update($id, Request $request)
        {
            try
            {
                if ($id != $request->get('id'))
                {
                    $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(),ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null );

                    return response()->json($response,Response::HTTP_NOT_FOUND);
                }

                $temporadaPartido = TemporadaPartido::find($id);

                $temporadaPartido-> status  = $request->get('status');
                $temporadaPartido-> name  = $request->get('name');
                $temporadaPartido-> description  = $request->get('description');

                $temporadaPartido-> save();

                $temporadaPartido-> refresh();

                $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(),ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getCode(), $temporadaPartido );

                return response()->json($response);
            }
            catch (\Exception $e)
            {
                $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage().$e->getMessage(),ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getCode(),null);

                return response()->json($response,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        public function destroy($id)
        {
            try
            {
                $temporadaPartido = TemporadaPartido::find($id);

                if (!$temporadaPartido)
                {
                    $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), ErroresExceptionEnum::OBJECT_NOT_FOUND()->getCode(), null);

                    return response()->json($response);
                }
                $temporadaPartido-> delete();

                $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getCode(), $temporadaPartido);

                return response()->json($response);
            }
            catch (\Throwable $e)
            {
                $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getMessage().$e->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getCode(), null);

                return response()->json($response);
            }
        }
}
