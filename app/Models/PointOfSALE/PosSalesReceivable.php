<?php

namespace App\Models\PointOfSale;

use App\Models\Wallet\WalletAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosSalesReceivable extends Model
{
    use HasFactory;

    protected $table = 'pos_sales_receivables';
    protected $fillable = [
        'wallet_account_id',
        'debtor_name',
        'debtor_last_name',
        'debtor_phone',
        'amount_paid',
        'is_paid',
        'is_canceled',
    ];

    public function wallet_account()
    {
        return $this->belongsTo(WalletAccount::class);
    }

    public function pos_sale()
    {
        return $this->hasOne(PosSale::class);
    }
}
