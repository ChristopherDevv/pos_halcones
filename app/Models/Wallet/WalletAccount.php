<?php

namespace App\Models\Wallet;

use App\Models\PointOfSale\PosSale;
use App\Models\PointOfSale\PosSalesReceivable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_currency_id',
        'phone_number',
        'current_balance',
        'account_number',
        'is_active',
    ];

    public function wallet_currency()
    {
        return $this->belongsTo(WalletCurrency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet_account_roles()
    {
        return $this->belongsToMany(WalletAccountRole::class, 'account_role_wallet_account', 'wallet_account_id', 'wallet_account_role_id')->withTimestamps();
    }

    public function wallet_transactions_origin()
    {
        return $this->hasMany(WalletTransaction::class, 'origin_wallet_account_id');
    }

    public function wallet_transactions_destination()
    {
        return $this->hasMany(WalletTransaction::class, 'destination_wallet_account_id');
    }

    public function wallet_transactions_seller()
    {
        return $this->hasMany(WalletTransaction::class, 'seller_wallet_account_id');
    }

    public function wallet_transaction_transfers_sender()
    {
        return $this->hasMany(WalletTransactionTransfer::class, 'sender_wallet_account_id');
    }

    public function wallet_transaction_transfers_receiver()
    {
        return $this->hasMany(WalletTransactionTransfer::class, 'receiver_wallet_account_id');
    }

    public function super_admin_wallet_transactions()
    {
        return $this->hasMany(SuperAdminWalletTransaction::class, 'super_admin_wallet_account_id');
    }

    public function pos_sales()
    {
        return $this->hasMany(PosSale::class);
    }

    public function pos_sales_receivables()
    {
        return $this->hasMany(PosSalesReceivable::class);
    }
    
}
