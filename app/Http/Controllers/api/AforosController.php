<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Interfaces\DataResponse;
use App\Models\Interfaces\ErroresExceptionEnum;
use Illuminate\Http\Request;
use App\Models\Aforos;
use Illuminate\Http\Response as ResultSetResponse;
use Illuminate\Support\Facades\DB;

class AforosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $resultSet = Aforos::where('status',1)->with(['distribucionInf','partido'])->get();
            return response()->json($resultSet);
        }catch (\Exception $ex) {
            $heaader = ErroresExceptionEnum::ERROR_PROCESS_INSERT();
            $reponse = new DataResponse($ex->getMessage(),$heaader->getCode(), []);
            return response()->json($reponse,ResultSetResponse::HTTP_INTERNAL_SERVER_ERROR);
         }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Aforos  $aforos)
    {
        try {
            $request->validate([
                'partido' => 'unique:aforos'
            ]);
            DB::beginTransaction();
            $resultSet = $aforos->create($request->all());
            DB::commit();
            $header = ErroresExceptionEnum::SUCCESS_PROCESS_INSERT();
            $response = new DataResponse($header->getMessage(),$header->getCode(),$resultSet);
            return response()->json($response,ResultSetResponse::HTTP_OK);
        }catch (\Exception $exception) {
            DB::rollBack();
            $heaader = ErroresExceptionEnum::ERROR_PROCESS_INSERT();
            $reponse = new DataResponse($exception->getMessage(),$heaader->getCode(), $request->all());
            return response()->json($reponse,ResultSetResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
