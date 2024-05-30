<?php

namespace App\Models\PointOfSale;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransactionAcknowledgment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_manager_id',
        'warehouse_supplier_id',
        'global_payment_type_id',
        'global_type_card_payment_id',
        'global_card_cash_payment_id',
        'inventory_transaction_type_id',
        'pos_product_warehouse_id', 
        'acknowledgment_key',
        'is_active',
        'reason'
    ];

    public function user_manager()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse_supplier()
    {
        return $this->belongsTo(WarehouseSupplier::class);
    }

    public function global_payment_type()
    {
        return $this->belongsTo(GlobalPaymentType::class);
    }

    public function global_type_card_payment()
    {
        return $this->belongsTo(GlobalTypeCardPayment::class);
    }

    public function global_card_cash_payment()
    {
        return $this->belongsTo(GlobalCardCashPayment::class);
    }

    public function inventory_transaction_type()
    {
        return $this->belongsTo(InventoryTransactionType::class);
    }

    public function global_inventory_transactions()
    {
        return $this->hasMany(GlobalInventoryTransaction::class);
    }

    public function pos_product_warehouse()
    {
        return $this->belongsTo(PosProductWarehouse::class);
    }
 
}
