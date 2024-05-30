<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\PosProductCategory;
use App\Models\PointOfSale\PosProductSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosProductSubcategoryController extends Controller
{
    /* 
    *
    * Get all pos product subcategories by Christoper Patiño
    *
    */
    public function index()
    {
        try {
            
            $posProductSubcategories = PosProductSubcategory::with('pos_product_categories')->get();

            return response()->json([
                'message' => 'Success, all pos product subcategories',
                'data' => $posProductSubcategories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, not found pos product subcategories',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Get pos product subcategory by id by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'pos_product_categories_ids' => 'nullable|array',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'image_file' => 'nullable'
            ]);

            /* 
            * validaciones de datos
            */
            if($request->pos_product_categories_ids) {
                $posProductCategoriesIds = $request->pos_product_categories_ids;
                foreach ($posProductCategoriesIds as $posProductCategoryId) {
                    $existCategory = PosProductCategory::find($posProductCategoryId);
                    if (!$existCategory) {
                        return response()->json([
                            'message' => 'Error, the category does not exist',
                            'data' => $existCategory
                        ], 404);
                    }
                }
            }
           
            $posProductSubacategoryName = str_replace(' ', '_', strtolower($request->name));
            $existName = PosProductSubcategory::where('name', $posProductSubacategoryName)->first();
            if ($existName) {
                return response()->json([
                    'message' => 'Error, the product subcategory already exists',
                    'data' => $existName
                ], 400);
            }

            /* 
            * Creamos una nueva instancia de PosProductSubcategory
            */
            $posProductSubcategory = new PosProductSubcategory();
            $posProductSubcategory->name = $posProductSubacategoryName;
            $posProductSubcategory->description = $request->description ?? null;
            
            /* 
            * Imagen recibida como tipo file para guardar en la carpeta posupload
            */
            if($request->hasFile('image_file')) {
                $image = $request->file('image_file');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('posupload'), $imageName);
                $posProductSubcategory->image_file = $imageName;
            } else {
                $posProductSubcategory->image_file = null;
            }

            $posProductSubcategory->save();
            /* 
            * Guardamos la relacion de la subcategoria con las categorias si es que se envian en la peticion
            */
            if($request->pos_product_categories_ids) {
                $posProductSubcategory->pos_product_categories()->attach($posProductCategoriesIds);
            }

            DB::commit();

            return response()->json([
                'message' => 'Success, the product subcategory has been created',
                'data' => $posProductSubcategory
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, not found pos product subcategory',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Associate a product subcategory with a product category by Christoper Patiño
    *
    */
    public function associateProductSubcategoriToCategory(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'pos_product_subcategories_ids' => 'required|array',
                'pos_product_categories_ids' => 'required|array'
            ]);

            /* 
            * Validacion de datos
            */
            $posProductSubcategoriesIds = $request->pos_product_subcategories_ids;
            $posProductCategoriesIds = $request->pos_product_categories_ids;

            foreach ($posProductSubcategoriesIds as $posProductSubcategoryId) {
                $existSubcategory = PosProductSubcategory::find($posProductSubcategoryId);
                if (!$existSubcategory) {
                    return response()->json([
                        'message' => 'Error, the subcategory does not exist',
                        'data' => $existSubcategory
                    ], 404);
                }
            }

            foreach ($posProductCategoriesIds as $posProductCategoryId) {
                $existCategory = PosProductCategory::find($posProductCategoryId);
                if (!$existCategory) {
                    return response()->json([
                        'message' => 'Error, the category does not exist',
                        'data' => $existCategory
                    ], 404);
                }
            }

            /* 
            * Validacion de asociacion
            */
            foreach ($posProductSubcategoriesIds as $posProductSubcategoryId) {
                foreach ($posProductCategoriesIds as $posProductCategoryId) {
                    $existAssociation = PosProductCategory::find($posProductCategoryId)->pos_product_subcategories()->find($posProductSubcategoryId);
                    if ($existAssociation) {
                        return response()->json([
                            'message' => 'Error, the association already exists',
                            'data' => $existAssociation
                        ], 400);
                    }
                }
            }

            /* 
            * Asociamos las subcategorias con las categorias
            */
            foreach ($posProductSubcategoriesIds as $posProductSubcategoryId) {
                foreach ($posProductCategoriesIds as $posProductCategoryId) {
                    PosProductCategory::find($posProductCategoryId)->pos_product_subcategories()->attach($posProductSubcategoryId);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Success, the association has been created'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, not associated product subcategory to category',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

}
