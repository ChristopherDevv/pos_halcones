<?php

namespace App\Models\PointOfSale;

use App\Models\Wallet\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalPaymentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    public function wallet_transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function pos_tickets()
    {
        return $this->hasMany(PosTicket::class);
    }

    public function warehouse_transaction_acknowledgments()
    {
        return $this->hasMany(WarehouseTransactionAcknowledgment::class);
    }
}
