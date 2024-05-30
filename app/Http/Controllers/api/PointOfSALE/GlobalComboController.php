<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\GlobalCombo;
use Illuminate\Http\Request;

class GlobalComboController extends Controller
{
    /* 
    * Get all global combos by Christoper Patiño
    */
    public function index()
    {
        try {
            
            $globalCombos = GlobalCombo::where('is_active', true)->get();

            return response()->json([
                'message' => 'Success, all global combos',
                'data' => $globalCombos
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Error, not found global combos",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }
}
