<?php

namespace App\Models\Wallet;

use App\Models\PointOfSale\PosProductWarehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperAdminWalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'super_admin_wallet_account_id',
        'pos_product_warehouse_id',
        'wallet_transaction_id',
        'description',
        'amount',
        'balance_account_before_transaction',
        'balance_account_after_transaction',
    ];

    public function super_admin_wallet_account()
    {
        return $this->belongsTo(WalletAccount::class, 'super_admin_wallet_account_id');
    }

    public function pos_product_warehouse()
    {
        return $this->belongsTo(PosProductWarehouse::class);
    }

    public function wallet_transaction()
    {
        return $this->belongsTo(WalletTransaction::class);
    }
}
