<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\Imagenes;
use App\Models\PointOfSALE\ClothingCategory;
use App\Models\PointOfSALE\ClothingSize;
use App\Models\PointOfSale\PosProductSubcategory;
use App\Models\PointOfSale\PosUnitMeasurement;
use App\Models\PointOfSale\WarehouseProductCatalog;
use App\Models\PointOfSale\WarehouseProductInventory;
use App\Models\PointOfSale\WarehouseProductUpdate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WarehouseProductUpdateController extends Controller
{
    /* 
    *
    * Update a warehouse product inventory(optional) and warehouse product catalog(optional) by Christoper Patiño
    *
    */
    public function update(Request $request)
    {
        try {
            
            DB::beginTransaction();
            $request->validate([
                'warehouse_product_inventory_id' => 'nullable|integer',
                'warehouse_product_catalog_id' => 'required|integer', 
                'user_seller_id' => 'nullable|integer',
                'stock_entered' => 'nullable|integer',
                'price_entered' => 'nullable',
                'reason' => 'nullable|string|max:255',
                'pos_product_subcategory_ids' => 'nullable|array',
                'pos_unit_measurement_id' => 'nullable|integer',
                'name' => 'nullable|string|max:255',
                'clothing_size_id' => 'nullable|integer',
                'clothing_category_id' => 'nullable|integer',
                'unit_measurement_quantity' => 'nullable',
                'description' => 'nullable|string|max:255',
                'image_files' => 'nullable|array',
            ]);

            /* 
            * validacion de datos opcionales
            */
           if($request->warehouse_product_inventory_id) {
                $warehouseProductInventory = WarehouseProductInventory::find($request->warehouse_product_inventory_id);
                if (!$warehouseProductInventory) {
                    return response()->json([
                        'message' => 'Error, warehouse product inventory not found',
                        'data' => $request->warehouse_product_inventory_id
                    ], 404);
                }
           }

           if($request->warehouse_product_catalog_id) {
                $warehouseProductCatalog = WarehouseProductCatalog::find($request->warehouse_product_catalog_id);
                if (!$warehouseProductCatalog) {
                    return response()->json([
                        'message' => 'Error, warehouse product catalog not found',
                        'data' => $request->warehouse_product_catalog_id
                    ], 404);
                }
           }
               
           if($request->user_seller_id) {
                $userSeller = User::find($request->user_seller_id);
                if (!$userSeller) {
                    return response()->json([
                        'message' => 'Error, user seller not found',
                        'data' => $request->user_seller_id
                    ], 404);
                }
           }

            /* 
            * validacion de datos opcionales
            */
            $posProductSubcategoryIds = $request->pos_product_subcategory_ids;
            if ($posProductSubcategoryIds) {
                foreach ($posProductSubcategoryIds as $posProductSubcategoryId) {
                    $posProductSubcategory = PosProductSubcategory::find($posProductSubcategoryId);
                    if (!$posProductSubcategory) {
                        return response()->json([
                            'message' => 'Error, pos product subcategory not found',
                            'data' => $posProductSubcategoryId
                        ], 404);
                    }
                }
            }

            $posUnitMeasurementId = $request->pos_unit_measurement_id;
            if ($posUnitMeasurementId) {
                $posUnitMeasurement = PosUnitMeasurement::find($posUnitMeasurementId);
                if (!$posUnitMeasurement) {
                    return response()->json([
                        'message' => 'Error, pos unit measurement not found',
                        'data' => $posUnitMeasurementId
                    ], 404);
                }
            }

            $clothingSizeId = $request->clothing_size_id;
            if ($clothingSizeId) {
                $clothingSize = ClothingSize::find($clothingSizeId);
                if (!$clothingSize) {
                    return response()->json([
                        'message' => 'Error, clothing size not found',
                        'data' => $clothingSizeId
                    ], 404);
                }
            }

            $clothingCategoryId = $request->clothing_category_id;
            if ($clothingCategoryId) {
                $clothingCategory = ClothingCategory::find($clothingCategoryId);
                if (!$clothingCategory) {
                    return response()->json([
                        'message' => 'Error, clothing category not found',
                        'data' => $clothingCategoryId
                    ], 404);
                }
            }

            /* 
            * Si en el request se recibe el campo stock_entered o price_entered se procede a crear un nuevo registro en la tabla warehouse_product_updates 
            * y se actualiza el stock y precio del producto en la tabla warehouse_product_inventories
            */
            $price_entered = $request->price_entered;
            $stock_entered = $request->stock_entered;
            if($price_entered || $stock_entered) {
                $warehouseProductUpdate = new WarehouseProductUpdate();
                $warehouseProductUpdate->warehouse_product_inventory_id = $warehouseProductInventory->id;
                $warehouseProductUpdate->user_seller_id = $userSeller->id;
                $warehouseProductUpdate->previous_price = $warehouseProductInventory->price;
                $warehouseProductUpdate->price_entered = $price_entered ?? $warehouseProductInventory->price;
                $warehouseProductUpdate->new_price = $price_entered ?? $warehouseProductInventory->price;
                $warehouseProductUpdate->previous_stock = $warehouseProductInventory->stock;
                $warehouseProductUpdate->stock_entered = $stock_entered ?? $warehouseProductInventory->stock;
                $warehouseProductUpdate->new_stock = $stock_entered ? $warehouseProductInventory->stock + $stock_entered : $warehouseProductInventory->stock;
                $warehouseProductUpdate->reason = $request->reason;
                $warehouseProductUpdate->save();

                $warehouseProductInventory->price = $price_entered ?? $warehouseProductInventory->price;
                $warehouseProductInventory->stock = $stock_entered ? $warehouseProductInventory->stock + $stock_entered : $warehouseProductInventory->stock;
                $warehouseProductInventory->save();

                $warehouseProductCatalog = $warehouseProductInventory->warehouse_product_catalog;

            }

            /* 
            * Actualizacion de los datos de la tabla warehouse_product_catalogs (productos globales)
            */
            if ($warehouseProductCatalog) {
                $warehouseProductCatalog->pos_unit_measurement_id = $request->pos_unit_measurement_id ? $request->pos_unit_measurement_id : $warehouseProductCatalog->pos_unit_measurement_id;
                $warehouseProductCatalog->user_seller_id = $request->user_seller_id ? $request->user_seller_id : $warehouseProductCatalog->user_seller_id;
                $warehouseProductCatalog->name = $request->name ?? $warehouseProductCatalog->name;
                $warehouseProductCatalog->clothing_size_id = $request->clothing_size_id ? $request->clothing_size_id : $warehouseProductCatalog->clothing_size_id;
                $warehouseProductCatalog->clothing_category_id = $request->clothing_category_id ? $request->clothing_category_id : $warehouseProductCatalog->clothing_category_id;
                $warehouseProductCatalog->unit_measurement_quantity = $request->unit_measurement_quantity ?? $warehouseProductCatalog->unit_measurement_quantity;
                $warehouseProductCatalog->description = $request->description ?? $warehouseProductCatalog->description;
                $warehouseProductCatalog->save();

                if($request->hasFile('image_file')) {
                    /* 
                    * Eliminar la imagen anterior
                    */
                    if ($warehouseProductCatalog->images->count() > 0){
                        foreach ($warehouseProductCatalog->images as $image) {
                            Storage::disk('public')->delete('posupload/' . $image->uri_path);
                            $image->delete();
                        }
                    }
                    /* 
                    * Guardar la nueva imagen
                    */
                   /*  $image = $request->file('image_file');
                    $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('posupload'), $imageName);
                    $warehouseProductCatalog->image_file = $imageName; */

                    foreach ($request->file('image_files') as $image) {
                        $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                        $image->move(public_path('posupload'), $imageName);
                        
                        $imageModel = new Imagenes();
                        $imageModel->warehouse_product_catalog_id = $warehouseProductCatalog->id;
                        $imageModel->uri_path = 'posupload/' . $imageName;
                        $imageModel->rel_id = null;
                        $imageModel->rel_type = 'pos';
                        $imageModel->name = $imageName;
                        $imageModel->save();
                    }
                }


                /* 
                * Actualizar las subcategorias de la tabla pos_subcategory_product_catalog
                */
                if($posProductSubcategoryIds) {
                    /* 
                    * Desvincular las subcategorias de la tabla pos_subcategory_product_catalog
                    */
                    $warehouseProductCatalog->pos_product_subcategories()->detach();
                    /* 
                    * vincular las nuevas subcategorias de la tabla pos_subcategory_product_catalog
                    */
                    $warehouseProductCatalog->pos_product_subcategories()->attach($posProductSubcategoryIds);
                }
               
            }

            DB::commit();

            return response()->json([
                'message' => 'Success, warehouse product inventory updated',
               // 'data' => $warehouseProductInventory
            ], 200);

        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, not found warehouse product inventory',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
