<?php

namespace App\Http\Controllers\api\PointOfSALE;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\InventoryTransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransactionTypeController extends Controller
{
    /* 
    *
    * Get all Inventory Transaction Types where is_active by Christoper Patiño
    *
    */
    public function index()
    {
        try {

            $inventoryTransactionTypes = InventoryTransactionType::where('is_active', 1)->get();

            return response()->json([
                'message' => 'Get all Inventory Transaction Types',
                'data' => $inventoryTransactionTypes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, do not get Inventory Transaction Types',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new Inventory Transaction Type by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string',
                'description' => 'required|string',
                'is_active' => 'required|boolean'
            ]);

           /* 
           * Validacion de datos
           */
          $inventoryTransactionTypeName = str_replace(' ', '_', strtolower($request->name));
          $inventoryTransactionType = InventoryTransactionType::where('name', $inventoryTransactionTypeName)->first();
          if($inventoryTransactionType){
              return response()->json([
                  'message' => 'Error, Inventory Transaction Type already exists'
              ], 400);
          }

            $inventoryTransactionType = new InventoryTransactionType();
            $inventoryTransactionType->name = $inventoryTransactionTypeName;
            $inventoryTransactionType->description = $request->description;
            $inventoryTransactionType->is_active = $request->is_active;
            $inventoryTransactionType->save();

            DB::commit();

            return response()->json([
                'message' => 'Inventory Transaction Type created',
                'data' => $inventoryTransactionType
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, Inventory Transaction Type not created',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
