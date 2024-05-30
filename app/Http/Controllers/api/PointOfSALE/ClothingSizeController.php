<?php

namespace App\Http\Controllers\api\PointOfSALE;

use App\Http\Controllers\Controller;
use App\Models\PointOfSALE\ClothingSize;
use Illuminate\Http\Request;

class ClothingSizeController extends Controller
{
    /* 
    * Get all clothing sizes by Christoper Patiño
    */
    public function index()
    {
        try {

            $clothingSizes = ClothingSize::where('is_active', true)->get();

            return response()->json([
                'message' => 'Clothing sizes found',
                'data' => $clothingSizes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, clothing sizes not found',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
