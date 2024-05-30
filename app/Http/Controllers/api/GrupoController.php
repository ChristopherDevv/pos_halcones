<?php

namespace App\Http\Controllers\api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\GruposAsientos;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseData;

use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;


class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function constantCortesy()
    {
        try {

            $groupo = GruposAsientos::where([
                ['grupo', '=', 2],
                ['tipo_grupo', '=', 1]
            ])->get();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), true, $groupo);

            return response()->json($response);


        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, null);

            return response()->json($response);

        }

    }

    public function constantConsign()
    {
        try {

            $groupo = GruposAsientos::where([
                ['grupo', '=', 4],
                ['tipo_grupo', '=', 1]
            ])->get();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), true, $groupo);

            return response()->json($response);


        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, null);

            return response()->json($response);

        }


    }

    public function constantReservation()
    {
        try {

            $groupo = GruposAsientos::where([
                ['grupo', '=', 1],
                ['tipo_grupo', '=', 1]
            ])->get();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), true, $groupo);

            return response()->json($response);


        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, null);

            return response()->json($response);

        }

    }

    public function storage(Request $request)
    {
        try
        {
            $group = $request->only('grupo','nombre','descripcion', 'tipo_grupo');

            $seatGroup = GruposAsientos::where('nombre', '=', $group['nombre'] )->first();

            if ($seatGroup == null)
            {
                $seatGroup = GruposAsientos::create($group);

                return response()->json(new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getCode(), $seatGroup ));
            }

            return response()->json(new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getCode(), $seatGroup ));
        }
        catch (\Throwable $th)
        {
            return response()->json(new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage(), ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getCode(), "Grupo"));
        }

    }

}
