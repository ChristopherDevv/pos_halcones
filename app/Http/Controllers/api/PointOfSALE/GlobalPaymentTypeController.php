<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\GlobalPaymentType;
use Illuminate\Http\Request;

class GlobalPaymentTypeController extends Controller
{
    /* 
    *
    * Get all payment types by Christoper Patiño
    *
    */
    public function index()
    {
        try {
            $globalPaymentTypes = GlobalPaymentType::where('is_active', true)->get();
            return response()->json([
                'message' => 'Success, payment types retrieved successfully.',
                'data' => $globalPaymentTypes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, payment types not found',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new payment type by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
            ]);

            /* 
            * validacion de existencia de datos
            */
            $globalPaymentTypeName = str_replace(' ', '_', strtolower($request->name));
            $existName = GlobalPaymentType::where('name', $globalPaymentTypeName)->first();
            if ($existName) {
                return response()->json([
                    'message' => 'Error, payment type name already exists.',
                    'data' => $existName
                ], 400);
            }

            $globalPaymentType = new GlobalPaymentType();
            $globalPaymentType->name = $globalPaymentTypeName;
            $globalPaymentType->description = $request->description ? $request->description : null;
            $globalPaymentType->is_active = true;
            $globalPaymentType->save();

            return response()->json([
                'message' => 'Success, payment type created successfully.',
                'data' => $globalPaymentType
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, payment type not created',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
