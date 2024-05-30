<?php

namespace App\Http\Controllers\api\pointofsale;

use App\Http\Controllers\Controller;
use App\Mail\SendAcknowledgmentMail;
use App\Models\PointOfSale\GlobalCardCashPayment;
use App\Models\PointOfSale\GlobalPaymentType;
use App\Models\PointOfSale\GlobalTypeCardPayment;
use App\Models\PointOfSale\InventoryTransactionType;
use App\Models\PointOfSale\PosProductWarehouse;
use App\Models\PointOfSale\WarehouseSupplier;
use App\Models\PointOfSale\WarehouseTransactionAcknowledgment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class WarehouseTransactionAcknowledgmentController extends Controller
{
    /* 
    *
    * Get all Warehouse Transaction Acknowledgment by Christoper Patiño
    *
    */
    public function index(Request $request)
    {
        try {

            $request->validate([
                'pos_product_warehouse_id' => 'required|numeric',
            ]);

            $posProductWarehouse = PosProductWarehouse::find($request->pos_product_warehouse_id);
            if(!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, Pos Product Warehouse not found'
                ], 404);
            }

            $warehouse_transaction_acknowledgment = WarehouseTransactionAcknowledgment::where('is_active', true)
                ->where('pos_product_warehouse_id', $request->pos_product_warehouse_id)
                ->get();

            $formatted = $warehouse_transaction_acknowledgment->map(function($item) {
                return [
                    'id' => $item->id,
                    'transaction_type' => $item->inventory_transaction_type->name,
                    'description' => $item->inventory_transaction_type->description ?? 'no aplica',
                    'user_manager' => $item->user_manager->nombre,
                    'warehouse_supplier' => $item->warehouse_supplier->name ?? 'no aplica',
                    'acknowledgment_key' => $item->acknowledgment_key,
                    'is_active' => $item->is_active,
                    'reason' => $item->reason ?? 'no aplica',
                    'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'message' => 'All Warehouse Transaction Acknowledgment',
                'data' => $formatted
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, cannot get all Warehouse Transaction Acknowledgment',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Get Warehouse Transaction Acknowledgment by date by Christoper Patiño
    *
    */
    public function showByDate(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date_format:Y-m-d'
            ]);

            $date = $request->date;
            $warehouse_transaction_acknowledgment = WarehouseTransactionAcknowledgment::whereDate('created_at', $date)->get();

            $formatted = $warehouse_transaction_acknowledgment->map(function($item) {
                return [
                    'id' => $item->id,
                    'transaction_type' => $item->inventory_transaction_type->name,
                    'user_manager' => $item->user_manager->nombre,
                    'warehouse_supplier' => $item->warehouse_supplier->name ?? 'no aplica',
                    'acknowledgment_key' => $item->acknowledgment_key,
                    'is_active' => $item->is_active,
                    'reason' => $item->reason ?? 'no aplica',
                    'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'message' => 'Warehouse Transaction Acknowledgment by date',
                'data' => $formatted
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, cannot get Warehouse Transaction Acknowledgment by date',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create Warehouse Transaction Acknowledgment by Christoper Patiño
    *
    */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'user_manager_id' => 'required|numeric',
                'warehouse_supplier_id' => 'nullable|numeric',
                'inventory_transaction_type_id' => 'required|numeric',
                'pos_product_warehouse_id' => 'required|numeric',
                'reason' => 'required|max:255'
            ]);

            /* 
            * Validacion de datos
            */
            $userManager = User::find($request->user_manager_id);
            if(!$userManager || $userManager->id_rol != 'super_admin') {
                return response()->json([
                    'message' => 'Error, User Manager not found or not a User Manager'
                ], 404);
            }

            $posProductWarehouse = PosProductWarehouse::find($request->pos_product_warehouse_id);
            if(!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, Pos Product Warehouse not found'
                ], 404);
            }

           if($request->warehouse_supplier_id) {
                $warehouseSupplier = WarehouseSupplier::find($request->warehouse_supplier_id);
                if(!$warehouseSupplier) {
                    return response()->json([
                        'message' => 'Error, Warehouse Supplier not found'
                    ], 404);
                }
            }

            $invertoryTransactionType = InventoryTransactionType::find($request->inventory_transaction_type_id);
            if(!$invertoryTransactionType) {
                return response()->json([
                    'message' => 'Error, Inventory Transaction Type not found'
                ], 404);
            }

            /* 
            * Validar que no haya un acuse activo para la tienda con el mismo tipo de transaccion
            */
            $warehouseTransactionAcknowledgmentExist = WarehouseTransactionAcknowledgment::where('pos_product_warehouse_id', $request->pos_product_warehouse_id)
                ->where('inventory_transaction_type_id', $request->inventory_transaction_type_id)
                ->where('is_active', true)
                ->first();
            if($warehouseTransactionAcknowledgmentExist) {
                return response()->json([
                    'message' => 'Error, Warehouse Transaction Acknowledgment already exists'
                ], 400);
            }

            if(!$request->warehouse_supplier_id && $invertoryTransactionType->name == 'compra_de_stock') {
                return response()->json([
                    'message' => 'Error, Warehouse Supplier is required'
                ], 400);
            }

            /* 
            * Crear una nueva instancia de Warehouse Transaction Acknowledgment
            */
            $warehouseTransactionAcknowledgment = new WarehouseTransactionAcknowledgment();
            $warehouseTransactionAcknowledgment->user_manager_id = $request->user_manager_id;
            $warehouseTransactionAcknowledgment->warehouse_supplier_id = $request->warehouse_supplier_id ?? null;
            $warehouseTransactionAcknowledgment->inventory_transaction_type_id = $request->inventory_transaction_type_id;
            $warehouseTransactionAcknowledgment->pos_product_warehouse_id = $request->pos_product_warehouse_id;
            $warehouseTransactionAcknowledgment->acknowledgment_key = 'WTA-' . uniqid();
            $warehouseTransactionAcknowledgment->is_active = true;
            $warehouseTransactionAcknowledgment->reason = $request->reason ?? null;
            $warehouseTransactionAcknowledgment->save();

            $warehouseTransactionAcknowledgment->description = $warehouseTransactionAcknowledgment->inventory_transaction_type->description ?? 'no aplica';
            
            DB::commit();

            return response()->json([
                'message' => 'Warehouse Transaction Acknowledgment created successfully',
                'data' => $warehouseTransactionAcknowledgment
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, cannot create Warehouse Transaction Acknowledgment',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    public function finalizeAcknowledgement(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'acknowledgment_key' => 'required|string',
                'global_payment_type_id' => 'nullable|numeric',
                'global_type_card_payment_id' => 'nullable|numeric',
                'amount_received' => 'nullable|numeric',
            ]);

            /* 
            * Validacion de datos
            */
            $warehouseTransactionAcknowledgment = WarehouseTransactionAcknowledgment::where('acknowledgment_key', $request->acknowledgment_key)
                ->where('is_active', true)
                ->first();
            if(!$warehouseTransactionAcknowledgment) {
                return response()->json([
                    'message' => 'Error, Warehouse Transaction Acknowledgment not found'
                ], 404);
            }

            if($request->global_payment_type_id) {
                $globalPaymentType = GlobalPaymentType::find($request->global_payment_type_id);
                if(!$globalPaymentType) {
                    return response()->json([
                        'message' => 'Error, Global Payment Type not found'
                    ], 404);
                }
            }

            $genericInventoryTransactionType = $warehouseTransactionAcknowledgment->global_inventory_transactions->first()->inventory_transaction_type->name;
            

            if($genericInventoryTransactionType == 'compra_de_stock') {
                /* 
                * Validamos con que tipo de pago se completara la transaccion
                */
                if(!$request->global_payment_type_id) {
                    return response()->json([
                        'message' => 'Error, Global Payment Type is required'
                    ], 400);
                }

                if($globalPaymentType->name == 'efectivo' || $globalPaymentType->name == 'tarjeta' || $globalPaymentType->name == 'credito' || $globalPaymentType->name == 'donacion') {

                    $globalTypeCardPayment = null;

                    if($globalPaymentType->name == 'tarjeta') {
                        if(!$request->global_type_card_payment_id) {
                            return response()->json([
                                'message' => 'Error, Global Type Card Payment is required'
                            ], 400);
                        }

                        $globalTypeCardPayment = GlobalTypeCardPayment::find($request->global_type_card_payment_id);
                        if(!$globalTypeCardPayment) {
                            return response()->json([
                                'message' => 'Error, Global Type Card Payment not found'
                            ], 404);
                        }
                    }

                    /* 
                    * Validamos que el monto sea igual o mayor al total de la transaccion
                    */
                    $totalAmount = $warehouseTransactionAcknowledgment->global_inventory_transactions->sum(function($item) {
                        return $item->stock_movement * $item->global_inventory->purchase_price;
                    });

                    /* 
                    * Crear una nueva instancia de Global Card Cash Payment
                    */
                    $globalCardCashPayment = new GlobalCardCashPayment();
                    $globalCardCashPayment->global_type_card_payment_id = $globalTypeCardPayment->id ?? null;
                    $globalCardCashPayment->amount_received = $request->amount_received ?? 0;
                    $globalCardCashPayment->amount_change_given = $request->amount_received ? $request->amount_received - $totalAmount : 0;
                    $globalCardCashPayment->save();

                    /* 
                    * Actualizamos el acuse para su finalizacion
                    */
                    $warehouseTransactionAcknowledgment->global_payment_type_id = $globalPaymentType->id;
                    $warehouseTransactionAcknowledgment->global_type_card_payment_id = $globalTypeCardPayment->id ?? null;
                    $warehouseTransactionAcknowledgment->global_card_cash_payment_id = $globalCardCashPayment->id;
                    $warehouseTransactionAcknowledgment->is_active = false;
                    $warehouseTransactionAcknowledgment->save();

                    /* 
                    * Enviamos el pdf del acuse al correo del administrador de la tienda
                    */
                    $sendByEmail = true;
                    $this->downloadGlobalInventoryMovementAcknowledgment($request, $sendByEmail);
                    $pdf = public_path('pdfs/' . $warehouseTransactionAcknowledgment->acknowledgment_key . '.pdf');
                    $managerEmail = $warehouseTransactionAcknowledgment->global_inventory_transactions->first()->global_inventory->pos_product_warehouse->email;
                    $managerName = $warehouseTransactionAcknowledgment->global_inventory_transactions->first()->global_inventory->pos_product_warehouse->user_manager->nombre;
                    $reason = 'Acuse que confirma la compra de stock para la tienda' . ' ' . $warehouseTransactionAcknowledgment->global_inventory_transactions->first()->global_inventory->pos_product_warehouse->name;
                    
                   // Mail::send(new SendAcknowledgmentMail($managerEmail, $managerName, $reason, $pdf));

                    /* 
                    * Eliminamos el pdf del servidor
                    */
                    if(file_exists($pdf) && !unlink($pdf)) {
                        return response()->json([
                            'message' => 'Error, cannot delete pdf'
                        ], 500);
                    }

                    DB::commit();

                    return response()->json([
                        'message' => 'Warehouse Transaction Acknowledgment finalized successfully',
                        'data' => $warehouseTransactionAcknowledgment
                    ], 200);
                    
                } else {
                    return response()->json([
                        'message' => 'Error, Global Payment Type not valid'
                    ], 400);
                }
            } else if($genericInventoryTransactionType == 'transferencia_de_stock_a_tienda'){

                /* 
                * Actualizamos el acuse para su finalizacion
                */
                $warehouseTransactionAcknowledgment->is_active = false;
                $warehouseTransactionAcknowledgment->save();

                /* 
                * Enviamos el pdf del acuse al correo del administrador de la tienda
                */
                $sendEmail = true;
                $this->downloadGlobalInventoryMovementAcknowledgment($request, $sendEmail);
                $pdf = public_path('pdfs/' . $warehouseTransactionAcknowledgment->acknowledgment_key . '.pdf');
                $managerEmail = $warehouseTransactionAcknowledgment->global_inventory_transactions->first()->global_inventory->pos_product_warehouse->email;
                $managerName = $warehouseTransactionAcknowledgment->global_inventory_transactions->first()->global_inventory->pos_product_warehouse->user_manager->nombre;
                $reason = 'Acuse que confirma la transferencia de stock para la tienda' . ' ' . $warehouseTransactionAcknowledgment->global_inventory_transactions->first()->global_inventory->pos_product_warehouse->name;

                //Mail::send(new SendAcknowledgmentMail($managerEmail, $managerName, $reason, $pdf));

                /* 
                * Eliminamos el pdf del servidor
                */
                if(file_exists($pdf) && !unlink($pdf)) {
                    return response()->json([
                        'message' => 'Error, cannot delete pdf'
                    ], 500);
                }
                    
                DB::commit();

                return response()->json([
                    'message' => 'Warehouse Transaction Acknowledgment finalized successfully',
                    'data' => $warehouseTransactionAcknowledgment
                ], 200);
            }
            

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, cannot finalize Warehouse Transaction Acknowledgment',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * download global inventory movement acknowledgment in pdf by Christoper Patiño
    *
    */
    public function downloadGlobalInventoryMovementAcknowledgment(Request $request,  $sendByEmail = false)
    {
        try {
            $request->validate([
                'acknowledgment_key' => 'required|string'
            ]);

            $warehouseTransactionAcknowledgment = WarehouseTransactionAcknowledgment::where('acknowledgment_key', $request->acknowledgment_key)->first();
            if(!$warehouseTransactionAcknowledgment) {
                return response()->json([
                    'message' => 'Error, Warehouse Transaction Acknowledgment not found'
                ], 404);
            }

             /* 
            * Encontramos los tipos de transacciones de inventario
            */
            $inventoryTransactionType1 = InventoryTransactionType::where('name', 'compra_de_stock')->first();
            $inventoryTransactionType2 = InventoryTransactionType::where('name', 'devolucion_de_stock')->first();
            $inventoryTransactionType3 = InventoryTransactionType::where('name', 'actualizacion_de_propiedades')->first();
            $inventoryTransactionType4 = InventoryTransactionType::where('name', 'transferencia_de_stock_a_tienda')->first();
            $inventoryTransactionType5 = InventoryTransactionType::where('name', 'devolucion_de_stock_a_almacen')->first();
            $inventoryTransactionType6 = InventoryTransactionType::where('name', 'ajustamiento')->first();

            /* 
            * Obtener las (global_inventory_transactions) que pertenecen al acuse de recibo de transacción de almacén
            */
            $globalInventoryTransactions = $warehouseTransactionAcknowledgment->global_inventory_transactions;
            if($globalInventoryTransactions->isEmpty()) {
                return response()->json([
                    'message' => 'Error, Global Inventory Transactions not found'
                ], 404);
            }

            $globalInventoryTransaction = $globalInventoryTransactions->first();
            $productDataFormat = [];
            $managerDataFormat = [];
            $detailDataFormat = [];
            $totalAmount = 0;

            if($globalInventoryTransaction->inventory_transaction_type_id == $inventoryTransactionType1->id) {

                foreach($globalInventoryTransactions as $item) {
                    if(isset($item->global_inventory)) {
                        $totalAmount += $item->stock_movement * $item->global_inventory->purchase_price;
                        $productKey = $item->global_inventory->warehouse_product_catalog->name . ' ' . 
                                    $item->global_inventory->warehouse_product_catalog->unit_measurement_quantity . ' ' . 
                                    ($item->global_inventory->clothing_size ? $item->global_inventory->clothing_size->name : 'N/A');
                
                        if(array_key_exists($productKey, $productDataFormat)){
                            $productDataFormat[$productKey]['quantity'] += $item->stock_movement;
                            $productDataFormat[$productKey]['total'] += $item->stock_movement * $item->global_inventory->purchase_price;
                        } else {
                            $productDataFormat[$productKey] = [
                                'name' => $item->global_inventory->warehouse_product_catalog->name,
                                'unit_measurement_quantity' => $item->global_inventory->warehouse_product_catalog->unit_measurement_quantity,
                                'unit_measurement_abbr' => $item->global_inventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                                'clothing_size' => $item->global_inventory->clothing_size->name ?? 'N/A',
                                'clothing_category' => $item->global_inventory->warehouse_product_catalog->clothing_category->name ?? 'N/A',
                                'price' => $item->global_inventory->purchase_price,
                                'quantity' => $item->stock_movement,
                                'total' => $item->global_inventory->purchase_price * $item->stock_movement,
                            ];
                        }
                    }
                }

                $managerDataFormat = [
                    'sender_name' => $warehouseTransactionAcknowledgment->warehouse_supplier->name . ' ' . '(proveedor)',
                    'sender_phone' => $warehouseTransactionAcknowledgment->warehouse_supplier->phone_number,
                    'receiver_name' => $warehouseTransactionAcknowledgment->user_manager->nombre . ' ' . '(comprador)',
                    'receiver_phone' => $warehouseTransactionAcknowledgment->user_manager->phone_number ?? 'no aplica',
                ];

                $detailDataFormat = [
                    'acknowledgment_key' => $warehouseTransactionAcknowledgment->acknowledgment_key,
                    'type' => 'compra de stock',
                    'total_amount' => '$' . $totalAmount,
                    'amount_received' => $warehouseTransactionAcknowledgment->global_card_cash_payment !== null && $warehouseTransactionAcknowledgment->global_card_cash_payment->amount_received !== null ? ('$' . $warehouseTransactionAcknowledgment->global_card_cash_payment->amount_received) : 'no aplica',
                    'amount_change_given' => $warehouseTransactionAcknowledgment->global_card_cash_payment !== null && $warehouseTransactionAcknowledgment->global_card_cash_payment->amount_change_given !== null ? ('$' . $warehouseTransactionAcknowledgment->global_card_cash_payment->amount_change_given) : 'no aplica',
                    'payment_type' => $warehouseTransactionAcknowledgment->global_payment_type->name ?? 'no aplica',
                    'status' => $warehouseTransactionAcknowledgment->is_active ? 'pendiente' : 'completado',
                    'warehouse' => $warehouseTransactionAcknowledgment->global_inventory_transactions->first()->global_inventory->pos_product_warehouse->name,
                    'created_at' => $warehouseTransactionAcknowledgment->created_at->format('Y-m-d H:i:s'),            
                ];
            } else if($globalInventoryTransaction->inventory_transaction_type_id == $inventoryTransactionType4->id){

                foreach($globalInventoryTransactions as $item) {
                    if(isset($item->global_inventory)) {
                        $productKey = $item->global_inventory->warehouse_product_catalog->name . ' ' . 
                                    $item->global_inventory->warehouse_product_catalog->unit_measurement_quantity . ' ' . 
                                    ($item->global_inventory->clothing_size ? $item->global_inventory->clothing_size->name : 'no aplica');

                        if(array_key_exists($productKey, $productDataFormat)){
                            $productDataFormat[$productKey]['quantity'] += $item->stock_movement;
                        } else {
                            $productDataFormat[$productKey] = [
                                'name' => $item->global_inventory->warehouse_product_catalog->name,
                                'unit_measurement_quantity' => $item->global_inventory->warehouse_product_catalog->unit_measurement_quantity,
                                'unit_measurement_abbr' => $item->global_inventory->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                                'clothing_size' => $item->global_inventory->clothing_size->name ?? 'no aplica',
                                'clothing_category' => $item->global_inventory->warehouse_product_catalog->clothing_category->name ?? 'no aplica',
                                'price' => $item->global_inventory->sale_price,
                                'quantity' => $item->stock_movement,
                            ];
                        }
                    }
                    
                }

                $managerDataFormat = [
                    'sender_name' => $warehouseTransactionAcknowledgment->user_manager->nombre . ' ' . '(manager)',
                    'sender_phone' => $warehouseTransactionAcknowledgment->global_inventory_transactions->first()->global_inventory->pos_product_warehouse->phone,
                    'receiver_name' => $warehouseTransactionAcknowledgment->global_inventory_transactions->first()->global_inventory->pos_product_warehouse->name . ' ' . '(punto de venta)',
                    'receiver_phone' => $warehouseTransactionAcknowledgment->global_inventory_transactions->first()->global_inventory->pos_product_warehouse->phone,
                ];

                $detailDataFormat = [
                    'acknowledgment_key' => $warehouseTransactionAcknowledgment->acknowledgment_key,
                    'type' => 'transferencia a tienda',
                    'status' => $warehouseTransactionAcknowledgment->is_active ? 'pendiente' : 'completado',
                    'warehouse' => $warehouseTransactionAcknowledgment->global_inventory_transactions->first()->global_inventory->pos_product_warehouse->name,
                    'created_at' => $warehouseTransactionAcknowledgment->created_at->format('Y-m-d H:i:s'),            
                ];
            }

            /* 
            * Cargar la vista de PDF
            */
            if($globalInventoryTransaction->inventory_transaction_type_id == $inventoryTransactionType1->id) {
                $pdf = PDF::loadView('pdfs.inventarios_globales.acuse_compra', [
                    'productDataFormat' => $productDataFormat,
                    'managerDataFormat' => $managerDataFormat,
                    'detailDataFormat' => $detailDataFormat
                ]);
            } else if ($globalInventoryTransaction->inventory_transaction_type_id == $inventoryTransactionType4->id){
                $pdf = PDF::loadView('pdfs.inventarios_globales.acuse_transferencia', [
                    'productDataFormat' => $productDataFormat,
                    'managerDataFormat' => $managerDataFormat,
                    'detailDataFormat' => $detailDataFormat
                ]);
            }

            /* 
            * Determinar si se envia el pdf por correo o se descarga
            */
            if($sendByEmail) {
                $pdf->save(public_path('pdfs/' . $warehouseTransactionAcknowledgment->acknowledgment_key . '.pdf'));
            } else {
                return $pdf->stream();
            }
        
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error, cannot download Global Inventory Movement Acknowledgment',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
