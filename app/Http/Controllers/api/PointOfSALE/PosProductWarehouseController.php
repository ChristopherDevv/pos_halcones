<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\PosProductWarehouse;
use App\Models\PointOfSale\StadiumLocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosProductWarehouseController extends Controller
{
    /* 
    *
    * Get all product warehouses by Christoper Patiño
    *
    */
    public function index()
    {
        try {

            $productWarehouses = PosProductWarehouse::with(['stadium_location' => function($stadium_location){
                $stadium_location->select(['id','name']);
            }])->get();

            return response()->json([
                'message' => 'Success, product warehouses retrieved successfully.',
                'data' => $productWarehouses
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error to get product warehouses',
                'error data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new product warehouse by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'stadium_location_id' => 'required|integer',
                'user_manager_id' => 'required|integer',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'email' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
            ]);

            $productWarehouseName = str_replace(' ', '_', strtolower($request->name));
            $stadiumLocationId = StadiumLocation::where('id', $request->stadium_location_id)->first();
            if (!$stadiumLocationId) {
                return response()->json([
                    'message' => 'Error, stadium location id does not exist.',
                    'data' => $stadiumLocationId
                ], 400);
            }
            $userManagerId = User::where('id', $request->user_manager_id)->first();
            if (!$userManagerId) {
                return response()->json([
                    'message' => 'Error, user manager id does not exist.',
                    'data' => $userManagerId
                ], 400);
            }

            /* 
            * Validar que no exista el nombre del almacén de productos con el mismo estadio relacionado
            */
            $existName = PosProductWarehouse::where('name', $productWarehouseName)
                ->where('stadium_location_id', $request->stadium_location_id)
                ->first();
            if ($existName) {
                return response()->json([
                    'message' => 'Error, product warehouse name already exists.',
                    'data' => $existName
                ], 400);
            }

            $productWarehouse = new PosProductWarehouse();
            $productWarehouse->stadium_location_id = $request->stadium_location_id;
            $productWarehouse->user_manager_id = $request->user_manager_id;
            $productWarehouse->name = $productWarehouseName;
            $productWarehouse->description = $request->description ? $request->description : null;
            $productWarehouse->email = $request->email ? $request->email : null;
            $productWarehouse->phone = $request->phone ? $request->phone : null;
            $productWarehouse->save();

            $productWarehouse->load(['stadium_location' => function($stadium_location){
                $stadium_location->select(['id','name']);            
            }]);

            return response()->json([
                'message' => 'Success, product warehouse created successfully.',
                'data' => $productWarehouse
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error to create product warehouse',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

}
