<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\PosCashRegisterMovement;
use App\Models\PointOfSale\PosMovementType;
use App\Models\PointOfSale\PosProductCancelation;
use App\Models\PointOfSale\PosTicket;
use App\Models\PointOfSale\PosTicketCancelation;
use App\Models\PointOfSale\PosTicketStatus;
use App\Models\User;
use App\Models\Wallet\WalletTransaction;
use App\Models\Wallet\WalletTransactionStatus;
use App\Models\Wallet\WalletTransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosTicketCancelationController extends Controller
{
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
    * 
    * Cancellation of a pos sale (ticket) by Christoper Patiño
    *
    */
    public function posCancelTicket(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'pos_ticket_id' => 'required|integer',
                'user_cashier_id' => 'required|integer',
            ]);

            /* 
            * Validacion de existencia de los datos
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

            /* 
            * Validar que el status del ticket sea diferente a cancelado
            */
            if($posTicket->pos_ticket_status->name == 'cancelado'){
                return response()->json([
                    'message' => 'Error, the ticket is already canceled.',
                    'data' => $posTicket
                ], 400);
            }

            /* 
            * Obtenemos los productos de la venta del ticket
            */
            $warehouseProductInventories = $posTicket->pos_sale->warehouse_product_inventories;
            $previousTotalAmountInCashRegister = $posTicket->pos_cash_register->current_balance;
            $sale = $posTicket->pos_sale;
            $changeGiven = $sale->total_amount;
            $originalTotalAmount = $sale->total_amount;

            /* 
            * Cancelamos los productos de la venta
            */
            foreach($warehouseProductInventories as $warehouseProductInventory){
                /* 
                * Actualizamos el stock del producto en el almacen de productos
                */
                $warehouseProductInventory->stock += $warehouseProductInventory->pivot->quantity;
                $warehouseProductInventory->save();

                /* 
                * reducimos la cantidad de productos vendidos en la venta
                */
                $sale->warehouse_product_inventories()->updateExistingPivot($warehouseProductInventory->id, ['quantity' => 0]);

                /* 
                * Actualizamos el total de la venta y el total del ticket
                */
                $sale->total_amount = 0;
                $posTicket->total_amount = 0;
            }

            /* 
            * Actualizamos el status del ticket
            */
            $statusCancelled = PosTicketStatus::where('name', 'cancelado')->first();
            $posTicket->pos_ticket_status_id = $statusCancelled->id;
            $posTicket->save();

            /* 
            * Validar si una venta por cobrar
            */
            if($sale->pos_sales_receivable){
                $posSalesReceivable = $sale->pos_sales_receivable;
                $posSalesReceivable->is_paid = false;
                $posSalesReceivable->is_canceled = true;
                $posSalesReceivable->save();
            }

            /* 
            * Validar si fue una venta de combos
             */
            if($sale->is_combo_sale){
                $comboSales = $sale->combo_sales;
                foreach($comboSales as $comboSale){
                    $comboSale->is_canceled = true;
                    $comboSale->save();
                }
            }

            /* 
            * Creamos una isntacion de pos ticket cancelation
            */
            $posTicketCancelation = new PosTicketCancelation();
            $posTicketCancelation->user_cashier_id = $request->user_cashier_id;
            $posTicketCancelation->pos_ticket_id = $request->pos_ticket_id;
            $posTicketCancelation->total_amount = $originalTotalAmount;
            $posTicketCancelation->reason = 'Cancelación de ticket de venta';
            $posTicketCancelation->save();

            /* 
            * Creamos un movimiento en la caja registradora
            */
            $posCashRegisterMovement = new PosCashRegisterMovement();
            $posCashRegisterMovement->pos_cash_register_id = $posTicket->pos_cash_register_id;
            $posCashRegisterMovement->pos_movement_type_id = PosMovementType::where('name', 'cancelacion_ticket')->first()->id;
            $posCashRegisterMovement->pos_ticket_id = $request->pos_ticket_id;
            $posCashRegisterMovement->pos_ticket_cancelation_id = $posTicketCancelation->id;
            $posCashRegisterMovement->previous_balance = $previousTotalAmountInCashRegister;
            $posCashRegisterMovement->movement_amount = $changeGiven;
            $posCashRegisterMovement->new_balance = $previousTotalAmountInCashRegister - $changeGiven;
            $posCashRegisterMovement->reason = 'Cancelación de ticket de venta';
            $posCashRegisterMovement->save();

            /* 
            * Actualizamos el total de la caja registradora
            */
            $posTicket->pos_cash_register->current_balance -= $changeGiven;
            $posTicket->pos_cash_register->save();

            DB::commit();

            return response()->json([
                'message' => 'Success, ticket canceled.',
                'data' => [
                    'change_given' => $changeGiven,
                    'pos_cash_register_movement' => $posCashRegisterMovement
                ]
            ], 200);

        } catch( \Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error to cancel ticket',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
