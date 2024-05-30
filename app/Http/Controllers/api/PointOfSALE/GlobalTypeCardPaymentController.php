<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\GlobalTypeCardPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalTypeCardPaymentController extends Controller
{
    /* 
    *
    * Get all card payment types where is_active = true by Christoper Patiño
    *
    */
    public function index()
    {
        try {
            $card_payment_types = GlobalTypeCardPayment::where('is_active', true)->get();

            return response()->json([
                'message' => 'Card payment types found',
                'data' => $card_payment_types
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, not found card payment types',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new card payment type by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'is_active' => 'required|boolean'
            ]);

            /* 
            * Validacion de datos
            */
            $globalTypeCardPaymentName = str_replace(' ', '', strtolower($request->name));
            $existName = GlobalTypeCardPayment::where('name', $globalTypeCardPaymentName)->first();
            if ($existName) {
                return response()->json([
                    'message' => 'Error, card payment type already exists',
                    'data' => $existName
                ], 400);
            }

            /* 
            * Creacion de una nueva instancia de GlobalTypeCardPayment
            */
            $newGlobalTypeCardPayment = new GlobalTypeCardPayment();
            $newGlobalTypeCardPayment->name = $request->name;
            $newGlobalTypeCardPayment->description = $request->description ?? null;
            $newGlobalTypeCardPayment->is_active = $request->is_active;
            $newGlobalTypeCardPayment->save();

            DB::commit();

            return response()->json([
                'message' => 'Card payment type created',
                'data' => $newGlobalTypeCardPayment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, not found card payment types',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
