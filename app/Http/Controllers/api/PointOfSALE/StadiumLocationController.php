<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\StadiumLocation;
use Illuminate\Http\Request;

class StadiumLocationController extends Controller
{
    /* 
    ** Get all stadium locations by Christoper Patiño
    */
    public function index()
        {
            try {
                $stadiumLocations = StadiumLocation::all();
                return response()->json([
                    'message' => 'Success, stadium locations retrieved successfully.',
                    'data' => $stadiumLocations
                ]);
    
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error to get stadium locations',
                    'error data' => $e->getMessage()
                ], 500);
            }
        }
        
        /* 
        * Create a new stadium location by Christoper Patiño
        */
        public function store(Request $request)
        {
            try {
                $request->validate([
                    'name' => 'required|string|max:255',
                    'city' => 'required|string|max:255',
                    'state' => 'required|string|max:255',
                    'country' => 'required|string|max:255',
                    'description' => 'nullable|string|max:255',
                ]);
    
                /* 
                * validacion de existencia de datos
                */
                $stadiumLocationName = str_replace(' ', '_', strtolower($request->name));
                $existName = StadiumLocation::where('name', $stadiumLocationName)->first();
                if ($existName) {
                    return response()->json([
                        'message' => 'Error, stadium location name already exists.',
                        'data' => $existName
                    ], 400);
                }
    
                $stadiumLocation = new StadiumLocation();
                $stadiumLocation->name = $stadiumLocationName;
                $stadiumLocation->description = $request->description ? $request->description : null;
                $stadiumLocation->address = $request->address ? $request->address : null;
                $stadiumLocation->city = $request->city;
                $stadiumLocation->state = $request->state;
                $stadiumLocation->country = $request->country;
                $stadiumLocation->zip_code = $request->zip_code ? $request->zip_code : null;
                $stadiumLocation->phone = $request->phone ? $request->phone : null;
                $stadiumLocation->email = $request->email ? $request->email : null;
                $stadiumLocation->save();
    
                return response()->json([
                    'message' => 'Success, stadium location created successfully.',
                    'data' => $stadiumLocation
                ]);
    
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error to create stadium location',
                    'error data' => $e->getMessage()
                ], 500);
            }
        }
}
