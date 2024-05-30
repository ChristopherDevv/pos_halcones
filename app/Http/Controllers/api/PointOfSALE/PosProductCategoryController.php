<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\PosProductCategory;
use App\Models\PointOfSale\PosProductWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosProductCategoryController extends Controller
{
    /* 
    *
    * Get all product categories by Christoper Patiño
    */
    public function index()
    {
        try {
            $product_categories = PosProductCategory::all();
            return response()->json([
                'message' => 'Success, all product categories',
                'data' => $product_categories
            ], 200);
        
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, not found product categories',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Get all categories of a warehouse by Christoper Patiño
    *
    */
    public function showProductCategoriesByWarehouse(Request $request)
    {
        try {
            $request->validate([
                'pos_product_warehouse_id' => 'required|integer'
            ]);

            $posProductWarehouseId = $request->pos_product_warehouse_id;
            $product_categories = PosProductWarehouse::find($posProductWarehouseId)->pos_product_categories;
            
            return response()->json([
                'message' => 'Success, all product categories of a warehouse',
                'data' => $product_categories
            ], 200);
        
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, not found product categories',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    * 
    * Get all subcategories of a category by Christoper Patiño
    *
    */
    public function showSubcategoriesByCategory(Request $request)
    {
        try {
            $request->validate([
                'pos_product_category_id' => 'required|integer'
            ]);

            $posProductCategoryId = $request->pos_product_category_id;
            $subcategories = PosProductCategory::find($posProductCategoryId)->pos_product_subcategories;
            
            return response()->json([
                'message' => 'Success, all subcategories of a category',
                'data' => $subcategories
            ], 200);
        
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, not found subcategories',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new product category by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'pos_product_warehouses_ids' => 'nullable|array',
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
                'image_file' => 'nullable'
            ]);

            /* 
            * validaciones de datos
            */
            if($request->pos_product_warehouses_ids) {
                $posProductWarehousesIds = $request->pos_product_warehouses_ids;
                foreach ($posProductWarehousesIds as $posProductWarehouseId) {
                    $existWarehouse = PosProductWarehouse::find($posProductWarehouseId);
                    if (!$existWarehouse) {
                        return response()->json([
                            'message' => 'Error, the warehouse does not exist',
                            'data' => $existWarehouse
                        ], 400);
                    }
                }
            } 
            $posProductCategoryName = str_replace(' ', '_', strtolower($request->name));
            $existName = PosProductCategory::where('name', $posProductCategoryName)->first();
            if ($existName) {
                return response()->json([
                    'message' => 'Error, the product category already exists',
                    'data' => $existName
                ], 400);
            }

            /* 
            * Crear una nueva instancia de PosProductCategory
            */
            $newProductCategory = new PosProductCategory();
            $newProductCategory->name = $posProductCategoryName;
            $newProductCategory->description = $request->description ?? null;

            /* 
            * Imagen recibida como tipo file para gauardar en la carpeta posupload
            */
            if($request->hasFile('image_file')) {
                $image = $request->file('image_file');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('posupload'), $imageName);
                $newProductCategory->image_file = $imageName;
            } else {
                $newProductCategory->image_file = null;
            }

            $newProductCategory->save();
            /* 
            * Guardar la relación muchos a muchos entre PosProductCategory y PosProductWarehouse si se envía el array de ids
            */
            if($request->pos_product_warehouses_ids){
                $newProductCategory->pos_product_warehouses()->attach($posProductWarehousesIds);
            }

            DB::commit();

            return response()->json([
                'message' => 'Success, created product category',
                'data' => $newProductCategory
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, not created product category',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Associate a product category with a warehouse by Christoper Patiño
    *
    */
    public function associateProductCategoryToWarehouse(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'pos_product_category_id' => 'required|integer',
                'pos_product_warehouse_id' => 'required|integer'
            ]);

            /* 
            * Validacion de datos
            */

            $posProductCategoryId = $request->pos_product_category_id;
            $posProductWarehouseId = $request->pos_product_warehouse_id;

            $existProductCategory = PosProductCategory::find($posProductCategoryId);
            if (!$existProductCategory) {
                return response()->json([
                    'message' => 'Error, the product category does not exist',
                    'data' => $existProductCategory
                ], 400);
            }

            $existWarehouse = PosProductWarehouse::find($posProductWarehouseId);
            if (!$existWarehouse) {
                return response()->json([
                    'message' => 'Error, the warehouse does not exist',
                    'data' => $existWarehouse
                ], 400);
            }

            /* 
            * Validar si la asociacion ya existe
            */
            $existAssociation = PosProductWarehouse::find($posProductWarehouseId)->pos_product_categories()->find($posProductCategoryId);
            if ($existAssociation) {
                return response()->json([
                    'message' => 'Error, the association already exists',
                    'data' => $existAssociation
                ], 400);
            }

            /* 
            * Asociar la categoria de producto con el almacen
            */
            $existWarehouse->pos_product_categories()->attach($posProductCategoryId);

            DB::commit();

            return response()->json([
                'message' => 'Success, associated product category with warehouse',
                'data' => $existWarehouse
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, not associated product category with warehouse',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
