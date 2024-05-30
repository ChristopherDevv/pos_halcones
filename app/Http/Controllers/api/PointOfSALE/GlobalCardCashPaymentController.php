<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\GlobalCardCashPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalCardCashPaymentController extends Controller
{
    /* 
    *
    * Get all card cash payments by Christoper Patiño
    *
    */
    public function index()
    {
        try {
         
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, card cash payments not found',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    
}
