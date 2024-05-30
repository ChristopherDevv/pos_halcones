<?php

namespace App\Http\Controllers\api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Wallet\WalletRechargeAmount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletRechargeAmountController extends Controller
{
    /* 
    *
    * Get all recharge amounts where is_active = true by Christoper Patiño
    *
    */
    public function index()
    {
        try {

            $recharge_amounts = WalletRechargeAmount::where('is_active', true)->get();

            return response()->json([
                'message' => 'Recharge amounts found',
                'data' => $recharge_amounts
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, not found recharge amounts',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new recharge amount by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'amount' => 'required|numeric',
                'description' => 'nullable|string',
                'is_active' => 'required|boolean'
            ]);

            /* 
            * Validacion de datos
            */
            $amountToCreate = WalletRechargeAmount::where('amount', $request->amount)->first();
            if ($amountToCreate) {
                return response()->json([
                    'message' => 'Error, recharge amount already exists',
                    'data' => $amountToCreate
                ], 400);
            }

            /* 
            * Creacion de una nueva instancia de WalletRechargeAmount
            */
            $newAmountRecharge = new WalletRechargeAmount();
            $newAmountRecharge->amount = $request->amount;
            $newAmountRecharge->description = $request->description ?? null;
            $newAmountRecharge->is_active = $request->is_active;
            $newAmountRecharge->save();

            DB::commit();

            return response()->json([
                'message' => 'Recharge amount created',
                'data' => $newAmountRecharge
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, recharge amount not created',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Change the status of a recharge amount by Christoper Patiño
    *
    */
    public function changeStatus(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'wallet_recharge_amount_ids' => 'required|array',
                'is_active' => 'required|boolean'
            ]);

            /* 
            * Validacion de datos
            */
            $rechargeAmounts = WalletRechargeAmount::whereIn('id', $request->wallet_recharge_amount_ids)->get();
            if ($rechargeAmounts->isEmpty()) {
                return response()->json([
                    'message' => 'Error, recharge amount not found',
                    'data' => $request->wallet_recharge_amount_ids
                ], 404);
            }

            /* 
            * Cambio de estado de los montos de recarga
            */
            foreach ($rechargeAmounts as $rechargeAmount) {
                $rechargeAmount->is_active = $request->is_active;
                $rechargeAmount->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Recharge amount status changed',
                'data' => $rechargeAmounts
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, recharge amount status not changed',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
