<?php

namespace App\Http\Controllers\api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Wallet\WalletTransactionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletTransactionStatusController extends Controller
{
    /* 
    *
    * Get all wallet transaction statuses
    *
    */
    public function index()
    {
        try {

            $walletTransactionStatuses = WalletTransactionStatus::all();

            return response()->json([
                'message' => 'Succcess, wallet transaction statuses retrieved',
                'data' => $walletTransactionStatuses
            ], 200);
        

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, could not get wallet transaction statuses',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new wallet transaction status
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
            * validacion de datos
            */
            $walletTransactionStatusName = str_replace(' ', '_', strtolower($request->name));
            $existName = WalletTransactionStatus::where('name', $walletTransactionStatusName)->first();
            if ($existName) {
                return response()->json([
                    'message' => 'Error, wallet transaction status already exists',
                    'data' => $existName
                ], 400);
            }

            /* 
            * Creamos una nueva instancia de WalletTransactionStatus
            */
            $walletTransactionStatus = new WalletTransactionStatus();
            $walletTransactionStatus->name = $walletTransactionStatusName;
            $walletTransactionStatus->description = $request->description ?? null;
            $walletTransactionStatus->color = $request->color ?? null;
            $walletTransactionStatus->save();

            DB::commit();

            return response()->json([
                'message' => 'Success, wallet transaction status created',
                'data' => $walletTransactionStatus
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, could not create wallet transaction status',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
