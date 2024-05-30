<?php

namespace App\Http\Controllers\api\PointOfSale;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\BucketVendorProduct;
use App\Models\PointOfSale\ComboSale;
use App\Models\PointOfSale\GlobalCardCashPayment;
use App\Models\PointOfSale\GlobalCombo;
use App\Models\PointOfSale\GlobalPaymentType;
use App\Models\PointOfSale\GlobalTypeCardPayment;
use App\Models\PointOfSale\PosCashRegister;
use App\Models\PointOfSale\PosCashRegisterMovement;
use App\Models\PointOfSale\PosMovementType;
use App\Models\PointOfSale\PosProductWarehouse;
use App\Models\PointOfSale\PosSale;
use App\Models\PointOfSale\PosSalesReceivable;
use App\Models\PointOfSale\PosTicket;
use App\Models\PointOfSale\PosTicketStatus;
use App\Models\PointOfSale\ProductsForBucketvendor;
use App\Models\PointOfSale\WarehouseProductCatalog;
use App\Models\PointOfSale\WarehouseProductInventory;
use App\Models\User;
use App\Models\Wallet\SuperAdminWalletTransaction;
use App\Models\Wallet\WalletAccount;
use App\Models\Wallet\WalletTransaction;
use App\Models\Wallet\WalletTransactionStatus;
use App\Models\Wallet\WalletTransactionType;
use App\Policies\WalletAccountPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosTicketController extends Controller
{
    /* 
    *
    *  Create a new sale ticket by Christoper Patiño
    *
    */
    public function storePosSaleTicket(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'sale_in_pos' => 'required|boolean', // true si la venta se realiza en punto de venta, false si la venta se realiza en la app de cubetero
                'user_cashier_id' => 'nullable|integer', // id del usuario que realiza la venta es en punto de venta
                'bucket_vendor_product_id' => 'nullable|integer', // id del usuario si la venta la realiza un cubetero
                'account_number_member' => 'nullable|string',
                'global_payment_type_id' => 'required|integer',
                'global_type_card_payment_id' => 'nullable|integer',
                'pos_product_warehouse_id' => 'required|integer',
                'pos_cash_register_id' => 'required|integer',
                'warehouse_product_inventories' => 'required|array',
                'amount_received' => 'required|numeric',
                'is_combo_sale' => 'required|boolean',
                'global_combo_id' => 'nullable|integer',
                'combos_quantity' => 'nullable|integer',
                'debtor_name' => 'nullable|string|max:255',
                'debtor_last_name' => 'nullable|string|max:255',
                'debtor_phone' => 'nullable|string|max:255',
                'advance_payment' => 'nullable|numeric',
                'is_for_bucketvendor' => 'required|boolean',
                'products_for_bucketvendor_id' => 'nullable|integer',
            ]);


            /* 
            * Validacion de datos 
            */
            
            /* 
            * Encontrar el tipo de pago global y validar si es tarjeta
            */
            $globalPaymentType = GlobalPaymentType::find($request->global_payment_type_id);
            if (!$globalPaymentType) {
                return response()->json([
                    'message' => 'Error, the global payment type does not exist'
                ], 404);
            }
            if($globalPaymentType->name == 'tarjeta'){
                if(!$request->global_type_card_payment_id){
                    return response()->json([
                        'message' => 'Error, the card payment type is required'
                    ], 403);
                }
            }

            if($request->global_type_card_payment_id){
                $globalTypeCardPayment = GlobalTypeCardPayment::find($request->global_type_card_payment_id);
                if (!$globalTypeCardPayment) {
                    return response()->json([
                        'message' => 'Error, the global type card payment does not exist'
                    ], 404);
                }
            }

            if($request->is_for_bucketvendor){
                if($globalPaymentType->name != 'tarjeta' && $globalPaymentType->name != 'efectivo'){
                    return response()->json([
                        'message' => 'Error, el tipo de pago no es valido para la venta de productos de cubetero'
                    ], 403);
                }
                
                if($request->products_for_bucketvendor_id){
                    $productsForBucketvendor = ProductsForBucketvendor::find($request->products_for_bucketvendor_id);
                    if(!$productsForBucketvendor){
                        return response()->json([
                            'message' => 'Error, the bucket vendor does not exist'
                        ], 404);
                    }
                }else {
                    if(!$request->bucketvendor_name || !$request->bucketvendor_last_name || !$request->bucketvendor_phone){
                        return response()->json([
                            'message' => 'Error, es necesario el nombre, apellido y telefono del cubetero'
                        ], 403);
                    }else {
                        $productsForBucketvendor = new ProductsForBucketvendor();
                        $productsForBucketvendor->bucketvendor_name = $request->bucketvendor_name;
                        $productsForBucketvendor->bucketvendor_last_name = $request->bucketvendor_last_name;
                        $productsForBucketvendor->bucketvendor_phone = $request->bucketvendor_phone;
                        $productsForBucketvendor->is_active = true;
                        $productsForBucketvendor->save();
                    }
                }

            }

            /* 
            * Manejar el monto recibido dependiendo del tipo de pago
            */
            if($globalPaymentType->name == 'cortesia' || $globalPaymentType->name == 'por_cobrar'){
                if($request->is_combo_sale) {
                    return response()->json([
                        'message' => 'Error, por el momento esta transacción no se permite para combos'
                    ], 403);
                }
               $request->merge([
                'amount_received' => '0.00'
               ]);
            } 

            /* 
            * Validar que el 'vendedor' exista y tenga el role de 'seller' 
            */
           if($request->sale_in_pos){
                $userCashier = User::find($request->user_cashier_id);
                if (!$userCashier) {
                    return response()->json([
                        'message' => 'Error, the cashier user does not exist'
                    ], 404);
                }
                
                $policy = new WalletAccountPolicy();
                $sellerWalletAccount = $userCashier->wallet_account;
                /* if (!$policy->handleSellerTransaction(null, $sellerWalletAccount, 'venta')) {
                    return response()->json([
                        'message' => 'Error, the cashier user does not have the role of seller'
                    ], 403);
                } */

            }

            /* 
            * Validar el id del combo global si la venta es por combo
            */
            if($request->is_combo_sale){
                if(!$request->global_combo_id || !$request->combos_quantity){ 
                    return response()->json([
                        'message' => 'Error, the global combo id and the quantity of combos are required'
                    ], 403);
                }
            }

            $posCashRegister = PosCashRegister::find($request->pos_cash_register_id);
            if (!$posCashRegister) {
                return response()->json([
                    'message' => 'Error, the cash register does not exist'
                ], 404);
            }

            /* 
            * Validar que la caja este abierta
            */
            if(!$posCashRegister->is_open){
                return response()->json([
                    'message' => 'Error, the cash register is closed'
                ], 403);
            }

            /* 
            * Validar que el almacen de productos exista
            */
            $posProductWarehouse = PosProductWarehouse::find($request->pos_product_warehouse_id);
            if (!$posProductWarehouse) {
                return response()->json([
                    'message' => 'Error, the product warehouse does not exist'
                ], 404);
            }

            /* 
            * Validar que la caja registradora pertenezca al almacen de productos
            */
            if ($posCashRegister->pos_product_warehouse_id != $request->pos_product_warehouse_id) {
                return response()->json([
                    'message' => 'Error, the cash register does not belong to the product warehouse'
                ], 403);
            }

            /* 
            * Validar que los productos existan en el almacen y pertenezcan al almacen
            */
            $warehouseProductInventories = $request->warehouse_product_inventories;
            foreach ($warehouseProductInventories as $warehouseProductInventory) {
                $warehouseProductInventory = WarehouseProductInventory::find($warehouseProductInventory['id']);
                if (!$warehouseProductInventory) {
                    return response()->json([
                        'message' => 'Error, the product does not exist in the warehouse'
                    ], 404);
                }
                if ($warehouseProductInventory->pos_product_warehouse_id != $request->pos_product_warehouse_id) {
                    return response()->json([
                        'message' => 'Error, the product does not belong to the warehouse',
                        'product_name' => $warehouseProductInventory->warehouse_product_catalog->name
                    ], 403);
                }
            }

            /* 
            * Validar 'bucket_vendor_product_id' si la venta se realiza en la app de cubetero
            */
            if(!$request->sale_in_pos){
                $bucketVendorProduct = BucketVendorProduct::find($request->bucket_vendor_product_id);
                if(!$bucketVendorProduct){
                    return response()->json([
                        'message' => 'Error, the bucket vendor product does not exist'
                    ], 404);
                }

                /* 
                * Validar que el 'vendedor' tenga el role de 'seller' 
                */
                $bucketSellerWalletAccount = $bucketVendorProduct->user_bucket_vendor->wallet_account();
                /* if (!$policy->handleSellerTransaction(null, $bucketSellerWalletAccount, 'venta')) {
                    return response()->json([
                        'message' => 'Error, the bucket vendor user does not have the role of seller'
                    ], 403);
                } */
            }

            /* 
            * Identificar si la venta es por combo y crear una instaciona de ComboSale
            */
            if($request->is_combo_sale){

                $globalCombo = GlobalCombo::find($request->global_combo_id);
                if(!$globalCombo){
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Error, the global combo does not exist'
                    ], 404);
                }

                /* 
                * Verificamos que la cantidad de productos recibidos sea igual a la cantidad de productos 
                * permitidos en el combo multiplicacdo por la cantidad de combos a vender
                */
                $totalProductsInProductInventories = array_reduce($warehouseProductInventories, function($map, $item) {
                    return $map + $item['quantity'];
                }, 0);
                if($totalProductsInProductInventories != ($globalCombo->permitted_products * $request->combos_quantity)){
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Error, the number of products to sell does not match the number of products in the combo'
                    ], 403);
                }

                /* 
                * Indentificar si ya exite una instacia de ComboSale creada para el combo en el dia
                */
                /* $comboSale = ComboSale::where('global_combo_id', $globalCombo->id)
                                        ->whereDate('created_at', now()->toDateString())
                                        ->where('pos_product_warehouse_id', $posProductWarehouse->id)
                                        ->where('pos_cash_register_id', $request->pos_cash_register_id)
                                        ->first(); */
                /* if($comboSale){
                    $comboSale->sale_count += $request->combos_quantity;
                    $comboSale->save();
                } else {
                    $newComboSale = new ComboSale();
                    $newComboSale->global_combo_id = $globalCombo->id;
                    $newComboSale->pos_product_warehouse_id = $posProductWarehouse->id;
                    $newComboSale->pos_cash_register_id = $request->pos_cash_register_id;
                    $newComboSale->sale_count = $request->combos_quantity;
                    $newComboSale->save();
                } */
            }

            /* 
            * Calcular el total de la compra y actualizar el stock de los productos en el almacen
            */
            $totalSales = 0;
            $totalSalesPorCobrar = 0;
            foreach($warehouseProductInventories as $productData) {
                $product = WarehouseProductInventory::find($productData['id']);
                
                if($product){
                    /* 
                    * Comprobar el tipo de pago y asignar monto total de la compra
                    */
                    if($request->is_for_bucketvendor) {
                        $totalSales += $product->discount_sale_price * $productData['quantity']; 
                    } else if($globalPaymentType->name == 'cortesia' || $globalPaymentType->name == 'por_cobrar'){
                        $totalSales = 0;
                        if($request->is_combo_sale){
                            $totalSalesPorCobrar = $globalCombo->sale_price * $request->combos_quantity;
                        } else {
                            $totalSalesPorCobrar += $product->sale_price * $productData['quantity'];
                        }
                    }else if($request->is_combo_sale){
                        $totalSales = $globalCombo->sale_price * $request->combos_quantity;
                    } else {
                        $totalSales += $product->sale_price * $productData['quantity'];
                    }

                    /* 
                    * Verificar que la cantidad de productos a vender sea menor o igual a la cantidad en el almacen
                    */
                    if($product->stock < $productData['quantity']){
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Error, the quantity of products to sell is greater than the quantity in the warehouse',
                            'product_name' => $product->warehouse_product_catalog->name,
                            'unit_measurement_abbreviation' => $product->warehouse_product_catalog->pos_unit_measurement->abbreviation,
                            'unit_measurement_quantity' => $product->warehouse_product_catalog->unit_measurement_quantity,
                            'stock' => $product->stock
                        ], 403);
                    }

                    /* 
                    * Actualizamos el stock del producto en el almacen
                    */
                    $product->stock -= $productData['quantity'];
                    $product->save();
                }
            }

            /* 
            * Validar que el tipo de pago sea 'halcones_wallet' y asignar el monto recibido
            */
            if($globalPaymentType->name == 'halcones_wallet'){
                $amountReceivedWalletAccount = $totalSales;
            }

            /* 
            * identificar si el tipo de pago es con 'halcones_wallet' y realizar las validaciones correspondientes
            */
            if($globalPaymentType->name == 'halcones_wallet'){

                $walletAccountMember = WalletAccount::where('account_number', $request->account_number_member)->first();
                if(!$walletAccountMember){
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Error, the member account does not exist'
                    ], 404);
                }

                /* 
                * Validar que una cuenta con el roles de 'super_admin' no pueda comprar productos
                */
                /* if ($policy->handleSuperAdminTransaction(null, $walletAccountMember)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Error, the member account has the role of super_admin'
                    ], 403);
                } */

                /* 
                * Validar que el monto de la billetera del usuario 'member' sea mayor o igual al total de la compra
                */
                if($walletAccountMember->current_balance < $totalSales){
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Error, the member account balance is less than the total purchase',
                        'balance' => $walletAccountMember->balance
                    ], 403);
                }
            } else {
                /* 
                * Validar que el monto recibido sea mayor o igual al total de la compra 
                * solo si el tipo de pago es distinto a 'cortesia' y 'por_cobrar'
                */
                if($globalPaymentType->name != 'cortesia' && $globalPaymentType->name != 'por_cobrar') {
                    if($request->amount_received < $totalSales){
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Error, the amount received is less than the total purchase',
                            'total_sales' => $totalSales
                        ], 403);
                    }
                }
            }

            /* 
            * Indentificar si la venta es 'por_cobrar' y crear una instacia de 'PosSalesReceivable'
            */
            if($globalPaymentType->name == 'por_cobrar'){
                if(!$request->debtor_name || !$request->debtor_last_name){
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Error, the debtor name and last name are required'
                    ], 403);
                }

                $debtorName = strtolower($request->debtor_name);
                $debtorLastName = strtolower($request->debtor_last_name);

                $posSalesReceivable = new PosSalesReceivable();
                $posSalesReceivable->wallet_account_id = $walletAccountMember->id ?? null;
                $posSalesReceivable->debtor_name = $debtorName;
                $posSalesReceivable->debtor_last_name = $debtorLastName;
                $posSalesReceivable->debtor_phone = $request->debtor_phone ?? null;
                $posSalesReceivable->amount_paid = '0.00';
                $posSalesReceivable->is_paid = false;
                $posSalesReceivable->save();
            }

            /* 
            * Crear la venta en la tabla 'pos_sales' y el registro en la tabla pivot 'pos_sale_product_inventory'
            */
            $posSale = new PosSale();
            $posSale->wallet_account_id = $walletAccountMember->id ?? null;
            $posSale->global_combo_id = $globalCombo->id ?? null;
            $posSale->pos_sales_receivable_id = $posSalesReceivable->id ?? null;
            $posSale->products_for_bucketvendor_id = $productsForBucketvendor->id ?? null;
            $posSale->is_bucketvendor_sale = $request->is_for_bucketvendor;
            $posSale->total_amount = $totalSales; 
            $posSale->total_amount_payable = $globalPaymentType->name == 'por_cobrar' ? $totalSalesPorCobrar : null;
            $posSale->is_combo_sale = $request->is_combo_sale ?? false;
            $posSale->combos_quantity = $request->combos_quantity ?? null;
            $posSale->paid_with_courtesy = $globalPaymentType->name == 'cortesia' ? true : false;
            $posSale->save();

            foreach($warehouseProductInventories as $productData) {
                $product = WarehouseProductInventory::find($productData['id']);
                $posSale->warehouse_product_inventories()->attach($product->id, [
                    'quantity' => $productData['quantity'],
                    'quantity_if_removed_product' => $productData['quantity'],
                    'original_quantity' => $productData['quantity']
                ]);
            }

            if($request->is_combo_sale){
                $newComboSale = new ComboSale();
                $newComboSale->global_combo_id = $globalCombo->id;
                $newComboSale->pos_product_warehouse_id = $posProductWarehouse->id;
                $newComboSale->pos_cash_register_id = $request->pos_cash_register_id;
                $newComboSale->pos_sale_id = $posSale->id;
                $newComboSale->sale_count = $request->combos_quantity;
                $newComboSale->save();
            }

            /* 
            * Creamos la transaccion en la tabla 'wallet_transactions' si es que el tipo de pago es 'halcones_wallet'
            */
            if($globalPaymentType->name == 'halcones_wallet'){
                $balanceAccountBeforeTransaction = $walletAccountMember->current_balance;
                $balanceAccountAfterTransaction = $balanceAccountBeforeTransaction - $totalSales;

                /* 
                * Actualizamos el saldo de la cuenta del usuario 'member'
                */
                $walletAccountMember->current_balance = $balanceAccountAfterTransaction;
                $walletAccountMember->save();

                /* 
                * La cuenta de destino es la cuenta de la empresa (en donde el role deber ser 'super_admin')
                */
                $destinationWalletAccount = WalletAccount::whereHas('wallet_account_roles', function($query){
                    $query->where('name', 'super_admin');
                })->first();

                $walletTransaction = new WalletTransaction();
                $walletTransaction->origin_wallet_account_id = $walletAccountMember->id;
                $walletTransaction->destination_wallet_account_id = $destinationWalletAccount->id;
                $walletTransaction->wallet_transaction_type_id = WalletTransactionType::where('name', 'compra')->first()->id;
                $walletTransaction->wallet_transaction_status_id = WalletTransactionStatus::where('name', 'completada')->first()->id;
                $walletTransaction->global_payment_type_id = $globalPaymentType->id;
                $walletTransaction->global_card_cash_payment_id = null;
                $walletTransaction->pos_sale_id = $posSale->id;
                $walletTransaction->seller_wallet_account_id = $sellerWalletAccount->id;
                $walletTransaction->amount = $totalSales;
                $walletTransaction->description = 'Compra de productos';
                $walletTransaction->balance_account_before_transaction = $balanceAccountBeforeTransaction;
                $walletTransaction->balance_account_after_transaction = $balanceAccountAfterTransaction;
                $walletTransaction->save();

                /* 
                * Creamos un registro en la tabla 'super_admin_wallet_transactions'
                */
                $balanceAccountBeforeTransactionAdmin = $destinationWalletAccount->current_balance;
                $balanceAccountAfterTransactionAdmin = $balanceAccountBeforeTransactionAdmin + $totalSales;
                $destinationWalletAccount->current_balance = $balanceAccountAfterTransactionAdmin;
                $destinationWalletAccount->save();

            }

            /* 
            * Indetificamos si el tipo pago es en efectivo o targeta y crear una instacia en la tabla 'global_card_cash_payments
            */
            if($globalPaymentType->name == 'efectivo' || $globalPaymentType->name == 'tarjeta' || $globalPaymentType->name == 'halcones_wallet' || $globalPaymentType->name == 'cortesia' || $globalPaymentType->name == 'por_cobrar'){
                $newGlobalCardCashPayment = new GlobalCardCashPayment();
                $newGlobalCardCashPayment->global_type_card_payment_id = $globalTypeCardPayment->id ?? null; 
                $newGlobalCardCashPayment->amount_received = $globalPaymentType->name == 'halcones_wallet' ? $amountReceivedWalletAccount : $request->amount_received;
                $newGlobalCardCashPayment->amount_change_given = $globalPaymentType->name == 'halcones_wallet' ? $amountReceivedWalletAccount - $totalSales : $request->amount_received - $totalSales;
                $newGlobalCardCashPayment->save();
            }
                
            /* 
            * Creamos el ticket de venta en la tabla 'pos_tickets'
            */
            $posTicket = new PosTicket();
            $posTicket->user_cashier_id = $request->user_cashier_id ?? null;
            $posTicket->pos_cash_register_id = $request->pos_cash_register_id;
            $posTicket->global_payment_type_id = $request->global_payment_type_id;
            $posTicket->global_card_cash_payment_id = $newGlobalCardCashPayment->id ?? null;
            $posTicket->pos_ticket_status_id = PosTicketStatus::where('name', 'pagado')->first()->id;
            $posTicket->pos_sale_id = $posSale->id;
            $posTicket->bucket_vendor_product_id = $request->bucket_vendor_product_id ?? null;
            $posTicket->total_amount = $totalSales;

            /* 
            * Calculamos el folio de la venta para esta caja
            */
            $lastPosTicket = PosTicket::where('pos_cash_register_id', $request->pos_cash_register_id)->orderBy('created_at', 'desc')->first();
            $nextFolio = $lastPosTicket ? $lastPosTicket->sale_folio + 1 : 1;

            $posTicket->sale_folio = $nextFolio;
            $posTicket->save();

            /* 
            * Creamos el movimiento de caja en la tabla 'pos_cash_register_movements'
            */
            $posCashRegisterMovement = new PosCashRegisterMovement();
            $posCashRegisterMovement->pos_cash_register_id = $request->pos_cash_register_id;
            $posCashRegisterMovement->pos_movement_type_id = PosMovementType::where('name', 'venta')->first()->id;
            $posCashRegisterMovement->pos_ticket_id = $posTicket->id;
            $posCashRegisterMovement->pos_ticket_cancelation_id = null;
            $posCashRegisterMovement->previous_balance = $posCashRegister->current_balance;
            $posCashRegisterMovement->movement_amount = $totalSales;
            $posCashRegisterMovement->new_balance = $posCashRegister->current_balance + $totalSales;
            $posCashRegisterMovement->reason = 'Venta de productos';
            $posCashRegisterMovement->save();

            /* 
            * Actualizamos el saldo de la caja
            */
            $posCashRegister->current_balance += $totalSales;
            $posCashRegister->save();

            /* 
            * Creamos un registro en la tabla 'super_admin_wallet_transactions' si el tipo de pago es 'halcones_wallet'
            */
            if($globalPaymentType == 'halcones_wallet'){
                $superAdminWalletTransaction = new SuperAdminWalletTransaction();
                $superAdminWalletTransaction->super_admin_wallet_account_id = $destinationWalletAccount->id;
                $superAdminWalletTransaction->pos_product_warehouse_id = $posProductWarehouse->id;
                $superAdminWalletTransaction->wallet_transaction_id = $walletTransaction->id;
                $superAdminWalletTransaction->description = 'Venta de productos';
                $superAdminWalletTransaction->amount = $totalSales;
                $superAdminWalletTransaction->balance_account_before_transaction = $balanceAccountBeforeTransactionAdmin;
                $superAdminWalletTransaction->balance_account_after_transaction = $balanceAccountAfterTransactionAdmin;
                $superAdminWalletTransaction->save();
            }
            
            /* 
            * Formateo de la respuesta para generar el ticket de venta
            */

            /* 
            * Datos de estadio
            */
            $stadiumName = $posCashRegister->stadium_location->name;
            $stadiumAddress = $posCashRegister->stadium_location->address;
            $stadiumCity = $posCashRegister->stadium_location->city;
            $stadiumZipCode = $posCashRegister->stadium_location->zip_code;

            /* 
            * Datos de la caja
            */
            $posCasgRegisterTypeName = $posCashRegister->pos_cash_register_type->name;
            if($request->sale_in_pos){
                $cashierName = $userCashier->nombre;
            }else {
                $cashierName = $bucketVendorProduct->user_bucket_vendor->nombre;
            }
            $movementTypeName = $posCashRegisterMovement->pos_movement_type->name;
            $movementReason = $posCashRegisterMovement->reason;
            $globalTypePaymentName = $globalPaymentType->name;

            /* 
            * Productos vendidos
            */
            $products = [];
            foreach($warehouseProductInventories as $productData) {
                $product = WarehouseProductInventory::find($productData['id']);
                $productCatalog = $product->warehouse_product_catalog;
                $products[] = [
                    'name' => $productCatalog->name,
                    'price' => $request->is_combo_sale ? 'N/A' : ($request->is_for_bucketvendor ? $product->discount_sale_price : $product->sale_price),
                    'quantity' => $productData['quantity'],
                    'unit_measurement_name' => $productCatalog->pos_unit_measurement->name,
                    'unit_measurement_abbreviation' => $productCatalog->pos_unit_measurement->abbreviation,
                    'unit_measurement_quantity' => $productCatalog->unit_measurement_quantity,
                    'total' => $request->is_combo_sale ? 'N/A' : (($request->is_for_bucketvendor ? $product->discount_sale_price : $product->sale_price) * $productData['quantity'])
                ];
            }

           DB::commit();

            return response()->json([
                'message' => 'The sale ticket has been created successfully',
                'ticket' => [
                    'stadium' => [
                        'name' => $stadiumName,
                        'address' => $stadiumAddress,
                        'city' => $stadiumCity,
                        'zip_code' => $stadiumZipCode
                    ],
                    'pos_cash_register' => [
                        'type' => $posCasgRegisterTypeName,
                        'cashier' => $cashierName
                    ],
                    'cash_in_account'=> [
                        'balance_before_transaction' => $balanceAccountBeforeTransaction ?? 'no aplica',
                        'balance_after_transaction' => $balanceAccountAfterTransaction ?? 'no aplica'
                    ],
                    'movement' => [
                        'type' => $movementTypeName,
                        'reason' => $movementReason
                    ],
                    'sale_folio' => $posTicket->sale_folio,
                    'global_payment_type' => $globalTypePaymentName,
                    'global_payment_type_datail' => $globalTypeCardPayment->name ?? null,
                    'products' => $products,
                    'is_combo_sale' => [
                        'is_combo_sale' => $request->is_combo_sale,
                        'combo_name' => $globalCombo->name ?? null,
                        'price_for_combo' => $globalCombo->sale_price ?? null,
                        'combos_quantity' => $request->combos_quantity ?? null,
                        'total_sales' => $request->is_combo_sale ? $globalCombo->sale_price * $request->combos_quantity : 'N/A'
                    ],
                    'total_sales' => $totalSales,
                    'amount_received' => $globalPaymentType->name == 'halcones_wallet' ? $amountReceivedWalletAccount : $request->amount_received,
                    'amount_change_given' => $globalPaymentType->name == 'halcones_wallet' ? $amountReceivedWalletAccount - $totalSales : $request->amount_received - $totalSales,
                    'sale_date' => $posTicket->created_at->format('Y-m-d H:i:s')
                ]

            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, the sale ticket could not be created',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }
}
