<?php

namespace App\Http\Controllers\api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet\WalletAccount;
use App\Models\Wallet\WalletAccountRole;
use App\Models\Wallet\WalletCurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WalletAccountController extends Controller
{
    /* 
    *
    * Get history of wallet account by Christoper Patiño
    *
    */
    public function showHistoryWalletAccount(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'nullable|numeric',
                'wallet_account' => 'nullable|max:255'
            ]);

            /* 
            * Validacion de datos
            */
            if ($request->user_id) {
                $walletAccount = WalletAccount::where('user_id', $request->user_id)->first();
                if (!$walletAccount) {
                    return response()->json([
                        'message' => "Error, wallet account not found"
                    ], 404);
                }
            } else {
                $walletAccount = WalletAccount::where('account_number', $request->wallet_account)->first();
                if (!$walletAccount) {
                    return response()->json([
                        'message' => "Error, wallet account not found"
                    ], 404);
                }
            }

            /* 
            * Obtener las transaciones cuando el usuario sea 'origin account' y 'destination account' y ordenarlas por fecha de creacion
            */
            $walletAccountIsSuperAdmin = $walletAccount->wallet_account_roles->contains('name', 'super_admin');
            $transactionsOriginAccount = $walletAccount->wallet_transactions_origin()->orderBy('created_at', 'desc')->get();
            $transactionDestinationAccount = $walletAccount->wallet_transactions_destination()->orderBy('created_at', 'desc')->get();
            $concatAllTransactions = $transactionsOriginAccount->concat($transactionDestinationAccount);
            $sordAllTransaction = $concatAllTransactions->sortByDesc('created_at');

            /* 
            * Decimos el formato a devolver dependiendo si la cuenta tiene el rol de 'super_admin'
            */
            $formattedAccountTransactions = $walletAccountIsSuperAdmin 
            ? ''
            : $this->formatMemberAccountTransactions($sordAllTransaction, $walletAccount);

            $formattedWallerAccount = [
                'user_id' => $walletAccount->user_id,
                'user_name' => $walletAccount->user->nombre,
                'last_name' => $walletAccount->user->apellidoP ?? null,
                'email' => $walletAccount->user->correo,
                'phone_number' => $walletAccount->phone_number ?? null,
                'account_number' => $walletAccount->account_number,
                'current_balance' => $walletAccount->current_balance,
                'is_active' => $walletAccount->is_active,
                'roles' => $walletAccount->wallet_account_roles->pluck('name'),
                'created_at' => $walletAccount->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $walletAccount->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json([
                'message' => $walletAccountIsSuperAdmin ? "Success, wallet account history" : "Success, member account history",
                'wallet_account' => $formattedWallerAccount,
                'account_transactions' => $formattedAccountTransactions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Error, can not get history of wallet account",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Get all Wallet accounts withouth asociated user by Christoper Patiño
    *
    */
    public function indexWalletAccountWithoutUser()
    {
        try {
            $walletAccounts = WalletAccount::whereNull('user_id')->get();
            return response()->json([
                'message' => "Success, wallet accounts without user",
                'data' => $walletAccounts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Error, can not get wallet accounts without user",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create a new wallet account by Christoper Patiño
    *
    */
    public function storeWalletAccount(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'user_name' => 'nullable|max:255',
                'last_name' => 'nullable|max:255',
                'wallet_account_role_id' => 'required|numeric',
                'user_role' => 'nullable|max:255',
                'email' => 'required|email|max:255',
                'phone_number' => 'nullable|max:255',
            ]);

            /* 
            * validacion de datos 
            */
            $walletCurrency = WalletCurrency::where('name', 'halcones_wallet')->first();
            if (!$walletCurrency) {
                return response()->json([
                    'message' => "Error, wallet currency not found"
                ], 404);
            }

            $walletAccountRoles = WalletAccountRole::where('id', $request->wallet_account_role_id)->first();
            if (!$walletAccountRoles) {
                return response()->json([
                    'message' => "Error, wallet account role not found"
                ], 404);
            }

            /* 
            * comprobamos si el usuario ya existe en la tabla users
            */
            $user = User::where('correo', $request->email)->first();
            if ($user) {
                /*
                * Si el usuario ya existe, verificamos si ya tiene una cuenta wallet 
                */
                $existingWalletAccount = WalletAccount::where('user_id', $user->id)->first();
                if ($existingWalletAccount) {
                    return response()->json([
                        'message' => "Error, user already has a wallet account"
                    ], 400);
                }
            } else {
                /*
                * Si el usuario no existe, creamos un nuevo usuario
                */
                if(!$request->user_name || !$request->last_name || !$request->user_role){
                    return response()->json([
                        'message' => "Error, missing user data"
                    ], 400);
                }
                $user = $this->storeUserIfNotExist($request->email, $request->user_name, $request->last_name, $request->user_role);
            }

            /* 
            * Creacion de una nueva instancia de wallet account
            */
            $walletAccount = new WalletAccount();
            $walletAccount->user_id = $user->id;
            $walletAccount->wallet_currency_id = $walletCurrency->id;
            $walletAccount->phone_number = $request->phone_number ?? null;
            $walletAccount->current_balance = 0.0000;
            /* 
            * Generamos un numero de cuenta unico para la nueva cuenta wallet
            */
            $walletAccount->account_number = $this->generateUniqueAccountNumber();
            $walletAccount->is_active = true;
            $walletAccount->save();

            /* 
            * Asignamos el rol a la cuenta wallet
            */
            $walletAccount->wallet_account_roles()->attach($walletAccountRoles->id);

            /* 
            * Revizamos si el usuario fue creado en esta peticion y enviamos el password temporal
            */
            $isNewUser = $user->wasRecentlyCreated;
            // dispatch(new SendWelcomeEmail($user->correo, $user->nombre, $walletAccount->account_number, $isNewUser ? $user->temporal_password : null));

            DB::commit();
            return response()->json([
                'message' => "Success, wallet account created",
                'data' => $walletAccount,
                'temporal_password' => $isNewUser ? $user->temporal_password : ''
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error, can not create wallet account",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }

    /* 
    * 
    * Create account without asociating a user by Christoper Patiño
    *
    */
    public function storeWalletAccountWithoutUser()
    {
        try {
            DB::beginTransaction();

            /* 
            * Validacion de datos
            */
            $walletCurrency = WalletCurrency::where('name', 'halcones_wallet')->first();
            if (!$walletCurrency) {
                return response()->json([
                    'message' => "Error, wallet currency not found"
                ], 404);
            }

            /* 
            * creamos una nueva instancia de wallet account
            */
            $walletAccount = new WalletAccount();
            $walletAccount->wallet_currency_id = $walletCurrency->id;
            $walletAccount->current_balance = 0.0000;
            /* 
            * Generamos un numero de cuenta unico para la nueva cuenta wallet
            */
            $walletAccount->account_number = $this->generateUniqueAccountNumber();
            $walletAccount->is_active = true;
            $walletAccount->save();

            /* 
            * Asignamos el rol 'member' a la cuenta wallet generica
            */
            $walletAccountRole = WalletAccountRole::where('name', 'member')->first();
            $walletAccount->wallet_account_roles()->attach($walletAccountRole->id);

            DB::commit();

            return response()->json([
                'message' => "Success, wallet account created",
                'data' => $walletAccount
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error, can not create wallet account",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }

    /* 
    *
    * Create user if not exist by Christoper Patiño
    *
    */
    public function storeUserIfNotExist($email, $name, $lastName = null, $role)
    {
        /* 
        * NOTA: cambiamos la collation de la tabla users a utf8mb4_spanish2_ci (ya que la collation por defecto de la db es utf8mb4_spanish2_ci)
        * ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci;
        */

        /* 
        * Creamos un password generico para el nuevo usuario
        */
        $temporalPassword = 'HW-' . str_pad(rand(0, 9999), 7, '0', STR_PAD_LEFT);
        $user = new User();
        $user->nombre = $name;
        $user->correo = $email;
        $user->apellidoP = $lastName ?? null;
        $user->id_rol = $role;
        $user->password = Hash::make($temporalPassword);

        $user->save();

        /* 
        * Guardamos el password temporal sin hash en una propiedad separada
        */
        $user->temporal_password = $temporalPassword;
        return $user;
    }

    /* 
    *
    * Generate unique account number by Christoper Patiño
    *
    */
    public function generateUniqueAccountNumber()
    {
        do {
            $accountNumber = 'HW-' . str_pad(rand(0, pow(10, 12)-1), 12, '0', STR_PAD_LEFT);
        } while (WalletAccount::where('account_number', $accountNumber)->exists());
    
        return $accountNumber;
    }

    /* 
    *
    * Associate a wallet account to a user by Christoper Patiño
    *
    */
    public function associateWalletAccountToUser(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'name' => 'nullable|max:255',
                'last_name' => 'nullable|max:255',
                'email' => 'required|email|max:255',
                'phone_number' => 'nullable|max:255',
                'account_number' => 'required|max:255'
            ]);

            /* 
            * validacion de datos
            * Comprobamos que la cuenta wallet exista y no este asociada a un usuario
            */
            $walletAccount = WalletAccount::where('account_number', $request->account_number)->whereNull('user_id')->first();
            if(!$walletAccount){
                return response()->json([
                    'message' => "Error, wallet account not found or already associated to a user"
                ], 404);
            }

            $user = User::where('correo', $request->email)->first();
            if ($user) {
                /* 
                * Verificamos que el usuario no tenga una cuenta wallet asociada
                */
                $existingWalletAccount = WalletAccount::where('user_id', $user->id)->first();
                if ($existingWalletAccount) {
                    return response()->json([
                        'message' => "Error, user already has a wallet account"
                    ], 400);
                }
            } else {
                /* 
                * Si el usuario no existe, creamos un nuevo usuario
                */
                if(!$request->name || !$request->last_name ){
                    return response()->json([
                        'message' => "Error, missing user data"
                    ], 400);
                }
                $user = $this->storeUserIfNotExist($request->email, $request->name, $request->last_name, 'usuario');
            }

            /* 
            * Asociamos la cuenta wallet al usuario
            */
            $walletAccount->user_id = $user->id;
            $walletAccount->phone_number = $request->phone_number ?? null;
            $walletAccount->save();

            /* 
            * comprobamos si el usuario fue creado en esta peticion y enviamos el password temporal
            */
            $isNewUser = $user->wasRecentlyCreated;

            /* 
            * Enviamos un correo de bienvenida al usuario
            */
            // dispatch(new SendWelcomeEmail($user->correo, $user->nombre, $walletAccount->account_number, $isNewUser ? $user->temporal_password : null));

            DB::commit();

            return response()->json([
                'message' => "Success, wallet account associated to user",
                'data' => $walletAccount,
                'temporal_password' => $isNewUser ? $user->temporal_password : ''
            ], 201);
             
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Error, can not associate wallet account to user",
                "error_data" => $e->getMessage()
            ], 500);
        }
    }

    /* 
    * 
    * Format member account transactions by Christoper Patiño
    *
    */
    public function formatMemberAccountTransactions($sordAllTransaction, $walletAccount)
    {
        try {
            return $sordAllTransaction->map(function ($transaction) use ($walletAccount){

                if(!$transaction->seller_wallet_account_id){
                    /* 
                    * Datos del vendedor generico (algunas transacciones no tienen un vendedor asociado)
                    */
                    $sellerName = 'Seller Halcones Wallet';
                    $sellerAccountNumber = 'HW-000000000000';
                }else {
                    $sellerAccount = WalletAccount::where('id', $transaction->seller_wallet_account_id)->first();
                    $sellerName = $sellerAccount->user->nombre;
                    $sellerAccountNumber = $sellerAccount->account_number;
                    $sellerPhone = $sellerAccount->phone_number;
                }

                /* 
                * Formateamos los datos de la transaccion
                */
                //$transaction->wallet_transaction_type->name
                $paymentDetail = $transaction->global_payment_type == null 
                ? 'no aplica' 
                : ($transaction->global_payment_type->name == 'halcones_wallet' 
                    ? 'cartera halcones' 
                    : ($transaction->global_payment_type->name == 'efectivo' 
                        ? 'pago en efectivo' 
                        : $transaction->global_card_cash_payment->global_type_card_payment->name));
                    
                $formattedTransaction = [
                    'transaction_id' => $transaction->id,
                    'transaction_type' => $transaction->wallet_transaction_type->name,
                    'transaction_description' => $transaction->description,
                    'payment_type' => $transaction->global_payment_type->name ?? 'no aplica',
                    'payment_detail' => $paymentDetail,
                    'transaction_status' => $transaction->wallet_transaction_status->name,
                    'seller_info' => [
                        'seller_name' => $sellerName,
                        'seller_account_number' => $sellerAccountNumber,
                        'seller_phone' => $sellerPhone ?? null
                    ],
                    'balance_account_before_transaction' => $transaction->balance_account_before_transaction,
                    'balance_account_after_transaction' => $transaction->balance_account_after_transaction,
                ];

                if($transaction->wallet_transaction_type->name == 'recarga') {
                    /* 
                    * Validamos que role complio el usuario en esta transacion
                    */
                    $isSeller = $walletAccount->id == $transaction->seller_wallet_account_id;
                    if($isSeller){
                        $formattedTransaction['account_role_of_this_transaction'] = 'seller';
                        unset($formattedTransaction['seller_info']);
                        unset($formattedTransaction['balance_account_before_transaction']);
                        unset($formattedTransaction['balance_account_after_transaction']);
                        $formattedTransaction['buyer_info'] = [
                            'buyer_name' => $walletAccount->user->nombre,
                            'buyer_account_number' => $walletAccount->account_number,
                            'buyer_phone' => $walletAccount->phone_number ?? null,
                            'balance_account_before_transaction' => $transaction->balance_account_before_transaction,
                            'balance_account_after_transaction' => $transaction->balance_account_after_transaction
                        ];

                    } else {
                        $formattedTransaction['account_role_of_this_transaction'] = 'buyer';
                    }

                    $formattedTransaction['recharge_amount'] = $transaction->amount;

                }else if($transaction->wallet_transaction_type->name == 'compra') {

                    $transaction = $this->handleSaleTransactionFormat($transaction);
                    $formattedTransaction['total_amount'] = $transaction->pos_sale->total_amount;
                    $formattedTransaction['products'] = $transaction->pos_sale->warehouse_product_inventories->map(function ($inventory) {
                        if ($inventory->warehouse_product_catalog) {
                            return [
                                'product_name' => $inventory->warehouse_product_catalog->name,
                                'product_price' => $inventory->price,
                                'product_quantity' => $inventory->pivot->quantity
                            ];
                        }
                    });
                    
                } else if($transaction->wallet_transaction_type->name == 'transferencia') {
                   $transfer = $transaction->wallet_transaction_transfer;
                    $formattedTransaction['transfer_amount'] = $transfer->amount;
                }

                $formattedTransaction['created_at'] = $transaction->created_at->format('Y-m-d H:i:s');
                $formattedTransaction['updated_at'] = $transaction->updated_at->format('Y-m-d H:i:s');

                return $formattedTransaction;
                    
            })->values(); 
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function handleSaleTransactionFormat($transaction)
    {
        try {
             /* 
             * Cargamos la compra asociada a la transaccion de venta con los productos
             */
            $transaction->load(['pos_sale.warehouse_product_inventories' => function ($query) {
                $query->with('warehouse_product_catalog');
            }]);

            return $transaction;
           
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    } 
    
}
