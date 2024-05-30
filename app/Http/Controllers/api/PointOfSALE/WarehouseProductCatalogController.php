<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\Imagenes;
use App\Models\PointOfSALE\ClothingCategory;
use App\Models\PointOfSALE\ClothingSize;
use App\Models\PointOfSale\PosProductSubcategory;
use App\Models\PointOfSale\PosUnitMeasurement;
use App\Models\PointOfSale\WarehouseProductCatalog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WarehouseProductCatalogController extends Controller
{
    /* 
    *
    * Get all warehouse product catalogs by Christoper Patiño
    *
    */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'is_clothing' => 'nullable|boolean'
            ]);
            /* 
            * Obtener todos los warehouse product catalogs con sus subcategorias asociadas y sus imagenes
            */
            if($request->is_clothing === null) {
                $warehouseProductCatalogs = WarehouseProductCatalog::with('pos_product_subcategories.pos_product_categories','pos_unit_measurement', 'clothing_category', 'images')->get();
            } else {
                $warehouseProductCatalogs = WarehouseProductCatalog::where('is_clothing', $request->is_clothing)->with('pos_product_subcategories.pos_product_categories','pos_unit_measurement', 'clothing_category', 'images')->get();
            }
            
            return response()->json([
                'message' => 'Success, all warehouse product catalogs',
                'data' => $warehouseProductCatalogs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Error, not found warehouse product catalogs",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new warehouse product catalog by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {

           DB::beginTransaction();

           $request->merge([ 
            'is_clothing' => $request->is_clothing == 'true' ? true : false, 
            'clothing_category_id' => $request->clothing_category_id == 'null' ? null : $request->clothing_category_id, 
           ]);

           $request->validate([
                'pos_product_subcategory_ids' => 'required|array',
                'pos_unit_measurement_id' => 'nullable',
                'user_seller_id' => 'required',
                'name' => 'required|string|max:255',
                'unit_measurement_quantity' => 'nullable',
                'is_clothing' => 'required',
                'clothing_category_id' => 'nullable',
                'description' => 'nullable|string|max:255',
                'image_files' => 'nullable|array',
           ]);

           /* 
           * validacion de datos
           */
            $userSellerId = $request->user_seller_id;
            $existUserSeller = User::find($userSellerId);
            if (!$existUserSeller) {
                return response()->json([
                    'message' => 'Error, user seller not found',
                    'data' => $existUserSeller
                ], 400);
            }

            $warehouseProductCatalogName = strtolower($request->name);
            if($request->is_clothing) {

                $existWarehouseProductCatalog = WarehouseProductCatalog::firstWhere([
                    'name' => $warehouseProductCatalogName,
                    'clothing_category_id' => $request->clothing_category_id
                ]);
                
            } else {
                $existWarehouseProductCatalog = WarehouseProductCatalog::firstWhere([
                    'name' => $warehouseProductCatalogName,
                    'pos_unit_measurement_id' => $request->pos_unit_measurement_id,
                    'unit_measurement_quantity' => $request->unit_measurement_quantity
                ]);
            }
            
            if ($existWarehouseProductCatalog) {
                return response()->json([
                    'message' => 'Error, warehouse product catalog already exists',
                    'data' => $existWarehouseProductCatalog
                ], 400);
            }

            if($request->pos_product_subcategory_ids) {
                $posProductSubcategoryIds = $request->pos_product_subcategory_ids;
                foreach ($posProductSubcategoryIds as $posProductSubcategoryId) {
                    $existPosProductSubcategory = PosProductSubcategory::find($posProductSubcategoryId);
                    if (!$existPosProductSubcategory) {
                        return response()->json([
                            'message' => 'Error, pos product subcategory not found',
                            'data' => $existPosProductSubcategory
                        ], 400);
                    }
                }
            }

            if($request->is_clothing) {
                if(!$request->clothing_category_id) {
                    return response()->json([
                        'message' => 'Error, clothing category are required',
                        'data' => null
                    ], 400);
                }
                
                $clothingCategoryId = $request->clothing_category_id;
                $existClothingCategory = ClothingCategory::find($clothingCategoryId);
                if (!$existClothingCategory) {
                    return response()->json([
                        'message' => 'Error, clothing category not found',
                        'data' => $existClothingCategory
                    ], 400);
                }
            } else {
                if(!$request->pos_unit_measurement_id || !$request->unit_measurement_quantity) {
                    return response()->json([
                        'message' => 'Error, unit measurement and unit measurement quantity are required',
                        'data' => null
                    ], 400);
                }
            }

            /* 
            * Creacion de una nueva instancia de warehouse product catalog
            */
            $warehouseProductCatalog = new WarehouseProductCatalog();
            $warehouseProductCatalog->pos_unit_measurement_id = $request->is_clothing == true ? PosUnitMeasurement::where('name', 'unidades')->first()->id : $request->pos_unit_measurement_id;
            $warehouseProductCatalog->user_seller_id = $request->user_seller_id;
            $warehouseProductCatalog->clothing_category_id = $request->clothing_category_id ?? null;
            $warehouseProductCatalog->name = $warehouseProductCatalogName;
            $warehouseProductCatalog->unit_measurement_quantity = $request->is_clothing == true ? 1 : $request->unit_measurement_quantity;
            $warehouseProductCatalog->is_clothing = $request->is_clothing;
            $warehouseProductCatalog->is_active = true;
            $warehouseProductCatalog->sales_code = $this->storeSalesCode();
            $warehouseProductCatalog->description = $request->description ?? null;
            $warehouseProductCatalog->save();

           /* 
            * Imagenes recibidas como tipo file para guardar en la carpeta posupload
            */
            if($request->hasFile('image_files')) {
                /* $image = $request->file('image_file');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('posupload'), $imageName);
                $warehouseProductCatalog->image_file = $imageName; */

                foreach($request->file('image_files') as $image) {
                    $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('posupload'), $imageName);
                    /* 
                    * Creacion de una nueva instancia de images y asociacion con warehouse product catalog
                    */
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
            * Relacion de warehouse product catalog con pos product subcategories (many to many)
            */
            if($request->pos_product_subcategory_ids) {
                $warehouseProductCatalog->pos_product_subcategories()->attach($posProductSubcategoryIds);
            }
           
            DB::commit();

            $warehouseProductCatalog->load(['images', 'pos_product_subcategories.pos_product_categories', 'pos_unit_measurement', 'clothing_category']);
            
            return response()->json([
                'message' => 'Success, warehouse product catalog created',
                'data' => $warehouseProductCatalog
            ], 201);


        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error, warehouse product catalog not created",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }

    /* 
    * 
    * Create sales code by Christoper Patiño
    *
    */
    public function storeSalesCode()
    {
        try {
            DB::beginTransaction();

            do {

                $salesCode = 'SC-' . str_pad(rand(0, pow(10, 12)-1), 12, '0', STR_PAD_LEFT);

            } while (WarehouseProductCatalog::where('sales_code', $salesCode)->exists());

            DB::commit();

            return $salesCode;

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, sales code not created',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
    /* 
    *
    * Destroy a warehouse product catalog by Christoper Patiño
    *
    */
    public function destroy(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'warehouse_product_catalog_id' => 'required|integer'
            ]);

            /* 
            * validacion de existencia de datos
            */
            $warehouseProductCatalogId = $request->warehouse_product_catalog_id;
            $existWarehouseProductCatalog = WarehouseProductCatalog::find($warehouseProductCatalogId);
            if (!$existWarehouseProductCatalog) {
                return response()->json([
                    'message' => 'Error, warehouse product catalog not found',
                    'data' => $existWarehouseProductCatalog
                ], 400);
            }

            /* 
            * Desvinculacion de warehouse product catalog con pos product subcategories (many to many)
            */
            $existWarehouseProductCatalog->pos_product_subcategories()->detach();

            /* 
            * Eliminacion de las actualizaciones de la tabla warehouse_product _updates
            */
            $existWarehouseProductCatalog->warehouse_product_updates()->delete();

            /* 
            * Eliminacion de imagen de la carpeta posupload
            */
            if ($existWarehouseProductCatalog->image_file) {
                Storage::disk('public')->delete('posupload/' . $existWarehouseProductCatalog->image_file);
            }

            $existWarehouseProductCatalog->delete();

            DB::commit();

            return response()->json([
                'message' => 'Success, warehouse product catalog deleted',
                'data' => $existWarehouseProductCatalog
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, warehouse product catalog not deleted',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Associate a warehouse product catalog with a pos product subcategory by Christoper Patiño
    *
    */
    public function associateProductCatalogToSubcategory(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'warehouse_product_catalog_id' => 'required|integer',
                'pos_product_subcategory_ids' => 'required|array'
            ]);

            /* 
            * Validacion de datos
            */
            $warehouseProductCatalogId = $request->warehouse_product_catalog_id;
            $existWarehouseProductCatalog = WarehouseProductCatalog::find($warehouseProductCatalogId);
            if (!$existWarehouseProductCatalog) {
                return response()->json([
                    'message' => 'Error, warehouse product catalog not found',
                    'data' => $existWarehouseProductCatalog
                ], 400);
            }

            $posProductSubcategoryIds = $request->pos_product_subcategory_ids;
            foreach ($posProductSubcategoryIds as $posProductSubcategoryId) {
                $existPosProductSubcategory = PosProductSubcategory::find($posProductSubcategoryId);
                if (!$existPosProductSubcategory) {
                    return response()->json([
                        'message' => 'Error, pos product subcategory not found',
                        'data' => $existPosProductSubcategory
                    ], 400);
                }
            }

            /* 
            * Validar que no existan subcategorias repetidas
            */
            $existWarehouseProductCatalogSubcategories = $existWarehouseProductCatalog->pos_product_subcategories;
            foreach ($posProductSubcategoryIds as $posProductSubcategoryId) {
                foreach ($existWarehouseProductCatalogSubcategories as $existWarehouseProductCatalogSubcategory) {
                    if ($existWarehouseProductCatalogSubcategory->id == $posProductSubcategoryId) {
                        return response()->json([
                            'message' => 'Error, the subcategory is already associated with the warehouse product catalog',
                            'data' => $existWarehouseProductCatalogSubcategory
                        ], 400);
                    }
                }
            }

            /* 
            * Relacion de warehouse product catalog con pos product subcategories (many to many)
            */
            $existWarehouseProductCatalog->pos_product_subcategories()->attach($posProductSubcategoryIds);

            DB::commit();

            return response()->json([
                'message' => 'Success, warehouse product catalog associated with pos product subcategories',
                'data' => $existWarehouseProductCatalog
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, not found warehouse product catalog',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
