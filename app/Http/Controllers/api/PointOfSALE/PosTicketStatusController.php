<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\PosTicketStatus;
use Illuminate\Http\Request;

class PosTicketStatusController extends Controller
{
    /* 
    *
    * Get all ticket statuses by Christoper Patiño
    *
    */
    public function index()
    {
        try {

            $posTicketStatuses = PosTicketStatus::all();
            return response()->json([
                'message' => 'Ticket statuses found',
                'data' => $posTicketStatuses
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, no ticket statuses found',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Get ticket status by id by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {
            
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:255'
            ]);

            /* 
            * validacion de datos
            */
            $posTicketStatusName = str_replace(' ', '_', strtolower($request->name));
            $posTicketStatus = PosTicketStatus::where('name', $posTicketStatusName)->first();
            if ($posTicketStatus) {
                return response()->json([
                    'message' => 'Error, ticket status already exists',
                    'data' => $posTicketStatus
                ], 400);
            }

            /* 
            * creacion de instancia de ticket status
            */
            $posTicketStatus = new PosTicketStatus();
            $posTicketStatus->name = $posTicketStatusName;
            $posTicketStatus->description = $request->description;
            $posTicketStatus->color = $request->color;
            $posTicketStatus->save();

            return response()->json([
                'message' => 'Ticket status created successfully',
                'data' => $posTicketStatus
            ], 201);
            

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, ticket status not created',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
