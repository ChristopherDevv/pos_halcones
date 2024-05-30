<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\BucketVendorProduct;
use App\Models\PointOfSale\PosProductWarehouse;
use App\Models\PointOfSale\WarehouseProductInventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BucketVendorProductController extends Controller
{
    /* 
    *
    * Create a new bucket vendor product by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'user_bucket_vendor_id' => 'required|integer',
                'pos_product_warehouse_id' => 'required|integer',
            ]);

            /* 
            * validacion de datos
            */
            $posProductWarehouse = PosProductWarehouse::find($request->pos_product_warehouse_id);
            if (!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, product not found'
                ], 404);
            }

            $userBucketVendor = User::find($request->user_bucket_vendor_id);
            if (!$userBucketVendor) {
                return response()->json([
                    'message' => 'Error, vendor not found'
                ], 404);
            }

            /* 
            * Crar una nueva instancia de BucketVendorProduct
            */
            $newBucketVendorProduct = new BucketVendorProduct();
            $newBucketVendorProduct->user_bucket_vendor_id = $request->user_bucket_vendor_id;
            $newBucketVendorProduct->pos_product_warehouse_id = $request->pos_product_warehouse_id;
            $newBucketVendorProduct->save();
            
            DB::commit();

            return response()->json([
                'message' => 'Success, new bucjket vendor product created',
                'data' => $newBucketVendorProduct
            ], 201);
            

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, product not assigned to vendor',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Assing products to a bucket vendor for sale by Christoper Patiño
    *
    */
    public function assignProductToBucketVendor(Request $request)
    {
        try {

            DB::beginTransaction();
             /* 
             * ejemplo de datos recividos esperados
             *  {
                "bucket_vendor_product_id": 1,
                "warehouse_product": [
                    {
                        "warehouse_product_inventory_id": 1,
                        "stock_received": 10
                    },
                ]
                }
            */
            $request->validate([
                'bucket_vendor_product_id' => 'required|integer',
                'warehouse_product' => 'required|array',
                'warehouse_product.*.warehouse_product_inventory_id' => 'required|integer',
                'warehouse_product.*.stock_received' => 'required|integer'
            ]);

            /* 
            * validacion de datos
            */
            $bucketVendorProduct = BucketVendorProduct::find($request->bucket_vendor_product_id);
            if (!$bucketVendorProduct) {
                return response()->json([
                    'message' => 'Error, bucket vendor product not found'
                ], 404);
            }

            foreach ($request->warehouse_product as $product) {
                $warehouseProductInventory = WarehouseProductInventory::find($product['warehouse_product_inventory_id']);
                if (!$warehouseProductInventory) {
                    return response()->json([
                        'message' => 'Error, warehouse product inventory not found'
                    ], 404);
                }
            }

            /* 
            * Asignar productos a un bucket vendor (utilizando la tabla pivote product_inventory_bucket_vendor)
            * Si es que el producto ya esta asignado al bucket vendor, se actualiza la cantidad de stock recibido y su stock actual
            */
            foreach ($request->warehouse_product as $product) {
               $pivot = $bucketVendorProduct->warehouse_product_inventories()->where('warehouse_product_inventory_id', $product['warehouse_product_inventory_id'])->first();
               if($pivot) {
                    /* 
                    * El producto ya esta asignado al bucket vendor, se actualiza la cantidad de stock recibido y su stock actual
                    */
                    $bucketVendorProduct->warehouse_product_inventories()->updateExistingPivot($product['warehouse_product_inventory_id'], [
                        'stock_received' => $pivot->pivot->stock_received + $product['stock_received'],
                        'current_stock' => $pivot->pivot->current_stock + $product['stock_received']
                    ]);
               } else {
                    /*
                    * El producto no esta asignado al bucket vendor, se crea un nuevo registro en la tabla pivote 
                    */
                    $bucketVendorProduct->warehouse_product_inventories()->attach($product['warehouse_product_inventory_id'], [
                        'stock_received' => $product['stock_received'],
                        'stock_sold' => 0,
                        'stock_returned' => 0,
                        'current_stock' => $product['stock_received']
                    ]);
               }
            }

            DB::commit();

            return response()->json([
                'message' => 'Success, product assigned to vendor',
                'data' => $bucketVendorProduct
            ], 201);
            

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, product not assigned to vendor',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

}
