<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\PosMovementType;
use Illuminate\Http\Request;

class PosMovementTypeController extends Controller
{
    /* 
    *
    * Get all pos movement types by Christoper Patiño
    *
    */
    public function index()
    {
        try {
            $posMovementTypes = PosMovementType::all();
            if($posMovementTypes->isEmpty()) {
                return response()->json([
                    'message' => 'Error, pos movement types not found',
                    'data' => []
                ], 404);
            }
            
            return response()->json([
                'message' => 'Success, pos movement types found',
                'data' => $posMovementTypes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, pos movement types not found',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new pos movement type by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:255'
            ]);

            /* 
            * Validaciones de existencia de los datos
            */
            $posMovementTypeName = str_replace(' ', '_', strtolower($request->name));
            $posMovementType = PosMovementType::where('name', $posMovementTypeName)->first();
            if ($posMovementType) {
                return response()->json([
                    'message' => 'Error, pos movement type already exist.',
                    'data' => $posMovementType
                ], 400);
            }

            $posMovementType = new PosMovementType();
            $posMovementType->name = $posMovementTypeName;
            $posMovementType->description = $request->description;
            $posMovementType->save();

            return response()->json([
                'message' => 'Success, pos movement type has been created',
                'data' => $posMovementType
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, pos movement type has not been created',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
