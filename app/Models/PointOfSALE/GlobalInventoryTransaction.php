<?php

namespace App\Models\PointOfSale;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalInventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'global_inventory_id',
        'inventory_transaction_type_id',
        'warehouse_transaction_acknowledgment_id',
        'previous_stock',
        'stock_movement',
        'new_stock',
        'previous_sale_price',
        'new_sale_price',
        'previous_discount_price',
        'new_discount_price',
        'reason'
    ];

    public function global_inventory()
    {
        return $this->belongsTo(GlobalInventory::class);
    }

    public function inventory_transaction_type()
    {
        return $this->belongsTo(InventoryTransactionType::class);
    }

    public function warehouse_supplier()
    {
        return $this->belongsTo(WarehouseSupplier::class);
    }

    public function warehouse_transaction_acknowledgment()
    {
        return $this->belongsTo(WarehouseTransactionAcknowledgment::class);
    }
    
}
