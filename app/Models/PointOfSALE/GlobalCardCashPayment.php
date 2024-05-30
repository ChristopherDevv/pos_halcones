<?php

namespace App\Models\PointOfSale;

use App\Models\Wallet\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalCardCashPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'global_type_card_payment_id',
        'amount_received',
        'amount_change_given'
    ];

    public function pos_ticket()
    {
        return $this->hasOne(PosTicket::class);
    }

    public function global_type_card_payment()
    {
        return $this->belongsTo(GlobalTypeCardPayment::class);
    }

    public function wallet_transaction()
    {
        return $this->hasOne(WalletTransaction::class);
    }

    public function warehouse_transaction_acknowledgment()
    {
        return $this->hasOne(WarehouseTransactionAcknowledgment::class);
    }

}
