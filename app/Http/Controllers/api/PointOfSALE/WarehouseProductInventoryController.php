<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\PosProductWarehouse;
use App\Models\PointOfSale\ProductsForBucketvendor;
use App\Models\PointOfSale\WarehouseProductCatalog;
use App\Models\PointOfSale\WarehouseProductInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseProductInventoryController extends Controller
{
    /*  
    *
    * Get all warehouse product inventories by Christoper Patiño
    *
    */
    public function index()
    {
        try {

            $warehouseProductInventories = WarehouseProductInventory::all();
            return response()->json([
                'message' => 'Success, all warehouse product inventories',
                'data' => $warehouseProductInventories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Error, not found warehouse product inventories",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Get warehouse product inventory, product catalog, subcategory and category by id by Christoper Patiño
    *
    */
    public function showRelationshipsByProductWarehouse(Request $request)
{
    try {
        $request->validate([
            'pos_product_warehouse_id' => 'required|integer'
        ]);

        $posProductWarehouseId = $request->pos_product_warehouse_id;
        $posProductWarehouse = PosProductWarehouse::find($posProductWarehouseId);
        if (!$posProductWarehouse) {
            return response()->json([
                'message' => 'Error, pos product warehouse not found',
                'data' => $posProductWarehouseId
            ], 404);
        }

        $warehouseProductInventories = $posProductWarehouse->warehouse_product_inventories;
        if ($warehouseProductInventories->isEmpty()) {
            return response()->json([
                'message' => 'Error, warehouse product inventories not found',
                'data' => $warehouseProductInventories
            ], 404);
        }

        // Agrupar los inventarios por el código de venta
        $groupedInventories = $warehouseProductInventories->groupBy('warehouse_product_catalog.sales_code');

        $response = $groupedInventories->map(function ($items, $sales_code) {
            // Tomar el primer elemento como base para los datos comunes
            $baseItem = $items->first()->warehouse_product_catalog;

            // Crear un arreglo para los datos variables
            $variableData = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'stock' => $item->stock,
                    'price' => $item->sale_price,
                    'discount_price' => $item->discount_sale_price,
                    'clothing_size' => $item->global_inventory->clothing_size->name ?? 'no aplica',
                    'clothing_size_abbr' => $item->global_inventory->clothing_size->abbreviation ?? 'no aplica',
                ];
            });

            // Construir la respuesta
            return [
                'product' => [
                    'name' => $baseItem->name,
                    'description' => $baseItem->description,
                    'unit_measurement' => $baseItem->pos_unit_measurement->name,
                    'unit_measurement_abbr' => $baseItem->pos_unit_measurement->abbreviation,
                    'unit_measurement_quantity' => $baseItem->unit_measurement_quantity,
                    'is_active' => $baseItem->is_active,
                    'sales_code' => $sales_code,
                    'is_clothing' => $baseItem->is_clothing,
                    'clothing_category' => $baseItem->clothing_category->name ?? 'no aplica',
                    'associated_categories' => $baseItem->pos_product_subcategories->map(function ($subcategory) {
                        return $subcategory->pos_product_categories->pluck('name');
                    })->collapse()->unique()->toArray(),
                    'associated_subcategories' => $baseItem->pos_product_subcategories->pluck('name')->toArray(),
                    'images' => $baseItem->images->pluck('uri_path')->toArray() ?? 'No images found',
                    'variable_data' => $variableData
                ],
            ];
        });

        return response()->json([
            'message' => 'Success, all warehouse product inventories, product catalogs, subcategories and categories',
            'data' => $response->values()->all()
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => "Error, not found warehouse product inventory",
            "error_data" => $e->getMessage()
        ], 500);
    }
}

    /* 
    *
    * Create a new warehouse product inventory by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'pos_product_warehouse_id' => 'required|integer',
                'warehouse_product_catalog_id' => 'required|integer',
                'price' => 'required',
                'stock' => 'required|integer',
                'is_active' => 'nullable|boolean'
            ]);

            /* 
            * validacion de datos
            */
            $posProductWarehouse = PosProductWarehouse::find($request->pos_product_warehouse_id);
            if (!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, pos product warehouse not found',
                    'data' => $request->pos_product_warehouse_id
                ], 404);
            }

            $warehouseProductCatalog = WarehouseProductCatalog::find($request->warehouse_product_catalog_id);
            if (!$warehouseProductCatalog) {
                return response()->json([
                    'message' => 'Error, warehouse product catalog not found',
                    'data' => $request->warehouse_product_catalog_id
                ], 404);
            }

            /* 
            * Creacion de una nueva instancia de WarehouseProductInventory
            */
            $warehouseProductInventory = new WarehouseProductInventory();
            $warehouseProductInventory->pos_product_warehouse_id = $request->pos_product_warehouse_id;
            $warehouseProductInventory->warehouse_product_catalog_id = $request->warehouse_product_catalog_id;
            $warehouseProductInventory->price = $request->price;
            $warehouseProductInventory->stock = $request->stock;
            $warehouseProductInventory->is_active = $request->is_active ?? true;
            $warehouseProductInventory->save();

            DB::commit();

            return response()->json([
                'message' => 'Success, warehouse product inventory created',
                'data' => $warehouseProductInventory
            ], 201);
            
           
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error, warehouse product inventory not created",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Pass products to bucketvendors by Christoper Patiño
    *
    */
    public function passProductsToBucketvendor(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'products_for_bucketvendor_id' => 'required|integer',
                'warehouse_product_inventories' => 'required|array',
            ]);

            $productsForBucketvendor = ProductsForBucketvendor::find($request->products_for_bucketvendor_id);
            if (!$productsForBucketvendor) {
                return response()->json([
                    'message' => 'Error, products for bucketvendor not found',
                    'data' => $request->products_for_bucketvendor_id
                ], 404);
            }

            $warehouseProductInventories = $request->warehouse_product_inventories;
            foreach ($warehouseProductInventories as $inventory) {
                $warehouseProductInventory = WarehouseProductInventory::find($inventory['warehouse_product_inventory_id']);
                if (!$warehouseProductInventory) {
                    return response()->json([
                        'message' => 'Error, warehouse product inventory not found',
                        'data' => $inventory['warehouse_product_inventory_id']
                    ], 404);
                }

                /* 
                * Comprovar que haya stock suficiente
                */
                if ($warehouseProductInventory->stock < $inventory['stock_transfered']) {
                    DB::rollBack(); 
                    return response()->json([
                        'message' => 'Error, insufficient stock',
                        'data' => $inventory['warehouse_product_inventory_id']
                    ], 400);
                }

                /* 
                * Comprovar que el producto no este en la lista de productos del vendedor el dia actual bucketvendor
                * si ya esta, sumar la cantidad
                */
                $productExists = $productsForBucketvendor->warehouse_product_inventories()
                ->where('warehouse_product_inventories.id', $inventory['warehouse_product_inventory_id'])
                ->whereHas('products_for_bucketvendors', function ($query) use ($productsForBucketvendor) {
                    $query->where('products_for_bucketvendor_id', $productsForBucketvendor->id)
                          ->whereDate('warehouse_product_bucketvendor.created_at', now()->toDateString());
                })
                ->first();
            
                if ($productExists) {
                    $productExists->pivot->quantity += $inventory['stock_transfered'];
                    $productExists->pivot->save();
                } else {
                    $productsForBucketvendor->warehouse_product_inventories()->attach($inventory['warehouse_product_inventory_id'], ['quantity' => $inventory['stock_transfered']]);
                }
                
                /* 
                * Restar la cantidad de stock del inventario
                */
                $warehouseProductInventory->stock -= $inventory['stock_transfered'];
                $warehouseProductInventory->save();

            }

            DB::commit();

            $productsForBucketvendor->load('warehouse_product_inventories');

            return response()->json([
                'message' => 'Success, products passed to bucketvendor',
                'data' => $productsForBucketvendor
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error, not found warehouse product inventory",
                "error_data" => $e->getMessage()
            ], 500);
        }

    }

    /* 
    *
    * Return products of a bucketvendor to the warehouse by Christoper Patiño
    *
    */
    public function returnProductsToWarehouse(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'products_for_bucketvendor_id' => 'required|integer',
                'warehouse_product_inventories' => 'required|array',
            ]);

            $productsForBucketvendor = ProductsForBucketvendor::find($request->products_for_bucketvendor_id);
            if (!$productsForBucketvendor) {
                return response()->json([
                    'message' => 'Error, products for bucketvendor not found',
                    'data' => $request->products_for_bucketvendor_id
                ], 404);
            }

            $warehouseProductInventories = $request->warehouse_product_inventories;
            foreach ($warehouseProductInventories as $inventory) {
                $warehouseProductInventory = WarehouseProductInventory::find($inventory['warehouse_product_inventory_id']);
                if (!$warehouseProductInventory) {
                    return response()->json([
                        'message' => 'Error, warehouse product inventory not found',
                        'data' => $inventory['warehouse_product_inventory_id']
                    ], 404);
                }

                /* 
                * Comprovar que el producto este en la lista de productos del vendedor el dia actual bucketvendor
                * si no esta, retornar un error
                */
                $productExists = $productsForBucketvendor->warehouse_product_inventories()
                ->where('warehouse_product_inventories.id', $inventory['warehouse_product_inventory_id'])
                ->whereHas('products_for_bucketvendors', function ($query) use ($productsForBucketvendor) {
                    $query->where('products_for_bucketvendor_id', $productsForBucketvendor->id)
                          ->whereDate('warehouse_product_bucketvendor.created_at', now()->toDateString());
                })
                ->first();
            
                if (!$productExists) {
                    DB::rollBack(); 
                    return response()->json([
                        'message' => 'Error, product not found in bucketvendor list',
                        'data' => $inventory['warehouse_product_inventory_id']
                    ], 400);
                }

                /* 
                * Validar que la cantidad a retornar no sea mayor a la cantidad actual
                */
                if ($productExists->pivot->quantity < $inventory['stock_returned']) {
                    DB::rollBack(); 
                    return response()->json([
                        'message' => 'Error, the quantity to be returned is greater than the current quantity',
                        'data' => $inventory['warehouse_product_inventory_id']
                    ], 400);
                }

                /* 
                * restar la cantidad de stock del producto en la lista del vendedor
                */
                $productExists->pivot->quantity -= $inventory['stock_returned'];
                $productExists->pivot->save();

                /* 
                * Sumar la cantidad de stock del inventario
                */
                $warehouseProductInventory->stock += $inventory['stock_returned'];
                $warehouseProductInventory->save();

            }

            //DB::commit();

            $productsForBucketvendor->load('warehouse_product_inventories');

            return response()->json([
                'message' => 'Success, products returned to warehouse',
                'data' => $productsForBucketvendor
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error, not found warehouse product inventory",
                "error_data" => $e->getMessage()
            ], 500);
        }

    }
}


