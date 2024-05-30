<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Mail\SendSalesSummariesMail;
use App\Models\PointOfSale\ComboSale;
use App\Models\PointOfSale\GlobalInventory;
use App\Models\PointOfSale\GlobalInventoryTransaction;
use App\Models\PointOfSale\InventoryTransactionType;
use App\Models\PointOfSale\PosCashRegister;
use App\Models\PointOfSale\PosCashRegisterMovement;
use App\Models\PointOfSale\PosCashRegisterType;
use App\Models\PointOfSale\PosMovementType;
use App\Models\PointOfSale\PosProductCancelation;
use App\Models\PointOfSale\PosProductWarehouse;
use App\Models\PointOfSale\PosSale;
use App\Models\PointOfSale\PosTicket;
use App\Models\PointOfSale\PosTicketStatus;
use App\Models\PointOfSale\StadiumLocation;
use App\Models\PointOfSale\WarehouseTransactionAcknowledgment;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use App\Models\Wallet\WalletTransaction;
use App\Models\Wallet\WalletTransactionStatus;
use App\Models\Wallet\WalletTransactionType;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PosCashRegisterController extends Controller
{
    /* 
    *
    * Get history of pos cash register by Christoper Patiño
    *
    */
    public function posCashRegisterGeneralHistory(Request $request)
    {
        try {

            $request->validate([
                'pos_product_warehouse_id' => 'required|integer',
                'pos_cash_register_id' => 'required|integer',
                'stadium_location_id' => 'required|integer',
            ]);

            /* 
            * validacion de datos
            */
            $posProductWarehouse = PosProductWarehouse::where('id', $request->pos_product_warehouse_id)->first();
            if (!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, product warehouse does not exist.',
                    'data' => $posProductWarehouse
                ], 400);
            }

            $stadiumLocation = StadiumLocation::where('id', $request->stadium_location_id)->first();
            if(!$stadiumLocation) {
                return response()->json([
                    'message' => 'Error, stadium location does not exist.',
                    'data' => $stadiumLocation
                ], 400);
            }

            $posCashRegister = PosCashRegister::where('id', $request->pos_cash_register_id)->first();
            if (!$posCashRegister) {
                return response()->json([
                    'message' => 'Error, cash register does not exist.',
                    'data' => $posCashRegister
                ], 400);
            }

            /* 
            * Validar que la caja pertenezca al almacen de productos y al estadio
            */
            $posCashRegisterExist = PosCashRegister::where('id', $request->pos_cash_register_id)
                ->where('pos_product_warehouse_id', $request->pos_product_warehouse_id)
                ->where('stadium_location_id', $request->stadium_location_id)
                ->first();
            if (!$posCashRegisterExist) {
                return response()->json([
                    'message' => 'Error, cash register does not exist in this product warehouse and stadium location',
                    'data' => $posCashRegisterExist
                ], 400);
            }

            /* 
            * Obtener los movimientos de la caja
            */
            $posCashRegisterMovements = $posCashRegister->pos_cash_register_movements->sortByDesc('created_at');

            $formattedPosCashRegisterMovements = [];

            foreach($posCashRegisterMovements as $posCashRegisterMovement) {
                
                $movementTypeName = $posCashRegisterMovement->pos_movement_type->name;
                $cashierName = $posCashRegisterMovement->pos_ticket->user_cashier->nombre;

                $dataVenta = [];
                $dataCancelacion = [];

                /* 
                * Obtener los productos vendidos
                */
                if($movementTypeName == 'venta'){
                    if($posCashRegisterMovement->pos_ticket && $posCashRegisterMovement->pos_ticket->pos_sale){
                        $posSale = $posCashRegisterMovement->pos_ticket->pos_sale;
                        if($posSale->warehouse_product_inventories){
                            foreach($posSale->warehouse_product_inventories as $warehouseProductInventory){
                                $dataVenta[] = [
                                    'id_inventory' => $warehouseProductInventory->id,
                                    'is_combo_sale' => $posSale->is_combo_sale,
                                    'name' => $warehouseProductInventory->warehouse_product_catalog->name,
                                    'unit_measurement_name' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->name,
                                    'unit_measurement_abbr' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                                    'unit_measurement_quantity' => $warehouseProductInventory->warehouse_product_catalog->unit_measurement_quantity,
                                    'clothing_size' => $warehouseProductInventory->global_inventory->clothing_size ? $warehouseProductInventory->global_inventory->clothing_size->name : 'No aplica',
                                    'clothing_category' => $warehouseProductInventory->warehouse_product_catalog->clothing_category ? $warehouseProductInventory->warehouse_product_catalog->clothing_category->name : 'No aplica',
                                    'images' => $warehouseProductInventory->warehouse_product_catalog->images->pluck('uri_path')->toArray() ?? 'No images found',
                                    'quantity' => $warehouseProductInventory->pivot->original_quantity,
                                    'price' => $posSale->is_combo_sale ? 'precio por combo' : $warehouseProductInventory->sale_price,
                                    'total_amount' => $posSale->is_combo_sale ? 'total por combo' : ($warehouseProductInventory->sale_price * $warehouseProductInventory->pivot->original_quantity),
                                ];
                            }
                        }

                         // Si la venta es un combo, agregamos información adicional
                        if($posSale->is_combo_sale && $posSale->global_combo){
                            $combo = $posSale->global_combo;
                            $dataVenta[] = [
                                'combo_id' => $combo->id,
                                'combo_name' => $combo->name,
                                'combo_price' => $combo->sale_price,
                                'combo_quantity' => $posSale->combos_quantity,
                                'total_combo_amount' => $combo->sale_price * $posSale->combos_quantity,
                            ];
                        }
                    }
                        
                }

                if($movementTypeName == 'cancelacion_producto') {
                    $posProductCancelations = $posCashRegisterMovement->pos_product_cancelations;
                    foreach($posProductCancelations as $posProductCancelation){
                        $posSaleOrigin = $posProductCancelation->pos_ticket->pos_sale;
                        $dataCancelacion[] = [
                            'id_inventory' => $posProductCancelation->warehouse_product_inventory->id,
                            'name' => $posProductCancelation->warehouse_product_inventory->warehouse_product_catalog->name,
                            'unit_measurement_name' => $posProductCancelation->warehouse_product_inventory->warehouse_product_catalog->pos_unit_measurement->name,
                            'unit_measurement_abbr' => $posProductCancelation->warehouse_product_inventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                            'unit_measurement_quantity' => $posProductCancelation->warehouse_product_inventory->warehouse_product_catalog->unit_measurement_quantity,
                            'clothing_size' => $posProductCancelation->warehouse_product_inventory->global_inventory->clothing_size ? $posProductCancelation->warehouse_product_inventory->global_inventory->clothing_size->name : 'No aplica',
                            'clothing_category' => $posProductCancelation->warehouse_product_inventory->warehouse_product_catalog->clothing_category ? $posProductCancelation->warehouse_product_inventory->warehouse_product_catalog->clothing_category->name : 'No aplica',
                            'images' => $posProductCancelation->warehouse_product_inventory->warehouse_product_catalog->images->pluck('uri_path')->toArray() ?? 'No images found',
                            'quantity' => $posProductCancelation->quantity,
                            'price' => $posSaleOrigin->is_combo_sale ? $posProductCancelation->warehouse_product_inventory->discount_sale_price : $posProductCancelation->warehouse_product_inventory->sale_price,
                            'total_amount' => $posProductCancelation->total_amount,
                        ];
                    }
                } 

                /* 
                * Optenemos el tipo de pago para esta transaccion
                */
                $paymentTypeName = $posCashRegisterMovement->pos_ticket->global_payment_type->name;
                if($paymentTypeName == 'efectivo' || $paymentTypeName == 'tarjeta' || $paymentTypeName == 'halcones_wallet' || $paymentTypeName == 'cortesia' || $paymentTypeName == 'por_cobrar'){
                    $amountReceived = $posCashRegisterMovement->pos_ticket->global_card_cash_payment->amount_received;
                    $amountChange = $posCashRegisterMovement->pos_ticket->global_card_cash_payment->amount_change_given;
                } 

                if($movementTypeName == 'venta'){
                    $formattedPosCashRegisterMovements[] = [
                        'movement_id' => $posCashRegisterMovement->id,
                        'is_combo_sale' => $posCashRegisterMovement->pos_ticket->pos_sale->is_combo_sale ? true : false,
                        'movement_type' => $movementTypeName,
                        'ticktet_id' => $posCashRegisterMovement->pos_ticket->id,
                        'ticket_status' => $posCashRegisterMovement->pos_ticket->pos_ticket_status->name,
                        'reason' => $posCashRegisterMovement->reason,
                        'cashier_name' => $cashierName,
                        'payment_type' => $paymentTypeName,
                        'amount_received' => $amountReceived,
                        'amount_change' => $amountChange,
                        'cash_register_info' => [
                            'previous_balance' => $posCashRegisterMovement->previous_balance,
                            'movement_amount' => $posCashRegisterMovement->movement_amount,
                            'new_balance' => $posCashRegisterMovement->new_balance,
                        ],
                        'sale_info' => [
                            'sale_status' => $posCashRegisterMovement->pos_ticket->pos_ticket_status->name,
                            'total_amount' => $posCashRegisterMovement->movement_amount,
                            'amount_received' => $amountReceived,
                            'amount_change' => $amountChange,
                        ],
                        'data_venta' => $dataVenta,
                        'created_at' => (new DateTime($posCashRegisterMovement->created_at))->format('Y-m-d H:i:s'),
                    ];
    
                }

                if($movementTypeName == 'cancelacion_producto'){
                    $formattedPosCashRegisterMovements[] = [
                        'movement_id' => $posCashRegisterMovement->id,
                        'is_combo_sale' => $posCashRegisterMovement->pos_ticket->pos_sale->is_combo_sale ? true : false,
                        'movement_type' => $movementTypeName,
                        'ticktet_id' => $posCashRegisterMovement->pos_ticket->id,
                        'ticket_status' => $posCashRegisterMovement->pos_ticket->pos_ticket_status->name,
                        'reason' => 'Cancelación de producto',
                        'cashier_name' => $cashierName,
                        'cash_register_info' => [
                            'previous_balance' => $posCashRegisterMovement->previous_balance,
                            'movement_amount' => $posCashRegisterMovement->movement_amount,
                            'new_balance' => $posCashRegisterMovement->new_balance,
                        ],
                        'data_cancelacion' => $dataCancelacion,
                        'created_at' => (new DateTime($posCashRegisterMovement->created_at))->format('Y-m-d H:i:s'),
                    ];
                }
            }

            return response()->json([
                'message' => 'Success, history of pos cash register.',
                'cash_register_name' => $posCashRegister->pos_cash_register_type->name,
                'is_open' => $posCashRegister->is_open,
                'opening_balance' => $posCashRegister->opening_balance,
                'current_balance' => $posCashRegister->current_balance,
                'closing_balance' => $posCashRegister->closing_balance ? $posCashRegister->closing_balance : 'Caja abierta',
                'opening_cashier' => $posCashRegister->user_cashier_opening->nombre,
                'closing_cashier' => $posCashRegister->user_cashier_closing ? $posCashRegister->user_cashier_closing->nombre : 'Caja abierta',
                'opening_time' => (new DateTime($posCashRegister->opening_time))->format('Y-m-d H:i:s'),
                'closing_time' => $posCashRegister->closing_time ? (new DateTime($posCashRegister->closing_time))->format('Y-m-d H:i:s') : 'Caja abierta',
                'movements' => $formattedPosCashRegisterMovements,
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error to get history of pos cash register',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Open a new cash register by Christoper Patiño
    *
    */
    public function openPosCashRegister(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'pos_product_warehouse_id' => 'required|integer',
                'pos_cash_register_type_id' => 'required|integer',
                'user_cashier_opening_id' => 'required|integer',
                'stadium_location_id' => 'required|integer',
                'opening_balance' => 'required|numeric',
            ]);

            /* 
            * Validaciones de existencia de los datos            
            */
            $userCashierOpening = User::where('id', $request->user_cashier_opening_id)->first();
            if (!$userCashierOpening) {
                return response()->json([
                    'message' => 'Error, user cashier opening does not exist.',
                    'data' => $userCashierOpening
                ], 400);
            }
            $stadiumLocation = StadiumLocation::where('id', $request->stadium_location_id)->first();
            if (!$stadiumLocation) {
                return response()->json([
                    'message' => 'Error, stadium location does not exist.',
                    'data' => $stadiumLocation
                ], 400);
            }

            $posProductWarehouse = PosProductWarehouse::where('id', $request->pos_product_warehouse_id)->first();
            if (!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, product warehouse does not exist.',
                    'data' => $posProductWarehouse
                ], 400);
            }

            /* 
            * Validar los tipos de caja que tiene el almacen de productos
            */
            $posCashRegisterType = PosCashRegisterType::where('id', $request->pos_cash_register_type_id)->first();
            if (!$posCashRegisterType) {
                return response()->json([
                    'message' => 'Error, cash register type does not exist.',
                    'data' => $posCashRegisterType
                ], 400);
            }

            $posCashRegisterTypeExist = PosCashRegisterType::where('id', $request->pos_cash_register_type_id)
                ->whereHas('pos_product_warehouses', function ($query) use ($request) {
                    $query->where('pos_product_warehouse_id', $request->pos_product_warehouse_id);
                })->first();
            
            if (!$posCashRegisterTypeExist) {
                return response()->json([
                    'message' => 'Error, cash register type does not exist in this product warehouse',
                    'data' => $posCashRegisterTypeExist
                ], 400);
            }

            /* 
            * Verificar que la caja no este abierta para este almacen de productos
            */
            $posCashRegisterExist = PosCashRegister::where('pos_cash_register_type_id', $request->pos_cash_register_type_id)
            ->where('pos_product_warehouse_id', $request->pos_product_warehouse_id)
            ->where('user_cashier_opening_id', $request->user_cashier_opening_id)
            ->where('stadium_location_id', $request->stadium_location_id)
            ->where('is_open', true)
            ->first();
            
            if ($posCashRegisterExist) {
                return response()->json([
                    'message' => 'Error, cash register already open for this user cashier and stadium location',
                    'data' => $posCashRegisterExist
                ], 400);
            }

            /* 
            * Verificamos si existe una caja que ya se haya abierto para este almacen de productos en el mismo dia y mismo estadio
            * (si existe, no se puede abrir otra caja y aperturamos la caja del mismo dia)
            */
            $posCashRegisterExistToday = PosCashRegister::where('pos_product_warehouse_id', $request->pos_product_warehouse_id)
                ->where('stadium_location_id', $request->stadium_location_id)
                ->where('pos_cash_register_type_id', $request->pos_cash_register_type_id)
                ->where('is_open', false)
                ->whereDate('opening_time', now()->toDateString())
                ->first();

            /* 
            * Crear nueva caja si no esta abierta para este almacen de productos
            */
           if(!$posCashRegisterExistToday){
                $posCashRegister = new PosCashRegister();
                $posCashRegister->pos_product_warehouse_id = $request->pos_product_warehouse_id;
                $posCashRegister->pos_cash_register_type_id = $request->pos_cash_register_type_id;
                $posCashRegister->user_cashier_opening_id = $request->user_cashier_opening_id;
                $posCashRegister->user_cashier_closing_id = null;
                $posCashRegister->stadium_location_id = $request->stadium_location_id;
                $posCashRegister->description = 'cash register opening by ' . $userCashierOpening->nombre;
                $posCashRegister->is_open = true;
                $posCashRegister->opening_balance = $request->opening_balance;
                $posCashRegister->current_balance = $request->opening_balance;
                $posCashRegister->closing_balance = null;
                $posCashRegister->opening_time = now();
                $posCashRegister->closing_time = null;
                $posCashRegister->save();
           } else {
                /* 
                * Aperturar la caja que ya se abrio en el mismo dia
                */
                $posCashRegister = $posCashRegisterExistToday;
                $posCashRegister->user_cashier_closing_id = null;
                $posCashRegister->is_open = true;
                $posCashRegister->closing_balance = null;
                $posCashRegister->closing_time = null;
                $posCashRegister->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Success, cash register opened successfully.',
                'is_reopenig' => $posCashRegisterExistToday ? true : false,
                'data' => $posCashRegister
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error to open cash register',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Close a cash register by Christoper Patiño
    *
    */
    public function closePosCashRegister(Request $request)
    {   
        try{
            DB::beginTransaction();
            $request->validate([
                'pos_cash_register_id' => 'required|integer',
                'user_cashier_closing_id' => 'required|integer',
                'warehouse_transaction_acknowledgment_id' => 'required|integer',
                'return_stock_to_warehouse' => 'nullable|boolean',
            ]);

            /* 
            * Validaciones de existencia de los datos            
            */
            $posCashRegister = PosCashRegister::where('id', $request->pos_cash_register_id)->first();
            if (!$posCashRegister) {
                return response()->json([
                    'message' => 'Error, cash register does not exist.',
                    'data' => $posCashRegister
                ], 400);
            }
            $originalCashRegister = $posCashRegister;  
            
            $userCashierClosing = User::where('id', $request->user_cashier_closing_id)->first();
            if (!$userCashierClosing) {
                return response()->json([
                    'message' => 'Error, user cashier closing does not exist.',
                    'data' => $userCashierClosing
                ], 400);
            }

            /* 
            * Comprobar que el acuse esta activo y no tenga 'warehouse_supplier_id' 
            */
            $warehouseTransactionAcknowledgment = WarehouseTransactionAcknowledgment::where('id', $request->warehouse_transaction_acknowledgment_id)
                ->where('is_active', 1)
                ->whereNull('warehouse_supplier_id')
                ->first();
            if(!$warehouseTransactionAcknowledgment){
                return response()->json([
                    'message' => 'Error, acknowledgment not active'
                ]);
            }

            /* 
            * Comprobar que la caja pertenese al mismo warehouse al que pertenece el acuse
            */
            if($posCashRegister->pos_product_warehouse_id != $warehouseTransactionAcknowledgment->pos_product_warehouse_id){
                return response()->json([
                    'message' => 'Error, cash register does not belong to the same warehouse as the acknowledgment.'
                ], 400);
            }

            /* 
            * Verificar que la caja este abierta 
            */
            if(!$posCashRegister->is_open){
                return response()->json([
                    'message' => 'Error, cash register is already closed.',
                    'data' => $posCashRegister
                ], 400);
            }

            /* 
            * Cerrar caja 
            */
            $posCashRegister->user_cashier_closing_id = $request->user_cashier_closing_id;
            $posCashRegister->is_open = false;
            $posCashRegister->closing_balance = $posCashRegister->current_balance;
            $posCashRegister->closing_time = now();
            $posCashRegister->save();

            
            /*
            * conocer si existe alguna caja bierta para la tienda u pos
            */
            $someCashRegisterIsOpen = PosCashRegister::where('pos_product_warehouse_id', $posCashRegister->pos_product_warehouse_id)
                ->where('is_open', true)
                ->first();
            
            /* 
            * Finalzar el acuse y enviar emails si no hay cajas abiertas
            */
            if(!$someCashRegisterIsOpen){

                /* 
                * Validar la estacion de stock en pos de la tienda
                */
                if($request->return_stock_to_warehouse === null){
                    return response()->json([
                        'message' => 'Error, return_stock_to_warehouse is required.'
                    ], 400);
                }

                /* 
                * Recuperar acuses de la tenda para cerrarlos
                */
                $warehouseTransactionAcknowledgments = WarehouseTransactionAcknowledgment::where('pos_product_warehouse_id', $posCashRegister->pos_product_warehouse_id)
                    ->where('is_active', 1)
                    ->whereNull('warehouse_supplier_id')
                    ->get();

                if($warehouseTransactionAcknowledgments->count() != 0){
                    foreach($warehouseTransactionAcknowledgments as $warehouseTransactionAcknowledgment){
                        if($warehouseTransactionAcknowledgment->inventory_transaction_type->name == 'actualizacion_de_propiedades' || $warehouseTransactionAcknowledgment->inventory_transaction_type->name == 'transferencia_de_stock_a_tienda' || $warehouseTransactionAcknowledgment->inventory_transaction_type->name == 'devolucion_de_stock_a_almacen') {
                            $warehouseTransactionAcknowledgment->is_active = 0;
                            $warehouseTransactionAcknowledgment->save();   
                        }
                    }   
                }

                /* 
                * Transferimos el stock de los productos del punto de venta restantes al almacen global de la tienda
                * Solo si se selecciono la opcion de devolver el stock al almacen
                */
                $posProductWarehouse = PosProductWarehouse::where('id', $posCashRegister->pos_product_warehouse_id)->first();
                $warehouseProductInventories = $posProductWarehouse->warehouse_product_inventories;
                if($request->return_stock_to_warehouse) {
                    foreach($warehouseProductInventories as $warehouseProductInventory){
                        /* 
                        * Obtenermos su inventari global
                        */
                        $globalInventory = GlobalInventory::find($warehouseProductInventory->global_inventory_id);
    
                        if(!$globalInventory){
                            DB::rollBack();
                            return response()->json([
                                'message' => 'Error, global inventory does not exist.',
                                'data' => $globalInventory
                            ], 400);
                        }
                        /* 
                        * Creamos una nueva transacion de para la devolucion de stock 
                        */
                        $globalInventoryTransaction = new GlobalInventoryTransaction();
                        $globalInventoryTransaction->global_inventory_id = $globalInventory->id;
                        $globalInventoryTransaction->inventory_transaction_type_id = InventoryTransactionType::where('name', 'devolucion_de_stock_a_almacen')->first()->id;
                        $globalInventoryTransaction->warehouse_transaction_acknowledgment_id = $warehouseTransactionAcknowledgment->id;
                        $globalInventoryTransaction->previous_stock = $globalInventory->stock;
                        $globalInventoryTransaction->stock_movement = $warehouseProductInventory->stock;
                        $globalInventoryTransaction->new_stock = $globalInventory->current_stock + $warehouseProductInventory->stock;
                        $globalInventoryTransaction->previous_sale_price = $globalInventory->sale_price;
                        $globalInventoryTransaction->new_sale_price = $globalInventory->sale_price;
                        $globalInventoryTransaction->previous_discount_price = $globalInventory->discount_price;
                        $globalInventoryTransaction->new_discount_price = $globalInventory->discount_price;
                        $globalInventoryTransaction->reason = 'Devolución de stock de punto de venta a almacén global';
                        $globalInventoryTransaction->save();
    
                        /* 
                        * Actaulizamos el stock del inventario global
                        */
                        $globalInventory->current_stock = $globalInventory->current_stock + $warehouseProductInventory->stock;
                        $globalInventory->save();
    
                        /* 
                        * Actualizamos el stock del inventario del punto de venta
                        */
                        $warehouseProductInventory->stock = 0;
                        $warehouseProductInventory->save();
    
                    }
                }

                /* 
                * Recuperamos el resumen de los productos vendidos en cajas registradoras
                */
                $request = new Request();
                $request->merge([
                    'pos_product_warehouse_id' => $posCashRegister->pos_product_warehouse_id,
                    'date' => now()->toDateString()
                ]);
                $sendEmail = true;
                $this->summaryPosOfWarehouse($request, $sendEmail);
                $pdfSummaryPos = public_path('pdfs/resumen_ventas_de_' . $posCashRegister->pos_product_warehouse->name . '_' . now()->toDateString() . '.pdf');
                $managerEmail = $posCashRegister->pos_product_warehouse->email;

                /* 
                * Recuperamos las cajas que se abrieron en el dia actual para la tienda
                */
                $posCashRegisters = PosCashRegister::where('pos_product_warehouse_id', $posCashRegister->pos_product_warehouse_id)
                    ->whereDate('opening_time', now()->toDateString())
                    ->get();
                
                /* 
                * Optenemos los pdfs de los acuses de corte de caja sin combos
                */
                $cashRegisterWithoutComboPdfs = [];
                foreach($posCashRegisters as $posCashRegister){
                    $request = new Request();
                    $request->merge([
                        'pos_cash_register_id' => $posCashRegister->id,
                    ]);
                    $sendEmail = true;
                    $this->summarySaleWithoutCombo($request, $sendEmail);
                    $cashRegisterWithoutComboPdfs[] = public_path('pdfs/corte_de_' . $posCashRegister->pos_cash_register_type->name . '_' . $posCashRegister->pos_product_warehouse->name . '_' . now()->toDateString() . '.pdf');
                }

                /* 
                * Optenemos el pdf del resumen de combos vendidos
                */
                $request = new Request();
                $request->merge([
                    'pos_product_warehouse_id' => $posCashRegister->pos_product_warehouse_id,
                    'date' => now()->toDateString()
                ]);
                $sendEmail = true;
                $this->summaryCombosSold($request, $sendEmail);
                $pdfSummaryCombos = public_path('pdfs/combos_vendidos_' . $posCashRegister->pos_product_warehouse->name . '_' . now()->toDateString() . '.pdf');

                /* 
                * Optenemos el pdf del resumen de inventario global de la tienda
                */
                $request = new Request();
                $request->merge([
                    'pos_product_warehouse_id' => $posCashRegister->pos_product_warehouse_id,
                ]);
                $sendEmail = true;
                $this->summaryGlobalInventory($request, $sendEmail); 
                $pdfSummaryGlobalInventory = public_path('pdfs/inventario_global_' . $posCashRegister->pos_product_warehouse->name . '_' . now()->toDateString() . '.pdf');

                /* 
                * Optenemos el pdf del resumen de inventario en pos de la tienda
                */
                $request = new Request();
                $request->merge([
                    'pos_product_warehouse_id' => $posCashRegister->pos_product_warehouse_id,
                ]);
                $sendEmail = true;
                $this->summaryPosInventoryOfWarehouse($request, $sendEmail);
                $pdfSummaryPosInventory = public_path('pdfs/inventario_pos_' . $posCashRegister->pos_product_warehouse->name . '_' . now()->toDateString() . '.pdf');

                /* 
                * Optenemos el pdf de los productos vendidos en combos
                */
                $request = new Request();
                $request->merge([
                    'pos_product_warehouse_id' => $posCashRegister->pos_product_warehouse_id,
                    'date' => now()->toDateString()
                ]);
                $sendEmail = true;
                $this->summaryProductsSoldInCombos($request, $sendEmail);
                $pdfSummaryProductsCombos = public_path('pdfs/productos_vendidos_en_combos_' . $posCashRegister->pos_product_warehouse->name . '_' . now()->toDateString() . '.pdf');

                /* 
                * Enviar email con los acuses de corte de caja y el resumen de ventas
                */
                $managerEmails = [$managerEmail];
                $pdfs = array_merge([$pdfSummaryPos], $cashRegisterWithoutComboPdfs, [$pdfSummaryCombos], [$pdfSummaryPosInventory], [$pdfSummaryGlobalInventory]);
                $reason = 'Cierre de cajas de la tienda ' . $posCashRegister->pos_product_warehouse->name;
                Mail::send(new SendSalesSummariesMail($managerEmails, $pdfs, $reason));

                /* 
                * Eliminamos los pdfs generados
                */
                /* if(file_exists($pdfSummaryPos) && !unlink($pdfSummaryPos)){
                    return response()->json([
                        'message' => 'Error, could not delete the pdf file.',
                        'pdf_path' => $pdfSummaryPos
                    ], 400);
                }
                if(file_exists($pdfSummaryCombos) && !unlink($pdfSummaryCombos)){
                    return response()->json([
                        'message' => 'Error, could not delete the pdf file.',
                        'pdf_path' => $pdfSummaryCombos
                    ], 400);
                }
                if(file_exists($pdfSummaryGlobalInventory) && !unlink($pdfSummaryGlobalInventory)){
                    return response()->json([
                        'message' => 'Error, could not delete the pdf file.',
                        'pdf_path' => $pdfSummaryGlobalInventory
                    ], 400);
                }
                if(file_exists($pdfSummaryPosInventory) && !unlink($pdfSummaryPosInventory)){
                    return response()->json([
                        'message' => 'Error, could not delete the pdf file.',
                        'pdf_path' => $pdfSummaryPosInventory
                    ], 400);
                }
                if(file_exists($pdfSummaryProductsCombos) && !unlink($pdfSummaryProductsCombos)){ 
                    return response()->json([
                        'message' => 'Error, could not delete the pdf file.',
                        'pdf_path' => $pdfSummaryProductsCombos
                    ], 400);
                }
                foreach($cashRegisterWithoutComboPdfs as $cashRegisterPdf){
                    if(file_exists($cashRegisterPdf) && !unlink($cashRegisterPdf)){
                        return response()->json([
                            'message' => 'Error, could not delete the pdf file.',
                            'pdf_path' => $cashRegisterPdf
                        ], 400);
                    }
                } */

                /* 
                * Finalizar el acuse
                */
                $warehouseTransactionAcknowledgment->is_active = 0;
                $warehouseTransactionAcknowledgment->save();
            }

            DB::commit();

            /* 
            * Obtener datos de la caja cerrada y retornar el resumen de la caja
            */
            $request = new Request();
            $request->merge([
                'pos_cash_register_id' => $originalCashRegister->id,
            ]);

            return  $this->summarySaleWithoutCombo($request, false);

        } catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'message' => 'Error to close cash register',
                'error_data' => $e->getMessage()
            ], 500);
        }

    }

    /* 
    * Summary of products sold in combos by Christoper Patiño
    */
    public function summaryProductsSoldInCombos (Request $request, $sendEmail = false)
    {
        try {

            $request->validate([
                'pos_product_warehouse_id' => 'required|integer',
                'date' => 'required|date_format:Y-m-d', 
            ]);

            $posProductWarehouse = PosProductWarehouse::where('id', $request->pos_product_warehouse_id)->first();
            if (!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, product warehouse does not exist.',
                    'data' => $posProductWarehouse
                ], 400);
            }

            /* 
            * Optenes los movimientos de las cajas registradoras de la tienda en la fecha indicada
            */
            $posCashRegisters = PosCashRegister::where('pos_product_warehouse_id', $request->pos_product_warehouse_id)
                ->whereDate('opening_time', $request->date)
                ->get();
            
            $posSalesInCombo = [];
            foreach($posCashRegisters as $posCashRegister){
                $posCashRegisterMovements = $posCashRegister->pos_cash_register_movements;
                foreach($posCashRegisterMovements as $posCashRegisterMovement){
                    if($posCashRegisterMovement->pos_ticket->pos_ticket_status->name == 'pagado' && $posCashRegisterMovement->pos_ticket->pos_sale){
                        $posSale = $posCashRegisterMovement->pos_ticket->pos_sale;
                        if($posSale->is_combo_sale){
                            $posSalesInCombo[] = $posSale;
                        }
                    }
                }
            }

            $detailDataFormat = [
                'warehouse_name' => $posProductWarehouse->name,
                'acknowledgment_key' => 'generic_acknowledgment_key',
                'created_at' => now()->toDateString(),
                'manager_name' => $posProductWarehouse->user_manager->nombre,
            ];

            /* 
            * Optener los productos vendidos en combos
            */
            $productsSoldInCombos = [];
            foreach($posSalesInCombo as $posSale){
                if($posSale->warehouse_product_inventories){
                    $comboPricePerProduct = $posSale->global_combo->sale_price / count($posSale->warehouse_product_inventories);
                    foreach($posSale->warehouse_product_inventories as $warehouseProductInventory){
                        $productKey = $warehouseProductInventory->warehouse_product_catalog->name . '_' . $warehouseProductInventory->warehouse_product_catalog->unit_measurement_quantity . '_' . $posSale->global_combo->name;
                        if(array_key_exists($productKey, $productsSoldInCombos)){
                            $productsSoldInCombos[$productKey]['quantity'] += $warehouseProductInventory->pivot->original_quantity;
                            $productsSoldInCombos[$productKey]['total_amount'] += $warehouseProductInventory->pivot->original_quantity * $comboPricePerProduct;
                        } else {
                            $productsSoldInCombos[$productKey] = [
                                'name' => $warehouseProductInventory->warehouse_product_catalog->name,
                                'unit_measurement_quantity' => $warehouseProductInventory->warehouse_product_catalog->unit_measurement_quantity,
                                'unit_measurement_abbr' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                                'clothing_size' => $warehouseProductInventory->global_inventory->clothing_size ? $warehouseProductInventory->global_inventory->clothing_size->name : 'N/A',
                                'clothing_category' => $warehouseProductInventory->warehouse_product_catalog->clothing_category ? $warehouseProductInventory->warehouse_product_catalog->clothing_category->name : 'N/A',
                                'quantity' => $warehouseProductInventory->pivot->original_quantity,
                                'price' => number_format($comboPricePerProduct, 4, '.', ''),
                                'total_amount' => number_format(($warehouseProductInventory->pivot->original_quantity * $comboPricePerProduct), 4, '.', ''),
                                'combo_name' => $posSale->global_combo->name,
                            ];
                        }
                    }
                }
            }

            $pdf = PDF::loadView('pdfs.inventarios_globales.acuse_productos_vendidos_combo', [
                'productsSoldInCombos' => $productsSoldInCombos,
                'detailDataFormat' => $detailDataFormat
            ]);

            if($sendEmail){
                $pdf->save(public_path('pdfs/productos_vendidos_en_combos_' . $posProductWarehouse->name . '_' . $request->date . '.pdf'));
            }else {
                return $pdf->stream();
            }

        
        } catch(\Exception $e){
            return response()->json([
                'message' => 'Error, could not get the summary of products sold in combos.',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    * Summary of global inventory by Christoper Patiño
    */
    public function summaryGlobalInventory(Request $request, $sendEmail = false)
    {
        try {

            $request->validate([
                'pos_product_warehouse_id' => 'required|integer',
            ]);

            $posProductWarehouse = PosProductWarehouse::where('id', $request->pos_product_warehouse_id)->first();
            if (!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, product warehouse does not exist.',
                    'data' => $posProductWarehouse
                ], 400);
            }

            $globalInventories = GlobalInventory::where('pos_product_warehouse_id', $request->pos_product_warehouse_id)->get();
            $globalInventoriesSummary = [];
            $cashRegisterData = [
                'pos_product_warehouse_name' => $posProductWarehouse->name,
                'manager_name' => $posProductWarehouse->user_manager->nombre,
                'acknowledgment_key' => 'generic_acknowledgment_key',
            ];

            foreach($globalInventories as $globalInventory){
                $globalInventoriesSummary[] = [
                    'name' => $globalInventory->warehouse_product_catalog->name,
                    'unit_measurement_quantity' => $globalInventory->warehouse_product_catalog->unit_measurement_quantity,
                    'unit_measurement_abbr' => $globalInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                    'clothing_size' => $globalInventory->clothing_size ? $globalInventory->clothing_size->name : 'N/A',
                    'clothing_category' => $globalInventory->warehouse_product_catalog->clothing_category ? $globalInventory->warehouse_product_catalog->clothing_category->name : 'N/A',
                    'sale_price' => $globalInventory->sale_price,
                    'discount_sale_price' => $globalInventory->discount_sale_price,
                    'current_stock' => $globalInventory->current_stock,
                ];
            }

            $pdf = PDF::loadView('pdfs.inventarios_globales.acuse_inventario_actual', [
                'globalInventoriesSummary' => $globalInventoriesSummary,
                'cashRegisterData' => $cashRegisterData
            ]);

            if($sendEmail){
                $pdf->save(public_path('pdfs/inventario_global_' . $posProductWarehouse->name .  '_' . now()->toDateString() . '.pdf'));
            }else {
                return $pdf->stream();
            }

        } catch(\Exception $e){
            return response()->json([
                'message' => 'Error, could not get the summary of global inventory.',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    * Summary of pos inventory of warehouse by Christoper Patiño
    */
    public function summaryPosInventoryOfWarehouse(Request $request, $sendEmail = false)
    {
        try {

            $request->validate([
                'pos_product_warehouse_id' => 'required|integer',
            ]);

            $posProductWarehouse = PosProductWarehouse::where('id', $request->pos_product_warehouse_id)->first();
            if (!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, product warehouse does not exist.',
                    'data' => $posProductWarehouse
                ], 400);
            }

            /* 
            * Recuperar los productos en el pos de la tienda
            */
            $warehouseProductInventories = $posProductWarehouse->warehouse_product_inventories;
            $posInventorySummary = [];
            $warehouseGeneralData = [
                'warehouse_name' => $posProductWarehouse->name,
                'manager_name' => $posProductWarehouse->user_manager->nombre,
                'acknowledgment_key' => 'generic_acknowledgment_key',
            ];

            /* 
            * Formatear los datos de los productos en el pos
            */
            foreach($warehouseProductInventories as $warehouseProductInventory){
                $posInventorySummary[] = [
                    'name' => $warehouseProductInventory->warehouse_product_catalog->name,
                    'unit_measurement_quantity' => $warehouseProductInventory->warehouse_product_catalog->unit_measurement_quantity,
                    'unit_measurement_abbr' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                    'clothing_size' => $warehouseProductInventory->global_inventory->clothing_size ? $warehouseProductInventory->global_inventory->clothing_size->name : 'N/A',
                    'clothing_category' => $warehouseProductInventory->warehouse_product_catalog->clothing_category ? $warehouseProductInventory->warehouse_product_catalog->clothing_category->name : 'N/A',
                    'sale_price' => $warehouseProductInventory->global_inventory->sale_price,
                    'discount_sale_price' => $warehouseProductInventory->global_inventory->discount_sale_price,
                    'current_stock' => $warehouseProductInventory->stock,
                ];
            }

            $pdf = PDF::loadView('pdfs.inventarios_globales.acuse_inventario_actual_pos', [
                'posInventorySummary' => $posInventorySummary,
                'warehouseGeneralData' => $warehouseGeneralData
            ]);

            if($sendEmail){
                $pdf->save(public_path('pdfs/inventario_pos_' . $posProductWarehouse->name .  '_' . now()->toDateString() . '.pdf'));
            }else {
                return $pdf->stream();
            }


        } catch(\Exception $e){
            return response()->json([
                'message' => 'Error, could not get the summary of pos inventory of warehouse.',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    * Summary of combos sold By Christoper Patiño
    */
    public function summaryCombosSold(Request $request, $sendEmail = false)
    {
        try {
            $request->validate([
                'pos_product_warehouse_id' => 'required|integer',
                'date' => 'required|date_format:Y-m-d', 
            ]);

            $posProductWarehouse = PosProductWarehouse::where('id', $request->pos_product_warehouse_id)->first();
            if (!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, product warehouse does not exist.',
                    'data' => $posProductWarehouse
                ], 400);
            }

            $comboSales = ComboSale::where('pos_product_warehouse_id', $request->pos_product_warehouse_id)
                ->whereDate('created_at', $request->date)
                 ->where('is_canceled', 0)
                ->get();
            
            $comboSalesSummary = [];
            $detailDataFormat = [
                'transaction_type' => 'venta de combos',
                'status' => 'completado',
                'acknowledgment_key' => 'generic_acknowledgment_key',
                'created_at' => $request->date,
                'total_amount' => 0, 
                'manager_name' => $posProductWarehouse->user_manager->nombre,
                'warehouse_name' => $posProductWarehouse->name,
            ];
            
            foreach($comboSales as $comboSale){
                $comboName = $comboSale->global_combo->name;
                $salePrice = $comboSale->global_combo->sale_price;
                $quantitySold = $comboSale->sale_count;
                $total = $salePrice * $quantitySold;
            
                if (!isset($comboSalesSummary[$comboName])) {
                    // Si el combo no está en el resumen se agrega
                    $comboSalesSummary[$comboName] = [
                        'combo_name' => $comboName,
                        'warehouse_name' => $posProductWarehouse->name,
                        'sale_price' => $salePrice,
                        'quantity_sold' => $quantitySold,
                        'total' => $total,
                    ];
                } else {
                    // Si el combo ya está en el resumen se actualiza la cantidad vendida y el total
                    $comboSalesSummary[$comboName]['quantity_sold'] += $quantitySold;
                    $comboSalesSummary[$comboName]['total'] += $total;
                }
            
                $detailDataFormat['total_amount'] += $total; 
            }

            $pdf = PDF::loadView('pdfs.inventarios_globales.acuse_combos_vendidos', [
                'comboSalesSummary' => $comboSalesSummary,
                'detailDataFormat' => $detailDataFormat
            ]);

            if($sendEmail){
                $pdf->save(public_path('pdfs/combos_vendidos_' . $posProductWarehouse->name .  '_' . $request->date . '.pdf'));
            }else {
                return $pdf->stream();
            }

        } catch(\Exception $e){
            return response()->json([
                'message' => 'Error, could not get the summary of combos sold.',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    * Summary of cash register by Christoper Patiño
    */
    public function summarySaleWithoutCombo(Request $request, $sendEmail = false)
    {
        try {
            $request->validate([
                'pos_cash_register_id' => 'required|integer',
            ]);

            $posCashRegister = PosCashRegister::where('id', $request->pos_cash_register_id)->first();
            if (!$posCashRegister) {
                return response()->json([
                    'message' => 'Error, cash register does not exist.',
                    'data' => $posCashRegister
                ], 400);
            }

            /*
            * Recuperar los productos vendidos en la caja
            */
            $posCashRegisterMovements = $posCashRegister->pos_cash_register_movements;
            $totalAmount = 0;
            $productSummary = [];
            $productSummaryCourtesies = [];
            $productSummaryOnCredit = [];
            $productSummaryForBucketVendors = [];

            foreach($posCashRegisterMovements as $posCashRegisterMovement){
                if($posCashRegisterMovement->pos_movement_type->name == 'venta' && $posCashRegisterMovement->pos_ticket->global_payment_type->name != 'cortesia' && $posCashRegisterMovement->pos_ticket->global_payment_type->name != 'por_cobrar' && $posCashRegisterMovement->pos_ticket->pos_ticket_status->name == 'pagado' && !$posCashRegisterMovement->pos_ticket->pos_sale->is_bucketvendor_sale){
                    $totalAmount += $posCashRegisterMovement->movement_amount;
            
                    // Si el movimiento tiene un ticket y una venta
                    if($posCashRegisterMovement->pos_ticket && $posCashRegisterMovement->pos_ticket->pos_sale){
                        $posSale = $posCashRegisterMovement->pos_ticket->pos_sale;
            
                        // Si la venta no es una venta de combo
                        if(!$posSale->is_combo_sale){
                            // Si la venta tiene inventarios de productos de almacén
                            if($posSale->warehouse_product_inventories){
                                foreach($posSale->warehouse_product_inventories as $warehouseProductInventory){
                                    // Crear una clave única para el producto
                                    $productKey = $warehouseProductInventory->warehouse_product_catalog->name . ' ' . $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation . ' ' . ($warehouseProductInventory->global_inventory->clothing_size ? $warehouseProductInventory->global_inventory->clothing_size->name : 'N/A');
            
                                    // Si el producto ya existe en el resumen, incrementar la cantidad y el total
                                    if(array_key_exists($productKey, $productSummary)){
                                        $productSummary[$productKey]['quantity'] += $warehouseProductInventory->pivot->quantity;
                                        $productSummary[$productKey]['total_amount'] += $warehouseProductInventory->sale_price * $warehouseProductInventory->pivot->quantity;
                                    }
                                    // Si el producto no existe en el resumen, agregarlo
                                    else{
                                        $productSummary[$productKey] = [
                                            'id_inventory' => $warehouseProductInventory->id,
                                            'name' => $warehouseProductInventory->warehouse_product_catalog->name,
                                            'unit_measurement_name' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->name,
                                            'unit_measurement_abbr' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                                            'unit_measurement_quantity' => $warehouseProductInventory->warehouse_product_catalog->unit_measurement_quantity,
                                            'clothing_size' => $warehouseProductInventory->global_inventory->clothing_size ? $warehouseProductInventory->global_inventory->clothing_size->name : 'N/A',
                                            'clothing_category' => $warehouseProductInventory->warehouse_product_catalog->clothing_category ? $warehouseProductInventory->warehouse_product_catalog->clothing_category->name : 'N/A',
                                            'quantity' => $warehouseProductInventory->pivot->quantity,
                                            'price' => $warehouseProductInventory->sale_price,
                                            'is_combo_sale' => 'NO',
                                            'total_amount' => $warehouseProductInventory->sale_price * $warehouseProductInventory->pivot->quantity,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                } else if ($posCashRegisterMovement->pos_movement_type->name == 'venta' && $posCashRegisterMovement->pos_ticket->global_payment_type->name != 'cortesia' && $posCashRegisterMovement->pos_ticket->global_payment_type->name != 'por_cobrar' && $posCashRegisterMovement->pos_ticket->pos_ticket_status->name == 'pagado' && $posCashRegisterMovement->pos_ticket->pos_sale->is_bucketvendor_sale) {
                    
                    /* 
                    * resumen de ventas para vendedores de cubeteros 
                    */
                    $totalAmount += $posCashRegisterMovement->movement_amount;

                    // Si el movimiento tiene un ticket y una venta
                    if($posCashRegisterMovement->pos_ticket && $posCashRegisterMovement->pos_ticket->pos_sale){
                        $posSale = $posCashRegisterMovement->pos_ticket->pos_sale;
                
                        // Si la venta no es una venta de combo
                        if(!$posSale->is_combo_sale){
                            // Si la venta tiene inventarios de productos de almacén
                            if($posSale->warehouse_product_inventories){
                                foreach($posSale->warehouse_product_inventories as $warehouseProductInventory){
                                    // Crear una clave única para el producto
                                    $productKey = $warehouseProductInventory->warehouse_product_catalog->name . ' ' . $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation . ' ' . ($warehouseProductInventory->global_inventory->clothing_size ? $warehouseProductInventory->global_inventory->clothing_size->name : 'N/A');
            
                                    // Si el producto ya existe en el resumen, incrementar la cantidad y el total
                                    if(array_key_exists($productKey, $productSummaryForBucketVendors)){
                                        $productSummaryForBucketVendors[$productKey]['quantity'] += $warehouseProductInventory->pivot->quantity;
                                        $productSummaryForBucketVendors[$productKey]['total_amount'] += $warehouseProductInventory->discount_sale_price * $warehouseProductInventory->pivot->quantity;
                                    }
                                    // Si el producto no existe en el resumen, agregarlo
                                    else{
                                        $productSummaryForBucketVendors[$productKey] = [
                                            'id_inventory' => $warehouseProductInventory->id,
                                            'name' => $warehouseProductInventory->warehouse_product_catalog->name,
                                            'unit_measurement_name' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->name,
                                            'unit_measurement_abbr' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                                            'unit_measurement_quantity' => $warehouseProductInventory->warehouse_product_catalog->unit_measurement_quantity,
                                            'clothing_size' => $warehouseProductInventory->global_inventory->clothing_size ? $warehouseProductInventory->global_inventory->clothing_size->name : 'N/A',
                                            'clothing_category' => $warehouseProductInventory->warehouse_product_catalog->clothing_category ? $warehouseProductInventory->warehouse_product_catalog->clothing_category->name : 'N/A',
                                            'quantity' => $warehouseProductInventory->pivot->quantity,
                                            'price' => $warehouseProductInventory->discount_sale_price,
                                            'is_combo_sale' => 'NO',
                                            'total_amount' => $warehouseProductInventory->discount_sale_price * $warehouseProductInventory->pivot->quantity,
                                        ];

                                    }
                                }
                            }
                        }
                    }

                } else if ($posCashRegisterMovement->pos_movement_type->name == 'venta' && $posCashRegisterMovement->pos_ticket->global_payment_type->name == 'por_cobrar' && $posCashRegisterMovement->pos_ticket->pos_ticket_status->name == 'pagado') {
                    /* 
                    * Si el movimiento es una venta y el tipo de pago es por cobrar
                    */
                    $totalAmount += $posCashRegisterMovement->movement_amount;

                    // Si el movimiento tiene un ticket y una venta
                    if($posCashRegisterMovement->pos_ticket && $posCashRegisterMovement->pos_ticket->pos_sale) {
                        $posSale = $posCashRegisterMovement->pos_ticket->pos_sale;

                        // Si la venta no es una venta de combo
                        if(!$posSale->is_combo_sale){
                            // Si la venta tiene inventarios de productos de almacén
                            if($posSale->warehouse_product_inventories){
                                foreach($posSale->warehouse_product_inventories as $warehouseProductInventory){
                                    // Crear una clave única para el producto
                                    $productKey = $warehouseProductInventory->warehouse_product_catalog->name . ' ' . $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation . ' ' . ($warehouseProductInventory->global_inventory->clothing_size ? $warehouseProductInventory->global_inventory->clothing_size->name : 'N/A') . ' ' . $posCashRegisterMovement->pos_ticket->pos_sale->pos_sales_receivable->debtor_name . ' ' . $posCashRegisterMovement->pos_ticket->pos_sale->pos_sales_receivable->debtor_last_name;
            
                                    // Si el producto ya existe en el resumen, incrementar la cantidad y el total
                                    if(array_key_exists($productKey, $productSummaryOnCredit)){
                                        $productSummaryOnCredit[$productKey]['quantity'] += $warehouseProductInventory->pivot->quantity;
                                        $productSummaryOnCredit[$productKey]['total_amount'] += $warehouseProductInventory->sale_price * $warehouseProductInventory->pivot->quantity;
                                    }
                                    // Si el producto no existe en el resumen, agregarlo
                                    else{
                                        $productSummaryOnCredit[$productKey] = [
                                            'id_inventory' => $warehouseProductInventory->id,
                                            'debtor_full_name' => $posCashRegisterMovement->pos_ticket->pos_sale->pos_sales_receivable->debtor_name . ' ' . $posCashRegisterMovement->pos_ticket->pos_sale->pos_sales_receivable->debtor_last_name,
                                            'name' => $warehouseProductInventory->warehouse_product_catalog->name,
                                            'unit_measurement_name' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->name,
                                            'unit_measurement_abbr' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                                            'unit_measurement_quantity' => $warehouseProductInventory->warehouse_product_catalog->unit_measurement_quantity,
                                            'clothing_size' => $warehouseProductInventory->global_inventory->clothing_size ? $warehouseProductInventory->global_inventory->clothing_size->name : 'N/A',
                                            'clothing_category' => $warehouseProductInventory->warehouse_product_catalog->clothing_category ? $warehouseProductInventory->warehouse_product_catalog->clothing_category->name : 'N/A',
                                            'quantity' => $warehouseProductInventory->pivot->quantity,
                                            'price' => $warehouseProductInventory->sale_price,
                                            'is_combo_sale' => 'NO',
                                            'total_amount' => $warehouseProductInventory->sale_price * $warehouseProductInventory->pivot->quantity,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                    
                } else{
                    /* 
                    * Si el movimiento es una venta y el tipo de pago es cortesia
                    */
                    if($posCashRegisterMovement->pos_movement_type->name == 'venta' && $posCashRegisterMovement->pos_ticket->global_payment_type->name == 'cortesia' && $posCashRegisterMovement->pos_ticket->pos_ticket_status->name == 'pagado'){
                        $totalAmount += $posCashRegisterMovement->movement_amount;
            
                        // Si el movimiento tiene un ticket y una venta
                        if($posCashRegisterMovement->pos_ticket && $posCashRegisterMovement->pos_ticket->pos_sale){
                            $posSale = $posCashRegisterMovement->pos_ticket->pos_sale;
                
                            // Si la venta no es una venta de combo
                            if(!$posSale->is_combo_sale){
                                // Si la venta tiene inventarios de productos de almacén
                                if($posSale->warehouse_product_inventories){
                                    foreach($posSale->warehouse_product_inventories as $warehouseProductInventory){
                                        // Crear una clave única para el producto
                                        $productKey = $warehouseProductInventory->warehouse_product_catalog->name . ' ' . $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation . ' ' . ($warehouseProductInventory->global_inventory->clothing_size ? $warehouseProductInventory->global_inventory->clothing_size->name : 'N/A');
                
                                        // Si el producto ya existe en el resumen, incrementar la cantidad y el total
                                        if(array_key_exists($productKey, $productSummaryCourtesies)){
                                            $productSummaryCourtesies[$productKey]['quantity'] += $warehouseProductInventory->pivot->quantity;
                                            $productSummaryCourtesies[$productKey]['total_amount'] += $warehouseProductInventory->sale_price * $warehouseProductInventory->pivot->quantity;
                                        }
                                        // Si el producto no existe en el resumen, agregarlo
                                        else{
                                            $productSummaryCourtesies[$productKey] = [
                                                'id_inventory' => $warehouseProductInventory->id,
                                                'name' => $warehouseProductInventory->warehouse_product_catalog->name,
                                                'unit_measurement_name' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->name,
                                                'unit_measurement_abbr' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                                                'unit_measurement_quantity' => $warehouseProductInventory->warehouse_product_catalog->unit_measurement_quantity,
                                                'clothing_size' => $warehouseProductInventory->global_inventory->clothing_size ? $warehouseProductInventory->global_inventory->clothing_size->name : 'N/A',
                                                'clothing_category' => $warehouseProductInventory->warehouse_product_catalog->clothing_category ? $warehouseProductInventory->warehouse_product_catalog->clothing_category->name : 'N/A',
                                                'quantity' => $warehouseProductInventory->pivot->quantity,
                                                'price' => '0.00',
                                                'is_combo_sale' => 'NO',
                                                'total_amount' => '0.00',
                                            ];
                                            
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            /* 
            * Optenemos el resumen de los productos vendidos en combos
            */
            $posSalesInCombo = [];
            foreach($posCashRegisterMovements as $posCashRegisterMovement){
                if($posCashRegisterMovement->pos_ticket->pos_ticket_status->name == 'pagado' && $posCashRegisterMovement->pos_ticket->pos_sale){
                    $posSale = $posCashRegisterMovement->pos_ticket->pos_sale;
                    if($posSale->is_combo_sale){
                        $posSalesInCombo[] = $posSale;
                    }
                }
            }

            $productsSoldInCombos = [];
            foreach($posSalesInCombo as $posSale){
                if($posSale->warehouse_product_inventories){
                    $comboPricePerProduct = $posSale->global_combo->sale_price / count($posSale->warehouse_product_inventories);
                    foreach($posSale->warehouse_product_inventories as $warehouseProductInventory){
                        $productKey = $warehouseProductInventory->warehouse_product_catalog->name . '_' . $warehouseProductInventory->warehouse_product_catalog->unit_measurement_quantity . '_' . $posSale->global_combo->name;
                        if(array_key_exists($productKey, $productsSoldInCombos)){
                            $productsSoldInCombos[$productKey]['quantity'] += $warehouseProductInventory->pivot->original_quantity;
                            $productsSoldInCombos[$productKey]['total_amount'] += $warehouseProductInventory->pivot->original_quantity * $comboPricePerProduct;
                        } else {
                            $productsSoldInCombos[$productKey] = [
                                'id_inventory' => $warehouseProductInventory->id,
                                'name' => $warehouseProductInventory->warehouse_product_catalog->name,
                                'unit_measurement_quantity' => $warehouseProductInventory->warehouse_product_catalog->unit_measurement_quantity,
                                'unit_measurement_abbr' => $warehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                                'clothing_size' => $warehouseProductInventory->global_inventory->clothing_size ? $warehouseProductInventory->global_inventory->clothing_size->name : 'N/A',
                                'clothing_category' => $warehouseProductInventory->warehouse_product_catalog->clothing_category ? $warehouseProductInventory->warehouse_product_catalog->clothing_category->name : 'N/A',
                                'quantity' => $warehouseProductInventory->pivot->original_quantity,
                                'price' => number_format($comboPricePerProduct, 4, '.', ''),
                                'total_amount' => number_format(($warehouseProductInventory->pivot->original_quantity * $comboPricePerProduct), 4, '.', ''),
                                'combo_name' => $posSale->global_combo->name,
                            ];
                        }
                    }
                }
            }

            /* 
            * Obtemos el resumen de combos vendidos por la caja
            */
            $comboSales = ComboSale::where('pos_cash_register_id', $posCashRegister->id)
                                    ->where('is_canceled', 0)
                                    ->get();
            $comboSalesSummary = [];
            foreach($comboSales as $comboSale){
                $comboName = $comboSale->global_combo->name;
                $salePrice = $comboSale->global_combo->sale_price;
                $quantitySold = $comboSale->sale_count;
                $total = $salePrice * $quantitySold;
            
                if (!isset($comboSalesSummary[$comboName])) {
                    // Si el combo no está en el resumen se agrega
                    $comboSalesSummary[$comboName] = [
                        'combo_name' => $comboName,
                        'warehouse_name' => $posCashRegister->pos_product_warehouse->name,
                        'sale_price' => $salePrice,
                        'quantity_sold' => $quantitySold,
                        'total' => $total,
                    ];
                } else {
                    // Si el combo ya está en el resumen se actualiza la cantidad vendida y el total
                    $comboSalesSummary[$comboName]['quantity_sold'] += $quantitySold;
                    $comboSalesSummary[$comboName]['total'] += $total;
                }
            
            }

            /* 
            * Retornar la caja cerrada (Formato de respuesta)
            */
            $stadiumLocationName = $posCashRegister->stadium_location->name;
            $cashRegisterTypeName = $posCashRegister->pos_cash_register_type->name;
            $openingCashierName = $posCashRegister->user_cashier_opening->nombre;
            $closingCashierName = $posCashRegister->user_cashier_closing ? $posCashRegister->user_cashier_closing->nombre : 'cajero generico';
            $openingBalance = $posCashRegister->opening_balance;
            $closingBalance = $posCashRegister->closing_balance;
            $openingTime = (new DateTime($posCashRegister->opening_time))->format('Y-m-d H:i:s');
            $closingTime = (new DateTime($posCashRegister->closing_time))->format('Y-m-d H:i:s');

            $cashRegisterData = [
                'stadium_location' => $stadiumLocationName,
                'pos_product_warehouse_name' => $posCashRegister->pos_product_warehouse->name,
                'cash_register_type' => $cashRegisterTypeName,
                'opening_cashier' => $openingCashierName,
                'closing_cashier' => $closingCashierName,
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'opening_time' => $openingTime,
                'closing_time' => $closingTime,
            ];

            $pdf = PDF::loadView('pdfs.cajas_registradoras.acuse_corte_caja', [
                'cashRegisterData' => $cashRegisterData,
                'productSummary' => $productSummary,
                'productsSoldInCombos' => $productsSoldInCombos,
                'comboSalesSummary' => $comboSalesSummary,
                'productSummaryCourtesies' => $productSummaryCourtesies,
                'productSummaryOnCredit' => $productSummaryOnCredit,
                'productSummaryForBucketVendors' => $productSummaryForBucketVendors,
                'acknowledgmentKey' => 'generic_acknowledgment_key',
                'userManagerName' => $posCashRegister->pos_product_warehouse->user_manager->nombre,
            ]);

            if($sendEmail){
                $pdf->save(public_path('pdfs/corte_de_' . $cashRegisterTypeName . '_' . $posCashRegister->pos_product_warehouse->name . '_' . now()->toDateString() . '.pdf'));
            }else {
                return $pdf->stream();
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error to get summary of cash register',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    * Summary of the pos of warehouse by Christoper Patiño
    */
    public function summaryPosOfWarehouse(Request $request, $sendEmail = false)
    {
        try {

                $request->validate([
                    'pos_product_warehouse_id' => 'required|integer',
                    'date' => 'required|date_format:Y-m-d'
                ]);

                $globalTotalAmount = 0;
                $globalProductSummary = [];
                $globalPosCashRegisterMovements = [];
                $earliestOpeningTime = null;
                $latestClosingTime = null;
                $totalOpeningBalance = 0;
                $totalClosingBalance = 0;
                
                /* 
                * Obtener los movimientos de todas las cajas que se abrieron en el dia actual
                */
                $globalPosCashRegisters = PosCashRegister::where('pos_product_warehouse_id', $request->pos_product_warehouse_id)    
                    ->whereDate('opening_time', $request->date)
                    ->get();

                foreach($globalPosCashRegisters as $globalPosCashRegister) {
                    $globalPosCashRegisterMovements = $globalPosCashRegister->pos_cash_register_movements;
                    foreach($globalPosCashRegisterMovements as $globalPosCashRegisterMovement){
                        if($globalPosCashRegisterMovement->pos_movement_type->name == 'venta' && $globalPosCashRegisterMovement->pos_ticket->pos_ticket_status->name == 'pagado'){
                            $globalTotalAmount += $globalPosCashRegisterMovement->movement_amount;
                            // Si el movimiento tiene un ticket y una venta
                            if($globalPosCashRegisterMovement->pos_ticket && $globalPosCashRegisterMovement->pos_ticket->pos_sale){
                                $globalPosSale = $globalPosCashRegisterMovement->pos_ticket->pos_sale;
                                // Si la venta tiene inventarios de productos de almacén
                                if($globalPosSale->warehouse_product_inventories){
                                    foreach($globalPosSale->warehouse_product_inventories as $globalWarehouseProductInventory){
                                        // Crear una clave única para el producto
                                        $globalProductKey = $globalWarehouseProductInventory->warehouse_product_catalog->name . ' ' . $globalWarehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation . ' ' . ($globalWarehouseProductInventory->global_inventory->clothing_size ? $globalWarehouseProductInventory->global_inventory->clothing_size->name : 'N/A');
                                        // Si el producto ya existe en el resumen, incrementar la cantidad y el total
                                        if(array_key_exists($globalProductKey, $globalProductSummary)){
                                            $globalProductSummary[$globalProductKey]['quantity'] += $globalWarehouseProductInventory->pivot->quantity;
                                            $globalProductSummary[$globalProductKey]['total_amount'] += $globalWarehouseProductInventory->sale_price * $globalWarehouseProductInventory->pivot->quantity;
                                        }
                                        // Si el producto no existe en el resumen, agregarlo
                                        else{
                                            $globalProductSummary[$globalProductKey] = [
                                                'id_inventory' => $globalWarehouseProductInventory->id,
                                                'name' => $globalWarehouseProductInventory->warehouse_product_catalog->name,
                                                'unit_measurement_name' => $globalWarehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->name,
                                                'unit_measurement_abbr' => $globalWarehouseProductInventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                                                'unit_measurement_quantity' => $globalWarehouseProductInventory->warehouse_product_catalog->unit_measurement_quantity,
                                                'clothing_size' => $globalWarehouseProductInventory->global_inventory->clothing_size ? $globalWarehouseProductInventory->global_inventory->clothing_size->name : 'N/A',
                                                'clothing_category' => $globalWarehouseProductInventory->warehouse_product_catalog->clothing_category ? $globalWarehouseProductInventory->warehouse_product_catalog->clothing_category->name : 'N/A',
                                                'quantity' => $globalWarehouseProductInventory->pivot->quantity,
                                                'price' => $globalWarehouseProductInventory->sale_price,
                                                'total_amount' => $globalWarehouseProductInventory->sale_price * $globalWarehouseProductInventory->pivot->quantity,
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // Verificar si la caja registradora se abrió antes que las anteriores
                    if(!$earliestOpeningTime || $globalPosCashRegister->opening_time < $earliestOpeningTime) {
                        $earliestOpeningTime = $globalPosCashRegister->opening_time;
                    }
                    // Verificar si la caja registradora se cerró después que las anteriores
                    if(!$latestClosingTime || $globalPosCashRegister->closing_time > $latestClosingTime) {
                        $latestClosingTime = $globalPosCashRegister->closing_time;
                    }
                    // Sumar los saldos de apertura y cierre
                    $totalOpeningBalance += $globalPosCashRegister->opening_balance;
                    $totalClosingBalance += $globalPosCashRegister->closing_balance;
                }

                uasort($globalProductSummary, function($a, $b) {
                    return $b['quantity'] <=> $a['quantity'];
                });

                /* 
                * Obtener el total de ventas echas con tarjeta y efectivo
                */
                $totalCardAmount = 0;
                $totalTransactionsCard = 0;
                $totalCashAmount = 0;
                $totalTransactionsCash = 0;

                foreach($globalPosCashRegisters as $globalPosCashRegister){
                    $globalPosCashRegisterMovements = $globalPosCashRegister->pos_cash_register_movements;
                    foreach($globalPosCashRegisterMovements as $globalPosCashRegisterMovement){
                        if($globalPosCashRegisterMovement->pos_movement_type->name == 'venta' && $globalPosCashRegisterMovement->pos_ticket && $globalPosCashRegisterMovement->pos_ticket->pos_ticket_status->name == 'pagado'){
                            if($globalPosCashRegisterMovement->pos_ticket->global_payment_type->name == 'tarjeta'){
                                $totalCardAmount += $globalPosCashRegisterMovement->movement_amount;
                                $totalTransactionsCard++;
                            } else if($globalPosCashRegisterMovement->pos_ticket->global_payment_type->name == 'efectivo'){
                                $totalCashAmount += $globalPosCashRegisterMovement->movement_amount;
                                $totalTransactionsCash++;
                            }
                        }
                    }
                }
                
                $posData = [
                    'stadium_location' => $globalPosCashRegisters[0]->stadium_location->name,
                    'pos_product_warehouse_name' => $globalPosCashRegisters[0]->pos_product_warehouse->name,
                    'opening_manager' => $globalPosCashRegisters[0]->pos_product_warehouse->user_manager->nombre,
                    'opening_balance' => $totalOpeningBalance,
                    'closing_balance' => $totalClosingBalance,
                    'opening_time' => $earliestOpeningTime,
                    'closing_time' => $latestClosingTime,
                    'total_card_amount' => $totalCardAmount,
                    'total_transactions_card' => $totalTransactionsCard,
                    'total_cash_amount' => $totalCashAmount,
                    'total_transactions_cash' => $totalTransactionsCash,
                ];

                $pdf = PDF::loadView('pdfs.cajas_registradoras.acuse_resumen_venta', [
                    'cashRegisterData' => $posData,
                    'productSummary' => $globalProductSummary,
                    'acknowledgmentKey' => 'generic_acknowledgment_key',
                    'userManagerName' => $globalPosCashRegisters[0]->pos_product_warehouse->user_manager->nombre,
                ]);

                if($sendEmail){
                    $pdf->save(public_path('pdfs/resumen_ventas_de_' . $globalPosCashRegisters[0]->pos_product_warehouse->name . '_' . $request->date . '.pdf'));
                }else {
                    return $pdf->stream();
                }


        } catch(\Exception $e){
            return response()->json([
                'message' => 'Error to get summary of pos of warehouse',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /*  
    *
    * Get cash register are open by Christoper Patiño
    *
    */
    public function posCashRegisterAreOpen(Request $request)
    {
        try {
            $request->validate([
                'pos_product_warehouse_id' => 'required|integer',
                'stadium_location_id' => 'required|integer',
            ]);

            /* 
            * Validacion de datos
            */
            $posProductWarehouse = PosProductWarehouse::where('id', $request->pos_product_warehouse_id)->first();
            if (!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, product warehouse does not exist.',
                    'data' => $posProductWarehouse
                ], 400);
            }

            $stadiumLocation = StadiumLocation::where('id', $request->stadium_location_id)->first();
            if (!$stadiumLocation) {
                return response()->json([
                    'message' => 'Error, stadium location does not exist.',
                    'data' => $stadiumLocation
                ], 400);
            }

            /* 
            * Obtener las cajas que estan abiertas para el almacen de productos de un estadio
            */
            $posCashRegisters = PosCashRegister::where('pos_product_warehouse_id', $request->pos_product_warehouse_id)
                ->where('stadium_location_id', $request->stadium_location_id)
                ->where('is_open', true)
                ->get();
            
            if ($posCashRegisters->isEmpty()) {
                return response()->json([
                    'message' => 'Error, cash register are not open.',
                    'data' => $posCashRegisters
                ], 400);
            }
            
            /* 
            * Formatear la respuesta
            */
            $formattedPosCashRegisters = [];
            foreach($posCashRegisters as $posCashRegister) {
                $openingTime = $posCashRegister->opening_time ? 
                (new DateTime($posCashRegister->opening_time))
                ->modify('-1 hour')
                ->format('Y-m-d H:i:s') : null;
                $formattedPosCashRegisters[] = [
                    'pos_cash_register_id' => $posCashRegister->id,
                    'pos_cash_register_type' => $posCashRegister->pos_cash_register_type->name,
                    'stadium_location' => $posCashRegister->stadium_location->name,
                    'user_cashier_opening' => $posCashRegister->user_cashier_opening->nombre,
                    'opening_balance' => $posCashRegister->opening_balance,
                    'current_balance' => $posCashRegister->current_balance,
                    'opening_time' => $openingTime,
                ];
            }

            return response()->json([
                'message' => 'Success, cash register are open.',
                'data' => $formattedPosCashRegisters
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error to get cash register are open',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Cancellation of a product sale by Christoper Patiño
    * 
    */
    public function posCancelProductbyTicket(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'pos_ticket_id' => 'required|integer',
                'warehouse_product_inventories' => 'required|array',
                'user_cashier_id' => 'required|integer',
            ]);

            /* 
            * Validaciones de existencia de los datos            
            */
            $posTicket = PosTicket::where('id', $request->pos_ticket_id)->first();
            if (!$posTicket) {
                return response()->json([
                    'message' => 'Error, ticket does not exist.',
                    'data' => $posTicket
                ], 400);
            }

            $userCashier = User::where('id', $request->user_cashier_id)->first();
            if (!$userCashier) {
                return response()->json([
                    'message' => 'Error, user cashier does not exist.',
                    'data' => $userCashier
                ], 400);
            }
            $sellerWalletAccount = $userCashier->wallet_account;
            if (!$sellerWalletAccount) {
                return response()->json([
                    'message' => 'Error, seller wallet account does not exist.',
                    'data' => $sellerWalletAccount
                ], 400);
            }

            /* 
            * Determinar el tipo de pago de la venta
            */
            $paymentTypeName = $posTicket->global_payment_type->name;
            if($paymentTypeName == 'halcones_wallet') {
                /* 
                * Creamos una transaccion en la billetera del usuario
                */
                $walletTransaction = new WalletTransaction();
                $walletTransaction->origin_wallet_account_id = $userCashier->wallet_account->id;
                $walletTransaction->destination_wallet_account_id = null;
                $walletTransaction->wallet_transaction_type_id = WalletTransactionType::where('name', 'cancelacion_compra')->first()->id;
                $walletTransaction->wallet_transaction_status_id = WalletTransactionStatus::where('name', 'completada')->first()->id;
                $walletTransaction->global_payment_type_id = null;
                $walletTransaction->global_card_cash_payment_id = null;
                $walletTransaction->pos_sale_id = null;
                $walletTransaction->seller_wallet_account_id = $sellerWalletAccount->id;
                $walletTransaction->amount = null;
                $walletTransaction->description = null;
                $walletTransaction->balance_account_before_transaction = null;
                $walletTransaction->balance_account_after_transaction = null;
                $walletTransaction->save();

            }

            /* 
            * Crear un movimiento en la caja registradora
            */
            $posCashRegisterMovement = new PosCashRegisterMovement();
            $posCashRegisterMovement->pos_cash_register_id = $posTicket->pos_cash_register_id;
            $posCashRegisterMovement->pos_movement_type_id = PosMovementType::where('name', 'cancelacion_producto')->first()->id;
            $posCashRegisterMovement->pos_ticket_id = $request->pos_ticket_id;
            $posCashRegisterMovement->previous_balance = $posTicket->pos_cash_register->current_balance;
            $posCashRegisterMovement->movement_amount = 0;
            $posCashRegisterMovement->new_balance = 0;
            $posCashRegisterMovement->reason = 'Cancelación de producto en ticket de venta';
            $posCashRegisterMovement->save();

            $previousTotalAmountInCashRegister = $posTicket->pos_cash_register->current_balance;
            $total = 0;
            $cancelledProducts = [];
            
            /* 
            * Cancelar productos de la venta
            */
            foreach($request->warehouse_product_inventories as $warehouseProductInventory) {
                $warehouseProductInventoryExist = $posTicket->load('pos_sale.warehouse_product_inventories')->pos_sale->warehouse_product_inventories->firstWhere('id', $warehouseProductInventory['id']);

                if ($warehouseProductInventoryExist) {
                    /* 
                    * Identificar si la venta 
                    */
                    $sale = $posTicket->pos_sale;

                    $total += ($sale->is_combo_sale ? $warehouseProductInventoryExist->discount_sale_price : $warehouseProductInventoryExist->sale_price) * $warehouseProductInventory['quantity'];
                    /*
                    * Actualizamos el stock del producto en el almacen de productos
                    */
                    $warehouseProductInventoryExist->stock += $warehouseProductInventory['quantity'];
                    $warehouseProductInventoryExist->save();

                    $cancelledProducts[] = [
                        'id' => $warehouseProductInventoryExist->id,
                        'name' => $warehouseProductInventoryExist->warehouse_product_catalog->name,
                        'unit_measurement_name' => $warehouseProductInventoryExist->warehouse_product_catalog->pos_unit_measurement->name,
                        'unit_measurement_abbr' => $warehouseProductInventoryExist->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                        'unit_measurement_quantity' => $warehouseProductInventoryExist->warehouse_product_catalog->unit_measurement_quantity,
                        'quantity' => $warehouseProductInventory['quantity'],
                        'price' => ($sale->is_combo_sale ? $warehouseProductInventoryExist->discount_sale_price : $warehouseProductInventoryExist->sale_price),
                        'total_amount' => ($sale->is_combo_sale ? $warehouseProductInventoryExist->discount_sale_price : $warehouseProductInventoryExist->sale_price) * $warehouseProductInventory['quantity'],
                    ];

                    if($warehouseProductInventoryExist->pivot->quantity >= $warehouseProductInventory['quantity']){
                        /* 
                        * Reducir la cantidad de productos vendidos en la venta
                        */
                        $quantity = $warehouseProductInventoryExist->pivot->quantity - $warehouseProductInventory['quantity'];
                        if($quantity > 0){
                            $sale->warehouse_product_inventories()->updateExistingPivot($warehouseProductInventoryExist->id, ['quantity' => $quantity]);
                            $sale->warehouse_product_inventories()->updateExistingPivot($warehouseProductInventoryExist->id, ['quantity_if_removed_product' => $quantity]);
                        } else {
                            $sale->warehouse_product_inventories()->updateExistingPivot($warehouseProductInventoryExist->id, ['quantity' => 0]);
                            $sale->warehouse_product_inventories()->updateExistingPivot($warehouseProductInventoryExist->id, ['quantity_if_removed_product' => $quantity]);
                        }
                        /* 
                        * Actualizar el total de la venta
                        */
                        $sale->total_amount -= ($sale->is_combo_sale ? $warehouseProductInventoryExist->discount_sale_price : $warehouseProductInventoryExist->sale_price) * $warehouseProductInventory['quantity'];
                        $sale->save();
                        /* 
                        * Actualizar el total del ticket de la venta
                        */
                        $posTicket->total_amount -= ($sale->is_combo_sale ? $warehouseProductInventoryExist->discount_sale_price : $warehouseProductInventoryExist->sale_price) * $warehouseProductInventory['quantity'];
                        $posTicket->save();

                    } else {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Error, the quantity of the product to cancel is greater than the quantity of the product in the sale.',
                            'data' => $warehouseProductInventoryExist
                        ], 400);
                    }

                    /* 
                    * Actualizar el status del ticket
                    */
                    $statusPartiallyPaid = PosTicketStatus::where('name', 'parcialmente_cancelado')->first();
                    $posTicket->pos_ticket_status_id = $statusPartiallyPaid->id;
                    $posTicket->save();

                    /* 
                    * Creamos una instacia para la cancelacion del producto
                    */
                    $posProductCancelation = new PosProductCancelation();
                    $posProductCancelation->user_cashier_id = $request->user_cashier_id;
                    $posProductCancelation->pos_ticket_id = $request->pos_ticket_id;
                    $posProductCancelation->warehouse_product_inventory_id = $warehouseProductInventory['id'];
                    $posProductCancelation->wallet_transaction_id = $walletTransaction->id ?? null;
                    $posProductCancelation->pos_cash_register_movement_id = $posCashRegisterMovement->id;
                    $posProductCancelation->quantity = $warehouseProductInventory['quantity'];
                    $posProductCancelation->total_amount = ($sale->is_combo_sale ? $warehouseProductInventoryExist->discount_sale_price : $warehouseProductInventoryExist->sale_price) * $warehouseProductInventory['quantity'];
                    $posProductCancelation->reason = 'Cancelación de producto en ticket de venta';
                    $posProductCancelation->save();

                    /* 
                    * Actualizar el total de la caja registradora
                    */
                    $posTicket->pos_cash_register->current_balance -= ($sale->is_combo_sale ? $warehouseProductInventoryExist->discount_sale_price : $warehouseProductInventoryExist->sale_price) * $warehouseProductInventory['quantity'];
                    $posTicket->pos_cash_register->save();

                    /* 
                    * actualizar el movimiento de la caja registradora
                    */
                    $posCashRegisterMovement->movement_amount += ($sale->is_combo_sale ? $warehouseProductInventoryExist->discount_sale_price : $warehouseProductInventoryExist->sale_price) * $warehouseProductInventory['quantity'];
                    $posCashRegisterMovement->new_balance = $previousTotalAmountInCashRegister - $posCashRegisterMovement->movement_amount;
                    $posCashRegisterMovement->save();

                    if($paymentTypeName == 'halcones_wallet'){
                        /* 
                        * Encontramos al usuario para actualizar su saldo y actualizar la transaccion en su billetera
                        */
                        $userMember = $sale->wallet_transaction->origin_wallet_account;
                        /* 
                        * Creamos una transaccion en la billetera del usuario
                        */
                        $walletTransaction->destination_wallet_account_id = $userMember->id;
                        $walletTransaction->amount = ($sale->is_combo_sale ? $warehouseProductInventoryExist->discount_sale_price : $warehouseProductInventoryExist->sale_price) * $warehouseProductInventory['quantity'];
                        $walletTransaction->description = 'Cancelación de productos en ticket de venta';
                        $walletTransaction->balance_account_before_transaction = $userMember->balance;
                        $walletTransaction->balance_account_after_transaction = $userMember->balance + ($sale->is_combo_sale ? $warehouseProductInventoryExist->discount_sale_price : $warehouseProductInventoryExist->sale_price) * $warehouseProductInventory['quantity'];
                        $walletTransaction->save();
                        /* 
                        * Actualizar el saldo del usuario
                        */
                        $userMember->current_balance += ($sale->is_combo_sale ? $warehouseProductInventoryExist->discount_sale_price : $warehouseProductInventoryExist->sale_price) * $warehouseProductInventory['quantity'];
                        $userMember->save();
                    }
                    
                } else {
                    return response()->json([
                        'message' => 'Error, product does not exist in the ticket.',
                        'data' => $warehouseProductInventory
                    ], 400);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Success, product sale cancelled.',
                'data' => [
                    'change_given' => $total,
                    'cancelled_products' => $cancelledProducts,
                    'pos_cash_register_movement' => $posCashRegisterMovement
                ]
            ], 200);


        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error to cancel product sale',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    * Cancel producs of a ticket by bucketvendor by Christoper Patiño
    */
    public function posCancelProductbyBucketVendor(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'pos_ticket_id' => 'required|integer',
                'warehouse_product_inventories' => 'required|array',
            ]);

            /* 
            * Validaciones de existencia de los datos            
            */
            $posTicket = PosTicket::where('id', $request->pos_ticket_id)->first();
            if (!$posTicket) {
                return response()->json([
                    'message' => 'Error, ticket does not exist.',
                    'data' => $posTicket
                ], 400);
            }

            /* 
            * Regresar los productos al almacen de productos
            */
            $changeGiven = 0;
            foreach($request->warehouse_product_inventories as $warehouseProductInventory){
                $warehouseProductInventoryExist = $posTicket->load('pos_sale.warehouse_product_inventories')->pos_sale->warehouse_product_inventories->firstWhere('id', $warehouseProductInventory['id']);
                if($warehouseProductInventoryExist){
                    /* 
                    * Validar que la cantidad de productos a cancelar sea menor o igual a la cantidad de productos vendidos
                    */
                    if($warehouseProductInventoryExist->pivot->quantity < $warehouseProductInventory['quantity']){
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Error, the quantity of the product to cancel is greater than the quantity of the product in the sale.',
                            'data' => $warehouseProductInventoryExist
                        ], 400);
                    }
                    $changeGiven += $warehouseProductInventoryExist->discount_sale_price * $warehouseProductInventory['quantity'];
                    $warehouseProductInventoryExist->stock += $warehouseProductInventory['quantity'];
                    $warehouseProductInventoryExist->save();
            
                    /* 
                    * Actualizar el quantity de la venta en la tabla pivote
                    */
                    $warehouseProductInventoryExist->pivot->quantity -= $warehouseProductInventory['quantity'];
                    $warehouseProductInventoryExist->pivot->save();
                    
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Error, product does not exist in the ticket.',
                        'data' => $warehouseProductInventory
                    ], 400);
                }
            }

            /* 
            * Actualizar el total de la venta
            */
            $posTicket->total_amount -= $changeGiven;
            $posTicket->save();

            $posTicket->pos_sale->total_amount -= $changeGiven;
            $posTicket->pos_sale->save();

            /* 
            * Actualizar el saldo actual de la caja registradora
            */
            $posTicket->pos_cash_register->current_balance -= $changeGiven;
            $posTicket->pos_cash_register->save();

            /* 
            * Actualizar el movimiento de la caja registradora
            */
            $posCashRegisterMovement = $posTicket->pos_cash_register->pos_cash_register_movements->where('pos_ticket_id', $request->pos_ticket_id)->first();
            if($posCashRegisterMovement){
                $posCashRegisterMovement->movement_amount -= $changeGiven;
                $posCashRegisterMovement->save();
            }

           DB::commit();

            return response()->json([
                'message' => 'Success, product sale cancelled by bucket vendor.',
                'change_given' => $changeGiven
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error to cancel product sale by bucket vendor',
                'error_data' => $e->getMessage()
            ], 500);
        }

    }
}
