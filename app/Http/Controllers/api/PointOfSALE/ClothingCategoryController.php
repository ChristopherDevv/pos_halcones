<?php

namespace App\Http\Controllers\api\PointOfSALE;

use App\Http\Controllers\Controller;
use App\Models\PointOfSALE\ClothingCategory;
use Illuminate\Http\Request;

class ClothingCategoryController extends Controller
{
    /* 
    * Get all clothing categories by Christoper Patiño
    */
    public function index()
    {
        try {

            $clothingCategories = ClothingCategory::where('is_active', true)->get();

            return response()->json([
                'message' => 'Clothing categories found',
                'data' => $clothingCategories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, clothing categories not found',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
