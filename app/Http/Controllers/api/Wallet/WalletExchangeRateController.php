<?php

namespace App\Http\Controllers\api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Wallet\WalletExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletExchangeRateController extends Controller
{
    /* 
    *
    * Get all wallet exchange rates by Christoper Patiño
    *
    */
    public function index()
    {
        try {

            $walletExchangeRates = WalletExchangeRate::all();
            return response()->json([
                'message' => 'Success, all wallet exchange rates',
                'data' => $walletExchangeRates
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Error, not found wallet exchange rates",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new wallet exchange rate by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'from_wallet_currency_id' => 'required|integer',
                'to_wallet_currency_id' => 'required|integer',
                'rate' => 'required|numeric'
            ]);

            /* 
            * validacion de datos
            */
            $existRate = WalletExchangeRate::where('from_wallet_currency_id', $request->from_wallet_currency_id)
                ->where('to_wallet_currency_id', $request->to_wallet_currency_id)
                ->first();
            
            if($existRate){
                return response()->json([
                    'message' => 'Error, wallet exchange rate already exist'
                ], 409);
            }

            /* 
            * creacion de una nueva instancia de WalletExchageRate
            */
            $newWalletExchangeRate = new WalletExchangeRate();
            $newWalletExchangeRate->from_wallet_currency_id = $request->from_wallet_currency_id;
            $newWalletExchangeRate->to_wallet_currency_id = $request->to_wallet_currency_id;
            $newWalletExchangeRate->rate = $request->rate;
            $newWalletExchangeRate->save();

            DB::commit();

            return response()->json([
                'message' => 'Success, wallet exchange rate created',
                'data' => $newWalletExchangeRate
            ], 201);
            

        } catch(\Exception $e) {
            return response()->json([
                'message' => "Error, not found wallet exchange rates",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }
}
