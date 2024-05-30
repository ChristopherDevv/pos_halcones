<?php

namespace App\Models\PointOfSale;

use App\Models\User;
use App\Models\Wallet\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosProductCancelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_cashier_id',
        'pos_ticket_id',
        'warehouse_product_inventory_id',
        'wallet_transaction_id',
        'pos_cash_register_movement_id',
        'quantity',
        'total_amount',
        'reason',
    ];

    public function user_cashier()
    {
        return $this->belongsTo(User::class, 'user_cashier_id');
    }

    public function pos_ticket()
    {
        return $this->belongsTo(PosTicket::class);
    }

    public function warehouse_product_inventory()
    {
        return $this->belongsTo(WarehouseProductInventory::class);
    }

    public function wallet_transaction()
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    public function pos_cash_register_movement()
    {
        return $this->belongsTo(PosCashRegisterMovement::class);
    }

}
