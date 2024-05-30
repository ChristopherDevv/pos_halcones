<?php

namespace App\Http\Controllers\api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Wallet\WalletTransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletTransactionTypeController extends Controller
{
    /* 
    *
    * Get all wallet transaction types
    *
    */
    public function index()
    {
        try {

            $walletTransactionTypes = WalletTransactionType::all();

            return response()->json([
                'message' => 'Succcess, wallet transaction types retrieved',
                'data' => $walletTransactionTypes
            ], 200);
        

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, could not get wallet transaction types',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new wallet transaction type
    *
    */
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:255'
            ]);

            /* 
            * Validacion de datos
            */
            $walletTransactionTypeName = str_replace(' ', '_', strtolower($request->name));
            $existName = WalletTransactionType::where('name', $walletTransactionTypeName)->first();
            if ($existName) {
                return response()->json([
                    'message' => 'Error, wallet transaction type already exists'
                ], 400);
            }

            /* 
            * Crear una nueva instancia de wallet transaction type
            */
            $walletTransactionType = new WalletTransactionType();
            $walletTransactionType->name = $walletTransactionTypeName;
            $walletTransactionType->description = $request->description ?? null;
            $walletTransactionType->color = $request->color ?? null;
            $walletTransactionType->save();

            DB::commit();

            return response()->json([
                'message' => 'Success, wallet transaction type created',
                'data' => $walletTransactionType
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, could not create wallet transaction type',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
