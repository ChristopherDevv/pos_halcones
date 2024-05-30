<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\PosUnitMeasurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosUnitMeasurementController extends Controller
{
    /* 
    *
    * Get all pos unit measurements by Christoper Patiño
    *
    */
    public function index()
    {
        try {

            $posUnitMeasurements = PosUnitMeasurement::all();
            return response()->json([
                'message' => 'Success, all pos unit measurements',
                'data' => $posUnitMeasurements
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, not found pos unit measurements',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new pos unit measurement by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'abbreviation' => 'required|string|max:255'
            ]);

            /* 
            * validacion de datos
            */
            $posUnitMeasurementName = str_replace(' ', '', strtolower($request->name));
            $existName = PosUnitMeasurement::where('name', $posUnitMeasurementName)->first();
            if ($existName) {
                return response()->json([
                    'message' => 'Error, pos unit measurement name already exist',
                    'data' => $existName
                ], 400);
            }

            /* 
            * creacion de una nueva instancia de pos unit measurement
            */
            $posUnitMeasurement = new PosUnitMeasurement();
            $posUnitMeasurement->name = $posUnitMeasurementName;
            $posUnitMeasurement->description = $request->description ?? null;
            $posUnitMeasurement->abbreviation = $request->abbreviation;
            $posUnitMeasurement->save();

            DB::commit();

            return response()->json([
                'message' => 'Success, pos unit measurement created',
                'data' => $posUnitMeasurement
            ], 201);
           

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, pos unit measurement not created',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
