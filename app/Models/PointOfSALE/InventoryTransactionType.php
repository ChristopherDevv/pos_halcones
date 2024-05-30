<?php

namespace App\Models\PointOfSale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransactionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    public function global_inventory_transactions()
    {
        return $this->hasMany(GlobalInventoryTransaction::class);
    }

    public function warehouse_transaction_acknowledgments()
    {
        return $this->hasMany(WarehouseTransactionAcknowledgment::class);
    }
}
