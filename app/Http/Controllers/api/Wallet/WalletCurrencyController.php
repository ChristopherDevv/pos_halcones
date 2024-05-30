<?php

namespace App\Http\Controllers\api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Wallet\WalletCurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletCurrencyController extends Controller
{
    /* 
    *
    * Get all wallet currencies by Christoper Patiño
    *
    */
    public function index()
    {
        try {

            $walletCurrencies = WalletCurrency::all();
            return response()->json([
                'message' => 'Success, all wallet currencies',
                'data' => $walletCurrencies
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Error, not found wallet currencies",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new wallet currency by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {
            
            DB::beginTransaction();
            $request->validate([
                'name' => 'required|max:255',
                'description' => 'nullable|max:255',
                'symbol' => 'required|max:255',
                'image_file' => 'nullable'
            ]);

            /* 
            * validacion de datos
            */
            $walletCurrencyName = str_replace(' ', '_', strtolower($request->name));
            $existName = WalletCurrency::where('name', $walletCurrencyName)->first();
            if($existName){
                return response()->json([
                    'message' => 'Error, wallet currency name already exist'
                ], 409);
            }

            /* 
            * Creacion de una nueva instancia de WalletCurrency
            */
            $newWalletCurrency = new WalletCurrency();
            $newWalletCurrency->name = $walletCurrencyName;
            $newWalletCurrency->description = $request->description;
            $newWalletCurrency->symbol = $request->symbol;
            /* 
            * Imagen recibida como tipo file para guardar en la carpeta posupload
            */
            if($request->hasFile('image_file')){
                $image = $request->file('image_file');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('posupload'), $imageName);
                $newWalletCurrency->image_file = $imageName;
            } else {
                $newWalletCurrency->image_file = null;
            }
            
            $newWalletCurrency->save();

            DB::commit();

            return response()->json([
                'message' => 'Success, new wallet currency created',
                'data' => $newWalletCurrency
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, wallet currency not created',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
