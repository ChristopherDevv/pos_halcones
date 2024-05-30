<?php

namespace App\Http\Controllers\api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale\GlobalCardCashPayment;
use App\Models\PointOfSale\GlobalPaymentType;
use App\Models\PointOfSale\GlobalTypeCardPayment;
use App\Models\Wallet\WalletAccount;
use App\Models\Wallet\WalletRechargeAmount;
use App\Models\Wallet\WalletTransaction;
use App\Models\Wallet\WalletTransactionStatus;
use App\Models\Wallet\WalletTransactionType;
use App\Policies\WalletAccountPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletTransactionController extends Controller
{
    /* 
    *
    * Handle wallet account recharges by Christoper Patiño
    *
    */
    public function handleWalletAccountRecharge(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'account_number_member' => 'required',
                'wallet_recharge_amount_id' => 'required|integer',
                'global_payment_type_id' => 'required|integer',
                'global_type_card_payment_id' => 'nullable|integer',
                'recharge_in_app' => 'required|boolean',
                'amount_received' => 'required|numeric',
            ]);

            /* 
            * Se verifica que la recarga este siendo realizada desde kiosko o app
            */
            $rules = [];
            $rechargeInApp = $request->recharge_in_app;
            if(!$rechargeInApp){
                $rules['account_number_seller'] = 'required';
            }
            $request->validate($rules);

            /* 
            * validacion de datos
            */
            $paymentType = GlobalPaymentType::find($request->global_payment_type_id);
            if (!$paymentType || $paymentType->name == 'halcones_wallet') {
                return response()->json([
                    'message' => 'Error, payment type not found or invalid',
                    'data' => $request->global_payment_type_id
                ], 400);
            }

            if($paymentType->name == 'tarjeta' && !$request->global_type_card_payment_id){
                return response()->json([
                    'message' => 'Error, global type card payment id is required',
                    'data' => $request->global_type_card_payment_id
                ], 400);
            }

            $globalTypeCardPaymentId = null;
            if($request->global_type_card_payment_id){
                $globalTypeCardPaymentId = $request->global_type_card_payment_id;
                if ($globalTypeCardPaymentId) {
                    $globalTypeCardPayment = GlobalTypeCardPayment::find($globalTypeCardPaymentId);
                    if (!$globalTypeCardPayment) {
                        return response()->json([
                            'message' => 'Error, global type card payment not found',
                            'data' => $request->global_type_card_payment_id
                        ], 400);
                    }
                }
            }

            $rechargeAmount = WalletRechargeAmount::find($request->wallet_recharge_amount_id);
            if (!$rechargeAmount) {
                return response()->json([
                    'message' => 'Error, recharge amount not found',
                    'data' => $request->wallet_recharge_amount_id
                ], 400);
            }

            $amountReceived = $request->amount_received;
            if($amountReceived < $rechargeAmount->amount){
                return response()->json([
                    'message' => 'Error, amount received is less than the recharge amount',
                    'data' => $amountReceived
                ], 400);
            }

            $accountNumberMember = WalletAccount::where('account_number', $request->account_number_member)->first();
            if (!$accountNumberMember) {
                return response()->json([
                    'message' => 'Error, member account not found',
                    'data' => $request->account_number_member
                ], 400);
            }

            /* 
            * Validar que una cuenta con el role de 'super_admin' no pueda ser recargada
            */
            $walletAccountPolicy = new WalletAccountPolicy();
            if ($walletAccountPolicy->handleSuperAdminTransaction(null, $accountNumberMember)) {
                return response()->json([
                    'message' => 'Error, super admin account cannot be recharged',
                    'data' => $request->account_number_member
                ], 400);
            }

           /* 
           * Validacion de cuenta de vendedor
           */
          $accountMemberBeforeNewRecharge = $accountNumberMember->current_balance;
          if(!$rechargeInApp){
                $accountNumberSeller = WalletAccount::where('account_number', $request->account_number_seller)->first();
                if (!$accountNumberSeller) {
                    return response()->json([
                        'message' => 'Error, seller account not found',
                        'data' => $request->account_number_seller
                    ], 400);
                }
                
                $newWalletTransaction = $this->handleWalletAccountRechargeInKiosk($amountReceived, $accountNumberSeller, $accountNumberMember, $paymentType, $rechargeAmount, $accountMemberBeforeNewRecharge, $globalTypeCardPaymentId);
                
            } else {

                $newWalletTransaction = $this->handleWalletAccountRechargeInApp($amountReceived, $accountNumberMember, $paymentType, $rechargeAmount, $accountMemberBeforeNewRecharge, $globalTypeCardPaymentId);
            
            }

            /* 
            * Enviar email de confirmacion de recarga
            */
            $email = $accountNumberMember->user->correo;
            //dispatch(new SendRechargedEmail($email, $accountNumberMember->user->nombre, $rechargeAmount->amount, $accountNumberMember->current_balance));

            DB::commit();

            return response()->json([
                'message' => 'Wallet account recharge completed successfully',
                'data' => [
                    'transaction' => $newWalletTransaction,
                    'recharge_amount' => $rechargeAmount->amount,
                    'amount_received' => $amountReceived,
                    'amount_change_given' => $amountReceived - $rechargeAmount->amount,
                ],
                'wallet_account' => [
                    'before_balance' => $accountMemberBeforeNewRecharge,
                    'after_balance' => $accountNumberMember->current_balance
                ],
                'seller_name' => $accountNumberSeller->user->nombre ?? 'vendedor en app'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, wallet account recharge failed',
                'error_data' => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Handle wallet account recharge in app by Christoper Patiño
    *
    */
    public function handleWalletAccountRechargeInApp($amountReceived, $accountMember, $paymentType, $rechargeAmount, $accountMemberBeforeNewRecharge, $globalTypeCardPaymentId = null)
    {
        try {

            DB::beginTransaction();
            /* 
            * Buscamos la cuenta epecial 'app_seller' para realizar la recarga (utilizamos las relaciones de roles de cuenta)
            */
            $appSellerAccount = WalletAccount::whereHas('wallet_account_roles', function ($query) {
                $query->where('name', 'app_seller');
            })->first();

            /* 
            * Recarga de cuenta de miembro
            */
            $accountMember->current_balance += $rechargeAmount->amount;
            $accountMember->save();
            
            /* 
            * Creacion de una nueva instancia de GlobalCardCashPayment
            */
            $newGlobalCardCashPayment = new GlobalCardCashPayment();
            $newGlobalCardCashPayment->global_type_card_payment_id = $globalTypeCardPaymentId ?? null;
            $newGlobalCardCashPayment->amount_received = $rechargeAmount->amount;
            $newGlobalCardCashPayment->amount_change_given = 0;
            $newGlobalCardCashPayment->save();

             /* 
            * Creacion de una nueva instancia de WalletTransaction
            */
            $newWalletTransaction = new WalletTransaction();
            $newWalletTransaction->origin_wallet_account_id = $appSellerAccount->id;
            $newWalletTransaction->destination_wallet_account_id = $accountMember->id;
            $newWalletTransaction->wallet_transaction_type_id = WalletTransactionType::where('name', 'recarga')->first()->id;
            $newWalletTransaction->wallet_transaction_status_id = WalletTransactionStatus::where('name', 'completada')->first()->id;
            $newWalletTransaction->global_payment_type_id = $paymentType->id;
            $newWalletTransaction->global_card_cash_payment_id = $newGlobalCardCashPayment->id ?? null;
            $newWalletTransaction->pos_sale_id = null;
            $newWalletTransaction->seller_wallet_account_id = $appSellerAccount->id;
            $newWalletTransaction->amount = $rechargeAmount->amount;
            $newWalletTransaction->description = 'Recarga de saldo en app';
            $newWalletTransaction->balance_account_before_transaction = $accountMemberBeforeNewRecharge;
            $newWalletTransaction->balance_account_after_transaction = $accountMember->current_balance;
            $newWalletTransaction->save();

            DB::commit();

            return $newWalletTransaction;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    /* 
    *
    * Handle wallet account recharge in kiokso by Christoper Patiño
    *
    */
    public function handleWalletAccountRechargeInKiosk($amountReceived, $accountSeller, $accountMember, $paymentType, $rechargeAmount, $accountMemberBeforeNewRecharge, $globalTypeCardPaymentId = null)
    {
        try {
    
            DB::beginTransaction();
            /* 
            * Validacion de cuenta de vendedor
            */
            $policy = new WalletAccountPolicy();
            if (!$policy->handleSellerTransaction(null, $accountSeller, 'recarga')) {
                throw new \Exception('Seller do not have permission to perform this action');
            }


            /* 
            * Recarga de cuenta de miembro
            */
            $accountMember->current_balance += $rechargeAmount->amount;
            $accountMember->save();

            /* 
            * Creacion de una nueva instancia de GlobalCardCashPayment
            */
            $newGlobalCardCashPayment = new GlobalCardCashPayment();
            $newGlobalCardCashPayment->global_type_card_payment_id = $globalTypeCardPaymentId ?? null;
            $newGlobalCardCashPayment->amount_received = $amountReceived;
            $newGlobalCardCashPayment->amount_change_given = $amountReceived - $rechargeAmount->amount;
            $newGlobalCardCashPayment->save();
            
            /* 
            * Creacion de una nueva instancia de WalletTransaction
            */
            $newWalletTransaction = new WalletTransaction();
            $newWalletTransaction->origin_wallet_account_id = $accountSeller->id;
            $newWalletTransaction->destination_wallet_account_id = $accountMember->id;
            $newWalletTransaction->wallet_transaction_type_id = WalletTransactionType::where('name', 'recarga')->first()->id;
            $newWalletTransaction->wallet_transaction_status_id = WalletTransactionStatus::where('name', 'completada')->first()->id;
            $newWalletTransaction->global_payment_type_id = $paymentType->id;
            $newWalletTransaction->global_card_cash_payment_id = $newGlobalCardCashPayment->id ?? null;
            $newWalletTransaction->pos_sale_id = null;
            $newWalletTransaction->seller_wallet_account_id = $accountSeller->id;
            $newWalletTransaction->amount = $rechargeAmount->amount;
            $newWalletTransaction->description = 'Recarga de saldo en kiosko';
            $newWalletTransaction->balance_account_before_transaction = $accountMemberBeforeNewRecharge;
            $newWalletTransaction->balance_account_after_transaction = $accountMember->current_balance;
            $newWalletTransaction->save();

            DB::commit();

            return $newWalletTransaction;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error, wallet account recharge failed');
        }
    }
       
}
