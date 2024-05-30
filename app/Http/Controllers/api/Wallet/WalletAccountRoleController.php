<?php

namespace App\Http\Controllers\api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Wallet\WalletAccountRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletAccountRoleController extends Controller
{
    /* 
    *
    *  GET all wallet account roles by Christoper Patiño
    *
    */
    public function index()
    {
        try {

            $walletAccountRoles = WalletAccountRole::all();
            return response()->json([
                'message' => "Success, all wallet account roles",
                'data' => $walletAccountRoles
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Error, can not get wallet account roles",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new wallet account role by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();

            $request->validate([
                'name' => 'required|max:255',
                'description' => 'nullable|max:255'
            ]);

            /* 
            * Validacion de datos
            */
            $walletAccountName = str_replace(' ', '_', strtolower($request->name));
            $existWalletAccountRole = WalletAccountRole::where('name', $walletAccountName)->first();
            if ($existWalletAccountRole) {
                return response()->json([
                    'message' => "Error, wallet account role already exist"
                ], 400);
            }

            /* 
            * Creacion de una nueva instancia de wallet account role
            */
            $walletAccountRole = new WalletAccountRole();
            $walletAccountRole->name = $walletAccountName;
            $walletAccountRole->description = $request->description ?? null;
            $walletAccountRole->is_active = true;
            $walletAccountRole->save();

            DB::commit();

            return response()->json([
                'message' => "Success, wallet account role created",
                'data' => $walletAccountRole
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error, can not create wallet account role",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }
}
