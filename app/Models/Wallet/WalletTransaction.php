<?php

namespace App\Models\Wallet;

use App\Models\PointOfSale\GlobalCardCashPayment;
use App\Models\PointOfSale\GlobalPaymentType;
use App\Models\PointOfSale\PosProductCancelation;
use App\Models\PointOfSale\PosSale;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'origin_wallet_account_id',
        'destination_wallet_account_id',
        'wallet_transaction_type_id',
        'wallet_transaction_status_id',
        'global_payment_type_id',
        'global_card_cash_payment_id',
        'pos_sale_id',
        'seller_wallet_account_id',
        'amount',
        'description',
        'balance_account_before_transaction',
        'balance_account_after_transaction',
    ];
    
    public function origin_wallet_account()
    {
        return $this->belongsTo(WalletAccount::class, 'origin_wallet_account_id');
    }

    public function destination_wallet_account()
    {
        return $this->belongsTo(WalletAccount::class, 'destination_wallet_account_id');
    }

    public function wallet_transaction_type()
    {
        return $this->belongsTo(WalletTransactionType::class);
    }

    public function wallet_transaction_status()
    {
        return $this->belongsTo(WalletTransactionStatus::class);
    }

    public function global_payment_type()
    {
        return $this->belongsTo(GlobalPaymentType::class);
    }

    public function global_card_cash_payment()
    {
        return $this->belongsTo(GlobalCardCashPayment::class);
    }

    public function pos_sale()
    {
        return $this->belongsTo(PosSale::class);
    }

    public function pos_product_cancelations()
    {
       return $this->hasMany(PosProductCancelation::class);
    }

    public function seller_wallet_account()
    {
        return $this->belongsTo(WalletAccount::class, 'seller_wallet_account_id');
    }

    public function wallet_transaction_transfer()
    {
        return $this->hasOne(WalletTransactionTransfer::class);
    }

    public function super_admin_wallet_transaction()
    {
        return $this->hasOne(SuperAdminWalletTransaction::class);
    }

    
}
