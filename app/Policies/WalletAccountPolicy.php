<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet\WalletAccount;
use Illuminate\Auth\Access\HandlesAuthorization;

class WalletAccountPolicy
{
    use HandlesAuthorization;

     /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /* 
    * Politica de autorizacion para verificar que la cuenta tenga un role de 'vendedor' by Christoper Patiño
    * Acceso a tranciones "compra", "recarga"
    */
    public function handleSellerTransaction(User $user = null, WalletAccount $walletAccount, $transactionType)
    {
        $walletAccountRoles = $walletAccount->wallet_account_roles;
        foreach ($walletAccountRoles as $walletAccountRole) {
            if ($walletAccountRole->name == 'seller') {
                return true;
            }
        }

        return false;
    }

    /* 
    * Politica de autorizacion para verificar que la cuenta tenga un role de 'super_admin' by Christoper Patiño
    */
    public function handleSuperAdminTransaction(User $user = null, WalletAccount $walletAccount)
    {
        $walletAccountRoles = $walletAccount->wallet_account_roles;
        foreach ($walletAccountRoles as $walletAccountRole) {
            if ($walletAccountRole->name == 'super_admin') {
                return true;
            }
        }

        return false;
    }
}
