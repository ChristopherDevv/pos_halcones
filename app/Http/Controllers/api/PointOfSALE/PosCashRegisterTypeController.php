<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\PosCashRegisterType;
use App\Models\PointOfSale\PosProductWarehouse;
use Illuminate\Http\Request;

class PosCashRegisterTypeController extends Controller
{

    /* 
    *
    * Get all cash register types by Christoper Patiño
    *
    */
    public function index()
    {
        try {

            $cashRegisterTypes = PosCashRegisterType::all();
            
            return response()->json([
                'message' => 'Success, cash register types retrieved successfully.',
                'data' => $cashRegisterTypes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error to get cash register types',
                'error data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Get all cash register types by product warehouse by Christoper Patiño
    *
    */
    public function indexByWarehouse(Request $request)
    {
        try {
            
            $request->validate([
                'pos_product_warehouse_id' => 'required|integer',
            ]);
            /* 
            * Buscamos los tipos de cajas registradoras que tiene un almacen de productos
            */
            $cashRegisterTypes = PosCashRegisterType::whereHas('pos_product_warehouses', function ($query) use ($request) {
                $query->where('pos_product_warehouse_id', $request->pos_product_warehouse_id);
            })->get();
            
            return response()->json([
                'message' => 'Success, cash register types retrieved successfully.',
                'data' => $cashRegisterTypes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error to get cash register types',
                'error data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new cash register type by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'pos_product_warehouse_id' => 'nullable|integer',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'cash_register_number' => 'nullable|integer',
            ]);

            if($request->pos_product_warehouse_id) {
                $posProductWarehouseId = PosProductWarehouse::where('id', $request->pos_product_warehouse_id)->first();
                if (!$posProductWarehouseId) {
                    return response()->json([
                        'message' => 'Error, product warehouse id does not exist.',
                        'data' => $posProductWarehouseId
                    ], 400);
                }    
            }
            /* 
            * validamos si ya existe un tipo de caja con el mismo nombre
            */
            $posCashRegisterTypeName = str_replace(' ', '_', strtolower($request->name));
            $posCashRegisterTypeExist = PosCashRegisterType::where('name', $posCashRegisterTypeName)->first();

            if ($posCashRegisterTypeExist) {
                return response()->json([
                    'message' => 'Error, cash register type already exists',
                    'data' => $posCashRegisterTypeExist
                ], 400);
            }

            /* 
            * Validamos si esta caja ya esta asociada a un almacen de productos
            */
            if($request->pos_product_warehouse_id) {
                $posCashRegisterTypeExist = PosCashRegisterType::whereHas('pos_product_warehouses', function ($query) use ($request) {
                    $query->where('pos_product_warehouse_id', $request->pos_product_warehouse_id);
                })->first();
                if ($posCashRegisterTypeExist) {
                    return response()->json([
                        'message' => 'Error, cash register type already exists in this product warehouse',
                        'data' => $posCashRegisterTypeExist
                    ], 400);
                }
            }

            /* 
            * creacion de instancia de tipo de caja
            */
            $posCashRegisterType = new PosCashRegisterType();
            $posCashRegisterType->name = $posCashRegisterTypeName;
            $posCashRegisterType->description = $request->description;
            /* 
            * Incrementar el numero de caja registradora dependiendo de la cantidad de la ultima caja registradora creada
            */
            $posCashRegisterType->cash_register_number = PosCashRegisterType::max('cash_register_number') + 1;
            $posCashRegisterType->save();

            /* 
            * asociamos el tipo de caja con el almacen de productos
            */
            if($request->pos_product_warehouse_id){
                $posCashRegisterType->pos_product_warehouses()->attach($posProductWarehouseId);
            }

            return response()->json([
                'message' => 'Success, cash register type created successfully.',
                'data' => $posCashRegisterType
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error to create a new cash register type',
                'error data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Associate a cash register type with a product warehouse by Christoper Patiño
    *
    */
    public function associateToWarehouse(Request $request)
    {
        try {

            $request->validate([
                'pos_cash_register_type_id' => 'required|integer',
                'pos_product_warehouse_id' => 'required|integer',
            ]);

            /* 
            * Validacion de datos
            */

            $posCashRegisterType = PosCashRegisterType::where('id', $request->pos_cash_register_type_id)->first();
            if (!$posCashRegisterType) {
                return response()->json([
                    'message' => 'Error, cash register type id does not exist.',
                    'data' => $posCashRegisterType
                ], 400);
            }

            $posProductWarehouse = PosProductWarehouse::where('id', $request->pos_product_warehouse_id)->first();
            if (!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, product warehouse id does not exist.',
                    'data' => $posProductWarehouse
                ], 400);
            }

           /* 
           * Validar que el tipo de caja no este asociado al almacen de productos
           */
            $posCashRegisterTypeExist = PosCashRegisterType::where('id', $request->pos_cash_register_type_id)
            ->whereHas('pos_product_warehouses', function ($query) use ($request) {
                $query->where('pos_product_warehouse_id', $request->pos_product_warehouse_id);
            })->first();
        
            if ($posCashRegisterTypeExist) {
                return response()->json([
                    'message' => 'Error, cash register type already exists in this product warehouse',
                    'data' => $posCashRegisterTypeExist
                ], 400);
            }

            /* 
            * Asociar el tipo de caja con el almacen de productos
            */
            $posCashRegisterType->pos_product_warehouses()->attach($posProductWarehouse);

            return response()->json([
                'message' => 'Success, cash register type associated with product warehouse successfully.',
                'data' => $posCashRegisterType
            ]); 

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error to associate cash register type with product warehouse',
                'error data' => $e->getMessage()
            ], 500);
        }
    }
}
