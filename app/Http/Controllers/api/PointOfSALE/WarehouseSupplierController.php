<?php

namespace App\Http\Controllers\api\PointOfSALE;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\WarehouseSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseSupplierController extends Controller
{
    /* 
    *
    * Get all Warehouse Suppliers by Christoper Patiño
    *
    */
    public function index()
    {
        try {

            $warehouseSuppliers = WarehouseSupplier::all();

            return response()->json([
                'message' => 'Get all Warehouse Suppliers',
                'data' => $warehouseSuppliers
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, do not get Warehouse Suppliers',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new Warehouse Supplier by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {
                DB::beginTransaction();
                
                $request->validate([
                    'name' => 'required|string',
                    'last_name' => 'nullable|string',
                    'company_name' => 'nullable|string',
                    'email' => 'required|email',
                    'phone_number' => 'nullable|string',
                    'address' => 'nullable|string',
                    'city' => 'nullable|string',
                    'state' => 'nullable|string',
                    'country' => 'nullable|string',
                    'zip_code' => 'nullable|string',
                    'description' => 'nullable|string'
                ]);
                
    
                /* 
                * Creacion de nueva instancia de Warehouse Supplier
                */
                $warehouseSupplier = new WarehouseSupplier();
                $warehouseSupplier->name = $request->name ?? null;
                $warehouseSupplier->last_name = $request->last_name ?? null;
                $warehouseSupplier->company_name = $request->company_name ?? null;
                $warehouseSupplier->email = $request->email ?? null;
                $warehouseSupplier->phone_number = $request->phone_number ?? null;
                $warehouseSupplier->address = $request->address ?? null;
                $warehouseSupplier->city = $request->city ?? null;
                $warehouseSupplier->state = $request->state ?? null;
                $warehouseSupplier->country = $request->country ?? null;
                $warehouseSupplier->zip_code = $request->zip_code ?? null;
                $warehouseSupplier->description = $request->description ?? null;
                $warehouseSupplier->save();

                DB::commit();
    
                return response()->json([
                    'message' => 'Warehouse Supplier created',
                    'data' => $warehouseSupplier
                ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, Warehouse Supplier not created',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
