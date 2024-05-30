<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use App\Models\PrecioMembresia;

class PreciosMembresiaController extends Controller
{

    public function index()
    {

        try {

            $priceMembership = PrecioMembresia::all();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), true, $priceMembership);

            return response()->json($response);


        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, []);

            return response()->json($response);

        }

    }


    public function show($id)
    {

        try {

            $priceMembership = PrecioMembresia::find($id);

            if (!$priceMembership)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), true, []);

                return response()->json($response);
            }

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_SHOW()->getMessage(), true, $priceMembership);

            return response()->json($response);


        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_SHOW()->getMessage(), false, []);

            return response()->json($response);

        }

    }

    public function storage(Request $request)
    {
        try {

            $savePriceMemberShip = PrecioMembresia::create($request->only('price') );

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_INSERT()->getMessage(), true, $savePriceMemberShip);

            return response()->json($response);

        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_INSERT()->getMessage(), false, []);

            return response()->json($response);
        }

    }

    public function update(Request $request, $id)
    {
        try {

            $precioMembresia = PrecioMembresia::find($id);

            if (!$precioMembresia)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), true, []);

                return response()->json($response);
            }

            $precioMembresia-> price = $request->get('price');
            $precioMembresia-> updated_at = Carbon::now();

            $isUpdate = $precioMembresia-> save();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_UPDATE()->getMessage(), true, $precioMembresia);

            return response()->json($response);

        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_UPDATE()->getMessage(), false, []);

            return response()->json($response);
        }

    }


    public function destroy($id)
    {
        try {

            $precioMembresia = PrecioMembresia::find($id);

            if (!$precioMembresia)
            {
                $response = new DataResponse(ErroresExceptionEnum::OBJECT_NOT_FOUND()->getMessage(), true, []);

                return response()->json($response);
            }

            $precioMembresia->delete();

            $response = new DataResponse(ErroresExceptionEnum::SUCCESS_PROCESS_DELETE()->getMessage(), true, $precioMembresia);

            return response()->json($response);

        } catch (\Throwable $th) {

            $response = new DataResponse(ErroresExceptionEnum::ERROR_PROCESS_DELETE()->getMessage(), false, []);

            return response()->json($response);
        }

    }

}
